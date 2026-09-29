<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $user = $request->user();

        PushSubscription::query()->updateOrCreate(
            [
                'endpoint' => $data['endpoint'],
            ],
            [
                'user_id' => $user->id,
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'invalidado_em' => null,
            ]
        );

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Notificações ativadas com sucesso neste dispositivo.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $subscription = $request->user()->pushSubscriptions()
            ->where('endpoint', $data['endpoint'])
            ->first();

        if ($subscription) {
            $subscription->invalidar();
        }

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Notificações desativadas neste dispositivo.',
        ]);
    }
}