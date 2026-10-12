import 'package:flutter/material.dart';

import 'core.dart';
import 'games.dart';
import 'account.dart';
import 'forms.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final state = AppState(
    ApiClient(
      baseUrl: const String.fromEnvironment(
        'API_BASE_URL',
        defaultValue: 'http://10.0.2.2:8000/api/v1',
      ),
    ),
  );
  runApp(FreeGamesApp(state: state));
  await state.initialize();
}

class FreeGamesApp extends StatelessWidget {
  final AppState state;
  const FreeGamesApp({super.key, required this.state});
  @override
  Widget build(BuildContext context) => MaterialApp(
        title: 'FreeGames',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          useMaterial3: true,
          brightness: Brightness.dark,
          scaffoldBackgroundColor: const Color(0xff0b1220),
          colorScheme: const ColorScheme.dark(
            primary: Color(0xff55adff),
            onPrimary: Color(0xff07111d),
            secondary: Color(0xff80c7ff),
            secondaryContainer: Color(0xff203d59),
            onSecondaryContainer: Color(0xffd9edff),
            surface: Color(0xff142237),
          ),
          textTheme: const TextTheme(
              titleLarge: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
              titleMedium: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              bodyLarge: TextStyle(fontSize: 15, height: 1.45),
              bodyMedium: TextStyle(fontSize: 14, height: 1.45)),
          filledButtonTheme: FilledButtonThemeData(
              style: FilledButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)))),
          chipTheme: ChipThemeData(
              side: const BorderSide(color: Color(0xff29415d)),
              selectedColor: const Color(0xff203d59),
              showCheckmark: false,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10))),
          navigationBarTheme: const NavigationBarThemeData(
              indicatorColor: Color(0xff25476b),
              backgroundColor: Color(0xff0e1b2b)),
          appBarTheme: const AppBarTheme(
            backgroundColor: Color(0xff0b1220),
            surfaceTintColor: Colors.transparent,
          ),
          cardTheme: CardThemeData(
            elevation: 0,
            margin: EdgeInsets.zero,
            color: const Color(0xff142237),
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          ),
          inputDecorationTheme: InputDecorationTheme(
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            filled: true,
            fillColor: const Color(0xff16263b),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
          ),
        ),
        home: ListenableBuilder(
          listenable: state,
          builder: (context, _) => state.ready
              ? AppShell(state: state)
              : const Scaffold(
                  body: Center(child: CircularProgressIndicator())),
        ),
      );
}

class AppShell extends StatefulWidget {
  final AppState state;
  const AppShell({super.key, required this.state});
  @override
  State<AppShell> createState() => _AppShellState();
}

class _AppShellState extends State<AppShell> with WidgetsBindingObserver {
  int tab = 0;
  String? notificationSession;
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    syncNotifications();
  }

  @override
  void didUpdateWidget(covariant AppShell oldWidget) {
    super.didUpdateWidget(oldWidget);
    syncNotifications();
  }

  void syncNotifications() {
    final session = widget.state.signedIn ? widget.state.api.token : null;
    if (session == notificationSession) return;
    notificationSession = session;
    if (session != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) widget.state.refreshUnreadNotifications();
      });
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      widget.state.refreshUnreadNotifications();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  Future<void> openNotifications() async {
    if (!widget.state.signedIn) {
      await Navigator.push(
          context,
          MaterialPageRoute<void>(
              builder: (_) => AccountForm(
                  state: widget.state, action: AccountAction.login)));
    }
    if (!mounted || !widget.state.signedIn) return;
    await Navigator.push(
        context,
        MaterialPageRoute<void>(
            builder: (_) => AccountList(
                state: widget.state,
                title: 'Notifications',
                path: 'me/notifications')));
    if (mounted) await widget.state.refreshUnreadNotifications();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          title: FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xff2998ed),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.sports_esports_rounded,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(width: 10),
                const Text('Free',
                    style: TextStyle(fontWeight: FontWeight.w800)),
                const Text(
                  'Games',
                  style: TextStyle(
                    color: Color(0xff63b8ff),
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
          ),
          actions: [
            IconButton(
                tooltip: 'Notifications',
                onPressed: openNotifications,
                icon: Badge(
                    isLabelVisible: widget.state.unreadNotifications > 0,
                    label: Text(widget.state.unreadNotifications > 99
                        ? '99+'
                        : '${widget.state.unreadNotifications}'),
                    child: const Icon(Icons.notifications_outlined))),
            IconButton(
              tooltip: 'My account',
              onPressed: () => setState(() => tab = 3),
              icon: const Icon(Icons.person_outline),
            ),
          ],
        ),
        body: SafeArea(
          child: IndexedStack(
            index: tab,
            children: [
              CatalogScreen(state: widget.state),
              CatalogScreen(state: widget.state, discover: true),
              SavedGames(state: widget.state),
              AccountScreen(state: widget.state, active: tab == 3),
            ],
          ),
        ),
        bottomNavigationBar: NavigationBar(
          selectedIndex: tab,
          onDestinationSelected: (v) => setState(() => tab = v),
          destinations: const [
            NavigationDestination(
                icon: Icon(Icons.home_outlined), label: 'Home'),
            NavigationDestination(
              icon: Icon(Icons.explore_outlined),
              label: 'Explore',
            ),
            NavigationDestination(
              icon: Icon(Icons.favorite_border),
              label: 'Favorites',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              label: 'Account',
            ),
          ],
        ),
      );
}
