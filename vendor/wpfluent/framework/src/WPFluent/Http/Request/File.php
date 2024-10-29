<?php

namespace NinjaTables\Framework\Http\Request;

use RuntimeException;
use NinjaTables\Framework\Support\Util;
use NinjaTables\Framework\Foundation\App;
use NinjaTables\Framework\Validator\Contracts\File as Contract;

class File extends \SplFileInfo implements Contract
{
    /**
     * Original file name.
     *
     * @var string $originalName
     */
    private $originalName;

    /**
     * Mime type of the file.
     *
     * @var string $mimeType
     */
    private $mimeType;

    /**
     * File size in kilobyte.
     *
     * @var int|null $size
     */
    private $size;

    /**
     * File upload error.
     *
     * @var int $error
     */
    private $error;

    /**
     * HTTP File instantiator.
     *
     * @param $path
     * @param $originalName
     * @param null $mimeType
     * @param null $size
     * @param null $error
     */
    public function __construct(
        $path,
        $originalName,
        $mimeType = null,
        $size = null,
        $error = null
    ) {
        $this->originalName = $this->getName($originalName);
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->size = $size;
        $this->error = $error ?: UPLOAD_ERR_OK;

        parent::__construct($path);
    }

    /**
     * @taken from \Symfony\Component\HttpFoundation\File\File
     *
     * Returns locale independent base name of the given path.
     *
     * @param string $name The new file name
     *
     * @return string containing
     */
    public function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr(
            $originalName, $pos + 1
        );

        return $originalName;
    }

    /**
     * Get the file upload error.
     *
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return bool True if the file has been uploaded with HTTP and no error occurred
     */
    public function isValid()
    {
        $isOk = UPLOAD_ERR_OK === $this->getError();

        return $isOk && is_uploaded_file($this->getPathname());
    }

    /**
     * Returns the original file name.
     *
     * @return string The Name Of the file
     */
    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Returns the original file extension.
     *
     * It is extracted from the original file name that was uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string The extension
     */
    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    /**
     * Take an educated guess of the file's extension.
     *
     * @return mixed|null
     */
    public function guessExtension()
    {
        static $defaultExtensions;

        if (is_null($defaultExtensions)) {
            $defaultExtensions = require(__DIR__ . '/FileMimeTypes.php');
        }

        return $defaultExtensions[$this->getMimeType()] ?? null;
    }

    /**
     * Take an educated guess of the file's mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        $path = $this->getPathname();

        if(!function_exists('wp_check_filetype_and_ext')) {
            require_once ABSPATH .'wp-admin/includes/file.php';
        }

        $typeInfo = wp_check_filetype_and_ext($path, $this->originalName);
        
        return $typeInfo['type'];
    }

    /**
     * Get original HTTP file array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name'     => $this->originalName,
            'type'     => $this->mimeType,
            'tmp_name' => $this->getPathname(),
            'error'    => $this->error,
            'size'     => $this->size
        ];
    }

    /**
     * Returns the contents of the file.
     *
     * @return string the contents of the file
     *
     * @throws RuntimeException
     */
    public function getContents()
    {
        $level = error_reporting(0);
        $content = file_get_contents($this->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $content;
    }

    /**
     * Move the file to a new location.
     *
     * @param string $directory Target Path
     * @param string $name Target file name (optional)
     * @return self
     * @throws RuntimeException
     */
    public function move($directory, $name = null)
    {
        $err = '';

        $target = $this->getTargetFile($directory, $name);

        set_error_handler(function ($_, $msg) use (&$err) { $err = $msg; });

        try {
            $renamed = rename($this->getPathname(), $target);
        } finally {
            restore_error_handler();
        }

        if (!$renamed) {
            throw new RuntimeException(
                sprintf(
                    'Could not move the file "%s" to "%s" (%s).',
                    $this->getPathname(), $target, strip_tags($err)
                )
            );
        }

        @chmod($target, 0666 & ~umask());

        return $target;
    }

    /**
     * Save the uploaded file.
     * 
     * @param  string $path
     * @return self (File Object)
     * @throws RuntimeException
     */
    public function save($path = null)
    {
        $path = $this->resolveTargetPath($path);

        return $this->move($path);
    }

    /**
     * Save the uploaded file with a given name.
     * 
     * @param  string $name
     * @param  string $path
     * @return self (File Object)
     * @throws RuntimeException
     */
    public function saveAs($name, $path = null)
    {
        $path = $this->resolveTargetPath($path);

        return $this->move($path, $name);
    }

    /**
     * Check that the given path exists.
     * 
     * @param  string $path
     * @return string
     * @throws RuntimeException
     */
    protected function resolveTargetPath($path)
    {
        if ($this->isAbsolutePath($path)) {
            
            $pieces = array_values(
                array_filter(
                    explode(DIRECTORY_SEPARATOR, $path)
                )
            );

            // Sometimes developers can pass a relative directory like an
            // absolute directory so in that case, If the root is not a
            // real directory then make it relative and resolve it:
            // i.e: 'a/relative/path/looks/like/an/absolute/path'
            
            if (!is_dir(DIRECTORY_SEPARATOR.$pieces[0])) {
                return $this->resolveTargetPath(trim($path, DIRECTORY_SEPARATOR));
            }

            $path = dirname($this->getTargetFile($path));

            if (!is_dir($path)) {
                throw new RuntimeException("Invalid file upload path: {$path}");
            }

            return $path;
        }

        $config = App::make('config');

        $default = $config->get(
            'app.file_upload_path', function() use ($config) {
                $slug = $config->get('app.slug');
                $uploadDir = wp_upload_dir()['basedir'];
                $uploadDir .= DIRECTORY_SEPARATOR .  'wpf_' . $slug;
                return $uploadDir;
            }
        );
        
        $path = rtrim(
            ($default . DIRECTORY_SEPARATOR . $path), DIRECTORY_SEPARATOR
        );

        if (is_file($path)) {
            throw new RuntimeException("Invalid file upload path: {$path}");
        }

        if (!is_dir($path = dirname($this->getTargetFile($path)))) {
            throw new RuntimeException("Invalid file upload path: {$path}");
        }
        
        return $path;
    }

    /**
     * Check if given path is absolute.
     * 
     * @param  string $path
     * @return boolean
     */
    function isAbsolutePath($path)
    {
        if (!$path) return false;

        // For Unix-like systems
        if (DIRECTORY_SEPARATOR === '/') {
            return $path[0] === '/';
        }

        // For Windows
        if (DIRECTORY_SEPARATOR === '\\') {
            return preg_match(
                '/^[a-zA-Z]:\\\\/', $path
            ) || substr($path, 0, 2) === '\\\\';
        }

        return false;
    }

    /**
     * Get the URL from the file path.
     * 
     * @param  string $path
     * @return string
     */
    public function url($path = '')
    {
        return Util::pathToUrl($path ?: $this->getPathname());
    }

    /**
     * Get the target file name to move (full path).
     *
     * @param string $directory Target Path
     * @param string $name Target file name (optional)
     * @return self
     * @throws RuntimeException
     */
    protected function getTargetFile($directory, $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new RuntimeException(
                    sprintf('Unable to create the "%s" directory.', $directory)
                );
            }
        } elseif (!is_writable($directory)) {
            throw new RuntimeException(
                sprintf('Unable to write in the "%s" directory.', $directory)
            );
        }

        $target = rtrim($directory, "/\\") . DIRECTORY_SEPARATOR . (
            null === $name ? $this->originalName : $this->getName($name)
        );

        return new self($target, false);
    }
}
