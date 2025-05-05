<?php

namespace NinjaTables\App\Utils;
use Exception;
use NinjaTables\App\App;
use NinjaTables\Framework\Support\Arr;

class  Vite
{

    private array $moduleScripts = [];
    private bool $isScriptFilterAdded = false;
    private string $viteHostProtocol = 'http://';
    private string $viteHost = 'localhost';
    private string $vitePort = '8880';
    private string $resourceDirectory = 'resources/';

    protected static ?Vite $instance = null;
    public ?string $lastJsHandel = null;

    private ?string $manifestPath = "assets/manifest.json";
    private ?array $manifestData = null;

    public function __construct()
    {
        $serverConfigPath = NINJA_TABLES_DIR_PATH . 'config' . DIRECTORY_SEPARATOR . 'vite.json';
        if (file_exists($serverConfigPath)) {
            $serverConfig = json_decode(file_get_contents($serverConfigPath));
            $this->viteHost = $serverConfig->host ?: $this->viteHost;
            $this->viteHostProtocol = $serverConfig->protocol ?: $this->viteHostProtocol;
            $this->vitePort = $serverConfig->port ?: $this->vitePort;
            $this->manifestPath = $serverConfig->manifest_path ?: $this->manifestPath;
            $this->manifestPath = NINJA_TABLES_DIR_PATH . $this->manifestPath;
        }
    }

    private static function getInstance(): Vite
    {
        if (static::$instance === null) {
            static::$instance = new static();
            if (!static::$instance->usingDevMode()) {
                (static::$instance)->loadViteManifest();
            }
        }

        return static::$instance;
    }

    /**
     * @throws Exception
     */
    private function loadViteManifest()
    {
        if (!empty($this->manifestData)) {
            return;
        }



        if (!file_exists($this->manifestPath)) {
            throw new Exception('Vite Manifest Not Found. Run : npm run dev or npm run prod');
        }


        if (!function_exists('get_filesystem_method')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $manifestData = '';
        if (!(false === ($credentials = request_filesystem_credentials(site_url())) || !WP_Filesystem($credentials))) {
            global $wp_filesystem;
            $manifestData = $wp_filesystem->get_contents($this->manifestPath);
        }

        $this->manifestData = json_decode($manifestData, true);
    }


    public static function enqueueScript($handle, $src, $dependency = [], $version = null, $inFooter = false): Vite
    {
        return static::getInstance()->enqueue_script(
            $handle,
            $src,
            $dependency,
            $version,
            $inFooter
        );
    }

    private function enqueue_script($handle, $src, $dependency = [], $version = null, $inFooter = false): Vite
    {

        if (in_array($handle, $this->moduleScripts)) {
            if ($this->usingDevMode()) {
                $callerReference = (debug_backtrace(2)[1]);
                $fileName = explode('plugins', $callerReference['file']);
                $line = $callerReference['line'];
                //throw new \Exception("This handel Has been used'. 'Filename: $fileName Line: $line");
            }
        }

        $this->moduleScripts[] = $handle;

        $this->lastJsHandel = $handle;

        if (!$this->isScriptFilterAdded) {
            add_filter('script_loader_tag', function ($tag, $handle, $src) {
                return $this->addModuleToScript($tag, $handle, $src);
            }, 10, 3);
            $this->isScriptFilterAdded = true;
        }


        if (!$this->usingDevMode()) {
            $assetFile = $this->getFileFromManifest($src);
            $srcPath = $this->getProductionFilePath($assetFile);
        } else {
            $srcPath = $this->getVitePath() . $src;
        }

        if (empty($srcPath)) {
            return $this;
        }

        $version = empty($version) ? NINJA_TABLES_VERSION : $version;

        wp_enqueue_script(
            $handle,
            $srcPath,
            $dependency,
            $version,
            $inFooter
        );
        return $this;
    }

    private function getFileFromManifest($src)
    {

        if (isset($this->manifestData[$this->resourceDirectory . $src])) {
            return $this->manifestData[$this->resourceDirectory . $src];
        }

        if ($this->usingDevMode()) {
            throw new Exception(esc_html($src) . " file not found in vite manifest, Make sure it is in rollupOptions input and build again");
        }

        return '';
    }

    private function getProductionFilePath($file): string
    {

        if (!isset($file['file'])) {
            return '';
        }
        $assetPath = static::getAssetPath();

        $this->ensureChunkCssIsLoaded($file);

        return ($assetPath . $file['file']);
    }

    private function ensureChunkCssIsLoaded($file)
    {
        $assetPath = static::getAssetPath();
        if (isset($file['css']) && is_array($file['css'])) {
            foreach ($file['css'] as $key => $path) {
                wp_enqueue_style(
                    $file['file'] . '_' . $key . '_css',
                    $assetPath . $path,
                    [],
                    NINJA_TABLES_VERSION,
                
                );
            }
        }
    }

    public function with($params)
    {


        if (!is_array($params) || !Arr::isAssoc($params) || empty($this->lastJsHandel)) {
            $this->lastJsHandel = null;
            return;
        }

        foreach ($params as $key => $val) {

            if ($this->lastJsHandel === 'fluent_cart_fluent_products_search_bar_js') {
                //dd($this->lastJsHandel, $key, $val);
            }
            wp_localize_script($this->lastJsHandel, $key, $val);
        }
        $this->lastJsHandel = null;
    }


    public static function enqueueStyle($handle, $src, $dependency = [], $version = null, $media = 'all')
    {
        static::getInstance()->enqueue_style(
            $handle,
            $src,
            $dependency,
            $version,
            $media
        );
    }


    private function enqueue_style($handle, $src, $dependency = [], $version = null, $media = 'all')
    {
        if (!$this->usingDevMode()) {
            $assetFile = (static::$instance)->getFileFromManifest($src);
            $srcPath = $this->getProductionFilePath($assetFile);
        } else {
            $srcPath = $this->getVitePath() . $src;
        }

        if (empty($srcPath)) {
            return;
        }

        $version = empty($version) ? NINJA_TABLES_VERSION : $version;

        wp_enqueue_style(
            $handle,
            $srcPath,
            $dependency,
            $version,
            $media
        );
    }

    public static function enqueueStaticScript($handle, $src, $dependency = [], $version = null, $inFooter = false): Vite
    {
        return static::getInstance()->enqueue_static_script(
            $handle,
            $src,
            $dependency,
            $version,
            $inFooter
        );
    }

    private function enqueue_static_script($handle, $src, $dependency = [], $version = null, $inFooter = false): Vite
    {
        $version = empty($version) ? NINJA_TABLES_VERSION : $version;
        wp_enqueue_script(
            $handle,
            $this->getStaticEnqueuePath($src),
            $dependency,
            $version,
            $inFooter
        );

        return $this;
    }

    private function getStaticEnqueuePath($path): string
    {
        if (!$this->usingDevMode()) {
            $srcPath = $this->get_asset_url($path);
        } else {
            $srcPath = $this->getVitePath() . $path;
        }

        return $srcPath;
    }

    public static function enqueueStaticStyle($handle, $src, $dependency = [], $version = null, $media = 'all')
    {
        static::getInstance()->enqueue_static_style(
            $handle, $src, $dependency, $version, $media
        );
    }

    private function enqueue_static_style($handle, $src, $dependency = [], $version = null, $media = 'all')
    {

        $version = empty($version) ? NINJA_TABLES_VERSION : $version;

        wp_enqueue_style(
            $handle,
            $this->getStaticEnqueuePath($src),
            $dependency,
            $version,
            $media
        );
    }


    public static function underDevelopment(): bool
    {
        return static::getInstance()->usingDevMode();
    }

    public function usingDevMode(): bool
    {
        return App::config()->get('app.env') === 'dev';
    }

    public function getVitePath(): string
    {
        $protocol = rtrim($this->viteHostProtocol, ':/');
        $host = rtrim($this->viteHost, '/');
        $port = $this->vitePort;
        $resource = ltrim($this->resourceDirectory, '/');

        return sprintf('%s://%s:%s/%s', $protocol, $host, $port, $resource);
    }

    public static function getEnqueuePath($path = ''): string
    {
        $vite = static::getInstance();

        if (!$vite->usingDevMode()) {
            $assetFile = $vite->getFileFromManifest($path);
            $srcPath = $vite->getProductionFilePath($assetFile);
        } else {
            $srcPath = $vite->getVitePath() . $path;
        }

        return $srcPath;

    }

    public static function getAssetUrl($path = ''): string
    {
        return esc_url(static::getInstance()->get_asset_url($path));
    }

    private function get_asset_url($path = ''): string
    {
        if (!$this->usingDevMode()) {
            return NINJA_TABLES_DIR_URL . 'assets' . DIRECTORY_SEPARATOR . $path;
        } else {
            return $this->getVitePath() . $path;
        }
    }

    static function getAssetPath(): string
    {
        return App::getInstance()['url.assets'];
    }


    private function addModuleToScript($tag, $handle, $src)
    {


        if (in_array($handle, (static::$instance)->moduleScripts)) {
            return wp_get_script_tag(
                [
                    'src'  => esc_url($src),
                    'type' => 'module',
                    'id'   => $handle . '-js'
                ]
            );
        }
        return $tag;
    }


    public static function enqueueAllScripts(array $scripts, string $handlePrefix, $localizeableData = [])
    {
        $loopCount = 0;
        $isLocalized = false;
        foreach ($scripts as $index => $script) {

            $dependency = [];
            $version = null;
            $inFooter = false;
            $isStatic = false;

            if (is_array($script)) {
                $source = Arr::get($script, 'source');
                $dependency = Arr::get($script, 'dependencies', []);
                $version = Arr::get($script, 'version');
                $inFooter = Arr::get($script, 'inFooter', false);
                $isStatic = Arr::get($script, 'isStatic', false) === true;
            } else {
                $source = $script;
            }

            if ($loopCount < 1) {
                $handle = $handlePrefix;
            } else {
                $handle = $handlePrefix . '_' . $index;
            }

            if ($isStatic) {
                $vite = Vite::enqueueStaticScript(
                    $handle,
                    $source,
                    $dependency,
                    $version,
                    $inFooter
                );
            } else {
                $vite = Vite::enqueueScript(
                    $handle,
                    $source,
                    $dependency,
                    $version,
                    $inFooter
                );
            }

            // Localize the script only once during the first loop iteration,
            // and only if it's not already localized and the script is not static.
            if (($loopCount === 0 || !$isLocalized) && !$isStatic) {
                $vite->with($localizeableData);
                $isLocalized = true;
            }
            $loopCount++;
        }
    }

    public static function enqueueAllStyles(array $styles, string $handlePrefix)
    {
        $loopCount = 0;

        foreach ($styles as $index => $style) {

            $dependency = [];
            $version = null;
            $media = 'all';
            $isStatic = false;

            if (is_array($style)) {
                $source = Arr::get($style, 'source');
                $dependency = Arr::get($style, 'dependencies', []);
                $version = Arr::get($style, 'version');
                $media = Arr::get($style, 'media', false);
                $isStatic = Arr::get($style, 'isStatic', false) === true;
            } else {
                $source = $style;
            }


            if ($loopCount === 0) {
                $handle = $handlePrefix;
            } else {
                $handle = $handlePrefix . '_' . $index;
            }
            $loopCount++;

            if ($isStatic) {
                Vite::enqueueStaticStyle(
                    $handle,
                    $source,
                    $dependency,
                    $version,
                    $media
                );
            } else {
                Vite::enqueueStyle(
                    $handle,
                    $source,
                    $dependency,
                    $version,
                    $media
                );
            }
        }
    }


}