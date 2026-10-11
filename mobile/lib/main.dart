import 'package:flutter/material.dart';

import 'core.dart';
import 'games.dart';
import 'account.dart';

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
            secondary: Color(0xffb09bff),
            surface: Color(0xff142237),
          ),
          navigationBarTheme: const NavigationBarThemeData(
              indicatorColor: Color(0xff25476b),
              backgroundColor: Color(0xff0e1b2b)),
          appBarTheme: const AppBarTheme(
            backgroundColor: Color(0xff0b1220),
            surfaceTintColor: Colors.transparent,
          ),
          cardTheme: CardThemeData(
            elevation: 0,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          ),
          inputDecorationTheme: InputDecorationTheme(
            filled: true,
            fillColor: const Color(0xff16263b),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
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

class _AppShellState extends State<AppShell> {
  int tab = 0;
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
