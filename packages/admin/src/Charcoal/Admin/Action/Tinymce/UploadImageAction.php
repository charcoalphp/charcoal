<?php

namespace Charcoal\Admin\Action\Tinymce;

use Charcoal\Admin\AdminAction;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Action : Upload an image and return path.
 */
class UploadImageAction extends AdminAction
{
    public const DEFAULT_PUBLIC_ACCESS = true;
    public const DEFAULT_UPLOAD_PATH = 'uploads/tinymce/';
    public const DEFAULT_FILESYSTEM = 'public';
    public const DEFAULT_OVERWRITE = true;

    /**
     * Whether uploaded files should be accessible from the web root.
     */
    private bool $publicAccess = self::DEFAULT_PUBLIC_ACCESS;

    /**
     * The relative path to the storage directory.
     */
    private string $uploadPath = self::DEFAULT_UPLOAD_PATH;

    /**
     * Whether existing destinations should be overwritten.
     */
    private bool $overwrite = self::DEFAULT_OVERWRITE;

    /**
     * The base path for the Charcoal installation.
     *
     * @var string
     */
    private $basePath;

    /**
     * The path to the public / web directory.
     *
     * @var string
     */
    private $publicPath;

    private ?string $uploadedPath = null;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath   = $container['config']['base_path'];
        $this->publicPath = $container['config']['public_path'];
    }

    /**
     * Gets a psr7 request and response and returns a response.
     *
     * Called from `__invoke()` as the first thing.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $path = $request->getParam('upload_path');

        if ((bool) $path) {
            $this->setUploadPath($path);
        }

        $this->uploadedPath = $this->fileUpload($_FILES['file']);

        $this->setSuccess((bool) $this->uploadedPath);

        return $response;
    }

    /**
     * Upload to filesystem.
     *
     * @param array $fileData The file data (from $_FILES, typically).
     * @throws \InvalidArgumentException If the FILES data argument is missing `name` or `tmp_name`.
     */
    public function fileUpload(array $fileData): string
    {
        if (!isset($fileData['name'])) {
            throw new \InvalidArgumentException(
                'File data is invalid'
            );
        }

        $target = $this->uploadTarget($fileData['name']);

        $ret = move_uploaded_file($fileData['tmp_name'], $target);

        if ($ret === false) {
            $this->logger->warning(sprintf('Could not upload file %s', $target));

            return '';
        } else {
            $this->logger->notice(sprintf('File %s uploaded succesfully', $target));
            $basePath = $this->basePath();

            return str_replace($basePath, '', $target);
        }
    }

    /**
     * @param string $filename Optional. The filename to save. If unset, a default filename will be generated.
     * @throws \Exception If the target path is not writeable.
     */
    public function uploadTarget($filename = null): string
    {
        $basePath = $this->basePath();

        $dir      = $basePath . DIRECTORY_SEPARATOR . $this->uploadPath();
        $filename = ($filename) ? $this->sanitizeFilename($filename) : 'unnamed_file';

        if (!file_exists($dir)) {
            // @todo: Feedback
            $this->logger->debug(
                'Path does not exist. Attempting to create path ' . $dir . '.',
                [static::class . '::' . __FUNCTION__]
            );
            mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            throw new \Exception(
                'Error: upload directory is not writeable'
            );
        }

        $target = $dir . $filename;

        if ($this->fileExists($target)) {
            if ($this->overwrite()) {
                return $target;
            } else {
                $target = $dir . $this->generateUniqueFilename($filename);
                while ($this->fileExists($target)) {
                    $target = $dir . $this->generateUniqueFilename($filename);
                }
            }
        }

        return $target;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * PHP built-in's `file_exists` is only case-insensitive on case-insensitive filesystem (such as Windows)
     * This method allows to have the same validation across different platforms / filesystem.
     *
     * @param  string  $file            The full file to check.
     * @param  boolean $caseInsensitive Case-insensitive by default.
     */
    public function fileExists($file, $caseInsensitive = true): bool
    {
        if (!$this->isAbsolutePath($file)) {
            $file = $this->basePath() . DIRECTORY_SEPARATOR . $file;
        }

        if (file_exists($file)) {
            return true;
        }

        if ($caseInsensitive === false) {
            return false;
        }

        $files = glob(dirname($file) . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT);
        if ($files) {
            $pattern = preg_quote($file, '#');
            foreach ($files as $f) {
                if (preg_match("#{$pattern}#i", $f)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the given file path is am absolute path.
     *
     * Note: Adapted from symfony\filesystem.
     *
     * @see https://github.com/symfony/symfony/blob/v3.2.2/LICENSE
     *
     * @param  string $file A file path.
     * @return boolean Returns TRUE if the given path is absolute. Otherwise, returns FALSE.
     */
    protected function isAbsolutePath($file): bool
    {
        return strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3
                && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && strspn($file, '/\\', 2, 1))
            || null !== parse_url($file, PHP_URL_SCHEME);
    }

    /**
     * Sanitize a filename by removing characters from a blacklist and escaping dot.
     *
     * @param string $filename The filename to sanitize.
     * @return string The sanitized filename.
     */
    public function sanitizeFilename($filename): string
    {
        // Remove blacklisted caharacters
        $blacklist = ['/', '\\', '\0', '*', ':', '?', '"', '<', '>', '|', '#', '&', '!', '`', ' '];
        $filename  = str_replace($blacklist, '_', $filename);

        // Avoid hidden file
        $filename = ltrim($filename, '.');

        return $filename;
    }

    /**
     * Generate a unique filename.
     *
     * @param  string|array $filename The filename to alter.
     * @throws \InvalidArgumentException If the given filename is invalid.
     */
    public function generateUniqueFilename($filename): string
    {
        if (!is_string($filename) && !is_array($filename)) {
            throw new \InvalidArgumentException(sprintf(
                'The target must be a string or an array from [pathfino()], received %s',
                (get_debug_type($filename))
            ));
        }

        $info = is_string($filename) ? pathinfo($filename) : $filename;

        $filename = $info['filename'] . '-' . uniqid();

        if (isset($info['extension']) && $info['extension']) {
            $filename .= '.' . $info['extension'];
        }

        return $filename;
    }

    public function uploadPath(): string
    {
        return $this->uploadPath;
    }

    /**
     * Set the destination (directory) where uploaded files are stored.
     *
     * The path must be relative to the {@see self::basePath()},
     *
     * @param string $path The destination directory, relative to project's root.
     * @throws \InvalidArgumentException If the path is not a string.
     */
    public function setUploadPath($path): static
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                'Upload path must be a string'
            );
        }

        // Sanitize upload path (force trailing slash)
        $this->uploadPath = rtrim($path, '/') . '/';

        return $this;
    }

    /**
     * Set whether uploaded files should be publicly available.
     *
     * @param boolean $public Whether uploaded files should be accessible (TRUE) or not (FALSE) from the web root.
     */
    public function setPublicAccess($public): static
    {
        $this->publicAccess = (bool) $public;

        return $this;
    }

    /**
     * Determine if uploaded files should be publicly available.
     */
    public function publicAccess(): bool
    {
        return $this->publicAccess;
    }

    /**
     * Set whether existing destinations should be overwritten.
     *
     * @param boolean $overwrite Whether existing destinations should be overwritten (TRUE) or not (FALSE).
     */
    public function setOverwrite($overwrite): static
    {
        $this->overwrite = (bool) $overwrite;

        return $this;
    }

    /**
     * Determine if existing destinations should be overwritten.
     */
    public function overwrite(): bool
    {
        return $this->overwrite;
    }

    /**
     * Retrieve the path to the storage directory.
     *
     * @return string
     */
    protected function basePath()
    {
        if ($this->publicAccess()) {
            return $this->publicPath;
        } else {
            return $this->basePath;
        }
    }

    /**
     * Default response stub.
     */
    #[\Override]
    public function results(): array
    {
        return [
            'success'  => $this->success(),
            'location' => $this->uploadedPath
        ];
    }
}
