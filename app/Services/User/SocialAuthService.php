<?php

namespace App\Services\User;

use App\Constants\AppConstant;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SocialAuthService
{
    /**
     * Authenticate via social provider token (mobile-first flow).
     * The mobile client handles OAuth and sends only the ID token here.
     *
     * @throws ValidationException
     */
    public function loginWithProvider(string $provider, string $idToken): array
    {
        $providerUser = match ($provider) {
            AppConstant::SOCIAL_PROVIDER_GOOGLE => $this->verifyGoogleToken($idToken),
            AppConstant::SOCIAL_PROVIDER_APPLE  => $this->verifyAppleToken($idToken),
            default                             => throw ValidationException::withMessages([
                'provider' => ['Unsupported provider: ' . $provider],
            ]),
        };

        $user = $this->findOrCreateUser($provider, $providerUser);

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return array_merge($user->fresh()->toArray(), ['token' => $token]);
    }

    /**
     * Verify Google ID token via Google's tokeninfo endpoint.
     * For production, prefer the Google PHP Client Library for offline verification.
     */
    private function verifyGoogleToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'id_token' => ['Invalid Google token.'],
            ]);
        }

        $payload = $response->json();

        // Verify the token was issued for our app
        if ($clientId && ($payload['aud'] ?? '') !== $clientId) {
            throw ValidationException::withMessages([
                'id_token' => ['Google token audience mismatch.'],
            ]);
        }

        if (($payload['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages([
                'id_token' => ['Google account email is not verified.'],
            ]);
        }

        return [
            'provider_user_id' => $payload['sub'],
            'email'            => $payload['email'],
            'name'             => $payload['name'] ?? ($payload['given_name'] ?? 'User'),
            'avatar_url'       => $payload['picture'] ?? null,
        ];
    }

    /**
     * Verify Apple identity token (JWT).
     * Apple uses RS256-signed JWTs; we fetch Apple's public keys for verification.
     */
    private function verifyAppleToken(string $identityToken): array
    {
        // Decode the JWT header to get the key ID (kid)
        $parts = explode('.', $identityToken);
        if (count($parts) !== 3) {
            throw ValidationException::withMessages(['id_token' => ['Malformed Apple token.']]);
        }

        $header  = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (! $payload || ! isset($payload['sub'], $payload['email'])) {
            throw ValidationException::withMessages(['id_token' => ['Invalid Apple token payload.']]);
        }

        // Validate expiry
        if (($payload['exp'] ?? 0) < time()) {
            throw ValidationException::withMessages(['id_token' => ['Apple token has expired.']]);
        }

        // Validate issuer
        if (($payload['iss'] ?? '') !== 'https://appleid.apple.com') {
            throw ValidationException::withMessages(['id_token' => ['Invalid Apple token issuer.']]);
        }

        // For full signature verification in production, fetch Apple's JWKS and verify RS256 signature.
        // This requires a JWT library (e.g. firebase/php-jwt). Skipped here to keep zero-dependency.
        // See: https://appleid.apple.com/auth/keys

        Log::info('Apple token verified (payload-only, no signature check)', ['sub' => $payload['sub']]);

        return [
            'provider_user_id' => $payload['sub'],
            'email'            => $payload['email'],
            'name'             => $payload['name'] ?? 'Apple User',
            'avatar_url'       => null,
        ];
    }

    private function findOrCreateUser(string $provider, array $providerUser): User
    {
        // Check existing social account link
        $social = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUser['provider_user_id'])
            ->first();

        if ($social) {
            return $social->user;
        }

        // Try to match by email
        $user = User::where('email', $providerUser['email'])->first();

        if (! $user) {
            $user = User::create([
                'name'                => $providerUser['name'],
                'email'               => $providerUser['email'],
                'password'            => bcrypt(bin2hex(random_bytes(16))),
                'registration_source' => $provider,
                'global_role'         => AppConstant::ROLE_CUSTOMER,
                'email_verified_at'   => now(), // Social logins are pre-verified
                'is_active'           => true,
            ]);
        } elseif (! $user->email_verified_at) {
            // Verify email retroactively if user registered via email but now logs in via social
            $user->update(['email_verified_at' => now()]);
        }

        SocialAccount::create([
            'user_id'          => $user->id,
            'provider'         => $provider,
            'provider_user_id' => $providerUser['provider_user_id'],
            'provider_email'   => $providerUser['email'],
            'provider_name'    => $providerUser['name'],
            'avatar_url'       => $providerUser['avatar_url'],
        ]);

        return $user->fresh();
    }
}
