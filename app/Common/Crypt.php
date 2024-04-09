<?php

namespace App\Common;

use Throwable;

class Crypt
{
    public static function decrypt(string $payload, string $key): false|string
    {
        try {
            $iv = hex2bin(substr($payload, 0, 24));
            $tag = hex2bin(substr($payload, 24, 32));
            $payload = substr($payload, 56);
            $payload = hex2bin($payload);
            $aad = strtoupper(hash('sha512', $iv));
            $data = openssl_decrypt($payload, '2.16.840.1.101.3.4.1.46', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv, $tag, $aad);
            if (is_string($data)) {
                return $data;
            } else {
                return false;
            }
        } catch (Throwable) {
            return false;
        }
    }

    public static function encrypt(string $data, string $key): false|string
    {
        try {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('2.16.840.1.101.3.4.1.46'));
            $aad = strtoupper(hash('sha512', $iv));
            $tag = null;
            $payload = openssl_encrypt($data, '2.16.840.1.101.3.4.1.46', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv, $tag, $aad);
            $iv = strtoupper(bin2hex($iv)); // 24 characters long
            $tag = strtoupper(bin2hex($tag)); // 32 characters long
            if (strlen($payload) > 0) {
                $payload = strtoupper(bin2hex($payload));
                return $iv . $tag . $payload; // 24 + 32 + {$payload} | payload length equals to $data
            } else {
                return false;
            }
        } catch (Throwable) {
            return false;
        }
    }
}
