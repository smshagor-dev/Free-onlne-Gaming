# FreeGames mobile

Flutter Material 3 app for Android and iOS using the Laravel `/api/v1` API. Navy/blue design, responsive game cards, shared navigation and native forms. Platform projects and dependency lockfile are included.

## Included flows

- Home: search, categories and infinite scrolling in batches of 30 games.
- Explore: GameMonetize, FreeToGame, GamerPower and local website catalogs.
- Game detail pages, isolated HTML5 WebView player and external HTTPS provider links.
- Favorites, browser play history, account dashboard and notifications.
- Registration, email verification/resend, login with authenticator OTP, password recovery/reset.
- Secure session restoration/renewal, device revocation and sign-out from all devices.
- Profile and password editing, gaming preferences, authenticator QR setup/disable and confirmed account closure.
- Lottery draws, owned tickets/details and results, using the existing read-only API.

No casino, deposits, withdrawals or paid game access. Download/giveaway catalogs and local games open provider/website pages; website login remains separate. WebViews receive no bearer token, JavaScript bridge or application cookies. Main-frame game navigation is restricted to the GameMonetize HTTPS host. Historical lottery/website records remain retained after account closure.

## Run

Flutter >=3.35 / Dart >=3.6; validated with Flutter 3.47.7 / Dart 3.13.5.

```bash
flutter pub get
flutter analyze
flutter test
flutter run --dart-define=API_BASE_URL=https://YOUR_DOMAIN/api/v1
```

Android emulator default: `http://10.0.2.2:8000/api/v1`; run Laravel on `0.0.0.0:8000`. A physical phone needs a reachable API URL. Android cleartext is enabled only in debug builds. Release API calls require HTTPS. SMTP must work for verification/reset emails.

## Build and signing

```bash
flutter build apk --debug --dart-define=API_BASE_URL=https://YOUR_DOMAIN/api/v1
flutter build appbundle --release --dart-define=API_BASE_URL=https://YOUR_DOMAIN/api/v1
```

Release builds require an owner-controlled keystore and ignored `android/key.properties`:

```properties
storeFile=/absolute/path/to/upload-keystore.jks
storePassword=YOUR_STORE_PASSWORD
keyAlias=upload
keyPassword=YOUR_KEY_PASSWORD
```

Release configuration intentionally fails without these keys. Never commit signing credentials. Package ID: `com.smshagor.freegames_mobile`; display name: FreeGames. iOS Keychain entitlements are included; compile/sign iOS on macOS with Xcode and your Apple team.

## Verification

Current source: Flutter analyzer clean; 15 unit/widget tests passed. Coverage includes provider identity, public/private bearer handling, invalid/expired sessions, late unauthorized responses after a newer login, server validation messages, server-path isolation, navigation at 320/390/768 widths, registration, failed preferences loading and temporary authenticator secrets. Laravel: 70 tests passed, 901 assertions. Android debug APK compilation passed. The development APK targets the Android emulator/local API; these checks are not real-device certification.

Before publishing, set the live HTTPS API URL and release signing; validate actual mail delivery, games, provider ads/audio/orientation, keyboard and sessions on real Android/iOS devices. Universal/deep links for email recovery are not configured; the app accepts a pasted email reset link/token or browser completion.

Fixture-based home preview:

```bash
PREVIEW_FLUTTER_ROOT=/path/to/flutter flutter test --dart-define=SAVE_APP_PREVIEW=true test/app_test.dart
```

This preview contains explicitly mocked data, not a live account/catalog screenshot. Launcher icons are generated from `assets/app-icon.svg`; optional regeneration requires Playwright and Chromium: `node tool/app_icons.cjs` (set `CHROMIUM_PATH` if needed).
