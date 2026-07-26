<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

class SecureHelper
{
    static function ipAddress() : string
    {
        $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        return !empty($ip) ? $ip : 'UNKNOWN';
    }

    static function getDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->headers->get('User-Agent', ''),
            $request->headers->get('Accept-Language', ''),
            $request->headers->get('Accept-Encoding', ''),
        ];

        return hash('sha256', implode('|', $components));
    }
}
