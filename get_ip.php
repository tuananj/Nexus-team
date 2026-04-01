<?php
function getRealIP() {
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];

    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', $_SERVER[$key]);

            foreach ($ipList as $ip) {
                $ip = trim($ip);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    if ($ip === '::1' || $ip === '127.0.0.1') {
                        return 'LOCALHOST';
                    }
                    return $ip;
                }
            }
        }
    }

    return 'UNKNOWN';
}
?>