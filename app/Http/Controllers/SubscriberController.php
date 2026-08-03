<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Rules\CorreoSeguro;
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
                // CorreoSeguro se suma a 'email:rfc', no la sustituye: cierra la
                // inyección de cabeceras (CRLF) que la regla de Laravel 11 deja pasar.
                'email' => ['required', 'email:rfc', 'max:255', new CorreoSeguro],
                'name' => 'nullable|string|max:255',
                'source' => 'nullable|string|max:100',
            ], [
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'El email no parece válido.',
            ]);
        } catch (ValidationException $e) {
            RateLimiter::hit($key, 600);
            return response()->json([
                'ok' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        RateLimiter::hit($key, 600);

        // ── SEGURIDAD: respuesta uniforme ────────────────────────────────────
        // Antes el mensaje cambiaba según el estado del correo ("Ya estás
        // suscrito" / "Ya enviamos un correo" / "Gracias por suscribirte"), lo
        // que convertía el formulario en un oráculo: cualquiera podía ir
        // probando direcciones y averiguar cuáles están en la lista. Eso es un
        // dato personal, y además útil para preparar campañas de phishing
        // dirigidas.
        //
        // Ahora la respuesta es SIEMPRE la misma, pase lo que pase por dentro.
        $respuesta = response()->json([
            'ok' => true,
            'message' => 'Gracias. Si tu correo es válido, quedará registrado en el boletín.',
        ]);

        $existing = Subscriber::where('email', $data['email'])->first();

        if ($existing) {
            // Quien se dio de baja NO se reactiva solo: es una decisión
            // explícita suya, y revertirla porque alguien (quizá otra persona)
            // escriba su correo en el formulario sería reinscribirlo sin su
            // consentimiento. Para volver, tiene que pedirlo por contacto.
            //
            // Los estados 'confirmed' y 'pending' tampoco se tocan: ya están
            // en la lista.
            return $respuesta;
        }

        Subscriber::create([
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'source' => $data['source'] ?? 'unknown',
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return $respuesta;
    }
}
