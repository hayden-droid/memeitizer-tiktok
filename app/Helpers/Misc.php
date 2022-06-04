<?php
namespace App\Helpers;

class Misc {
    static public function getCursor(): int {
        return isset($_GET['cursor']) && is_numeric($_GET['cursor']) ? (int) $_GET['cursor'] : 0;
    }

    static public function getTtwid(): string {
        return isset($_GET['cursor']) ? $_GET['cursor'] : '';
    }

    static public function url(string $endpoint = ''): string {
        return self::env('APP_URL', '') . $endpoint;
    }

    static public function env(string $key, $default_value) {
        return $_ENV[$key] ?? $default_value;
    }

    /**
     * Returns absolute path for view
     */
    static public function getView(string $template): string {
        return __DIR__ . "/../../views/{$template}.latte";
    }
}
