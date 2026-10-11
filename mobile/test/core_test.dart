import 'dart:async';
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:freegames_mobile/core.dart';

void main() {
  setUp(() => FlutterSecureStorage.setMockInitialValues({}));
  test('provider-qualified identity avoids cross-provider collisions', () {
    expect(Game.fromJson({'id': '1', 'provider': 'local'}).key, 'local:1');
    expect(
      Game.fromJson({'id': '1', 'provider': 'freetogame', 'provider_id': '72'})
          .key,
      'freetogame:72',
    );
  });
  test('catalog website and genres are restored', () {
    final g = Game.fromJson({
      'id': 'a',
      'genres': ['Puzzle'],
      'website': 'https://example.com',
      'image': 'https://example.com/game.png',
    });
    expect(g.websiteUrl, 'https://example.com');
    expect(g.category, 'Puzzle');
    expect(g.playable, false);
  });
  test('public requests never receive the bearer token', () async {
    final api = ApiClient(
      baseUrl: 'https://site.test/api/v1',
      client: MockClient((r) async {
        expect(r.headers.containsKey('Authorization'), false);
        return http.Response('{"data":[]}', 200);
      }),
    )..token = 'secret';
    await api.request('games');
    api.dispose();
  });
  test('authenticated requests include the token', () async {
    final api = ApiClient(
      baseUrl: 'https://site.test/api/v1',
      client: MockClient((r) async {
        expect(r.headers['Authorization'], 'Bearer secret');
        return http.Response('{"data":{}}', 200);
      }),
    )..token = 'secret';
    await api.request('me', authenticated: true);
    api.dispose();
  });
  test('401 clears secure storage even when response is not JSON', () async {
    FlutterSecureStorage.setMockInitialValues({'access_token': 'old'});
    final api = ApiClient(
      baseUrl: 'https://site.test/api/v1',
      client: MockClient((_) async => http.Response('Unauthorized', 401)),
    );
    var cleared = false;
    api.onUnauthorized = () => cleared = true;
    await api.restore();
    await expectLater(
      api.request('me', authenticated: true),
      throwsA(isA<ApiException>()),
    );
    expect(api.token, isNull);
    expect(cleared, true);
    expect(await api.storage.read(key: 'access_token'), isNull);
    api.dispose();
  });
  test('expired stored token is discarded', () async {
    FlutterSecureStorage.setMockInitialValues({
      'access_token': 'old',
      'token_expires_at': '2020-01-01T00:00:00Z',
    });
    final api = ApiClient(baseUrl: 'https://site.test/api/v1');
    await api.restore();
    expect(api.token, isNull);
    api.dispose();
  });
  test('server field validation message reaches the user', () async {
    final api = ApiClient(
      baseUrl: 'https://site.test/api/v1',
      client: MockClient(
        (_) async => http.Response(
          jsonEncode({
            'errors': {
              'email': ['Invalid email'],
            },
          }),
          422,
        ),
      ),
    );
    await expectLater(
      api.request('auth/login'),
      throwsA(
        isA<ApiException>().having(
          (e) => e.message,
          'message',
          'Invalid email',
        ),
      ),
    );
    api.dispose();
  });
  test('authenticated requests cannot escape the configured server', () async {
    final api = ApiClient(baseUrl: 'https://site.test/api/v1');
    await expectLater(
      api.request('https://other.test/api/v1/me', authenticated: true),
      throwsA(isA<ApiException>()),
    );
    api.dispose();
  });
  test('a late 401 cannot revoke a newer login', () async {
    final sent = Completer<void>();
    final response = Completer<http.Response>();
    final api = ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((_) async {
          sent.complete();
          return response.future;
        }))
      ..token = 'old';
    var cleared = false;
    api.onUnauthorized = () => cleared = true;
    final pending = api.request('me', authenticated: true);
    final assertion = expectLater(pending, throwsA(isA<ApiException>()));
    await sent.future;
    await api.saveToken('new', expires: '2099-01-01T00:00:00Z');
    response.complete(http.Response('{"message":"Expired"}', 401));
    await assertion;
    expect(api.token, 'new');
    expect(cleared, false);
    api.dispose();
  });
}
