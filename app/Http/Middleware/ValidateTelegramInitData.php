<?php

namespace App\Http\Middleware;

use App\Models\TelegramUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates Telegram Mini App initData HMAC signature.
 *
 * Apply to routes that need authenticated Telegram user:
 *   Route::middleware('telegram.auth')->group(function () { ... });
 *
 * Docs: https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 */
class ValidateTelegramInitData
{
    public function handle(Request $request, Closure $next): Response
    {
        $initData = $request->header('X-Telegram-Init-Data')
            ?? $request->input('_tma_init_data');

        if (! $initData) {
            return response()->json(['error' => 'Missing Telegram init data'], 401);
        }

        if (! $this->validate($initData)) {
            return response()->json(['error' => 'Invalid Telegram init data'], 403);
        }

        // Parse and store user on request for downstream use
        parse_str($initData, $params);
        if (isset($params['user'])) {
            $userData = json_decode($params['user'], true);
            $user = TelegramUser::updateOrCreate(
              ['telegram_id' => $userData['id']],
              [
                  'first_name'    => $userData['first_name'] ?? '',
                  'last_name'     => $userData['last_name'] ?? null,
                  'username'      => $userData['username'] ?? null,
                  'language_code' => $userData['language_code'] ?? null,
                  'is_premium'    => $userData['is_premium'] ?? false,
              ]
          );
          $request->attributes->set('tg_user', $user);
        }

        return $next($request);
    }

    private function validate(string $initData): bool
    {
        $botToken = config('services.telegram.bot_token');

        if (! $botToken) {
            // In local dev without a token, skip validation
            return app()->isLocal();
        }

        parse_str($initData, $params);

        $hash = $params['hash'] ?? null;
        if (! $hash) {
            return false;
        }

        unset($params['hash']);
        ksort($params);

        $dataCheckString = collect($params)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $expectedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($expectedHash, $hash);
    }
}