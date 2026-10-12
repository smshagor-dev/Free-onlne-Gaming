import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import 'core.dart';
import 'forms.dart';
import 'games.dart';

Future<bool> confirm(BuildContext context, String title) async =>
    await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: Text(title),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(c, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(c, true),
            child: const Text('Confirm'),
          ),
        ],
      ),
    ) ??
    false;

class AccountScreen extends StatefulWidget {
  final AppState state;
  final bool active;
  const AccountScreen({super.key, required this.state, required this.active});
  @override
  State<AccountScreen> createState() => _AccountScreenState();
}

class _AccountScreenState extends State<AccountScreen> {
  Map<String, dynamic>? dashboard;
  Object? loadedUserId;
  bool busy = false;
  String? error;
  @override
  void initState() {
    super.initState();
    if (widget.active && widget.state.signedIn) load();
  }

  @override
  void didUpdateWidget(covariant AccountScreen old) {
    super.didUpdateWidget(old);
    if (!widget.state.signedIn) {
      loadedUserId = null;
      dashboard = null;
    } else if (widget.active &&
        (!old.active || loadedUserId != widget.state.user?['id'])) {
      load();
    }
  }

  Future<void> load() async {
    if (busy) return;
    final id = widget.state.user?['id'];
    loadedUserId = id;
    setState(() {
      busy = true;
      error = null;
    });
    try {
      final r = await widget.state.api.request(
        'me/dashboard',
        authenticated: true,
      );
      if (mounted && id == widget.state.user?['id']) {
        setState(() => dashboard = r['data'] as Map<String, dynamic>);
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  void form(AccountAction action) => Navigator.push(
        context,
        MaterialPageRoute<void>(
          builder: (_) => AccountForm(state: widget.state, action: action),
        ),
      );
  void list(String title, String path) => Navigator.push(
        context,
        MaterialPageRoute<void>(
          builder: (_) =>
              AccountList(state: widget.state, title: title, path: path),
        ),
      );
  @override
  Widget build(BuildContext context) {
    if (!widget.state.signedIn) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.person_outline,
                size: 56,
                color: Color(0xff80c7ff),
              ),
              const SizedBox(height: 20),
              const Text(
                'Make it your space',
                style: TextStyle(fontSize: 26, fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 12),
              const Text(
                'Save favorites, keep your history and pick up where you left off.',
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              if (widget.state.error != null)
                Text(widget.state.error!, textAlign: TextAlign.center),
              FilledButton(
                onPressed: () => form(AccountAction.login),
                child: const Text('Sign in'),
              ),
            ],
          ),
        ),
      );
    }
    final name = '${widget.state.user?['name'] ?? 'Player'}';
    final protected = widget.state.user?['google2fa_status'] == true ||
        widget.state.user?['google2fa_status'] == 1;
    return RefreshIndicator(
        onRefresh: load,
        child: ListView(
            padding: const EdgeInsets.all(20),
            physics: const AlwaysScrollableScrollPhysics(),
            children: [
              const Text('Your account',
                  style: TextStyle(fontSize: 26, fontWeight: FontWeight.w700)),
              const SizedBox(height: 6),
              const Text('Your games, activity and personal settings.',
                  style: TextStyle(color: Color(0xff9fb1c7))),
              const SizedBox(height: 20),
              Card(
                  child: Padding(
                      padding: const EdgeInsets.all(18),
                      child: Row(children: [
                        CircleAvatar(
                            radius: 26,
                            backgroundColor: const Color(0xff203d59),
                            child: Text(
                                name.isEmpty
                                    ? 'P'
                                    : name.substring(0, 1).toUpperCase(),
                                style: const TextStyle(
                                    fontSize: 24, color: Color(0xff80c7ff)))),
                        const SizedBox(width: 14),
                        Expanded(
                            child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                              Text(name,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w700)),
                              const SizedBox(height: 4),
                              Text('${widget.state.user?['email'] ?? ''}',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                      color: Color(0xff9fb1c7), fontSize: 13))
                            ])),
                        IconButton(
                            tooltip: 'Edit profile',
                            onPressed: () => form(AccountAction.profile),
                            icon: const Icon(Icons.edit_outlined, size: 20))
                      ]))),
              const SizedBox(height: 12),
              Card(
                  child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 18),
                      child: Row(children: [
                        for (final entry in [
                          ('Saved', 'favorites'),
                          ('Played', 'games_played'),
                          ('Tickets', 'lottery_tickets')
                        ])
                          Expanded(
                              child: Column(children: [
                            Text('${dashboard?[entry.$2] ?? '—'}',
                                style: const TextStyle(
                                    fontSize: 22, fontWeight: FontWeight.w700)),
                            const SizedBox(height: 4),
                            Text(entry.$1,
                                style: const TextStyle(
                                    fontSize: 12, color: Color(0xff9fb1c7)))
                          ]))
                      ]))),
              if (busy)
                const Padding(
                    padding: EdgeInsets.only(top: 12),
                    child: LinearProgressIndicator()),
              if (error != null) ...[
                Text(error!),
                TextButton(onPressed: load, child: const Text('Retry'))
              ],
              section('Your activity', [
                tile(Icons.history_rounded, 'Recently played',
                    () => list('Recently played', 'me/recent')),
                tile(Icons.tune_rounded, 'Gaming preferences',
                    () => form(AccountAction.preferences))
              ]),
              section('Account & security', [
                tile(Icons.person_outline_rounded, 'Personal details',
                    () => form(AccountAction.profile)),
                tile(Icons.lock_outline_rounded, 'Change password',
                    () => form(AccountAction.password)),
                ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                    leading: iconBox(Icons.shield_outlined),
                    title: const Text('Two-factor authentication',
                        style: TextStyle(fontSize: 14)),
                    subtitle: Text(
                        protected
                            ? 'Enabled · your account is protected'
                            : 'Add an extra layer of protection',
                        style: const TextStyle(
                            fontSize: 12, color: Color(0xff9fb1c7))),
                    trailing: const Icon(Icons.chevron_right, size: 20),
                    onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute<void>(
                            builder: (_) =>
                                SecurityScreen(state: widget.state)))),
                tile(Icons.devices_outlined, 'Devices & sessions',
                    () => list('Your devices', 'me/devices'))
              ]),
              section('Lottery', [
                tile(Icons.event_available_outlined, 'Browse draws',
                    () => list('Available lottery draws', 'lotteries')),
                tile(Icons.confirmation_number_outlined, 'My tickets',
                    () => list('My lottery tickets', 'me/lottery/tickets')),
                tile(Icons.emoji_events_outlined, 'Results',
                    () => list('Lottery results', 'me/lottery/wins'))
              ]),
              const SizedBox(height: 20),
              OutlinedButton.icon(
                  icon: const Icon(Icons.logout, size: 18),
                  label: const Text('Sign out'),
                  onPressed: () async {
                    try {
                      await widget.state.signOut();
                    } catch (e) {
                      if (context.mounted) showError(context, e);
                    }
                  }),
              const SizedBox(height: 8),
              TextButton(
                  style: TextButton.styleFrom(
                      foregroundColor: Theme.of(context).colorScheme.error),
                  onPressed: () => form(AccountAction.close),
                  child: const Text('Close account')),
              const SizedBox(height: 8)
            ]));
  }

  Widget section(String title, List<Widget> children) => Padding(
      padding: const EdgeInsets.only(top: 24),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(title,
            style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: Color(0xffb6c6d8))),
        const SizedBox(height: 10),
        Card(
            child: Column(children: [
          for (var i = 0; i < children.length; i++) ...[
            children[i],
            if (i < children.length - 1)
              const Divider(height: 1, indent: 64, color: Color(0xff25374e))
          ]
        ]))
      ]));
  Widget iconBox(IconData icon) => Container(
      width: 34,
      height: 34,
      decoration: BoxDecoration(
          color: const Color(0xff1b334c),
          borderRadius: BorderRadius.circular(10)),
      child: Icon(icon, size: 19, color: const Color(0xff80c7ff)));
  Widget tile(IconData icon, String title, VoidCallback onTap) => ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16),
      leading: iconBox(icon),
      title: Text(title, style: const TextStyle(fontSize: 14)),
      trailing: const Icon(Icons.chevron_right, size: 20),
      onTap: onTap);
}

class AccountList extends StatefulWidget {
  final AppState state;
  final String title, path;
  const AccountList({
    super.key,
    required this.state,
    required this.title,
    required this.path,
  });
  @override
  State<AccountList> createState() => _AccountListState();
}

class _AccountListState extends State<AccountList> {
  final scroll = ScrollController();
  final rows = <Map<String, dynamic>>[];
  bool busy = false, more = true;
  int page = 1;
  String? error;
  bool get devices => widget.path == 'me/devices';
  bool get recent => widget.path == 'me/recent';
  bool get notifications => widget.path == 'me/notifications';
  @override
  void initState() {
    super.initState();
    scroll.addListener(() {
      if (scroll.position.extentAfter < 400) load();
    });
    load();
  }

  Future<void> load({bool reset = false}) async {
    if (busy) return;
    if (reset) {
      rows.clear();
      page = 1;
      more = true;
    }
    if (!more) return;
    setState(() {
      busy = true;
      error = null;
    });
    try {
      final r = await widget.state.api.request(
        widget.path,
        authenticated: true,
        query: devices ? null : {'page': '$page'},
      );
      if (mounted) {
        setState(() {
          rows.addAll(
            (r['data'] as List).map((e) => Map<String, dynamic>.from(e as Map)),
          );
          more = devices
              ? false
              : r['meta'] is Map
                  ? (r['meta'] as Map)['has_more'] == true
                  : r['next_page_url'] != null;
          page++;
        });
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  Future<void> mutate(
    String path, {
    String method = 'PATCH',
    VoidCallback? success,
  }) async {
    if (busy) return;
    setState(() => busy = true);
    try {
      await widget.state.api.request(path, method: method, authenticated: true);
      if (mounted) {
        setState(() {
          success?.call();
          error = null;
        });
      }
      if (notifications) await widget.state.refreshUnreadNotifications();
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  Future<void> ticket(Map<String, dynamic> row) async {
    try {
      final r = await widget.state.api.request(
        'me/lottery/tickets/${row['id']}',
        authenticated: true,
      );
      if (!mounted) return;
      final t = r['data'] as Map;
      showModalBottomSheet<void>(
        context: context,
        showDragHandle: true,
        builder: (_) => SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'Your lottery ticket',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 16),
              SelectableText('${t['ticket_number']}'),
              Text('Amount: ${t['amount']}'),
              Text('Purchased: ${t['created_at']}'),
            ],
          ),
        ),
      );
    } catch (e) {
      if (mounted) showError(context, e);
    }
  }

  @override
  void dispose() {
    scroll.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          title: Text(widget.title),
          actions: [
            if (notifications)
              IconButton(
                tooltip: 'Mark all read',
                onPressed: busy
                    ? null
                    : () => mutate(
                          'me/notifications/read-all',
                          success: () {
                            for (final row in rows) {
                              row['is_read'] = true;
                            }
                          },
                        ),
                icon: const Icon(Icons.done_all),
              ),
            if (recent)
              IconButton(
                tooltip: 'Clear history',
                onPressed: busy
                    ? null
                    : () async {
                        if (await confirm(context, 'Clear mobile history?')) {
                          await mutate(
                            'me/recent',
                            method: 'DELETE',
                            success: () {
                              rows.clear();
                              page = 1;
                              more = false;
                            },
                          );
                        }
                      },
                icon: const Icon(Icons.delete_outline),
              ),
            if (devices)
              PopupMenuButton<String>(
                enabled: !busy,
                onSelected: (v) async {
                  try {
                    if (v == 'refresh') {
                      await widget.state.refreshSession();
                      await load(reset: true);
                    } else if (await confirm(
                      context,
                      'Sign out all mobile devices?',
                    )) {
                      await widget.state.signOut(all: true);
                      if (context.mounted) Navigator.pop(context);
                    }
                  } catch (e) {
                    if (context.mounted) showError(context, e);
                  }
                },
                itemBuilder: (_) => const [
                  PopupMenuItem(
                    value: 'refresh',
                    child: Text('Renew this session'),
                  ),
                  PopupMenuItem(
                      value: 'all', child: Text('Sign out all devices')),
                ],
              ),
          ],
        ),
        body: RefreshIndicator(
          onRefresh: () => load(reset: true),
          child: ListView(
            controller: scroll,
            padding: const EdgeInsets.all(20),
            physics: const AlwaysScrollableScrollPhysics(),
            children: [
              for (final row in rows)
                if (recent && row['snapshot'] is Map)
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.sports_esports),
                      title: Text(
                          '${(row['snapshot'] as Map)['title'] ?? 'Game'}'),
                      subtitle: Text('${row['last_played_at'] ?? ''}'),
                      onTap: () => openGame(
                        context,
                        widget.state,
                        Game.fromJson(
                          Map<String, dynamic>.from(row['snapshot'] as Map),
                        ),
                      ),
                    ),
                  )
                else
                  Card(
                    child: ListTile(
                      leading: devices
                          ? Icon(
                              row['current'] == true
                                  ? Icons.phone_android
                                  : Icons.devices,
                            )
                          : notifications
                              ? Icon(
                                  row['is_read'] == true || row['is_read'] == 1
                                      ? Icons.mark_email_read_outlined
                                      : Icons.mark_email_unread_outlined,
                                )
                              : null,
                      title: Text(
                        '${row['title'] ?? row['ticket_number'] ?? row['device_name'] ?? 'Result'}${devices && row['current'] == true ? ' · This device' : ''}',
                      ),
                      subtitle: Text(
                        '${row['message'] ?? row['description'] ?? row['draw_date'] ?? row['last_used_at'] ?? row['created_at'] ?? ''}${row['position'] != null ? ' · Position ${row['position']}' : ''}',
                      ),
                      trailing: devices
                          ? IconButton(
                              tooltip: 'Revoke device',
                              onPressed: busy
                                  ? null
                                  : () async {
                                      if (!await confirm(
                                        context,
                                        'Revoke this device?',
                                      )) {
                                        return;
                                      }
                                      await mutate(
                                        'me/devices/${row['id']}',
                                        method: 'DELETE',
                                        success: () => rows.remove(row),
                                      );
                                      if (row['current'] == true &&
                                          error == null) {
                                        await widget.state
                                            .signOut(localOnly: true);
                                        if (context.mounted) {
                                          Navigator.pop(context);
                                        }
                                      }
                                    },
                              icon: const Icon(Icons.logout),
                            )
                          : row['price'] != null
                              ? Text('${row['price']}')
                              : null,
                      onTap: busy
                          ? null
                          : widget.path == 'me/lottery/tickets'
                              ? () => ticket(row)
                              : notifications
                                  ? () => mutate(
                                        'me/notifications/${row['id']}/read',
                                        success: () => row['is_read'] = true,
                                      )
                                  : null,
                    ),
                  ),
              if (busy) const Center(child: CircularProgressIndicator()),
              if (error != null) ...[
                Text(error!),
                TextButton(onPressed: load, child: const Text('Retry')),
              ],
              if (!busy && error == null && rows.isEmpty)
                const Padding(
                  padding: EdgeInsets.all(30),
                  child: Text('Nothing here yet.', textAlign: TextAlign.center),
                ),
            ],
          ),
        ),
      );
}

class SecurityScreen extends StatefulWidget {
  final AppState state;
  const SecurityScreen({super.key, required this.state});
  @override
  State<SecurityScreen> createState() => _SecurityScreenState();
}

class _SecurityScreenState extends State<SecurityScreen> {
  final password = TextEditingController(), otp = TextEditingController();
  Map<String, dynamic>? setup;
  bool busy = false;
  String? error;
  bool get enabled =>
      widget.state.user?['google2fa_status'] == true ||
      widget.state.user?['google2fa_status'] == 1;
  Future<void> submit() async {
    if (busy) return;
    if ((enabled || setup == null) && password.text.isEmpty) {
      setState(() => error = 'Enter your current password.');
      return;
    }
    if ((enabled || setup != null) && !RegExp(r'^\d{6}$').hasMatch(otp.text)) {
      setState(() => error = 'Enter a six-digit authenticator code.');
      return;
    }
    setState(() {
      busy = true;
      error = null;
    });
    try {
      if (!enabled && setup == null) {
        final r = await widget.state.api.request(
          'me/2fa/setup',
          method: 'POST',
          authenticated: true,
          body: {'current_password': password.text},
        );
        if (mounted) setState(() => setup = r['data'] as Map<String, dynamic>);
      } else {
        await widget.state.api.request(
          enabled ? 'me/2fa/disable' : 'me/2fa/enable',
          method: 'POST',
          authenticated: true,
          body: {
            'otp': otp.text,
            if (enabled) 'current_password': password.text,
          },
        );
        await widget.state.signOut(localOnly: true);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Security updated. Sign in again.')),
          );
          Navigator.pop(context);
        }
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  void dispose() {
    password.dispose();
    otp.dispose();
    setup = null;
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('Two-factor authentication')),
        body: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const Icon(Icons.shield_outlined,
                size: 60, color: Color(0xff80c7ff)),
            const SizedBox(height: 24),
            Text(
              enabled
                  ? 'Your account has an extra layer of protection.'
                  : 'Protect your account with an authenticator app.',
              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 20),
            if (setup == null || enabled)
              TextField(
                controller: password,
                obscureText: true,
                enabled: !busy,
                decoration:
                    const InputDecoration(labelText: 'Current password'),
              ),
            if (setup != null) ...[
              const Text(
                'Scan this QR in your authenticator. Setup expires after 10 minutes.',
              ),
              const SizedBox(height: 16),
              Center(
                child: Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(12),
                  child:
                      QrImageView(data: '${setup!['otpauth_url']}', size: 220),
                ),
              ),
              const SizedBox(height: 12),
              SelectableText('${setup!['secret']}',
                  textAlign: TextAlign.center),
            ],
            if (enabled || setup != null) ...[
              const SizedBox(height: 20),
              TextField(
                controller: otp,
                enabled: !busy,
                maxLength: 6,
                keyboardType: TextInputType.number,
                decoration:
                    const InputDecoration(labelText: 'Authenticator code'),
              ),
            ],
            if (error != null) Text(error!),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: busy ? null : submit,
              child: Text(
                busy
                    ? 'Please wait…'
                    : enabled
                        ? 'Disable two-factor'
                        : setup == null
                            ? 'Set up authenticator'
                            : 'Enable two-factor',
              ),
            ),
          ],
        ),
      );
}
