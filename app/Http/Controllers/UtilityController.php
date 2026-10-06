<?php

namespace App\Http\Controllers;

use App\Events\TestEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class UtilityController extends Controller
{
    public function testBroadcast(): Response
    {
        event(new TestEvent('Hello from Laravel!'));

        return response('Event has been sent!');
    }

    public function bonusPopupSeen(): JsonResponse
    {
        session()->forget('show_bonus_popup');

        return response()->json(['status' => 'ok']);
    }
}
