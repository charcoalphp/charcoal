<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;

// Dependencies from PHP extensions
use \finfo;
use \PDO;

// Dependency from Pimple
use \Pimple\Container;

// Intra-Module `charcoal-property` dependency
use \Charcoal\Property\AbstractProperty;

/**
 * File Property
 */
class FileProperty extends AbstractProperty
{
    /**
     * Whether uploaded files should be accessible from the web root.
     *
     * @var boolean
     */
    private $publicAccess = false;

    /**
     * The relative path to the storage directory.
     *
     * @var string
     */
    private $uploadPath = 'uploads/';

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

    /**
     * Whether existing destinations should be overwritten.
     *
     * @var boolean
     */
    private $overwrite = false;

    /**
     * Collection of accepted MIME types.
     *
     * @var string[]
     */
    private $acceptedMimetypes = [];

    /**
     * Current file mimetype
     *
     * @var string
     */
    private $mimetype;

    /**
     * Maximum allowed file size, in bytes.
     *
     * @var integer
     */
    private $maxFilesize;

    /**
     * Current file size, in bytes.
     *
     * @var integer
     */
    private $filesize;

    /**
     * @return string
     */
    public function type()
    {
        return 'file';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath   = $container['config']['base_path'];
        $this->publicPath = $container['config']['public_path'];
    }

    /**
     * Set whether uploaded files should be publicly available.
     *
     * @param boolean $public Whether uploaded files should be accessible (TRUE) or not (FALSE) from the web root.
     * @return self
     */
    public function setPublicAccess($public)
    {
        $this->publicAccess = !!$public;

        return $this;
    }

    /**
     * Determine if uploaded files should be publicly available.
     *
     * @return boolean
     */
    public function publicAccess()
    {
        return $this->publicAccess;
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
     * Set the destination (directory) where uploaded files are stored.
     *
     * The path must be relative to the {@see self::basePath()},
     *
     * @param string $path The destination directory, relative to project's root.
     * @throws InvalidArgumentException If the path is not a string.
     * @return self
     */
    public function setUploadPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Upload path must be a string'
            );
        }

        // Sanitize upload path (force trailing slash)
        $this->uploadPath = rtrim($path, '/').'/';

        return $this;
    }

    /**
     * Retrieve the destination for the uploaded file(s).
     *
     * @return string
     */
    public function uploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * Set whether existing destinations should be overwritten.
     *
     * @param boolean $overwrite Whether existing destinations should be overwritten (TRUE) or not (FALSE).
     * @return self
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = !!$overwrite;

        return $this;
    }

    /**
     * Determine if existing destinations should be overwritten.
     *
     * @return boolean
     */
    public function overwrite()
    {
        return $this->overwrite;
    }

    /**
     * @param string[] $mimetypes The accepted mimetypes.
     * @return FileProperty Chainable
     */
    public function setAcceptedMimetypes(array $mimetypes)
    {
        $this->acceptedMimetypes = $mimetypes;
        return $this;
    }

    /**
     * @return array
     */
    public function acceptedMimetypes()
    {
        return $this->acceptedMimetypes;
    }

    /**
     * @param string $mimetype The file mimetype.
     * @throws InvalidArgumentException If the mimetype argument is not a string.
     * @return FileProperty Chainable
     */
    public function setMimetype($mimetype)
    {
        if (!is_string($mimetype)) {
            throw new InvalidArgumentException(
                'Mimetype must be a string'
            );
        }
        $this->mimetype = $mimetype;
        return $this;
    }

    /**
     * @return string
     */
    public function mimetype()
    {
        if (!$this->mimetype) {
            // Get mimetype from file
            $val = $this->val();
            if (!$val) {
                return '';
            }
            $info = new finfo(FILEINFO_MIME_TYPE);
            $this->mimetype = $info->file($val);
        }
        return $this->mimetype;
    }

    /**
     * Converts a php.ini notation for size to an integer.
     *
     * @param  mixed $size A php.ini notation for size.
     * @throws InvalidArgumentException If the given parameter is invalid.
     * @return integer Returns the size in bytes.
     */
    protected function parseIniSize($size)
    {
        if (is_numeric($size)) {
            return $size;
        }

        if (!is_string($size)) {
            throw new InvalidArgumentException(
                'Size must be an integer (in bytes, e.g.: 1024) or a string (e.g.: 1M).'
            );
        }

        $quant = 'bkmgtpezy';
        $unit  = preg_replace('/[^'.$quant.']/i', '', $size);
        $size  = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            $size = ($size * pow(1024, stripos($quant, $unit[0])));
        }

        return round($size);
    }

    /**
     * Set the maximium size accepted for an uploaded files.
     *
     * @param string|integer $size The maximum file size allowed, in bytes.
     * @throws InvalidArgumentException If the size argument is not an integer.
     * @return FileProperty Chainable
     */
    public function setMaxFilesize($size)
    {
        $this->maxFilesize = $this->parseIniSize($size);

        return $this;
    }

    /**
     * Retrieve the maximum size accepted for uploaded files.
     *
     * If null or 0, then no limit. Defaults to 128 MB.
     *
     * @return integer
     */
    public function maxFilesize()
    {
        if (!isset($this->maxFilesize)) {
            return $this->maxFilesizeAllowedByPhp();
        }

        return $this->maxFilesize;
    }

    /**
     * Retrieve the maximum size (in bytes) allowed for an uploaded file
     * as configured in {@link http://php.net/manual/en/ini.php `php.ini`}.
     *
     * @param string|null $iniDirective If $iniDirective is provided, then it is filled with
     *     the name of the PHP INI directive corresponding to the maximum size allowed.
     * @return integer
     */
    public function maxFilesizeAllowedByPhp(&$iniDirective = null)
    {
        $post_max_size       = $this->parseIniSize(ini_get('post_max_size'));
        $upload_max_filesize = $this->parseIniSize(ini_get('upload_max_filesize'));

        if ($post_max_size < $upload_max_filesize) {
            $iniDirective = 'post_max_size';

            return $post_max_size;
        } else {
            $iniDirective = 'upload_max_filesize';

            return $upload_max_filesize;
        }
    }

    /**
     * @param integer $size The file size, in bytes.
     * @throws InvalidArgumentException If the size argument is not an integer.
     * @return FileProperty Chainable
     */
    public function setFilesize($size)
    {
        if (!is_int($size)) {
            throw new InvalidArgumentException(
                'Filesize must be an integer, in bytes.'
            );
        }
        $this->filesize = $size;
        return $this;
    }

    /**
     * @return integer
     */
    public function filesize()
    {
        if (!$this->filesize) {
            $val = $this->val();
            if (!$val || !file_exists($val) || !is_readable($val)) {
                return 0;
            } else {
                $this->filesize = filesize($val);
            }
        }
        return $this->filesize;
    }

    /**
     * @return array
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();
        return array_merge($parentMethods, ['accepted_mimetypes', 'max_filesize']);
    }

    /**
     * @return boolean
     */
    public function validateAcceptedMimetypes()
    {
        $acceptedMimetypes = $this->acceptedMimetypes();
        if (empty($acceptedMimetypes)) {
            // No validation rules = always true
            return true;
        }

        if ($this->mimetype) {
            $mimetype = $this->mimetype;
        } else {
            $val = $this->val();
            if (!$val) {
                return true;
            }
            $info = new finfo(FILEINFO_MIME_TYPE);
            $mimetype = $info->file($val);
        }
        $valid = false;
        foreach ($acceptedMimetypes as $m) {
            if ($m == $mimetype) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            $this->validator()->error('Accepted mimetypes error', 'acceptedMimetypes');
        }

        return $valid;
    }

    /**
     * @return boolean
     */
    public function validateMaxFilesize()
    {
        $maxFilesize = $this->maxFilesize();
        if ($maxFilesize == 0) {
            // No max size rule = always true
            return true;
        }

        $filesize = $this->filesize();
        $valid = ($filesize <= $maxFilesize);
        if (!$valid) {
            $this->validator()->error('Max filesize error', 'maxFilesize');
        }

        return $valid;
    }

    /**
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for max_length under 255 and `TEXT` for other, longer strings
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this->multiple()) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }

    /**
     * @param mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        // Current ident
        $i = $this->ident();

        // Upload file, if in request.
        if (isset($_FILES[$i])
            && (isset($_FILES[$i]['name']) && $_FILES[$i]['name'])
            && (isset($_FILES[$i]['tmp_name']) && $_FILES[$i]['tmp_name'])) {
            $file = $_FILES[$i];

            if (is_array($file['name']) && $this->multiple() && $this->l10n()) {
                $f = [];
                foreach ($file['name'] as $lang => $langVal) {
                    $f[$lang] = [];
                    if (!$file['name'][$lang]) {
                        $f[$lang] = isset($val[$lang]) ? $val[$lang] : '';
                        continue;
                    }
                    $k = 0;
                    $total = count($file['name'][$lang]);
                    for (; $k < $total; $k++) {
                        $data = [];
                        $data['name']       = $file['name'][$lang][$k];
                        $data['tmp_name']   = $file['tmp_name'][$lang][$k];
                        $data['error']      = $file['error'][$lang][$k];
                        $data['type']       = $file['type'][$lang][$k];
                        $data['size']       = $file['size'][$lang][$k];
                        $f[$lang][] = $this->fileUpload($data);
                    }
                }
            } elseif (is_array($file['name']) && $this->multiple()) {
                $f = [];
                $k = 0;
                $total = count($file['name']);
                for (; $k< $total; $k++) {
                    $data = [];
                    $data['name']       = $file['name'][$k];
                    $data['tmp_name']   = $file['tmp_name'][$k];
                    $data['error']      = $file['error'][$k];
                    $data['type']       = $file['type'][$k];
                    $data['size']       = $file['size'][$k];
                    $f[] = $this->fileUpload($data);
                }
            } elseif (is_array($file['name']) && $this->l10n()) {
                // Not so cool
                // Both the multiple and l10n loop could and
                // should be combined into one.
                // Not sure how
                $f = [];
                foreach ($file['name'] as $lang => $langVal) {
                    $data = [];

                    if (!$file['name'][$lang]) {
                        $f[$lang] = isset($val[$lang]) ? $val[$lang] : '';
                        continue;
                    }
                    $data['name']       = $file['name'][$lang];
                    $data['tmp_name']   = $file['tmp_name'][$lang];
                    $data['error']      = $file['error'][$lang];
                    $data['type']       = $file['type'][$lang];
                    $data['size']       = $file['size'][$lang];

                    $f[$lang] = $this->fileUpload($data);
                }
            } else {
                $f = $this->fileUpload($file);
            }
            return $f;
        }

        // Check in vals for data: base64 images
        // val should be an array if multiple...
        if ($this->multiple()) {
            $k = 0;
            $total = count($val);
            $f = [];
            for (; $k<$total; $k++) {
                if (preg_match('/^data:/', $val[$k])) {
                    $f[] = $this->dataUpload($val[$k]);
                    return $f;
                }
            }
        } elseif (preg_match('/^data:/', $val)) {
            $f = $this->dataUpload($val);
            return $f;
        }

        return $val;
    }

    /**
     * Upload to filesystem, from data: content.
     *
     * @param string $fileData The file data, raw.
     * @throws Exception If data content decoding fails.
     * @return string
     */
    public function dataUpload($fileData)
    {
        $file_content = file_get_contents($fileData);
        if ($file_content === false) {
            throw new Exception(
                'File content could not be decoded.'
            );
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $this->setMimetype($info->buffer($file_content));
        $this->setFilesize(strlen($file_content));
        if (!$this->validateAcceptedMimetypes() || !$this->validateMaxFilesize()) {
            return '';
        }

        $target = $this->uploadTarget();

        $ret = file_put_contents($target, $file_content);
        if ($ret === false) {
            return '';
        } else {
            $basePath = $this->basePath();
            $target = str_replace($basePath, '', $target);

            return $target;
        }
    }

    /**
     * @param array $fileData The file data (from $_FILES, typically).
     * @throws InvalidArgumentException If the FILES data argument is missing `name` or `tmp_name`.
     * @return string
     */
    public function fileUpload(array $fileData)
    {
        if (!isset($fileData['name'])) {
            throw new InvalidArgumentException(
                'File data is invalid'
            );
        }

        $target = $this->uploadTarget($fileData['name']);

        if (file_exists($fileData['tmp_name'])) {
            $info = new finfo(FILEINFO_MIME_TYPE);
            $this->setMimetype($info->file($fileData['tmp_name']));
            $this->setFilesize(filesize($fileData['tmp_name']));
            if (!$this->validateAcceptedMimetypes() || !$this->validateMaxFilesize()) {
                return '';
            }
        }

        $ret = move_uploaded_file($fileData['tmp_name'], $target);

        if ($ret === false) {
            $this->logger->warning(sprintf('Could not upload file %s', $target));
            return '';
        } else {
            $this->logger->notice(sprintf('File %s uploaded succesfully', $target));
            $basePath = $this->basePath();
            $target = str_replace($basePath, '', $target);

            return $target;
        }
    }

    /**
     * @param string $filename Optional. The filename to save. If unset, a default filename will be generated.
     * @throws Exception If the target path is not writeable.
     * @return string
     */
    public function uploadTarget($filename = null)
    {
        $basePath = $this->basePath();

        $dir = $basePath.$this->uploadPath();
        $filename = ($filename) ? $this->sanitizeFilename($filename) : $this->generateFilename();

        if (!file_exists($dir)) {
            // @todo: Feedback
            $this->logger->debug(
                'Path does not exist. Attempting to create path '.$dir.'.',
                [get_called_class().'::'.__FUNCTION__]
            );
            mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            throw new Exception(
                'Error: upload directory is not writeable'
            );
        }

        $target = $dir.$filename;

        if ($this->fileExists($target)) {
            if ($this->overwrite() === true) {
                return $target;
            } else {
                $target = $dir.$this->generateUniqueFilename($filename);
            }
        }

        return $target;
    }

    /**
     * This function checks if a file exist, by default in a case-insensitive manner.
     *
     * PHP builtin's `file_exists` is only case-insensitive on case-insensitive filesystem (such as windows)
     * This method allows to have the same validation across different platforms / filesystem.
     *
     * @param string  $file             The full file to check.
     * @param boolean $case_insensitive Optional. Case insensitive flag.
     * @return boolean
     */
    public function fileExists($file, $case_insensitive = true)
    {
        if (file_exists($file)) {
            return true;
        }

        if ($case_insensitive === false) {
            return false;
        }

        $files = glob(dirname($file).DIRECTORY_SEPARATOR.'*', GLOB_NOSORT);
        foreach ($files as $f) {
            if (preg_match("#{$file}#i", preg_quote($f))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize a filename by removing characters from a blacklist and escaping dot.
     *
     * @param string $filename The filename to sanitize.
     * @return string The sanitized filename.
     */
    public function sanitizeFilename($filename)
    {
        // Remove blacklisted caharacters
        $blacklist = ['/', '\\', '\0', '*', ':', '?', '"', '<', '>', '|', '#', '&', '!', '`', ' '];
        $filename  = str_replace($blacklist, '_', $filename);

        // Avoid hidden file
        $filename = ltrim($filename, '.');

        return $filename;
    }

    /**
     * Generate a new filename from the property.
     *
     * @return string
     */
    public function generateFilename()
    {
        $filename  = $this->label().' '.date('Y-m-d H-i-s');
        $extension = $this->generateExtension();

        if ($extension) {
            return $filename.'.'.$extension;
        } else {
            return $filename;
        }
    }

    /**
     * Generate a unique filename.
     *
     * @param string $filename The filename to alter.
     * @return string
     * @todo Improve this feature Can not overwrite. Must rename file.
     */
    public function generateUniqueFilename($filename)
    {
        $info = pathinfo($filename);

        $filename = $info['filename'].'-'.uniqid();

        if (isset($info['extension']) && $info['extension']) {
            $filename .= '.'.$info['extension'];
        }

        return $filename;
    }

    /**
     * Generate the file extension from the property's value.
     *
     * @return string
     */
    public function generateExtension()
    {
        return '';
    }
}
