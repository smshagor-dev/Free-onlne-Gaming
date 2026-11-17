@extends('layouts.app')
@section('title', 'My Casino Game Activity | Free Games')
@section('styles')
<style>
.player-casino-activity {color:#ecf6ff;max-width:1250px;margin:0 auto}
.player-casino-activity h1{font-size:clamp(25px,3vw,34px);font-weight:850;margin:0 0 6px}
.player-casino-activity p{font-size:13px;color:#adc6de;margin:0 0 20px}
.player-casino-table-wrap{overflow-x:auto;border:1px solid #355270;border-radius:12px;background:#10253c}
.player-casino-table{width:100%;border-collapse:collapse;min-width:740px;font-size:13px}
.player-casino-table th,.player-casino-table td{padding:14px 17px;text-align:left;border-bottom:1px solid #2e4661}
.player-casino-table th{background:#182f4b;color:#c8dff5;font-weight:800}
.player-casino-table tr:last-child td{border-bottom:0}
.player-casino-muted{color:#9dbad6}
</style>
@endsection
@section('content')
<div class="player-casino-activity">
    <h1>My casino game activity</h1>
    <p>Your own recorded game sessions only. Bet/win amounts appear only when approved provider-linked transaction records exist; the existing ledger does not identify currency.</p>
    <div class="player-casino-table-wrap">
        <table class="player-casino-table">
            <thead><tr><th>Game</th><th>Recorded launches</th><th>Bet events</th><th>Win events</th><th>Total bet</th><th>Total win</th></tr></thead>
            <tbody>
                @forelse($games as $game)
                    <tr>
                        <td><strong>{{ $game->game_name }}</strong></td>
                        <td>{{ number_format($game->launches) }}</td>
                        <td>{{ $game->audited_events ? number_format($game->bet_count ?? 0) : '—' }}</td>
                        <td>{{ $game->audited_events ? number_format($game->win_count ?? 0) : '—' }}</td>
                        <td>{{ $game->audited_events ? number_format((float) $game->total_bet, 2) : '—' }}</td>
                        <td>{{ $game->audited_events ? number_format((float) $game->total_win, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="player-casino-muted">No casino session activity has been recorded for your account yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px">{{ $games->links() }}</div>
    <p style="margin-top:18px">Recorded launches are not completed rounds; totals are unlabelled provider units, not verified cash settlements.</p>
</div>
@endsection
