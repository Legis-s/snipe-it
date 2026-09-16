<?php

namespace App\Models\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

trait HasBitrixToken
{
    public function setBitrixToken(string $token): void
    {
        $this->bitrix_token = Crypt::encryptString($token);
    }

    public function decryptedBitrixToken(): ?string
    {
        if (! $this->bitrix_token) {
            return null;
        }

        try {
            return Crypt::decryptString($this->bitrix_token);
        } catch (DecryptException) {
            return null;
        }
    }
}
