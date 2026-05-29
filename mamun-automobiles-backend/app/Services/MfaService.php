<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class MfaService
{
    /**
     * Generate a new secret key for TOTP (Base32 format).
     */
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[rand(0, 31)];
        }
        return $secret;
    }

    /**
     * Get QR Code URL for Google Authenticator.
     */
    public function getQrCodeUrl(string $companyName, string $email, string $secret): string
    {
        return 'otpauth://totp/' . rawurlencode($companyName) . ':' . rawurlencode($email) . '?secret=' . $secret . '&issuer=' . rawurlencode($companyName);
    }

    /**
     * Verify a TOTP code against a secret.
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->calculateCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper to compute the HMAC-SHA1 TOTP code from secret and time slice.
     */
    protected function calculateCode(string $secret, int $timeSlice): string
    {
        $secretUpper = strtoupper($secret);
        $secretBytes = $this->base32Decode($secretUpper);
        
        $timeHex = str_pad(dechex($timeSlice), 16, '0', STR_PAD_LEFT);
        $timeBytes = pack('H*', $timeHex);
        
        $hash = hash_hmac('sha1', $timeBytes, $secretBytes, true);
        
        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);
        
        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $otp = $value % 1000000;
        
        return str_pad((string)$otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Helper to decode Base32 characters.
     */
    protected function base32Decode(string $base32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($chars));
        
        $binary = '';
        $base32 = str_replace('=', '', $base32);
        
        foreach (str_split($base32) as $char) {
            if (!isset($map[$char])) {
                continue;
            }
            $binary .= str_pad(decbin($map[$char]), 5, '0', STR_PAD_LEFT);
        }
        
        $bytes = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                break;
            }
            $bytes .= chr(bindec($chunk));
        }
        
        return $bytes;
    }

    /**
     * Evaluate the MFA grace period status for a user.
     * Returns: 'grace_day_0', 'grace_day_1', 'grace_day_2', 'locked' (day 3), or 'configured' (mfa active).
     */
    public function checkMfaStatus(User $user): string
    {
        $mfa = DB::table('user_mfa_secrets')->where('user_id', $user->id)->first();
        if ($mfa && $mfa->is_enabled) {
            return 'configured';
        }

        // If no secret, start grace period tracking
        if (!$mfa) {
            DB::table('user_mfa_secrets')->insert([
                'user_id' => $user->id,
                'secret_key' => Crypt::encryptString($this->generateSecret()),
                'backup_codes' => Crypt::encryptString(json_encode($this->generateBackupCodes())),
                'is_enabled' => false,
                'grace_started_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return 'grace_day_0';
        }

        if (is_null($mfa->grace_started_at)) {
            // Update grace started at
            DB::table('user_mfa_secrets')->where('id', $mfa->id)->update([
                'grace_started_at' => now()
            ]);
            return 'grace_day_0';
        }

        $started = Carbon::parse($mfa->grace_started_at);
        $diffInDays = $started->diffInDays(Carbon::now());

        if ($diffInDays >= 3) {
            return 'locked';
        }
        if ($diffInDays == 2) {
            return 'grace_day_2';
        }
        if ($diffInDays == 1) {
            return 'grace_day_1';
        }
        return 'grace_day_0';
    }

    /**
     * Generate backup recovery codes.
     */
    public function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = rand(100000, 999999) . '-' . rand(100000, 999999);
        }
        return $codes;
    }
}
