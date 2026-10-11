import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  final String message;
  final int status;
  ApiException(this.message, this.status);
  @override
  String toString() => message;
}

class ApiClient {
  final String baseUrl;
  final http.Client client;
  final FlutterSecureStorage storage;
  String? token;
  DateTime? expiresAt;
  VoidCallback? onUnauthorized;
  ApiClient({
    required this.baseUrl,
    http.Client? client,
    this.storage = const FlutterSecureStorage(),
  }) : client = client ?? http.Client();
  Future<void> saveToken(String? value, {String? expires}) async {
    token = value;
    expiresAt = DateTime.tryParse(expires ?? '');
    if (value == null) {
      await storage.delete(key: 'access_token');
      await storage.delete(key: 'token_expires_at');
    } else {
      await storage.write(key: 'access_token', value: value);
      if (expires != null) {
        await storage.write(key: 'token_expires_at', value: expires);
      }
    }
  }

  Future<void> restore() async {
    token = await storage.read(key: 'access_token');
    expiresAt = DateTime.tryParse(
      await storage.read(key: 'token_expires_at') ?? '',
    );
    if (expiresAt != null && !expiresAt!.isAfter(DateTime.now())) {
      await saveToken(null);
    }
  }

  Future<Map<String, dynamic>> request(
    String path, {
    String method = 'GET',
    bool authenticated = false,
    Map<String, dynamic>? body,
    Map<String, String>? query,
  }) async {
    final root = Uri.parse(baseUrl.endsWith('/') ? baseUrl : '$baseUrl/');
    final uri = root.resolve(path).replace(queryParameters: query);
    if (uri.host != root.host ||
        uri.port != root.port ||
        uri.scheme != root.scheme ||
        !uri.path.startsWith(root.path)) {
      throw ApiException('Invalid server path.', 0);
    }
    if (!kDebugMode && uri.scheme != 'https') {
      throw ApiException('A secure HTTPS server is required.', 0);
    }
    final req = http.Request(method, uri)
      ..headers.addAll({
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      });
    if (authenticated && token != null) {
      req.headers['Authorization'] = 'Bearer $token';
    }
    if (body != null) req.body = jsonEncode(body);
    try {
      final res = await http.Response.fromStream(
        await client.send(req).timeout(const Duration(seconds: 20)),
      ).timeout(const Duration(seconds: 20));
      if (res.statusCode == 401 && authenticated) {
        await saveToken(null);
        onUnauthorized?.call();
      }
      final decoded = jsonDecode(res.body);
      if (decoded is! Map<String, dynamic>) {
        throw ApiException('Unexpected server response.', res.statusCode);
      }
      if (res.statusCode >= 400) {
        final errors = decoded['errors'];
        final field =
            errors is Map && errors.isNotEmpty ? errors.values.first : null;
        throw ApiException(
          field is List && field.isNotEmpty
              ? '${field.first}'
              : '${decoded['message'] ?? 'Request failed. Try again.'}',
          res.statusCode,
        );
      }
      return decoded;
    } on ApiException {
      rethrow;
    } catch (_) {
      throw ApiException('Cannot connect. Check your connection and retry.', 0);
    }
  }

  void dispose() => client.close();
}

class Game {
  final String id, provider, title, category;
  final String? image, playUrl, websiteUrl;
  final bool playable;
  final String description;
  Game.fromJson(Map<String, dynamic> json)
      : id = '${json['provider_id'] ?? json['id']}',
        provider = '${json['provider'] ?? 'gamemonetize'}',
        title = '${json['title'] ?? json['name'] ?? 'Game'}',
        category =
            '${json['category'] ?? json['genre'] ?? ((json['genres'] is List && (json['genres'] as List).isNotEmpty) ? (json['genres'] as List).first : 'Game')}',
        image =
            (json['thumbnail'] ?? json['image'] ?? json['photo']) as String?,
        playUrl = json['play_url'] as String?,
        websiteUrl = (json['website'] ??
            json['deal_url'] ??
            json['game_url'] ??
            json['url'] ??
            json['open_giveaway_url'] ??
            json['play_url']) as String?,
        playable = json['playable'] == true,
        description =
            '${json['description'] ?? json['short_description'] ?? ''}';
  String get key => '$provider:$id';
}

class AppState extends ChangeNotifier {
  final ApiClient api;
  Map<String, dynamic>? user;
  final favorites = <String, Game>{};
  bool ready = false;
  String? error;
  AppState(this.api) {
    api.onUnauthorized = clearSession;
  }
  bool get signedIn => user != null && api.token != null;
  void clearSession() {
    user = null;
    favorites.clear();
    notifyListeners();
  }

  Future<void> initialize() async {
    try {
      await api.restore();
      if (api.token != null) {
        await reloadUser();
        if (api.expiresAt != null &&
            api.expiresAt!.difference(DateTime.now()).inDays < 2) {
          await refreshSession();
        }
        await loadFavorites();
      }
    } catch (e) {
      error = '$e';
    }
    ready = true;
    notifyListeners();
  }

  Future<void> reloadUser() async {
    user = (await api.request('me', authenticated: true))['data']
        as Map<String, dynamic>;
    notifyListeners();
  }

  Future<void> login(Map<String, dynamic> credentials) async {
    final res = await api.request(
      'auth/login',
      method: 'POST',
      body: {...credentials, 'device_name': defaultTargetPlatform.name},
    );
    await acceptSession(res);
    try {
      await loadFavorites();
    } catch (e) {
      error = '$e';
      notifyListeners();
    }
  }

  Future<void> acceptSession(Map<String, dynamic> res) async {
    final data = res['data'] as Map<String, dynamic>;
    await api.saveToken(
      data['access_token'] as String,
      expires: data['expires_at'] as String?,
    );
    user = data['user'] as Map<String, dynamic>;
    error = null;
    notifyListeners();
  }

  Future<void> refreshSession() async => acceptSession(
        await api.request('auth/refresh', method: 'POST', authenticated: true),
      );
  Future<void> loadFavorites() async {
    final next = <String, Game>{};
    var page = 1;
    while (true) {
      final res = await api.request(
        'me/favorites',
        authenticated: true,
        query: {'page': '$page'},
      );
      for (final row in res['data'] as List) {
        if (row['snapshot'] is Map) {
          final g = Game.fromJson(
            Map<String, dynamic>.from(row['snapshot'] as Map),
          );
          next[g.key] = g;
        }
      }
      if ((res['meta'] as Map)['has_more'] != true) break;
      page++;
    }
    if (!signedIn) return;
    favorites
      ..clear()
      ..addAll(next);
    notifyListeners();
  }

  Future<void> toggleFavorite(Game g) async {
    if (!signedIn) throw ApiException('Sign in to save games.', 401);
    final saved = favorites.containsKey(g.key);
    await api.request(
      'me/games/${Uri.encodeComponent(g.provider)}/${Uri.encodeComponent(g.id)}/favorite',
      method: saved ? 'DELETE' : 'PUT',
      authenticated: true,
    );
    if (!signedIn) return;
    if (saved) {
      favorites.remove(g.key);
    } else {
      favorites[g.key] = g;
    }
    notifyListeners();
  }

  Future<void> signOut({bool all = false, bool localOnly = false}) async {
    try {
      if (!localOnly) {
        await api.request(
          all ? 'auth/logout-all' : 'auth/logout',
          method: 'POST',
          authenticated: true,
        );
      }
    } finally {
      await api.saveToken(null);
      clearSession();
    }
  }

  @override
  void dispose() {
    api.dispose();
    super.dispose();
  }
}
