<?php

namespace NinjaTables\App\Utils;

class Vite
{
    protected static $moduleScripts = [];
    protected static $resourceURL = 'http://localhost:5173/resources/';
    protected static $assetsURL;

    public function __construct()
    {
        self::$assetsURL = NINJA_TABLES_DIR_URL . 'assets/';
    }

    public static function isDev(): bool
    {
        return defined('NINJA_TABLES_DEVELOPMENT') && NINJA_TABLES_DEVELOPMENT;
    }

    public static function enqueueScript(string $handle, string $src, array $deps = [], $ver = false, bool $inFooter = false)
    {
        static::$moduleScripts[] = $handle;
        $src = static::generateSrc($src);
        wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
        static::addModuleToScript();
    }

    private static function generateSrc(string $src): string
    {
        if (!static::isDev()) {
            $manifest = static::getManifest();
            $resourcePath = 'resources/' . $src;
            
            if (isset($manifest[$resourcePath])) {
                $entry = $manifest[$resourcePath];
                
                if (isset($entry['css'])) {
                    foreach ($entry['css'] as $css) {
                        wp_enqueue_style(
                            $css, 
                            static::$assetsURL . $css, 
                            [], 
                            NINJA_TABLES_VERSION
                        );
                    }
                }
                
                return static::$assetsURL . $entry['file'];
            }
        }
        
        return static::$resourceURL . $src;
    }

    private static function getManifest(): array
    {
        static $manifest = null;
        
        if ($manifest === null) {
            $manifestPath = NINJA_TABLES_DIR_PATH . 'assets/manifest.json';
            
            if (!file_exists($manifestPath)) {
                throw new \RuntimeException('Vite manifest not found. Please build assets first.');
            }
            
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
        
        return $manifest;
    }

    private static function addModuleToScript()
    {
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if (in_array($handle, static::$moduleScripts)) {
                return str_replace(
                    '<script ',
                    '<script type="module" ',
                    $tag
                );
            }
            return $tag;
        }, 10, 3);
    }

    public static function getAssetsUrl(): string
    {
        return static::isDev() ? static::$resourceURL : static::$assetsURL;
    }
}