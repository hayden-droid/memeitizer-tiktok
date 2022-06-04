<?php
namespace App\Helpers;

use App\Cache\JSONCache;
use App\Cache\RedisCache;

class Wrappers {
    /**
     * Setup of Latte template engine
     */
    static public function latte(): \Latte\Engine {
        $latte = new \Latte\Engine;
        $cache_path = Misc::env('LATTE_CACHE', __DIR__ . '/../../cache/latte');
        $latte->setTempDirectory($cache_path);

        // -- CUSTOM FUNCTIONS -- //
        // Get URL with optional endpoint
        $latte->addFunction('path', function (string $endpoint = ''): string {
            return Misc::url($endpoint);
        });
        // Version being used
        $latte->addFunction('version', function (): string {
            return \Composer\InstalledVersions::getVersion('pablouser1/proxitok');
        });
        $latte->addFunction('theme', function(): string {
            return Cookies::theme();
        });
        // https://stackoverflow.com/a/36365553
        $latte->addFunction('number', function (float $x) {
            if($x > 1000) {
                $x_number_format = number_format($x);
                $x_array = explode(',', $x_number_format);
                $x_parts = array('K', 'M', 'B', 'T');
                $x_count_parts = count($x_array) - 1;
                $x_display = $x;
                $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
                $x_display .= $x_parts[$x_count_parts - 1];
                return $x_display;
            }
            return $x;
        });
        return $latte;
    }

    /**
     * Setup of TikTok Api wrapper
     */
    static public function api(): \TikScraper\Api {
        $options = [
            'use_test_endpoints' => Misc::env('API_TEST_ENDPOINTS', false),
            'signer' => [
                'remote_url' => Misc::env('API_SIGNER_URL', ''),
                'browser_url' => Misc::env('API_BROWSER_URL', ''),
                'close_when_done' => false
            ]
        ];
        // Cache config
        $cacheEngine = false;
        if (isset($_ENV['API_CACHE'])) {
            switch ($_ENV['API_CACHE']) {
                case 'json':
                    $cacheEngine = new JSONCache();
                    break;
                case 'redis':
                    if (!(isset($_ENV['REDIS_URL']) || isset($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']))) {
                        throw new \Exception('You need to set REDIS_URL or REDIS_HOST and REDIS_PORT to use Redis Cache!');
                    }

                    if (isset($_ENV['REDIS_URL'])) {
                        $url = parse_url($_ENV['REDIS_URL']);
                        $host = $url['host'];
                        $port = $url['port'];
                        $password = $url['pass'] ?? null;
                    } else {
                        $host = $_ENV['REDIS_HOST'];
                        $port = (int) $_ENV['REDIS_PORT'];
                        $password = isset($_ENV['REDIS_PASSWORD']) ? $_ENV['REDIS_PASSWORD'] : null;
                    }
                    $cacheEngine = new RedisCache($host, $port, $password);
                    break;
            }
        }

        // Legacy mode
        $legacy = Misc::env('API_FORCE_LEGACY', false) || isset($_COOKIE['api-legacy']) && $_COOKIE['api-legacy'] === 'on';
        return new \TikScraper\Api($options, $legacy, $cacheEngine);
    }
}
