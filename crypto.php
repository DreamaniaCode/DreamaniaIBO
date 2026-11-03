<?php
/**
 * Dreamania IBO Pro - Cryptographic & Obfuscation Layer
 * Matches Dalvik class t2.g in SanSatUltraV10.apk
 */

declare(strict_types=1);

class CryptoHelper {
    public const ALPHABET = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

    /**
     * Decrypts an incoming client payload string.
     * Matches t2.g.d(String str)
     */
    public static function deobfuscate(string $str): string {
        $str = trim($str);
        if ($str === '') {
            return '';
        }

        // If string already starts with JSON bracket/brace, return as-is
        $firstChar = $str[0];
        if ($firstChar === '{' || $firstChar === '[') {
            return $str;
        }

        if (strlen($str) < 3) {
            return $str;
        }

        $char1 = $str[strlen($str) - 2];
        $char2 = $str[strlen($str) - 1];

        $pos = strpos(self::ALPHABET, $char1);
        $len = strpos(self::ALPHABET, $char2);

        if ($pos === false || $len === false) {
            // Fallback base64 decode if not matching alphabet
            $decoded = base64_decode($str, true);
            return $decoded !== false ? $decoded : $str;
        }

        $body = substr($str, 0, -2);
        if ($pos > strlen($body)) {
            $pos = strlen($body);
        }

        $part1 = substr($body, 0, $pos);
        $part2 = substr($body, $pos + $len);
        $cleanBase64 = $part1 . $part2;

        $decoded = base64_decode($cleanBase64, true);
        return $decoded !== false ? trim($decoded) : $str;
    }

    /**
     * Obfuscates an outgoing payload string.
     * Matches t2.g.h & MainActivity.W
     */
    public static function obfuscate(string $str): string {
        $b64 = trim(base64_encode($str));
        $len = random_int(1, 15);
        $maxPos = min(42, strlen($b64));
        $pos = random_int(0, max(0, $maxPos));

        $noise = '';
        $alphaLen = strlen(self::ALPHABET);
        for ($i = 0; $i < $len; $i++) {
            $noise .= self::ALPHABET[random_int(0, $alphaLen - 1)];
        }

        $injected = substr($b64, 0, $pos) . $noise . substr($b64, $pos);
        $injected .= self::ALPHABET[$pos];
        $injected .= self::ALPHABET[$len];

        return $injected;
    }
}

