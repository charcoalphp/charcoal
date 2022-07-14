<?php

namespace Charcoal\Config;

use Traversable;
use Throwable;
use Exception;
use LogicException;
use InvalidArgumentException;
use UnexpectedValueException;
// From 'symfony/yaml'
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Provides an object with the ability to read file contents.
 *
 * Supported file formats: INI, JSON, PHP, YAML*.
 *
 * Note: YAML requires the {@link https://packagist.org/packages/symfony/yaml Symfony YAML component}.
 *
 * This is a full implementation of {@see FileAwareInterface}.
 */
trait FileAwareTrait
{
    /**
     * Loads a configuration file.
     *
     * @param  string $path A path to a supported file.
     * @throws InvalidArgumentException If the path is invalid.
     * @return array An array on success.
     */
    public function loadFile($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'File must be a string'
            );
        }

        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf('File "%s" does not exist', $path)
            );
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'php':
                return $this->loadPhpFile($path);

            case 'json':
                return $this->loadJsonFile($path);

            case 'ini':
                return $this->loadIniFile($path);

            case 'yml':
            case 'yaml':
                return $this->loadYamlFile($path);
        }

        $validConfigExts = [ 'ini', 'json', 'php', 'yml' ];
        throw new InvalidArgumentException(sprintf(
            'Unsupported file format for "%s"; must be one of "%s"',
            $path,
            implode('", "', $validConfigExts)
        ));
    }

    /**
     * Load an INI file as an array.
     *
     * @param  string $path A path to an INI file.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An array on success.
     */
    private function loadIniFile($path)
    {
        $data = parse_ini_file($path, true);
        if ($data === false) {
            throw new UnexpectedValueException(
                sprintf('INI file "%s" is empty or invalid', $path)
            );
        }

        return $data;
    }

    /**
     * Load a JSON file as an array.
     *
     * @param  string $path A path to a JSON file.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An array on success.
     *     If the file is parsed as any other type, an empty array is returned.
     */
    private function loadJsonFile($path)
    {
        $data = null;
        $json = file_get_contents($path);
        if ($json) {
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = json_last_error_msg() ?: 'Unknown error';
                throw new UnexpectedValueException(
                    sprintf('JSON file "%s" could not be parsed: %s', $path, $error)
                );
            }
        }

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Load a PHP file, maybe as an array.
     *
     * Note:
     * - The context of $this is bound to the current object.
     * - Data may be any value; the {@see self::addFile()} method will ignore
     *   anything that isn't an (associative) array.
     *
     * @param  string $path A path to a PHP file.
     * @throws UnexpectedValueException If the file can not correctly be parsed.
     * @return array|Traversable An array or iterable object on success.
     *     If the file is parsed as any other type, an empty array is returned.
     */
    private function loadPhpFile($path)
    {
        try {
            $data = include $path;
        } catch (Exception $e) {
            $message = sprintf('PHP file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        } catch (Throwable $e) {
            $message = sprintf('PHP file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        }

        if (is_array($data) || ($data instanceof Traversable)) {
            return $data;
        }

        return [];
    }

    /**
     * Load a YAML file as an array.
     *
     * @param  string $path A path to a YAML/YML file.
     * @throws LogicException If a YAML parser is unavailable.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An array on success.
     *     If the file is parsed as any other type, an empty array is returned.
     */
    private function loadYamlFile($path)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new LogicException('YAML format requires the Symfony YAML component');
        }

        try {
            $yaml = new YamlParser();
            $data = $yaml->parseFile($path);
        } catch (Exception $e) {
            $message = sprintf('YAML file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        }

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }
}
