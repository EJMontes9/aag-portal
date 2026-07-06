<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SubscriberController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Rate limit: 5 intentos por IP cada 10 minutos
        $key = 'subscribe:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'ok' => false,
                'message' => "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.",
            ], 429);
        }

        // Honeypot: si llenan el campo 'website', es bot
        if ($request->filled('website')) {
            RateLimiter::hit($key, 600);
            return response()->json(['ok' => true, 'message' => 'Gracias por suscribirte.']);
        }

        try {
            $data = $request->validate([
                'email' => 'required|email:rfc|max:255',
                'name' => 'nullable|string|max:255',
                'source' => 'nullable|string|max:100',
            ], [
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'El email no parece valido.',
            ]);
        } catch (ValidationException $e) {
            RateLimiter::hit($key, 600);
            return response()->json([
                'ok' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        RateLimiter::hit($key, 600);

        // Buscar existente
        $existing = Subscriber::where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->status === 'confirmed') {
                return response()->json([
                    'ok' => true,
                    'message' => 'Ya estas suscrito al boletin. Gracias por seguirnos.',
                ]);
            }
            if ($existing->status === 'unsubscribed') {
                $existing->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
                return response()->json([
                    'ok' => true,
                    'message' => 'Bienvenido de vuelta. Te reactivamos al boletin.',
                ]);
            }
            // pending -> reenviar / confirmar
            return response()->json([
                'ok' => true,
                'message' => 'Ya enviamos un correo de confirmacion a este email.',
            ]);
        }

        // Crear nuevo. Por ahora marcamos 'confirmed' directo
        // (cuando configures mail real puedes cambiar a 'pending' y enviar correo).
        Subscriber::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'source' => $data['source'] ?? 'unknown',
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json([
            'ok' => true,
            'message' => '¡Gracias por suscribirte al boletin AAG!',
        ]);
    }
}
