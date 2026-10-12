import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import 'core.dart';
import 'forms.dart';

void showError(BuildContext context, Object e) =>
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
Future<void> openGame(BuildContext context, AppState state, Game g) =>
    Navigator.push(
      context,
      MaterialPageRoute<void>(
        builder: (_) => PlayerScreen(state: state, game: g),
      ),
    );

class GameCard extends StatelessWidget {
  final Game game;
  final AppState state;
  const GameCard({super.key, required this.game, required this.state});
  @override
  Widget build(BuildContext context) => Card(
        clipBehavior: Clip.antiAlias,
        margin: EdgeInsets.zero,
        child: InkWell(
          onTap: () => openGame(context, state, game),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Container(
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xff1d3858), Color(0xff192338)],
                        ),
                      ),
                      child: game.image == null
                          ? GameArtwork(category: game.category)
                          : Image.network(
                              game.image!,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) =>
                                  GameArtwork(category: game.category),
                            ),
                    ),
                    Positioned(
                      right: 6,
                      top: 6,
                      child: IconButton.filledTonal(
                        tooltip: state.favorites.containsKey(game.key)
                            ? 'Remove saved game'
                            : 'Save game',
                        style: IconButton.styleFrom(
                            backgroundColor: const Color(0xcc0b1220),
                            foregroundColor: Colors.white),
                        onPressed: () async {
                          try {
                            await state.toggleFavorite(game);
                          } catch (e) {
                            if (context.mounted) showError(context, e);
                          }
                        },
                        icon: Icon(
                          state.favorites.containsKey(game.key)
                              ? Icons.favorite
                              : Icons.favorite_border,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      game.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      game.category,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Color(0xff9fb1c7),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      );
}

class GameArtwork extends StatelessWidget {
  final String category;
  const GameArtwork({super.key, required this.category});
  @override
  Widget build(BuildContext context) {
    final (icon, color) = switch (category.toLowerCase()) {
      'puzzle' => (Icons.extension_rounded, const Color(0xff83dbc7)),
      'racing' => (Icons.sports_motorsports_rounded, const Color(0xffffae87)),
      'adventure' => (Icons.explore_rounded, const Color(0xffb4a8ff)),
      'strategy' => (Icons.grid_view_rounded, const Color(0xffe5d599)),
      'sports' => (Icons.sports_soccer_rounded, const Color(0xff9be0b0)),
      _ => (Icons.bolt_rounded, const Color(0xffffd378))
    };
    return Container(
        decoration: BoxDecoration(
            gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
              Color.alphaBlend(
                  color.withValues(alpha: .28), const Color(0xff142237)),
              const Color(0xff112033)
            ])),
        child: Stack(children: [
          Positioned(
              right: -15,
              bottom: -25,
              child:
                  Icon(icon, size: 150, color: color.withValues(alpha: .08))),
          Center(
              child: Container(
                  padding: const EdgeInsets.all(17),
                  decoration: BoxDecoration(
                      color: color.withValues(alpha: .12),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: color.withValues(alpha: .22))),
                  child: Icon(icon, size: 40, color: color)))
        ]));
  }
}

class CatalogScreen extends StatefulWidget {
  final AppState state;
  final bool discover;
  const CatalogScreen({super.key, required this.state, this.discover = false});
  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  final scroll = ScrollController(), search = TextEditingController();
  final games = <Game>[];
  List<String> categories = [];
  String provider = 'gamemonetize';
  String? category, error;
  bool loading = false, more = true;
  int page = 1, generation = 0;
  @override
  void initState() {
    super.initState();
    scroll.addListener(() {
      if (scroll.position.extentAfter < 600) load();
    });
    load();
    loadCategories();
  }

  Future<void> loadCategories() async {
    try {
      final r = await widget.state.api.request('categories');
      if (mounted) {
        setState(
          () => categories = (r['data'] as List).map((e) => '$e').toList(),
        );
      }
    } catch (_) {
      /* Metadata is optional. */
    }
  }

  Future<void> load({bool reset = false}) async {
    if (reset) {
      generation++;
      games.clear();
      page = 1;
      more = true;
      loading = false;
    }
    if (loading || !more) return;
    final current = generation;
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final r = await widget.state.api.request(
        'games',
        query: {
          'provider': provider,
          'page': '$page',
          'per_page': '30',
          'playable_only': '1',
          if (search.text.trim().isNotEmpty) 'q': search.text.trim(),
          if (category != null && provider == 'gamemonetize')
            'category': category!,
        },
      );
      if (!mounted || current != generation) return;
      final seen = games.map((g) => g.key).toSet();
      setState(() {
        games.addAll(
          (r['data'] as List)
              .map((e) => Game.fromJson(Map<String, dynamic>.from(e as Map)))
              .where((g) => g.playable)
              .where((g) => seen.add(g.key)),
        );
        more = (r['meta'] as Map)['has_more'] == true;
        page++;
      });
    } catch (e) {
      if (mounted && current == generation) setState(() => error = '$e');
    } finally {
      if (mounted && current == generation) setState(() => loading = false);
    }
  }

  @override
  void dispose() {
    scroll.dispose();
    search.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => RefreshIndicator(
        onRefresh: () => load(reset: true),
        child: CustomScrollView(
          controller: scroll,
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
              sliver: SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (!widget.discover)
                      Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(18),
                              gradient: const LinearGradient(colors: [
                                Color(0xff173c60),
                                Color(0xff142c46)
                              ])),
                          child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('A little play. A better day.',
                                    style: TextStyle(
                                        fontSize: 24,
                                        height: 1.2,
                                        fontWeight: FontWeight.w700)),
                                const SizedBox(height: 8),
                                const Text(
                                    'Find your next favorite. Play for free.',
                                    style: TextStyle(
                                        color: Color(0xffb1c3dc),
                                        fontSize: 13)),
                                const SizedBox(height: 16),
                                FilledButton.icon(
                                    onPressed: games.isEmpty
                                        ? null
                                        : () => openGame(
                                            context, widget.state, games.first),
                                    icon: const Icon(Icons.play_arrow_rounded,
                                        size: 20),
                                    label: const Text('Start playing'))
                              ])),
                    const SizedBox(height: 16),
                    SizedBox(
                      height: 40,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        children: [
                          for (final c in <String?>[
                            null,
                            if (provider == 'gamemonetize') ...categories,
                          ])
                            Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: ChoiceChip(
                                label: Text(c ?? 'All games'),
                                selected: category == c,
                                onSelected: (_) {
                                  category = c;
                                  load(reset: true);
                                },
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            widget.discover
                                ? 'Browse collection'
                                : 'Pick your next game',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                        const Icon(Icons.bolt, color: Color(0xff80c7ff)),
                      ],
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              sliver: SliverLayoutBuilder(
                builder: (_, constraints) => SliverGrid(
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: constraints.crossAxisExtent > 600 ? 4 : 2,
                    mainAxisSpacing: 14,
                    crossAxisSpacing: 14,
                    mainAxisExtent: 212,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (_, i) => GameCard(game: games[i], state: widget.state),
                    childCount: games.length,
                  ),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    if (loading) const CircularProgressIndicator(),
                    if (error != null) ...[
                      Text(error!),
                      TextButton(onPressed: load, child: const Text('Retry')),
                    ],
                    if (!loading && error == null && games.isEmpty)
                      const Text('No games found. Try another search.'),
                    if (!loading && !more && games.isNotEmpty)
                      const Text(
                        'You’re all caught up.',
                        style: TextStyle(color: Color(0xff9fb1c7)),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );
}

class SavedGames extends StatelessWidget {
  final AppState state;
  const SavedGames({super.key, required this.state});
  @override
  Widget build(BuildContext context) {
    if (!state.signedIn) {
      return Center(
          child: Padding(
              padding: const EdgeInsets.all(28),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Icon(Icons.favorite_border_rounded,
                    size: 54, color: Color(0xff80c7ff)),
                const SizedBox(height: 18),
                const Text('Keep your favorites together',
                    textAlign: TextAlign.center,
                    style:
                        TextStyle(fontSize: 23, fontWeight: FontWeight.w700)),
                const SizedBox(height: 10),
                const Text('Sign in to save games and find them here any time.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Color(0xff9fb1c7))),
                const SizedBox(height: 22),
                FilledButton(
                    onPressed: () => Navigator.push(
                        context,
                        MaterialPageRoute<void>(
                            builder: (_) => AccountForm(
                                state: state, action: AccountAction.login))),
                    child: const Text('Sign in'))
              ])));
    }
    return RefreshIndicator(
        onRefresh: () async {
          try {
            await state.loadFavorites();
          } catch (e) {
            if (context.mounted) showError(context, e);
          }
        },
        child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            slivers: [
              SliverPadding(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
                  sliver: SliverToBoxAdapter(
                      child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                        const Text('Saved games',
                            style: TextStyle(
                                fontSize: 26, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 6),
                        Text(
                            '${state.favorites.length} favorites, ready whenever you are.',
                            style: const TextStyle(color: Color(0xff9fb1c7)))
                      ]))),
              if (state.favorites.isEmpty)
                const SliverToBoxAdapter(
                    child: Padding(
                        padding: EdgeInsets.all(32),
                        child: Column(children: [
                          Icon(Icons.favorite_border_rounded,
                              size: 48, color: Color(0xff80c7ff)),
                          SizedBox(height: 16),
                          Text('Your next favorite is waiting.'),
                          SizedBox(height: 6),
                          Text('Tap the heart on any game to save it here.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Color(0xff9fb1c7)))
                        ])))
              else
                SliverPadding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    sliver: SliverGrid.builder(
                        itemCount: state.favorites.length,
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount:
                                MediaQuery.sizeOf(context).width > 600 ? 4 : 2,
                            mainAxisExtent: 212,
                            mainAxisSpacing: 14,
                            crossAxisSpacing: 14),
                        itemBuilder: (_, i) => GameCard(
                            game: state.favorites.values.elementAt(i),
                            state: state))),
              const SliverToBoxAdapter(child: SizedBox(height: 24))
            ]));
  }
}

class PlayerScreen extends StatefulWidget {
  final AppState state;
  final Game game;
  const PlayerScreen({super.key, required this.state, required this.game});
  @override
  State<PlayerScreen> createState() => _PlayerScreenState();
}

class _PlayerScreenState extends State<PlayerScreen> {
  WebViewController? controller;
  String? error;
  int progress = 0;
  bool recorded = false;
  bool allowed(Uri? uri) =>
      uri != null &&
      uri.scheme == 'https' &&
      uri.host == 'html5.gamemonetize.co';
  @override
  void initState() {
    super.initState();
    final uri = Uri.tryParse(widget.game.playUrl ?? '');
    if (!widget.game.playable || !allowed(uri)) {
      error = 'This game is unavailable in the mobile player.';
      return;
    }
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xff0b1220))
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (v) {
            if (mounted) setState(() => progress = v);
          },
          onNavigationRequest: (r) =>
              !r.isMainFrame || allowed(Uri.tryParse(r.url))
                  ? NavigationDecision.navigate
                  : NavigationDecision.prevent,
          onWebResourceError: (e) {
            if (mounted && e.isForMainFrame == true) {
              setState(() => error = 'The game could not load. Please retry.');
            }
          },
          onPageFinished: (_) async {
            if (!recorded && widget.state.signedIn) {
              recorded = true;
              try {
                await widget.state.api.request(
                  'me/games/gamemonetize/${Uri.encodeComponent(widget.game.id)}/played',
                  method: 'POST',
                  authenticated: true,
                );
              } catch (_) {
                /* History must not interrupt play. */
              }
            }
          },
        ),
      )
      ..loadRequest(uri!);
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(widget.game.title)),
        body: error != null
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(error!, textAlign: TextAlign.center),
                    if (controller != null)
                      TextButton(
                        onPressed: () {
                          setState(() => error = null);
                          controller!.reload();
                        },
                        child: const Text('Retry'),
                      ),
                  ],
                ),
              )
            : Column(
                children: [
                  if (progress < 100)
                    LinearProgressIndicator(value: progress / 100),
                  Expanded(child: WebViewWidget(controller: controller!)),
                ],
              ),
      );
}

class GameDetails extends StatefulWidget {
  final AppState state;
  final Game game;
  const GameDetails({super.key, required this.state, required this.game});
  @override
  State<GameDetails> createState() => _GameDetailsState();
}

class _GameDetailsState extends State<GameDetails> {
  Game? game;
  String? error;
  @override
  void initState() {
    super.initState();
    load();
  }

  Future<void> load() async {
    setState(() => error = null);
    try {
      final r = await widget.state.api.request(
        'games/${Uri.encodeComponent(widget.game.provider)}/${Uri.encodeComponent(widget.game.id)}',
      );
      if (mounted) {
        setState(() => game = Game.fromJson(r['data'] as Map<String, dynamic>));
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(widget.game.title)),
        body: error != null
            ? Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(error!),
                    TextButton(onPressed: load, child: const Text('Retry')),
                  ],
                ),
              )
            : game == null
                ? const Center(child: CircularProgressIndicator())
                : ListView(
                    padding: const EdgeInsets.all(24),
                    children: [
                      if (game!.image != null)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(18),
                          child: Image.network(
                            game!.image!,
                            height: 210,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) =>
                                const SizedBox(height: 40),
                          ),
                        ),
                      const SizedBox(height: 20),
                      Text(
                        game!.title,
                        style: const TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(game!.description),
                      const SizedBox(height: 20),
                      Text(game!.playable
                          ? 'Play directly in the app.'
                          : 'This game does not support in-app play.'),
                      const SizedBox(height: 16),
                      FilledButton.icon(
                        icon: const Icon(Icons.play_arrow_rounded),
                        label: const Text('Play now'),
                        onPressed: game!.playable
                            ? () => openGame(context, widget.state, game!)
                            : null,
                      ),
                      OutlinedButton.icon(
                        icon: const Icon(Icons.favorite_border),
                        label: const Text('Save game'),
                        onPressed: () async {
                          try {
                            await widget.state.toggleFavorite(game!);
                          } catch (e) {
                            if (context.mounted) showError(context, e);
                          }
                        },
                      ),
                    ],
                  ),
      );
}
