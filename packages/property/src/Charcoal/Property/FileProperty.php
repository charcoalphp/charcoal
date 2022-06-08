<?php

namespace Charcoal\Property;

use finfo;
use PDO;
use Exception;
use InvalidArgumentException;
use UnexpectedValueException;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * File Property
 */
class FileProperty extends AbstractProperty
{
    const DEFAULT_PUBLIC_ACCESS = false;
    const DEFAULT_UPLOAD_PATH = 'uploads/';
    const DEFAULT_FILESYSTEM = 'public';
    const DEFAULT_OVERWRITE = false;
    const ERROR_MESSAGES = [
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive'.
                                 'that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];

    /**
     * Whether uploaded files should be accessible from the web root.
     *
     * @var boolean
     */
    private $publicAccess = self::DEFAULT_PUBLIC_ACCESS;

    /**
     * The relative path to the storage directory.
     *
     * @var string
     */
    private $uploadPath = self::DEFAULT_UPLOAD_PATH;

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
    private $overwrite = self::DEFAULT_OVERWRITE;

    /**
     * Collection of accepted MIME types.
     *
     * @var string[]
     */
    private $acceptedMimetypes;

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
     * @var string
     */
    private $fallbackFilename;

    /**
     * The filesystem to use while uploading a file.
     *
     * @var string
     */
    private $filesystem = self::DEFAULT_FILESYSTEM;

    /**
     * Holds a list of all normalized paths.
     *
     * @var string[]
     */
    protected static $normalizePathCache = [];

    /**
     * @return string
     */
    public function type()
    {
        return 'file';
    }

    /**
     * Set whether uploaded files should be publicly available.
     *
     * @param  boolean $public Whether uploaded files should be accessible (TRUE) or not (FALSE) from the web root.
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
    public function getPublicAccess()
    {
        return $this->publicAccess;
    }

    /**
     * Set the destination (directory) where uploaded files are stored.
     *
     * The path must be relative to the {@see self::basePath()},
     *
     * @param  string $path The destination directory, relative to project's root.
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
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * Set whether existing destinations should be overwritten.
     *
     * @param  boolean $overwrite Whether existing destinations should be overwritten (TRUE) or not (FALSE).
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
    public function getOverwrite()
    {
        return $this->overwrite;
    }

    /**
     * Sets the acceptable MIME types for uploaded files.
     *
     * @param  mixed $types One or many MIME types.
     * @throws InvalidArgumentException If the $types argument is not NULL or a list.
     * @return self
     */
    public function setAcceptedMimetypes($types)
    {
        if (is_array($types)) {
            $types = array_filter($types);

            if (empty($types)) {
                $types = null;
            }
        }

        if ($types !== null && !is_array($types)) {
            throw new InvalidArgumentException(
                'Must be an array of acceptable MIME types or NULL'
            );
        }

        $this->acceptedMimetypes = $types;
        return $this;
    }

    /**
     * Determines if any acceptable MIME types are defined.
     *
     * @return boolean
     */
    public function hasAcceptedMimetypes()
    {
        if (!empty($this->acceptedMimetypes)) {
            return true;
        }

        return !empty($this->getDefaultAcceptedMimetypes());
    }

    /**
     * Retrieves a list of acceptable MIME types for uploaded files.
     *
     * @return string[]
     */
    public function getAcceptedMimetypes()
    {
        if ($this->acceptedMimetypes === null) {
            return $this->getDefaultAcceptedMimetypes();
        }

        return $this->acceptedMimetypes;
    }

    /**
     * Retrieves the default list of acceptable MIME types for uploaded files.
     *
     * This method should be overriden.
     *
     * @return string[]
     */
    public function getDefaultAcceptedMimetypes()
    {
        return [];
    }

    /**
     * Set the MIME type.
     *
     * @param  mixed $type The file MIME type.
     * @throws InvalidArgumentException If the MIME type argument is not a string.
     * @return FileProperty Chainable
     */
    public function setMimetype($type)
    {
        if ($type === null || $type === false) {
            $this->mimetype = null;
            return $this;
        }

        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'MIME type must be a string'
            );
        }

        $this->mimetype = $type;
        return $this;
    }

    /**
     * Retrieve the MIME type of the property value.
     *
     * @todo Refactor to support multilingual/multiple files.
     *
     * @return integer Returns the MIME type for the first value.
     */
    public function getMimetype()
    {
        if ($this->mimetype === null) {
            $files = $this->parseValAsFileList($this->val());
            if (empty($files)) {
                return null;
            }

            $file = reset($files);
            $type = $this->getMimetypeFor($file);
            if ($type === null) {
                return null;
            }

            $this->setMimetype($type);
        }

        return $this->mimetype;
    }

    /**
     * Extract the MIME type from the given file.
     *
     * @param  string $file The file to check.
     * @return integer|null Returns the file's MIME type,
     *     or NULL in case of an error or the file is missing.
     */
    public function getMimetypeFor($file)
    {
        if (!$this->isAbsolutePath($file)) {
            $file = $this->pathFor($file);
        }

        if (!$this->fileExists($file)) {
            return null;
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $type = $info->file($file);
        if (empty($type) || $type === 'inode/x-empty') {
            return null;
        }

        return $type;
    }

    /**
     * Set the maximium size accepted for an uploaded files.
     *
     * @param  string|integer $size The maximum file size allowed, in bytes.
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
    public function getMaxFilesize()
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
        $postMaxSize = $this->parseIniSize(ini_get('post_max_size'));
        $uploadMaxFilesize = $this->parseIniSize(ini_get('upload_max_filesize'));

        if ($postMaxSize < $uploadMaxFilesize) {
            $iniDirective = 'post_max_size';

            return $postMaxSize;
        } else {
            $iniDirective = 'upload_max_filesize';

            return $uploadMaxFilesize;
        }
    }

    /**
     * @param  integer $size The file size, in bytes.
     * @throws InvalidArgumentException If the size argument is not an integer.
     * @return FileProperty Chainable
     */
    public function setFilesize($size)
    {
        if (!is_int($size) && $size !== null) {
            throw new InvalidArgumentException(
                'File size must be an integer in bytes'
            );
        }

        $this->filesize = $size;
        return $this;
    }

    /**
     * Retrieve the size of the property value.
     *
     * @todo Refactor to support multilingual/multiple files.
     *
     * @return integer Returns the size in bytes for the first value.
     */
    public function getFilesize()
    {
        if ($this->filesize === null) {
            $files = $this->parseValAsFileList($this->val());
            if (empty($files)) {
                return 0;
            }

            $file = reset($files);
            $size = $this->getFilesizeFor($file);
            if ($size === null) {
                return 0;
            }

            $this->setFilesize($size);
        }

        return $this->filesize;
    }

    /**
     * Extract the size of the given file.
     *
     * @param  string $file The file to check.
     * @return integer|null Returns the file size in bytes,
     *     or NULL in case of an error or the file is missing.
     */
    public function getFilesizeFor($file)
    {
        if (!$this->isAbsolutePath($file)) {
            $file = $this->pathFor($file);
        }

        if (!$this->fileExists($file)) {
            return null;
        }

        $size = filesize($file);
        if ($size === false) {
            return null;
        }

        return $size;
    }

    /**
     * Convert number of bytes to largest human-readable unit.
     *
     * @param  integer $bytes    Number of bytes.
     * @param  integer $decimals Precision of number of decimal places. Default 0.
     * @return string|null Returns the formatted number or NULL.
     */
    public function formatFilesize($bytes, $decimals = 2)
    {
        if ($bytes === 0) {
            $factor = 0;
        } else {
            $factor = floor((strlen($bytes) - 1) / 3);
        }

        $unit = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];

        $factor = floor((strlen($bytes) - 1) / 3);

        if (!isset($unit[$factor])) {
            $factor = 0;
        }

        return sprintf('%.'.$decimals.'f', ($bytes / pow(1024, $factor))).' '.$unit[$factor];
    }

    /**
     * @return array
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'mimetypes',
            'filesizes',
        ]);
    }

    /**
     * Validates the MIME types for the property's value(s).
     *
     * @return boolean Returns TRUE if all values are valid.
     *     Otherwise, returns FALSE and reports issues.
     */
    public function validateMimetypes()
    {
        $acceptedMimetypes = $this['acceptedMimetypes'];
        if (empty($acceptedMimetypes)) {
            // No validation rules = always true
            return true;
        }

        $files = $this->parseValAsFileList($this->val());

        if (empty($files)) {
            return true;
        }

        $valid = true;

        foreach ($files as $file) {
            $mime = $this->getMimetypeFor($file);

            if ($mime === null) {
                $valid = false;

                $this->validator()->error(sprintf(
                    'File [%s] not found or MIME type unrecognizable',
                    $file
                ), 'acceptedMimetypes');
            } elseif (!in_array($mime, $acceptedMimetypes)) {
                $valid = false;

                $this->validator()->error(sprintf(
                    'File [%s] has unacceptable MIME type [%s]',
                    $file,
                    $mime
                ), 'acceptedMimetypes');
            }
        }

        return $valid;
    }

    /**
     * Validates the file sizes for the property's value(s).
     *
     * @return boolean Returns TRUE if all values are valid.
     *     Otherwise, returns FALSE and reports issues.
     */
    public function validateFilesizes()
    {
        $maxFilesize = $this['maxFilesize'];
        if (empty($maxFilesize)) {
            // No max size rule = always true
            return true;
        }

        $files = $this->parseValAsFileList($this->val());

        if (empty($files)) {
            return true;
        }

        $valid = true;

        foreach ($files as $file) {
            $filesize = $this->getFilesizeFor($file);

            if ($filesize === null) {
                $valid = false;

                $this->validator()->error(sprintf(
                    'File [%s] not found or size unknown',
                    $file
                ), 'maxFilesize');
            } elseif (($filesize > $maxFilesize)) {
                $valid = false;

                $this->validator()->error(sprintf(
                    'File [%s] exceeds maximum file size [%s]',
                    $file,
                    $this->formatFilesize($maxFilesize)
                ), 'maxFilesize');
            }
        }

        return $valid;
    }

    /**
     * Parse a multi-dimensional array of value(s) into a single level.
     *
     * This method flattens a value object that is "l10n" or "multiple".
     * Empty or duplicate values are removed.
     *
     * @param  mixed $value A multi-dimensional variable.
     * @return string[] The array of values.
     */
    public function parseValAsFileList($value)
    {
        $files = [];

        if ($value instanceof Translation) {
            $value = $value->data();
        }

        $array = $this->parseValAsMultiple($value);
        array_walk_recursive($array, function ($item) use (&$files) {
            $array = $this->parseValAsMultiple($item);
            $files = array_merge($files, $array);
        });

        $files = array_filter($files, function ($file) {
            return is_string($file) && isset($file[0]);
        });
        $files = array_unique($files);
        $files = array_values($files);

        return $files;
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for max_length under 255 and `TEXT` for other, longer strings
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this['multiple']) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }

    /**
     * Process file uploads {@see AbstractProperty::save() parsing values}.
     *
     * @param  mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        if ($val instanceof Translation) {
            $values = $val->data();
        } else {
            $values = $val;
        }

        $uploadedFiles = $this->getUploadedFiles();

        if ($this['l10n']) {
            foreach ($this->translator()->availableLocales() as $lang) {
                if (!isset($values[$lang])) {
                    $values[$lang] = $this['multiple'] ? [] : '';
                }

                $parsedFiles = [];

                if (isset($uploadedFiles[$lang])) {
                    $parsedFiles = $this->saveFileUploads($uploadedFiles[$lang]);
                }

                if (empty($parsedFiles)) {
                    $parsedFiles = $this->saveDataUploads($values[$lang]);
                }

                $values[$lang] = $this->parseSavedValues($parsedFiles, $values[$lang]);
            }
        } else {
            $parsedFiles = [];

            if (!empty($uploadedFiles)) {
                $parsedFiles = $this->saveFileUploads($uploadedFiles);
            }

            if (empty($parsedFiles)) {
                $parsedFiles = $this->saveDataUploads($values);
            }

            $values = $this->parseSavedValues($parsedFiles, $values);
        }

        return $values;
    }

    /**
     * Process and transfer any data URIs to the filesystem,
     * and carry over any pre-processed file paths.
     *
     * @param  mixed $values One or more data URIs, data entries, or processed file paths.
     * @return string|string[] One or more paths to the processed uploaded files.
     */
    protected function saveDataUploads($values)
    {
        // Bag value if singular
        if (!is_array($values) || isset($values['id'])) {
            $values = [ $values ];
        }

        $parsed = [];
        foreach ($values as $value) {
            if ($this->isDataArr($value) || $this->isDataUri($value)) {
                try {
                    $path = $this->dataUpload($value);
                    if ($path !== null) {
                        $parsed[] = $path;

                        $this->logger->notice(sprintf(
                            'File [%s] uploaded succesfully',
                            $path
                        ));
                    }
                } catch (Exception $e) {
                    $this->logger->warning(sprintf(
                        'Upload error on data URI: %s',
                        $e->getMessage()
                    ));
                }
            } elseif (is_string($value) && !empty($value)) {
                $parsed[] = $value;
            }
        }

        return $parsed;
    }

    /**
     * Process and transfer any uploaded files to the filesystem.
     *
     * @param  mixed $files One or more normalized $_FILE entries.
     * @return string[] One or more paths to the processed uploaded files.
     */
    protected function saveFileUploads($files)
    {
        // Bag value if singular
        if (isset($files['error'])) {
            $files = [ $files ];
        }

        $parsed = [];
        foreach ($files as $file) {
            if (isset($file['error'])) {
                try {
                    $path = $this->fileUpload($file);
                    if ($path !== null) {
                        $parsed[] = $path;

                        $this->logger->notice(sprintf(
                            'File [%s] uploaded succesfully',
                            $path
                        ));
                    }
                } catch (Exception $e) {
                    $this->logger->warning(sprintf(
                        'Upload error on file [%s]: %s',
                        $file['name'],
                        $e->getMessage()
                    ));
                }
            }
        }

        return $parsed;
    }

    /**
     * Finalize any processed files.
     *
     * @param  mixed $saved   One or more values, at time of saving.
     * @param  mixed $default The default value to return.
     * @return string|string[] One or more paths to the processed uploaded files.
     */
    protected function parseSavedValues($saved, $default = null)
    {
        $values = empty($saved) ? $default : $saved;

        if ($this['multiple']) {
            if (!is_array($values)) {
                $values = empty($values) && !is_numeric($values) ? [] : [ $values ];
            }
        } else {
            if (is_array($values)) {
                $values = reset($values);
            }
        }

        return $values;
    }

    /**
     * Upload to filesystem, from data URI.
     *
     * @param  mixed $data A data URI.
     * @throws Exception If data content decoding fails.
     * @throws InvalidArgumentException If the input $data is invalid.
     * @throws Exception If the upload fails or the $data is bad.
     * @return string|null The file path to the uploaded data.
     */
    public function dataUpload($data)
    {
        $filename = null;
        $contents = false;

        if (is_array($data)) {
            if (!isset($data['id'], $data['name'])) {
                throw new InvalidArgumentException(
                    '$data as an array MUST contain each of the keys "id" and "name", '.
                    'with each represented as a scalar value; one or more were missing or non-array values'
                );
            }
            // retrieve tmp file from temp dir
            $tmpDir  = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $tmpFile = $tmpDir.$data['id'];
            if (!file_exists($tmpFile)) {
                throw new Exception(sprintf(
                    'File %s does not exists',
                    $data['id']
                ));
            }

            $contents = file_get_contents($tmpFile);

            if (strlen($data['name']) > 0) {
                $filename = $data['name'];
            }

            // delete tmp file
            unlink($tmpFile);
        } elseif (is_string($data)) {
            $contents = file_get_contents($data);
        }

        if ($contents === false) {
            throw new Exception(
                'File content could not be decoded for data URI'
            );
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $mime = $info->buffer($contents);
        if (!$this->isAcceptedMimeType($mime)) {
            throw new Exception(sprintf(
                'Unacceptable MIME type [%s]',
                $mime
            ));
        }

        $size = strlen($contents);
        if (!$this->isAcceptedFilesize($size)) {
            throw new Exception(sprintf(
                'Maximum file size exceeded [%s]',
                $this->formatFilesize($this['maxFilesize'])
            ));
        }

        if ($filename === null) {
            $extension = $this->generateExtensionFromMimeType($mime);
            $filename  = $this->generateFilename($extension);
        }

        $targetPath = $this->uploadTarget($filename);

        $result = file_put_contents($targetPath, $contents);
        if ($result === false) {
            throw new Exception(sprintf(
                'Failed to write file to %s',
                $targetPath
            ));
        }

        $basePath   = $this->basePath();
        $targetPath = str_replace($basePath, '', $targetPath);

        return $targetPath;
    }

    /**
     * Upload to filesystem.
     *
     * @link https://github.com/slimphp/Slim/blob/3.12.1/Slim/Http/UploadedFile.php
     *     Adapted from slim/slim.
     *
     * @param  array $file A single $_FILES entry.
     * @throws InvalidArgumentException If the input $file is invalid.
     * @throws Exception If the upload fails or the $file is bad.
     * @return string|null The file path to the uploaded file.
     */
    public function fileUpload(array $file)
    {
        if (!isset($file['tmp_name'], $file['name'], $file['size'], $file['error'])) {
            throw new InvalidArgumentException(
                '$file MUST contain each of the keys "tmp_name", "name", "size", and "error", '.
                'with each represented as a scalar value; one or more were missing or non-array values'
            );
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $file['error'];
            throw new Exception(
                self::ERROR_MESSAGES[$errorCode]
            );
        }

        if (!file_exists($file['tmp_name'])) {
            throw new Exception(
                'File does not exist'
            );
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception(
                'File was not uploaded'
            );
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $mime = $info->file($file['tmp_name']);
        if (!$this->isAcceptedMimeType($mime)) {
            throw new Exception(sprintf(
                'Unacceptable MIME type [%s]',
                $mime
            ));
        }

        $size = filesize($file['tmp_name']);
        if (!$this->isAcceptedFilesize($size)) {
            throw new Exception(sprintf(
                'Maximum file size exceeded [%s]',
                $this->formatFilesize($this['maxFilesize'])
            ));
        }

        $targetPath = $this->uploadTarget($file['name']);

        $result = move_uploaded_file($file['tmp_name'], $targetPath);
        if ($result === false) {
            throw new Exception(sprintf(
                'Failed to move uploaded file to %s',
                $targetPath
            ));
        }

        $basePath   = $this->basePath();
        $targetPath = str_replace($basePath, '', $targetPath);

        return $targetPath;
    }

    /**
     * Parse the uploaded file path.
     *
     * This method will create the file's directory path and will sanitize the file's name
     * or generate a unique name if none provided (such as data URIs).
     *
     * @param  string|null $filename Optional. The filename to save as.
     *     If NULL, a default filename will be generated.
     * @return string
     */
    public function uploadTarget($filename = null)
    {
        $this->assertValidUploadPath();

        $uploadPath = $this->pathFor($this['uploadPath']);

        if ($filename === null) {
            $filename = $this->generateFilename();
        } else {
            $filename = $this->sanitizeFilename($filename);
        }

        $targetPath = $uploadPath.'/'.$filename;

        if ($this->fileExists($targetPath)) {
            if ($this['overwrite'] === true) {
                return $targetPath;
            }

            do {
                $targetPath = $uploadPath.'/'.$this->generateUniqueFilename($filename);
            } while ($this->fileExists($targetPath));
        }

        return $targetPath;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * PHP built-in's `file_exists` is only case-insensitive on
     * a case-insensitive filesystem (such as Windows). This method allows
     * to have the same validation across different platforms / filesystems.
     *
     * @param  string  $file            The full file to check.
     * @param  boolean $caseInsensitive Case-insensitive by default.
     * @return boolean
     */
    public function fileExists($file, $caseInsensitive = true)
    {
        $file = (string)$file;

        if (!$this->isAbsolutePath($file)) {
            $file = $this->pathFor($file);
        }

        if (file_exists($file)) {
            return true;
        }

        if ($caseInsensitive === false) {
            return false;
        }

        $files = glob(dirname($file).DIRECTORY_SEPARATOR.'*', GLOB_NOSORT);
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
     * Sanitize a filename by removing characters from a blacklist and escaping dot.
     *
     * @param  string $filename The filename to sanitize.
     * @throws Exception If the filename is invalid.
     * @return string The sanitized filename.
     */
    public function sanitizeFilename($filename)
    {
        // Remove blacklisted caharacters
        $blacklist = [ '/', '\\', '\0', '*', ':', '?', '"', '<', '>', '|', '#', '&', '!', '`', ' ' ];
        $filename  = str_replace($blacklist, '_', (string)$filename);

        // Avoid hidden file or trailing dot
        $filename = trim($filename, '.');

        if (strlen($filename) === 0) {
            throw new Exception(
                'Bad file name after sanitization'
            );
        }

        return $filename;
    }

    /**
     * Render the given file to the given pattern.
     *
     * This method does not rename the given path.
     *
     * @uses   strtr() To replace tokens in the form `{{foobar}}`.
     * @param  string         $from The string being rendered.
     * @param  string         $to   The pattern replacing $from.
     * @param  array|callable $args Extra rename tokens.
     * @throws InvalidArgumentException If the given arguments are invalid.
     * @throws UnexpectedValueException If the renaming failed.
     * @return string Returns the rendered target.
     */
    public function renderFileRenamePattern($from, $to, $args = null)
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(sprintf(
                'The target to rename must be a string, received %s',
                (is_object($from) ? get_class($from) : gettype($from))
            ));
        }

        if (!is_string($to)) {
            throw new InvalidArgumentException(sprintf(
                'The rename pattern must be a string, received %s',
                (is_object($to) ? get_class($to) : gettype($to))
            ));
        }

        $info = pathinfo($from);
        $args = $this->renamePatternArgs($info, $args);

        $to = strtr($to, $args);
        if (strpos($to, '{{') !== false) {
            preg_match_all('~\{\{\s*(.*?)\s*\}\}~i', $to, $matches);

            throw new UnexpectedValueException(sprintf(
                'The rename pattern failed. Leftover tokens found: %s',
                implode(', ', $matches[1])
            ));
        }

        $to = str_replace($info['basename'], $to, $from);

        return $to;
    }

    /**
     * Generate a new filename from the property.
     *
     * @param  string|null $extension An extension to append to the generated filename.
     * @return string
     */
    public function generateFilename($extension = null)
    {
        $filename = $this->sanitizeFilename($this['fallbackFilename']);
        $filename = $filename.' '.date('Y-m-d\TH-i-s');

        if ($extension !== null) {
            return $filename.'.'.$extension;
        }

        return $filename;
    }

    /**
     * Generate a unique filename.
     *
     * @param  string|array $filename The filename to alter.
     * @throws InvalidArgumentException If the given filename is invalid.
     * @return string
     */
    public function generateUniqueFilename($filename)
    {
        if (is_string($filename)) {
            $info = pathinfo($filename);
        } else {
            $info = $filename;
        }

        if (!isset($info['filename']) || strlen($info['filename']) === 0) {
            throw new InvalidArgumentException(sprintf(
                'File must be a string [file path] or an array [pathfino()], received %s',
                (is_object($filename) ? get_class($filename) : gettype($filename))
            ));
        }

        $filename = $info['filename'].'-'.uniqid();

        if (isset($info['extension']) && strlen($info['extension']) > 0) {
            $filename .= '.'.$info['extension'];
        }

        return $filename;
    }

    /**
     * Generate the file extension from the property value.
     *
     * @todo Refactor to support multilingual/multiple files.
     *
     * @return string Returns the file extension based on the MIME type for the first value.
     */
    public function generateExtension()
    {
        $type = $this->getMimetype();

        return $this->resolveExtensionFromMimeType($type);
    }

    /**
     * Generate a file extension from the given file path.
     *
     * @param  string $file The file to parse.
     * @return string|null The extension based on the file's MIME type.
     */
    public function generateExtensionFromFile($file)
    {
        if ($this->hasAcceptedMimetypes()) {
            $type = $this->getMimetypeFor($file);

            return $this->resolveExtensionFromMimeType($type);
        }

        if (!is_string($file) || !defined('FILEINFO_EXTENSION')) {
            return null;
        }

        // PHP 7.2
        $info = new finfo(FILEINFO_EXTENSION);
        $ext  = $info->file($file);

        if ($ext === '???') {
            return null;
        }

        if (strpos($ext, '/') !== false) {
            $ext = explode('/', $ext);
            $ext = reset($ext);
        }

        return $ext;
    }

    /**
     * Generate a file extension from the given MIME type.
     *
     * @param  string $type The MIME type to parse.
     * @return string|null The extension based on the MIME type.
     */
    public function generateExtensionFromMimeType($type)
    {
        if (in_array($type, $this->getAcceptedMimetypes())) {
            return $this->resolveExtensionFromMimeType($type);
        }

        return null;
    }

    /**
     * Resolve the file extension from the given MIME type.
     *
     * This method should be overriden to provide available extensions.
     *
     * @param  string $type The MIME type to resolve.
     * @return string|null The extension based on the MIME type.
     */
    protected function resolveExtensionFromMimeType($type)
    {
        switch ($type) {
            case 'text/plain':
                return 'txt';
        }

        return null;
    }

    /**
     * @param  mixed $fallback The fallback filename.
     * @return self
     */
    public function setFallbackFilename($fallback)
    {
        $this->fallbackFilename = $this->translator()->translation($fallback);
        return $this;
    }

    /**
     * @return Translation|null
     */
    public function getFallbackFilename()
    {
        if ($this->fallbackFilename === null) {
            return $this['label'];
        }

        return $this->fallbackFilename;
    }

    /**
     * @return string
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param string $filesystem The file system.
     * @return self
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath   = $container['config']['base_path'];
        $this->publicPath = $container['config']['public_path'];
    }

    /**
     * Retrieve the base path to the storage directory.
     *
     * @return string
     */
    protected function basePath()
    {
        if ($this['publicAccess']) {
            return $this->publicPath;
        }

        return $this->basePath;
    }

    /**
     * Build the path for a named route including the base path.
     *
     * The {@see self::basePath() base path} will be prepended to the given $path.
     *
     * If the given $path does not start with the {@see self::getUploadPath() upload path},
     * it will be prepended.
     *
     * @param  string $path The end path.
     * @return string
     */
    protected function pathFor($path)
    {
        $path       = trim($path, '/');
        $basePath   = rtrim($this->basePath(), '/');

        return $basePath.'/'.$path;
    }

    /**
     * Attempts to create the upload path.
     *
     * @throws Exception If the upload path is unavailable.
     * @return void
     */
    protected function assertValidUploadPath()
    {
        $uploadPath = $this->pathFor($this['uploadPath']);

        if (!file_exists($uploadPath)) {
            $this->logger->debug(sprintf(
                '[%s] Upload directory [%s] does not exist; attempting to create path',
                [ get_called_class().'::'.__FUNCTION__ ],
                $uploadPath
            ));

            mkdir($uploadPath, 0777, true);
        }

        if (!is_writable($uploadPath)) {
            throw new Exception(sprintf(
                'Upload directory [%s] is not writeable',
                $uploadPath
            ));
        }
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
                'Size must be an integer (in bytes, e.g.: 1024) or a string (e.g.: 1M)'
            );
        }

        $quant = 'bkmgtpezy';
        $unit = preg_replace('/[^'.$quant.']/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            $size = ($size * pow(1024, stripos($quant, $unit[0])));
        }

        return round($size);
    }

    /**
     * Determine if the given MIME type is acceptable.
     *
     * @param  string   $type     A MIME type.
     * @param  string[] $accepted One or many acceptable MIME types.
     *     Defaults to the property's "acceptedMimetypes".
     * @return boolean Returns TRUE if the MIME type is acceptable.
     *     Otherwise, returns FALSE.
     */
    protected function isAcceptedMimeType($type, array $accepted = null)
    {
        if ($accepted === null) {
            $accepted = $this['acceptedMimetypes'];
        }

        if (empty($accepted)) {
            return true;
        }

        return in_array($type, $accepted);
    }

    /**
     * Determine if the given file size is acceptable.
     *
     * @param  integer $size Number of bytes.
     * @param  integer $max  The maximum number of bytes allowed.
     *     Defaults to the property's "maxFilesize".
     * @return boolean Returns TRUE if the size is acceptable.
     *     Otherwise, returns FALSE.
     */
    protected function isAcceptedFilesize($size, $max = null)
    {
        if ($max === null) {
            $max = $this['maxFilesize'];
        }

        if (empty($max)) {
            return true;
        }

        return ($size <= $max);
    }

    /**
     * Determine if the given file path is an absolute path.
     *
     * Note: Adapted from symfony\filesystem.
     *
     * @see https://github.com/symfony/symfony/blob/v3.2.2/LICENSE
     *
     * @param  string $file A file path.
     * @return boolean Returns TRUE if the given path is absolute. Otherwise, returns FALSE.
     */
    protected function isAbsolutePath($file)
    {
        $file = (string)$file;

        return strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3
                && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && strspn($file, '/\\', 2, 1))
            || null !== parse_url($file, PHP_URL_SCHEME);
    }

    /**
     * Determine if the given value is a data URI.
     *
     * @param  mixed $val The value to check.
     * @return boolean
     */
    protected function isDataUri($val)
    {
        return is_string($val) && preg_match('/^data:/i', $val);
    }

    /**
     * Determine if the given value is a data array.
     *
     * @param  mixed $val The value to check.
     * @return boolean
     */
    protected function isDataArr($val)
    {
        return is_array($val) && isset($val['id']);
    }

    /**
     * Retrieve the rename pattern tokens for the given file.
     *
     * @param  string|array   $path The string to be parsed or an associative array of information about the file.
     * @param  array|callable $args Extra rename tokens.
     * @throws InvalidArgumentException If the given arguments are invalid.
     * @throws UnexpectedValueException If the given path is invalid.
     * @return string Returns the rendered target.
     */
    private function renamePatternArgs($path, $args = null)
    {
        if (!is_string($path) && !is_array($path)) {
            throw new InvalidArgumentException(sprintf(
                'The target must be a string or an array from [pathfino()], received %s',
                (is_object($path) ? get_class($path) : gettype($path))
            ));
        }

        if (is_string($path)) {
            $info = pathinfo($path);
        } else {
            $info = $path;
        }

        if (!isset($info['basename']) || $info['basename'] === '') {
            throw new UnexpectedValueException(
                'The basename is missing from the target'
            );
        }

        if (!isset($info['filename']) || $info['filename'] === '') {
            throw new UnexpectedValueException(
                'The filename is missing from the target'
            );
        }

        if (!isset($info['extension'])) {
            $info['extension'] = '';
        }

        $defaults = [
            '{{property}}'  => $this->ident(),
            '{{label}}'     => $this['label'],
            '{{fallback}}'  => $this['fallbackFilename'],
            '{{extension}}' => $info['extension'],
            '{{basename}}'  => $info['basename'],
            '{{filename}}'  => $info['filename'],
        ];

        if ($args === null) {
            $args = $defaults;
        } else {
            if (is_callable($args)) {
                /**
                 * Rename Arguments Callback Routine
                 *
                 * @param  array             $info Information about the file path from {@see pathinfo()}.
                 * @param  PropertyInterface $prop The related image property.
                 * @return array
                 */
                $args = $args($info, $this);
            }

            if (is_array($args)) {
                $args = array_replace($defaults, $args);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Arguments must be an array or a callable that returns an array, received %s',
                    (is_object($args) ? get_class($args) : gettype($args))
                ));
            }
        }

        return $args;
    }

    /**
     * Retrieve normalized file upload data for this property.
     *
     * @return array A tree of normalized $_FILE entries.
     */
    public function getUploadedFiles()
    {
        $propIdent = $this->ident();

        $filterErrNoFile = function (array $file) {
            return $file['error'] !== UPLOAD_ERR_NO_FILE;
        };
        $uploadedFiles = static::parseUploadedFiles($_FILES, $filterErrNoFile, $propIdent);

        return $uploadedFiles;
    }

    /**
     * Parse a non-normalized, i.e. $_FILES superglobal, tree of uploaded file data.
     *
     * @link https://github.com/slimphp/Slim/blob/3.12.1/Slim/Http/UploadedFile.php
     *     Adapted from slim/slim.
     *
     * @todo Add support for "dot" notation on $searchKey.
     *
     * @param  array    $uploadedFiles  The non-normalized tree of uploaded file data.
     * @param  callable $filterCallback If specified, the callback function to used to filter files.
     * @param  mixed    $searchKey      If specified, then only top-level keys containing these values are returned.
     * @return array A tree of normalized $_FILE entries.
     */
    public static function parseUploadedFiles(array $uploadedFiles, callable $filterCallback = null, $searchKey = null)
    {
        if ($searchKey !== null) {
            if (is_array($searchKey)) {
                $uploadedFiles = array_intersect_key($uploadedFiles, array_flip($searchKey));
                return static::parseUploadedFiles($uploadedFiles, $filterCallback);
            }

            if (isset($uploadedFiles[$searchKey])) {
                $uploadedFiles = [
                    $searchKey => $uploadedFiles[$searchKey],
                ];
                $parsedFiles = static::parseUploadedFiles($uploadedFiles, $filterCallback);
                if (isset($parsedFiles[$searchKey])) {
                    return $parsedFiles[$searchKey];
                }
            }

            return [];
        }

        $parsedFiles = [];
        foreach ($uploadedFiles as $field => $uploadedFile) {
            if (!isset($uploadedFile['error'])) {
                if (is_array($uploadedFile)) {
                    $subArray = static::parseUploadedFiles($uploadedFile, $filterCallback);
                    if (!empty($subArray)) {
                        if (!isset($parsedFiles[$field])) {
                            $parsedFiles[$field] = [];
                        }

                        $parsedFiles[$field] = $subArray;
                    }
                }
                continue;
            }

            if (!is_array($uploadedFile['error'])) {
                if ($filterCallback === null || $filterCallback($uploadedFile, $field) === true) {
                    if (!isset($parsedFiles[$field])) {
                        $parsedFiles[$field] = [];
                    }

                    $parsedFiles[$field] = [
                        'tmp_name' => $uploadedFile['tmp_name'],
                        'name'     => isset($uploadedFile['name']) ? $uploadedFile['name'] : null,
                        'type'     => isset($uploadedFile['type']) ? $uploadedFile['type'] : null,
                        'size'     => isset($uploadedFile['size']) ? $uploadedFile['size'] : null,
                        'error'    => $uploadedFile['error'],
                    ];
                }
            } else {
                $subArray = [];
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    // normalise subarray and re-parse to move the input's keyname up a level
                    $subArray[$fileIdx] = [
                        'tmp_name' => $uploadedFile['tmp_name'][$fileIdx],
                        'name'     => $uploadedFile['name'][$fileIdx],
                        'type'     => $uploadedFile['type'][$fileIdx],
                        'size'     => $uploadedFile['size'][$fileIdx],
                        'error'    => $uploadedFile['error'][$fileIdx],
                    ];

                    $subArray = static::parseUploadedFiles($subArray, $filterCallback);
                    if (!empty($subArray)) {
                        if (!isset($parsedFiles[$field])) {
                            $parsedFiles[$field] = [];
                        }

                        $parsedFiles[$field] = $subArray;
                    }
                }
            }
        }

        return $parsedFiles;
    }

    /**
     * Normalize a file path string so that it can be checked safely.
     *
     * Attempt to avoid invalid encoding bugs by transcoding the path. Then
     * remove any unnecessary path components including '.', '..' and ''.
     *
     * @link https://gist.github.com/thsutton/772287
     *
     * @param  string $path     The path to normalise.
     * @param  string $encoding The name of the path iconv() encoding.
     * @return string The path, normalised.
     */
    public static function normalizePath($path, $encoding = 'UTF-8')
    {
        $key = $path;

        if (isset(static::$normalizePathCache[$key])) {
            return static::$normalizePathCache[$key];
        }

        // Attempt to avoid path encoding problems.
        $path = iconv($encoding, $encoding.'//IGNORE//TRANSLIT', $path);

        if (strpos($path, '..') !== false || strpos($path, './') !== false) {
            // Process the components
            $parts = explode('/', $path);
            $safe = [];
            foreach ($parts as $idx => $part) {
                if ((empty($part) && !is_numeric($part)) || ($part === '.')) {
                    continue;
                } elseif ($part === '..') {
                    array_pop($safe);
                    continue;
                } else {
                    $safe[] = $part;
                }
            }

            // Return the "clean" path
            $path = implode(DIRECTORY_SEPARATOR, $safe);

            if ($key[0] === '/' && $path[0] !== '/') {
                $path = '/'.$path;
            }
        }

        static::$normalizePathCache[$key] = $path;

        return static::$normalizePathCache[$key];
    }
}
