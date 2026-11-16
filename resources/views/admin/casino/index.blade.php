@extends('layouts.admin')
@section('title', 'Casino game activity')
@section('page_title', 'Casino reports')
@section('styles')
<style>
.casino-game-thumb {width:56px;height:48px;object-fit:cover;border-radius:9px;background:#172c44}
.casino-game-placeholder {display:grid;place-items:center;width:56px;height:48px;background:#172c44;color:#88a6c7;border-radius:9px}
.casino-source {display:block;margin-top:4px;font-size:11px;color:#859bb5}
.casino-url {display:block;max-width:215px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size:11px;color:#8db9e3}
.casino-action {display:block;margin-top:5px;font-size:12px}
.casino-net {font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
.casino-net.is-positive {color:#15803d}
.casino-net.is-negative {color:#dc2626}
.casino-net.is-neutral {color:#7893ac}
.casino-table th,.casino-table td {white-space:nowrap;vertical-align:middle}
.casino-table td.casino-game-name {white-space:normal;min-width:140px}
</style>
@endsection
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">ADMIN · READ-ONLY ACTIVITY</span>
        <h1 style="margin-top:10px">Casino games &amp; player reports</h1>
        <p>View recorded game sessions, distinct players and provider-linked bet/win entries. No betting or settlement actions are available on this page.</p>
    </div>
    <a class="cms-button" href="{{ route('admin.games.index') }}">Manage local games</a>
</div>

<div class="cms-kpis">
    <div class="cms-kpi">
        <span class="cms-kpi-label">Casino games in sessions</span>
        <strong class="cms-kpi-number">{{ number_format($totals['games']) }}</strong>
        <span class="cms-kpi-note">Distinct recorded game names</span>
    </div>
    <div class="cms-kpi">
        <span class="cms-kpi-label">Recorded launches</span>
        <strong class="cms-kpi-number">{{ number_format($totals['sessions']) }}</strong>
        <span class="cms-kpi-note">Session records, not verified completed rounds</span>
    </div>
    <div class="cms-kpi">
        <span class="cms-kpi-label">Unique players</span>
        <strong class="cms-kpi-number">{{ number_format($totals['players']) }}</strong>
        <span class="cms-kpi-note">Distinct session user IDs</span>
    </div>
    <div class="cms-kpi">
        <span class="cms-kpi-label">Total recorded bets</span>
        <strong class="cms-kpi-number">{{ $totals['audited_events'] > 0 ? number_format((float) $totals['total_bet'], 2) : '—' }}</strong>
        <span class="cms-kpi-note">Approved provider-linked entries only</span>
    </div>
    <div class="cms-kpi">
        <span class="cms-kpi-label">Total recorded wins</span>
        <strong class="cms-kpi-number">{{ $totals['audited_events'] > 0 ? number_format((float) $totals['total_win'], 2) : '—' }}</strong>
        <span class="cms-kpi-note">Gross recorded win entries, not net profit</span>
    </div>
</div>

<div class="cms-card">
    <div class="cms-card-head">
        <div><h2>Report filters</h2><p>Search casino game names; optionally filter the member-by-game table by user ID.</p></div>
    </div>
    <form method="GET" action="{{ route('admin.casino.index') }}" class="cms-form-grid">
        <div class="cms-field">
            <label for="casino-search">Game name</label>
            <input id="casino-search" type="search" name="search" maxlength="80" value="{{ $search }}" placeholder="e.g. Roulette">
        </div>
        <div class="cms-field">
            <label for="casino-user-id">User database ID (optional)</label>
            <input id="casino-user-id" type="number" name="user_id" min="1" value="{{ $userId }}" placeholder="All players">
        </div>
        <div style="align-self:end">
            <button class="cms-button is-primary" type="submit">Apply filters</button>
            <a class="cms-button" href="{{ route('admin.casino.index') }}">Reset</a>
        </div>
    </form>
    <div class="cms-secret-hint">
        Amounts below are read from existing transaction records with approved status, provider name/transaction ID and an explicit bet or win action tied to the same session owner. The existing ledger has no transaction-level currency, so amounts are <strong>unlabelled units</strong> and must not be interpreted as USD, EUR, or combined monetary revenue. Pending, rejected and unverified legacy records are excluded; refunds/rollbacks are not netted.
    </div>
</div>

<section class="cms-card">
    <div class="cms-card-head">
        <div>
            <h2>Casino game table</h2>
            <p>Image, game, play URL, open/launch count, verified bet/win events and gross totals. Separate source rows prevent name-based misattribution.</p>
        </div>
        <span class="cms-badge">{{ number_format($gameList->total()) }} game records</span>
    </div>
    <div class="cms-table-wrap">
        <table class="cms-table casino-table">
            <thead>
                <tr>
                    <th>Image</th><th>Game name</th><th>Play URL</th><th>Play time</th>
                    <th>Bet</th><th>Win</th><th>Total bet</th><th>Total win</th>
                    <th>Admin win / lose</th>
                </tr>
            </thead>
            <tbody>
            @forelse($gameList as $item)
                <tr>
                    <td>
                        @php($imageUrl = $item->image && filter_var($item->image, FILTER_VALIDATE_URL) && in_array(parse_url($item->image, PHP_URL_SCHEME), ['http', 'https'], true) ? $item->image : null)
                        @if($imageUrl)
                            <img class="casino-game-thumb" src="{{ $imageUrl }}" alt="" width="56" height="48" loading="lazy">
                        @else
                            <span class="casino-game-placeholder" title="No game image available"><i class="fa-solid fa-gamepad" aria-hidden="true"></i></span>
                        @endif
                    </td>
                    <td class="casino-game-name">
                        <strong>{{ $item->game_name }}</strong>
                        <span class="casino-source">{{ $item->source === 'local' ? 'Locally managed game' : 'Recorded provider session' }} · {{ number_format($item->players ?? 0) }} <small>(unique)</small></span>
                    </td>
                    <td>
                        @if($item->source === 'local' && $item->record_id)
                            <code class="casino-url" title="{{ $item->play_url }}">{{ \Illuminate\Support\Str::limit((string) $item->play_url, 30) }}</code>
                            <a class="casino-action" href="{{ route('games.open', ['id' => $item->record_id]) }}" target="_blank" rel="noopener noreferrer">Open play page</a>
                        @else
                            <span title="Provider session table has no game URL">—</span>
                        @endif
                    </td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($item->play_time ?? 0) }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $item->audited_events ? number_format($item->bet_count ?? 0) : '—' }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $item->audited_events ? number_format($item->win_count ?? 0) : '—' }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $item->audited_events ? number_format((float) $item->total_bet, 2) : '—' }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $item->audited_events ? number_format((float) $item->total_win, 2) : '—' }}</td>
                    <td>
                        @if($item->audited_events)
                            @php($net = round((float) $item->total_bet - (float) $item->total_win, 2))
                            <span class="casino-net {{ $net > 0 ? 'is-positive' : ($net < 0 ? 'is-negative' : 'is-neutral') }}">
                                {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
                            </span>
                        @else
                            <span class="casino-net is-neutral" title="No qualifying provider bet/win records">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="cms-empty">No casino sessions recorded yet. No locally managed casino-style games match. Add approved games via Games Management.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:15px">{{ $gameList->links() }}</div>
    <div class="cms-secret-hint">
        <strong>Play time</strong> is page opens for local games or recorded launches for session rows, not time in minutes.
        <strong>Bet / Win</strong> are qualifying transaction event counts; <strong>Total bet / Total win</strong> are gross recorded amounts.
        <strong>Admin win / lose</strong> = gross bets − gross wins: green + means a positive gross difference, red − means a negative difference.
        This is not verified operator profit: transaction currency, costs, reversals, and complete settlement data are unavailable.
        Locally managed casino-style games are not linked to provider settlement sessions, so their wager fields are shown as —.
        Session-only records have no authoritative image or play URL in the current schema.
    </div>
</section>

<section class="cms-card">
    <div class="cms-card-head">
        <div><h2>Player-by-game table</h2><p>See which registered member launched each game and their confirmed ledger totals.</p></div>
        <span class="cms-badge">{{ number_format($players->total()) }} player/game pairs</span>
    </div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Member</th><th>Game</th><th>Recorded launches</th><th>Total bet</th><th>Total win</th></tr></thead>
            <tbody>
            @forelse($players as $player)
                <tr>
                    <td>
                        @if($player->name || $player->email)
                            <a href="{{ route('admin.users.view', ['id' => $player->user_id]) }}">{{ $player->name ?: $player->email }}</a>
                            <small style="display:block;color:#7890a6">ID #{{ $player->user_id }}</small>
                        @else
                            <span>Removed account #{{ $player->user_id }}</span>
                        @endif
                    </td>
                    <td>{{ $player->game_name }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($player->launches) }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $player->audited_events ? number_format((float) $player->total_bet, 2) : '—' }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ $player->audited_events ? number_format((float) $player->total_win, 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="cms-empty">No matching player activity records.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $players->links() }}</div>
</section>

@endsection
