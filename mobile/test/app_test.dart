import 'dart:io';
import 'dart:ui' as ui;
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:freegames_mobile/core.dart';
import 'package:freegames_mobile/main.dart';
import 'package:freegames_mobile/forms.dart';
import 'package:freegames_mobile/account.dart';

void main() {
  setUp(() => FlutterSecureStorage.setMockInitialValues({}));
  for (final width in [320.0, 390.0, 768.0]) {
    testWidgets('guest navigation has no overflow at width $width', (
      tester,
    ) async {
      if (const bool.fromEnvironment('SAVE_APP_PREVIEW')) {
        await tester.runAsync(() async {
          final root = Platform.environment['PREVIEW_FLUTTER_ROOT']!;
          for (final e in {
            'Ahem': 'Roboto-Regular.ttf',
            'Roboto': 'Roboto-Regular.ttf',
            'MaterialIcons': 'MaterialIcons-Regular.otf',
          }.entries) {
            final loader = FontLoader(e.key)
              ..addFont(
                File('$root/bin/cache/artifacts/material_fonts/${e.value}')
                    .readAsBytes()
                    .then((b) => ByteData.sublistView(b)),
              );
            await loader.load();
          }
        });
      }
      tester.view.physicalSize = Size(width, 844);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final state = AppState(
        ApiClient(
          baseUrl: 'https://site.test/api/v1',
          client: MockClient(
            (r) async => http.Response(
              r.url.path.endsWith('categories')
                  ? '{"data":["Puzzle"]}'
                  : '{"data":[{"id":"preview","provider":"gamemonetize","title":"Preview Puzzle","category":"Puzzle","playable":true,"play_url":"https://html5.gamemonetize.co/preview/"}],"meta":{"has_more":false}}',
              200,
            ),
          ),
        ),
      )..ready = true;
      final key = GlobalKey();
      await tester.pumpWidget(
        RepaintBoundary(
          key: key,
          child: FreeGamesApp(state: state),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('A little play. A better day.'), findsOneWidget);
      expect(tester.takeException(), isNull);
      if (const bool.fromEnvironment('SAVE_APP_PREVIEW') && width == 390) {
        await tester.runAsync(() async {
          final img = await (key.currentContext!.findRenderObject()!
                  as RenderRepaintBoundary)
              .toImage(pixelRatio: 2);
          final bytes = await img.toByteData(format: ui.ImageByteFormat.png);
          final file = File('docs/home-preview.png');
          await file.parent.create(recursive: true);
          await file.writeAsBytes(bytes!.buffer.asUint8List());
          img.dispose();
        });
      }
      await tester.tap(find.text('Account').last);
      await tester.pumpAndSettle();
      expect(find.text('Make it your space'), findsOneWidget);
      await tester.tap(find.text('Sign in').first);
      await tester.pumpAndSettle();
      expect(find.text('Create a new account'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox());
      state.dispose();
    });
  }
  testWidgets('registration confirms password then opens verification', (
    tester,
  ) async {
    Map<String, dynamic>? posted;
    final state = AppState(
      ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((r) async {
          expect(r.url.path, '/api/v1/auth/register');
          posted = jsonDecode(r.body) as Map<String, dynamic>;
          return http.Response('{"message":"Created"}', 201);
        }),
      ),
    );
    await tester.pumpWidget(
      MaterialApp(
        home: AccountForm(state: state, action: AccountAction.register),
      ),
    );
    final fields = find.byType(TextFormField);
    for (final e in [
      (0, 'Preview Player'),
      (1, 'preview'),
      (2, 'preview@example.com'),
      (3, 'strong-password'),
      (4, 'strong-password'),
    ]) {
      await tester.enterText(fields.at(e.$1), e.$2);
    }
    final button = find.widgetWithText(FilledButton, 'Create account');
    await tester.ensureVisible(button);
    await tester.drag(find.byType(ListView), const Offset(0, -180));
    await tester.pumpAndSettle();
    await tester.tap(button);
    await tester.pumpAndSettle();
    expect(posted?['password_confirmation'], 'strong-password');
    expect(find.text('Verify your email'), findsWidgets);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
    state.dispose();
  });
  testWidgets('failed preferences load cannot overwrite server data', (
    tester,
  ) async {
    var puts = 0;
    final state = AppState(
      ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((r) async {
          if (r.method == 'PUT') puts++;
          return http.Response('{"message":"Unavailable"}', 503);
        }),
      ),
    )..user = {'id': 1};
    state.api.token = 'test';
    await tester.pumpWidget(
      MaterialApp(
        home: AccountForm(state: state, action: AccountAction.preferences),
      ),
    );
    await tester.pumpAndSettle();
    await tester.scrollUntilVisible(
        find.widgetWithText(FilledButton, 'Save changes'), 200);
    expect(
      tester
          .widget<FilledButton>(
              find.widgetWithText(FilledButton, 'Save changes'))
          .onPressed,
      isNull,
    );
    expect(puts, 0);
    await tester.pumpWidget(const SizedBox());
    state.dispose();
  });
  testWidgets('authenticator setup renders QR without persisting the secret', (
    tester,
  ) async {
    final state = AppState(
      ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((r) async {
          expect(r.url.path, '/api/v1/me/2fa/setup');
          return http.Response(
            '{"data":{"secret":"PREVIEWSECRET","otpauth_url":"otpauth://totp/Preview?secret=PREVIEWSECRET","expires_in":600}}',
            200,
          );
        }),
      ),
    )..user = {'id': 1, 'google2fa_status': false};
    state.api.token = 'test';
    await tester.pumpWidget(MaterialApp(home: SecurityScreen(state: state)));
    await tester.enterText(find.byType(TextField).first, 'password');
    await tester.tap(find.text('Set up authenticator'));
    await tester.pumpAndSettle();
    expect(find.text('PREVIEWSECRET'), findsOneWidget);
    expect(
      (await state.api.storage.readAll()).values.contains('PREVIEWSECRET'),
      false,
    );
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
    state.dispose();
  });
  testWidgets('header notifications show unread count and clear after read-all',
      (tester) async {
    var unread = 2;
    final state = AppState(ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((r) async {
          final path = r.url.path;
          if (r.method == 'PATCH') {
            unread = 0;
            return http.Response('{"message":"Read"}', 200);
          }
          if (path.endsWith('me/dashboard')) {
            return http.Response(
                jsonEncode({
                  'data': {'unread_notifications': unread}
                }),
                200);
          }
          if (path.endsWith('me/notifications')) {
            return http.Response(
                '{"data":[{"id":1,"title":"New games this week","message":"Ready to explore?","is_read":false}],"next_page_url":null}',
                200);
          }
          return http.Response('{"data":[],"meta":{"has_more":false}}', 200);
        })))
      ..ready = true
      ..user = {'id': 7, 'name': 'Demo'};
    state.api.token = 'demo';
    await tester.pumpWidget(FreeGamesApp(state: state));
    await tester.pumpAndSettle();
    expect(tester.widget<Badge>(find.byType(Badge)).isLabelVisible, true);
    expect(find.text('2'), findsOneWidget);
    await tester.tap(find.byTooltip('Notifications'));
    await tester.pumpAndSettle();
    expect(find.text('New games this week'), findsOneWidget);
    await tester.tap(find.byTooltip('Mark all read'));
    await tester.pumpAndSettle();
    await tester.pageBack();
    await tester.pumpAndSettle();
    expect(tester.widget<Badge>(find.byType(Badge)).isLabelVisible, false);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
    state.dispose();
  });
  testWidgets('preferences chips save the selected genres and platforms',
      (tester) async {
    Map<String, dynamic>? posted;
    final state = AppState(ApiClient(
        baseUrl: 'https://site.test/api/v1',
        client: MockClient((r) async {
          if (r.method == 'PUT') {
            posted = jsonDecode(r.body) as Map<String, dynamic>;
            return http.Response('{"message":"Saved"}', 200);
          }
          return http.Response(
              '{"data":{"preferred_genres":["Puzzle"],"preferred_platforms":["Android"]}}',
              200);
        })))
      ..user = {'id': 7};
    state.api.token = 'demo';
    await tester.pumpWidget(MaterialApp(
        home: AccountForm(state: state, action: AccountAction.preferences)));
    await tester.pumpAndSettle();
    await tester.tap(find.widgetWithText(FilterChip, 'Adventure'));
    final save = find.widgetWithText(FilledButton, 'Save changes');
    await tester.scrollUntilVisible(save, 200);
    await tester.pumpAndSettle();
    await tester.tap(save);
    await tester.pumpAndSettle();
    expect(posted?['preferred_genres'], ['Puzzle', 'Adventure']);
    expect(posted?['preferred_platforms'], ['Android']);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
    state.dispose();
  });
}
