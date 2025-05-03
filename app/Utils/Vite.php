<?php

namespace NinjaTables\App\Utils;

class Vite
{
    protected static $moduleScripts = [];
    protected static $resourceURL = 'http://localhost:5173/';  // Changed: removed /resources/
    protected static $assetsURL;
    protected static $manifestCache = null;
    protected static $clientLoaded = false;

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
        try {
            if (static::isDev()) {
                // Check if dev server is running
                $devServerStatus = @file_get_contents(static::$resourceURL . '@vite/client');
                if ($devServerStatus === false) {
                    throw new \RuntimeException('Vite dev server not running. Please start it with npm run dev');
                }

                if (!static::$clientLoaded) {
                    // Load Vite client first
                    wp_enqueue_script(
                        'vite-client',
                        static::$resourceURL . '@vite/client',
                        [],
                        null,
                        false
                    );
                    static::$moduleScripts[] = 'vite-client';
                    static::$clientLoaded = true;
                }

                // Then load main script
                wp_enqueue_script(
                    $handle,
                    static::$resourceURL . 'resources/' . $src,
                    array_merge(['vite-client'], $deps),
                    null,
                    $inFooter
                );
                static::$moduleScripts[] = $handle;
            } else {
                static::$moduleScripts[] = $handle;
                $src = static::generateProductionSrc($src);
                wp_enqueue_script($handle, $src, $deps, $ver ?: NINJA_TABLES_VERSION, $inFooter);
            }
            
            static::addModuleToScript();
            
        } catch (\Exception $e) {
            error_log('NinjaTables Vite Error: ' . $e->getMessage());
            // Fallback to production assets if dev server fails
            static::$moduleScripts[] = $handle;
            $src = static::generateProductionSrc($src);
            wp_enqueue_script($handle, $src, $deps, $ver ?: NINJA_TABLES_VERSION, $inFooter);
        }
    }

    public static function enqueueStyle(string $handle, string $src, array $deps = [], $ver = false, string $media = 'all')
    {
        try {
            if (static::isDev()) {
                // In dev, CSS is handled by Vite
                return;
            }

            $manifest = static::getManifest();
            $resourcePath = 'resources/' . $src;
            
            if (isset($manifest[$resourcePath]['css'])) {
                foreach ($manifest[$resourcePath]['css'] as $css) {
                    wp_enqueue_style(
                        $handle . '-' . basename($css, '.css'),
                        static::$assetsURL . $css,
                        $deps,
                        $ver ?: NINJA_TABLES_VERSION,
                        $media
                    );
                }
            }
        } catch (\Exception $e) {
            error_log('NinjaTables Vite Style Error: ' . $e->getMessage());
        }
    }

    private static function generateProductionSrc(string $src): string
    {
        $manifest = static::getManifest();
        $resourcePath = 'resources/' . $src;
        
        if (isset($manifest[$resourcePath])) {
            $entry = $manifest[$resourcePath];
            
            if (isset($entry['css'])) {
                foreach ($entry['css'] as $css) {
                    wp_enqueue_style(
                        'ninjatable-' . basename($css, '.css'), 
                        static::$assetsURL . $css, 
                        [], 
                        NINJA_TABLES_VERSION
                    );
                }
            }
            
            return static::$assetsURL . $entry['file'];
        }

        throw new \RuntimeException("Entry {$resourcePath} not found in Vite manifest");
    }

    private static function getManifest(): array
    {
        if (static::$manifestCache === null) {
            $manifestPath = NINJA_TABLES_DIR_PATH . 'assets/manifest.json';
            
            if (!file_exists($manifestPath)) {
                throw new \RuntimeException('Vite manifest not found. Please build assets first with npm run build');
            }
            
            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid Vite manifest JSON');
            }
            
            static::$manifestCache = $manifest;
        }
        
        return static::$manifestCache;
    }

    private static function addModuleToScript()
    {
        static $filterAdded = false;
        
        if (!$filterAdded) {
            add_filter('script_loader_tag', function ($tag, $handle, $src) {
                if (in_array($handle, static::$moduleScripts)) {
                    // Force module type and remove any existing type
                    $tag = preg_replace('/<script.*?type=[\'"].*?[\'"]/', '<script', $tag);
                    return str_replace(
                        '<script',
                        '<script type="module"',
                        $tag
                    );
                }
                return $tag;
            }, 10, 3);
            
            $filterAdded = true;
        }
    }

    public static function getAssetsUrl(): string
    {
        return static::isDev() ? static::$resourceURL : static::$assetsURL;
    }

    public static function copyAssets()
    {
        if (!static::isDev()) {
            // Copy libs and images
            $libsSource = NINJA_TABLES_DIR_PATH . 'resources/libs';
            $libsDest = NINJA_TABLES_DIR_PATH . 'assets/libs';
            static::recursiveCopy($libsSource, $libsDest);

            $imgSource = NINJA_TABLES_DIR_PATH . 'resources/img';
            $imgDest = NINJA_TABLES_DIR_PATH . 'assets/img';
            static::recursiveCopy($imgSource, $imgDest);

            // Generate RTL CSS
            static::generateRtlCss([
                'ninja-tables-vendor.css' => 'ninja-tables-vendor-rtl.css',
                'ninjatables-public.css' => 'ninjatables-public-rtl.css'
            ]);
        }
    }

    private static function recursiveCopy($src, $dst) 
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    static::recursiveCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function generateRtlCss($files)
    {
        foreach ($files as $source => $target) {
            $sourcePath = NINJA_TABLES_DIR_PATH . 'assets/css/' . $source;
            $targetPath = NINJA_TABLES_DIR_PATH . 'assets/css/' . $target;
            
            if (file_exists($sourcePath)) {
                exec("rtlcss {$sourcePath} {$targetPath}");
            }
        }
    }
}