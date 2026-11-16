@extends('layouts.admin')
@section('title', 'Casino game activity')
@section('page_title', 'Casino reports')
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
        <div><h2>Casino game table</h2><p>One row per game name in the existing casino session ledger.</p></div>
        <span class="cms-badge">{{ number_format($games->total()) }} games</span>
    </div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Game</th><th>Views / players</th><th>Times played / launched</th><th>Total bet</th><th>Total win</th></tr></thead>
            <tbody>
            @forelse($games as $game)
                <tr>
                    <td><strong>{{ $game->game_name }}</strong></td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($game->players) }} <small style="color:#7d94ad">(unique)</small></td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($game->launches) }}</td>
                    <td style="font-variant-numeric:tabular-nums">
                        @if($game->audited_events)
                            {{ number_format((float) $game->total_bet, 2) }}
                        @else
                            <span title="No confirmed provider bet/win records">—</span>
                        @endif
                    </td>
                    <td style="font-variant-numeric:tabular-nums">
                        @if($game->audited_events)
                            {{ number_format((float) $game->total_win, 2) }}
                        @else
                            <span title="No confirmed provider bet/win records">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="cms-empty">No casino sessions recorded yet. Games cannot be counted without provider session data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $games->links() }}</div>
    <div class="cms-secret-hint">“Views / players” means distinct user IDs observed in sessions, not anonymous page views. “Times played” means recorded session launches, not completed casino rounds.</div>
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

<section class="cms-card">
    <div class="cms-card-head">
        <div><h2>Locally managed casino-style games</h2><p>Games added in the regular CMS that match casino, slot, roulette, poker, blackjack or baccarat categories/names.</p></div>
        <span class="cms-badge">{{ number_format($localGames->total()) }} managed games</span>
    </div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Game</th><th>Category</th><th>Recorded page opens</th><th>Known players</th><th>Total bet</th><th>Total win</th><th>Manage</th></tr></thead>
            <tbody>
            @forelse($localGames as $game)
                <tr>
                    <td><strong>{{ $game->name }}</strong></td>
                    <td>{{ $game->category?->name ?: 'Uncategorized' }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($game->play_time ?? 0) }}</td>
                    <td style="font-variant-numeric:tabular-nums">{{ number_format($game->game_opens_count) }}</td>
                    <td>—</td><td>—</td>
                    <td><a class="cms-button" href="{{ route('admin.games.edit', $game->id) }}">View / edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="cms-empty">No locally managed casino-style games found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $localGames->links() }}</div>
    <div class="cms-secret-hint">Local games are not linked to provider settlement sessions. Their bet and win totals remain unavailable. The regular public GameMonetize catalog still excludes casino titles; this admin report does not change that filtering.</div>
</section>
@endsection
