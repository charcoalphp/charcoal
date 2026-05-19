<?php

declare(strict_types=1);

namespace Charcoal\View;

/**
 * Base Template Loader
 *
 * Finds a template file in a collection of directory paths.
 */
abstract class AbstractLoader implements LoaderInterface
{
    private string $basePath = '';
    private array $paths = [];
    private array $dynamicTemplates = [];

    /**
     * The cache of searched template files.
     */
    private array $fileCache = [];

    /**
     * Default constructor, if none is provided by the concrete class implementations.
     *
     *
     * @param ?array $data The class dependencies map.
     */
    public function __construct(?array $data = null)
    {
        $this->setBasePath($data['base_path']);
        $this->setPaths($data['paths']);
    }

    /**
     * Load a template content
     *
     * @param  string $ident The template ident to load and render.
     * @return string
     */
    public function load($ident)
    {
        // Handle dynamic template
        if (str_starts_with($ident, '$')) {
            $ident = $this->dynamicTemplate(substr($ident, 1));
        }

        /**
         * Prevents the loader from passing a proper template through further
         * procedures meant for a template identifier.
         */
        if ($this->isTemplateString($ident)) {
            return $ident;
        }

        $file = $this->findTemplateFile($ident);
        if ($file === null || $file === '') {
            return $ident;
        }

        return file_get_contents($file);
    }

    /**
     * @param  string $varName The name of the variable to get template ident from.
     */
    public function dynamicTemplate(string $varName): string
    {
        if (!isset($this->dynamicTemplates[$varName])) {
            return '';
        }

        return $this->dynamicTemplates[$varName];
    }

    /**
     * @param  string      $varName       The name of the variable to set this template unto.
     * @param  string|null $templateIdent The "dynamic template" to set or NULL to clear.
     *     or if the template is not a string (and not null).
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void
    {
        if ($templateIdent === null) {
            $this->removeDynamicTemplate($varName);
            return;
        }

        $this->dynamicTemplates[$varName] = $templateIdent;
    }

    /**
     * @param  string $varName The name of the variable to remove.
     */
    public function removeDynamicTemplate(string $varName): void
    {
        unset($this->dynamicTemplates[$varName]);
    }

    public function clearDynamicTemplates(): void
    {
        $this->dynamicTemplates = [];
    }

    protected function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param  string $basePath The base path to set.
     */
    private function setBasePath(string $basePath): static
    {
        $basePath = realpath($basePath);
        $this->basePath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * @return string[]
     */
    protected function paths(): array
    {
        return $this->paths;
    }

    /**
     * @param  string[] $paths The list of path to add.
     */
    private function setPaths(array $paths): static
    {
        $this->paths = [];

        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * @param  string $path The path to add to the load.
     */
    private function addPath(string $path): static
    {
        $this->paths[] = $this->resolvePath($path);

        return $this;
    }

    /**
     * @param  string $path The path to resolve.
     */
    private function resolvePath(string $path): string
    {
        $basePath = $this->basePath();
        $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        if ($basePath && !str_contains($path, $basePath)) {
            $path = $basePath . DIRECTORY_SEPARATOR . $path;
        }

        return $path;
    }

    /**
     * Determine if the variable is a template literal.
     *
     * This method looks for any line-breaks in the given string,
     * which a file path would not allow.
     *
     * @param  string $ident The template being evaluated.
     * @return boolean Returns TRUE if the given value is most likely the template contents
     *     as opposed to a template identifier (file path).
     */
    protected function isTemplateString(string $ident): bool
    {
        return str_contains($ident, PHP_EOL);
    }

    /**
     * Get the template file (full path + filename) to load from an ident.
     *
     * This method first generates the filename for an identifier and search for it in all of the loader's paths.
     *
     * @param  string $ident The template identifier to load..
     * @return string|null The full path + filename of the found template. NULL if nothing was found.
     */
    protected function findTemplateFile(string $ident): ?string
    {
        $key = hash('md5', $ident);

        if (array_key_exists($key, $this->fileCache)) {
            return $this->fileCache[$key];
        }

        $filename    = $this->filenameFromIdent($ident);
        $searchPaths = $this->paths();
        foreach ($searchPaths as $searchPath) {
            $filepath = realpath($searchPath) . '/' . strtolower($filename);
            if (file_exists($filepath)) {
                $this->fileCache[$key] = $filepath;
                return $filepath;
            }
        }

        $filepath = null;
        $this->fileCache[$key] = $filepath;
        return $filepath;
    }

    /**
     * @param  string $ident The template identifier to convert to a filename.
     */
    abstract protected function filenameFromIdent(string $ident): string;
}
