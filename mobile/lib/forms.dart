import 'package:flutter/material.dart';

import 'core.dart';

enum AccountAction {
  login,
  register,
  verify,
  forgot,
  reset,
  profile,
  password,
  preferences,
  close,
}

class AccountForm extends StatefulWidget {
  final AppState state;
  final AccountAction action;
  final String email;
  const AccountForm({
    super.key,
    required this.state,
    required this.action,
    this.email = '',
  });
  @override
  State<AccountForm> createState() => _AccountFormState();
}

class _AccountFormState extends State<AccountForm> {
  final form = GlobalKey<FormState>();
  final fields = <String, TextEditingController>{};
  bool busy = false,
      loading = false,
      preferencesLoaded = false,
      confirmed = false;
  String? error;
  AccountAction get action => widget.action;
  String get title => switch (action) {
        AccountAction.login => 'Welcome back',
        AccountAction.register => 'Create account',
        AccountAction.verify => 'Verify your email',
        AccountAction.forgot => 'Recover password',
        AccountAction.reset => 'Reset password',
        AccountAction.profile => 'Edit profile',
        AccountAction.password => 'Change password',
        AccountAction.preferences => 'Gaming preferences',
        AccountAction.close => 'Close account',
      };
  Map<String, String> get labels => switch (action) {
        AccountAction.login => {
            'email': 'Email',
            'password': 'Password',
            'otp': 'Authenticator code (if enabled)',
          },
        AccountAction.register => {
            'name': 'Name',
            'username': 'Username',
            'email': 'Email',
            'password': 'Password',
            'password_confirmation': 'Confirm password',
          },
        AccountAction.verify => {'email': 'Email', 'code': 'Verification code'},
        AccountAction.forgot => {'email': 'Email'},
        AccountAction.reset => {
            'email': 'Email',
            'token': 'Reset link or token',
            'password': 'New password',
            'password_confirmation': 'Confirm password',
          },
        AccountAction.profile => {
            'name': 'Name',
            'country': 'Country (optional)',
            'mobile_number': 'Mobile number (optional)',
          },
        AccountAction.password => {
            'current_password': 'Current password',
            'password': 'New password',
            'password_confirmation': 'Confirm password',
          },
        AccountAction.preferences => {
            'preferred_genres': 'Genres, separated by commas',
            'preferred_platforms': 'Platforms, separated by commas',
          },
        AccountAction.close => {
            'current_password': 'Current password',
            'otp': 'Authenticator code (if enabled)',
          },
      };
  @override
  void initState() {
    super.initState();
    for (final key in labels.keys) {
      fields[key] = TextEditingController(
        text: key == 'email'
            ? widget.email
            : action == AccountAction.profile
                ? '${widget.state.user?[key] ?? ''}'
                : '',
      );
    }
    if (action == AccountAction.preferences) loadPreferences();
  }

  Future<void> loadPreferences() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final r = await widget.state.api.request(
        'me/preferences',
        authenticated: true,
      );
      if (mounted) {
        for (final key in fields.keys) {
          fields[key]!.text = ((r['data'] as Map)[key] as List).join(', ');
        }
        setState(() => preferencesLoaded = true);
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  String? validate(String key, String? value) {
    final v = value ?? '';
    if ([
      'country',
      'mobile_number',
      'preferred_genres',
      'preferred_platforms',
    ].contains(key)) {
      return null;
    }
    if (key == 'otp' &&
        widget.state.user?['google2fa_status'] != true &&
        widget.state.user?['google2fa_status'] != 1 &&
        v.isEmpty) {
      return null;
    }
    if (v.trim().isEmpty) return 'This field is required.';
    if (key == 'email' &&
        !RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$').hasMatch(v.trim())) {
      return 'Enter a valid email.';
    }
    if (['otp', 'code'].contains(key) && !RegExp(r'^\d{6}$').hasMatch(v)) {
      return 'Enter six digits.';
    }
    if (key == 'password' &&
        ![AccountAction.login].contains(action) &&
        v.length < 8) {
      return 'Use at least 8 characters.';
    }
    if (key == 'password_confirmation' && v != fields['password']?.text) {
      return 'Passwords do not match.';
    }
    return null;
  }

  Future<void> submit() async {
    if (busy || !form.currentState!.validate()) return;
    if (action == AccountAction.preferences && !preferencesLoaded) return;
    if (action == AccountAction.close && !confirmed) {
      setState(() => error = 'Confirm account closure first.');
      return;
    }
    setState(() {
      busy = true;
      error = null;
    });
    final body = <String, dynamic>{
      for (final e in fields.entries)
        e.key: e.key.contains('password') ? e.value.text : e.value.text.trim(),
    };
    if (body['otp'] == '') body.remove('otp');
    if (action == AccountAction.preferences) {
      for (final key in body.keys.toList()) {
        body[key] = (body[key] as String)
            .split(',')
            .map((s) => s.trim())
            .where((s) => s.isNotEmpty)
            .toList();
      }
    }
    if (action == AccountAction.reset) {
      final link = Uri.tryParse(body['token'] as String);
      if (link != null &&
          ['https', 'http'].contains(link.scheme) &&
          link.pathSegments.isNotEmpty) {
        body['token'] = link.queryParameters['token'] ?? link.pathSegments.last;
      }
    }
    try {
      if (action == AccountAction.login) {
        await widget.state.login(body);
      } else {
        final path = switch (action) {
          AccountAction.register => 'auth/register',
          AccountAction.verify => 'auth/verify-email',
          AccountAction.forgot => 'auth/forgot-password',
          AccountAction.reset => 'auth/reset-password',
          AccountAction.profile => 'me',
          AccountAction.password => 'me/password',
          AccountAction.preferences => 'me/preferences',
          AccountAction.close => 'me',
          AccountAction.login => 'auth/login',
        };
        final method = switch (action) {
          AccountAction.profile => 'PATCH',
          AccountAction.password || AccountAction.preferences => 'PUT',
          AccountAction.close => 'DELETE',
          _ => 'POST',
        };
        await widget.state.api.request(
          path,
          method: method,
          body: body,
          authenticated: [
            AccountAction.profile,
            AccountAction.password,
            AccountAction.preferences,
            AccountAction.close,
          ].contains(action),
        );
        if (action == AccountAction.profile) await widget.state.reloadUser();
        if ([AccountAction.password, AccountAction.close].contains(action)) {
          await widget.state.signOut(localOnly: true);
        }
      }
      if (!mounted) return;
      if (action == AccountAction.register) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute<void>(
            builder: (_) => AccountForm(
              state: widget.state,
              action: AccountAction.verify,
              email: body['email'] as String,
            ),
          ),
        );
      } else if (action == AccountAction.forgot) {
        setState(
          () => error = 'If the account exists, a reset link has been emailed.',
        );
      } else {
        ScaffoldMessenger.of(context)
            .showSnackBar(const SnackBar(content: Text('Done.')));
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) setState(() => error = '$e');
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  void go(AccountAction a) => Navigator.push(
        context,
        MaterialPageRoute<void>(
          builder: (_) => AccountForm(
            state: widget.state,
            action: a,
            email: fields['email']?.text.trim() ?? '',
          ),
        ),
      );
  @override
  void dispose() {
    for (final c in fields.values) {
      c.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(title)),
        body: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 520),
            child: ListView(
              padding: const EdgeInsets.all(24),
              children: [
                Text(
                  title,
                  style: const TextStyle(
                      fontSize: 28, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 12),
                Text(
                    switch (action) {
                      AccountAction.close =>
                        'Closing your account signs you out. Historical website and lottery records are retained.',
                      AccountAction.password =>
                        'Changing your password signs out every mobile device.',
                      AccountAction.reset =>
                        'Paste the reset link from your email, or finish resetting in your browser.',
                      AccountAction.preferences =>
                        'Choose the genres and platforms you enjoy. Leave blank to clear.',
                      _ => 'Make your gaming space yours.',
                    },
                    style: const TextStyle(color: Color(0xff9fb1c7))),
                const SizedBox(height: 24),
                if (loading) const Center(child: CircularProgressIndicator()),
                Form(
                  key: form,
                  child: Column(
                    children: [
                      for (final e in labels.entries)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 16),
                          child: TextFormField(
                            controller: fields[e.key],
                            enabled: !busy && !loading,
                            obscureText: e.key.contains('password'),
                            maxLength: ['otp', 'code'].contains(e.key)
                                ? 6
                                : e.key.contains('password')
                                    ? 128
                                    : null,
                            keyboardType: e.key == 'email'
                                ? TextInputType.emailAddress
                                : ['otp', 'code', 'mobile_number']
                                        .contains(e.key)
                                    ? TextInputType.phone
                                    : TextInputType.text,
                            decoration: InputDecoration(labelText: e.value),
                            validator: (v) => validate(e.key, v),
                          ),
                        ),
                    ],
                  ),
                ),
                if (action == AccountAction.close)
                  CheckboxListTile(
                    contentPadding: EdgeInsets.zero,
                    value: confirmed,
                    onChanged: busy
                        ? null
                        : (v) => setState(() => confirmed = v ?? false),
                    title: const Text('I want to close my account'),
                  ),
                if (error != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: Text(error!),
                  ),
                if (action == AccountAction.preferences && !preferencesLoaded)
                  TextButton(
                    onPressed: loading ? null : loadPreferences,
                    child: const Text('Retry loading preferences'),
                  ),
                FilledButton(
                  onPressed: busy ||
                          loading ||
                          (action == AccountAction.preferences &&
                              !preferencesLoaded)
                      ? null
                      : submit,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    child: busy
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Text(
                            action == AccountAction.login
                                ? 'Sign in'
                                : action == AccountAction.register
                                    ? 'Create account'
                                    : 'Save',
                          ),
                  ),
                ),
                if (action == AccountAction.login) ...[
                  TextButton(
                    onPressed: busy ? null : () => go(AccountAction.register),
                    child: const Text('Create a new account'),
                  ),
                  TextButton(
                    onPressed: busy ? null : () => go(AccountAction.verify),
                    child: const Text('Verify your email'),
                  ),
                  TextButton(
                    onPressed: busy ? null : () => go(AccountAction.forgot),
                    child: const Text('Forgot password?'),
                  ),
                  TextButton(
                    onPressed: busy ? null : () => go(AccountAction.reset),
                    child: const Text('I have a reset link'),
                  ),
                ],
                if (action == AccountAction.verify)
                  TextButton(
                    onPressed: busy
                        ? null
                        : () async {
                            setState(() => busy = true);
                            try {
                              await widget.state.api.request(
                                'auth/resend-verification',
                                method: 'POST',
                                body: {'email': fields['email']!.text.trim()},
                              );
                              if (mounted) {
                                setState(
                                  () => error =
                                      'If eligible, a verification code has been emailed.',
                                );
                              }
                            } catch (e) {
                              if (mounted) setState(() => error = '$e');
                            } finally {
                              if (mounted) setState(() => busy = false);
                            }
                          },
                    child: const Text('Resend verification code'),
                  ),
              ],
            ),
          ),
        ),
      );
}
