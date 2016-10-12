<?php

namespace Charcoal\Loader;

use \InvalidArgumentException;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

/**
 * Base File Loader
 */
class FileLoader implements
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The loader's identifier (for caching found paths).
     *
     * @var string
     */
    protected $ident;

    /**
     * The paths to search in.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * The base path to prepend to any relative paths to search in.
     *
     * @var string
     */
    private $basePath = '';

    /**
     * Return new FileLoader object.
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['base_path'])) {
            $this->setBasePath($data['base_path']);
        }

        if (isset($data['paths'])) {
            $this->addPaths($data['paths']);
        }

        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }

        $this->setLogger($data['logger']);
    }

    /**
     * Retrieve the loader's identifier.
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set the loader's identifier.
     *
     * @param  mixed $ident A subset of language identifiers.
     * @throws InvalidArgumentException If the ident is invalid.
     * @return self
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Identifier for [%1$s] must be a string.',
                    get_called_class()
                )
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the base path for relative search paths.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Assign a base path for relative search paths.
     *
     * @param  string $basePath The base path to use.
     * @throws InvalidArgumentException If the base path parameter is not a string.
     * @return self
     */
    public function setBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException(
                'Base path must be a string'
            );
        }

        $basePath = realpath($basePath);

        $this->basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Returns the content of the first file found in search path.
     *
     * @param  string|null $ident Optional. A file to search for.
     * @return string The file's content or an empty string.
     */
    public function load($ident = null)
    {
        if ($ident === null) {
            return '';
        }

        $fileContent = $this->loadFirstFromSearchPath($ident);

        if ($fileContent) {
            return $fileContent;
        }

        return '';
    }

    /**
     * Load the first match from search paths.
     *
     * @param  string $filename A file to search for.
     * @return string|null The matched file's content or an empty string.
     */
    protected function loadFirstFromSearchPath($filename)
    {
        $file = $this->firstMatchingFilename($filename);

        if ($file) {
            return file_get_contents($file);
        }

        return null;
    }

    /**
     * Retrieve the first match from search paths.
     *
     * @param  string $filename A file to search for.
     * @return string The full path to the matched file.
     */
    protected function firstMatchingFilename($filename)
    {
        if (file_exists($filename)) {
            return $filename;
        }

        $paths = $this->paths();

        if (empty($paths)) {
            return null;
        }

        foreach ($paths as $path) {
            $file = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Retrieve all matches from search paths.
     *
     * @param  string $filename A file to search for.
     * @return array An array of matches.
     */
    protected function allMatchingFilenames($filename)
    {
        $matches = [];

        if (file_exists($filename)) {
            $matches[] = $filename;
        }

        $paths = $this->paths();

        if (empty($paths)) {
            return $matches;
        }

        foreach ($paths as $path) {
            $file = $path.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($file)) {
                $matches[] = $file;
            }
        }

        return $matches;
    }
    /**
     * Load the contents of a JSON file.
     *
     * @param  mixed $filename The file path to retrieve.
     * @throws InvalidArgumentException If a JSON decoding error occurs.
     * @return array|null
     */
    protected function loadJsonFile($filename)
    {
        $content = file_get_contents($filename);

        if ($content === null) {
            return null;
        }

        $data  = json_decode($content, true);
        $error = json_last_error();

        if ($error == JSON_ERROR_NONE) {
            return $data;
        }

        switch ($error) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $issue = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $issue = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $issue = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $issue = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $issue = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $issue = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON %s could not be parsed: "%s"', $filename, $issue)
        );
    }

    /**
     * Retrieve the searchable paths.
     *
     * @return string[]
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * Assign a list of paths.
     *
     * @param  string[] $paths The list of paths to add.
     * @return self
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);

        return $this;
    }

    /**
     * Append a list of paths.
     *
     * @param  string[] $paths The list of paths to add.
     * @return self
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * Append a path.
     *
     * @param  string $path A file or directory path.
     * @throws InvalidArgumentException If the path does not exist or is invalid.
     * @return self
     */
    public function addPath($path)
    {
        $path = $this->resolvePath($path);

        if ($path && $this->validatePath($path)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Prepend a path.
     *
     * @param  string $path A file or directory path.
     * @return self
     */
    public function prependPath($path)
    {
        $path = $this->resolvePath($path);

        if ($path && $this->validatePath($path)) {
            array_unshift($this->paths, $path);
        }

        return $this;
    }

    /**
     * Parse a relative path using the base path if needed.
     *
     * @param  string $path The path to resolve.
     * @throws InvalidArgumentException If the path is invalid.
     * @return string
     */
    public function resolvePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $basePath = $this->basePath();
        $path = ltrim($path, '/\\');

        if ($basePath && strpos($path, $basePath) === false) {
            $path = $basePath.$path;
        }

        return $path;
    }

    /**
     * Validate a resolved path.
     *
     * @param  string $path The path to validate.
     * @return boolean Returns TRUE if the path is valid otherwise FALSE.
     */
    public function validatePath($path)
    {
        return file_exists($path);
    }
}
