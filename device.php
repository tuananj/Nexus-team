<?php
function getDevice($ua) {
    $browser = "Unknown";
    $os = "Unknown";

    $ua = strtolower($ua);

    if (strpos($ua, 'edg') !== false) {
        $browser = "Edge";
    } elseif (strpos($ua, 'chrome') !== false) {
        $browser = "Chrome";
    } elseif (strpos($ua, 'firefox') !== false) {
        $browser = "Firefox";
    } elseif (strpos($ua, 'safari') !== false) {
        $browser = "Safari";
    }

    if (strpos($ua, 'windows') !== false) {
        $os = "Windows";
    } elseif (strpos($ua, 'android') !== false) {
        $os = "Android";
    } elseif (strpos($ua, 'iphone') !== false) {
        $os = "iPhone";
    } elseif (strpos($ua, 'mac') !== false) {
        $os = "MacOS";
    } elseif (strpos($ua, 'linux') !== false) {
        $os = "Linux";
    }

    return trim($browser . " - " . $os);
}
?>