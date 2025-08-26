<?php

namespace JustusTheis\Registry\Services;

use Illuminate\Support\Facades\Crypt;

class RegistryEncryption
{
    /*
    |--------------------------------------------------------------------------
    | Registry Encryption Service
    |--------------------------------------------------------------------------
    |
    | Provides encryption and decryption services for registry values using
    | Laravel's built-in encryption facilities with secure serialization
    | and null value handling.
    |
    */

    /**
     * Encrypt a value for secure storage in the registry.
     *
     * Serializes the value and encrypts it using Laravel's Crypt facade.
     * Null values are converted to '__NULL__' marker before encryption.
     *
     * @param  mixed  $value The value to encrypt (null by default)
     * @return string The encrypted string
     */
    public static function encrypt(mixed $value = null): string
    {
        $value = $value ? $value : '__NULL__';

        return Crypt::encryptString(serialize($value));
    }

    /**
     * Decrypt an encrypted registry value.
     *
     * Decrypts the encrypted string and unserializes the value.
     * Converts '__NULL__' marker back to actual null value.
     *
     * @param  string $encryptedValue The encrypted string to decrypt
     * @return mixed  The decrypted and unserialized value
     */
    public static function decrypt(string $encryptedValue)
    {
        $decrypted = unserialize(Crypt::decryptString($encryptedValue));

        return $decrypted === '__NULL__' ? null : $decrypted;
    }
}
