<?php

namespace Charcoal\Admin\Script\Translation;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 * Find all strings to be translated in mustache or php files
 */
class TranslateScript extends AdminScript
{
    /**
     * @var string $fileType
     */
    protected $fileType;

    /**
     * @var string $output
     */
    protected $output;

    /**
     * @var string $path
     */
    protected $path;

    /**
     * @var array $locales
     */
    protected $locales;

    /**
     * Valid arguments:
     * - path : path/to/files
     * - type : mustache | php
     */
    #[\Override]
    public function defaultArguments(): array
    {
        $arguments = [
            'path' => [
                'longPrefix'   => 'path',
                'description'  => 'Path relative to the project installation (ex: templates/*/*/)',
                'defaultValue' => ''
            ],
            'type' => [
                'longPrefix'   => 'type',
                'description'  => 'File type (mustache || php)',
                'defaultValue' => ''
            ],
            'output' => [
                'longPrefix'   => 'output',
                'description'  => 'Output file path',
                'defaultValue' => ''
            ]
        ];
        return array_merge(parent::defaultArguments(), $arguments);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Unused
        unset($request);

        $climate = $this->climate();

        $climate->underline()->out(
            'TRANSLATIONS'
        );

        $path = $this->path();
        $type = $this->fileType();

        switch ($type) {
            case 'mustache':
            default:
                $regex = '/{{\s*#\s*_t\s*}}((.|\n|\r|\n\r)*?){{\s*\/\s*_t\s*}}/i';
                $file = '*.mustache';
                $index = 1;
                break;
            case 'php':
                $regex = '/([^\d\wA-Za-z])_t\(\s*\n*\r*(["\'])(?<text>(.|\n|\r|\n\r)*?)\2\s*\n*\r*\)/i';
                $index = 'text';
                $file = '*.php';
                break;
        }

        // Remove vendor/charcoal/app
        $base = $this->base();
        $glob = $this->globRecursive($base . $path . $file);


        $input = $climate->confirm(
            'Save to CSV?'
        );

        $translations = [];
        $toCSV = $input->confirmed();

        // Check out existing translations
        if ($toCSV) {
            $output = $this->file();
            if (file_exists($base . $output)) {
                // loop all
                $translations = $this->fromCSV();
            }
        }

        // Loop files to get original text.
        foreach ($glob as $f) {
            $text = file_get_contents($f);
            if (preg_match($regex, $text)) {
                preg_match_all($regex, $text, $array);

                $i = 0;
                $t = count($array[$index]);

                for (; $i < $t; $i++) {
                    $orig = $array[$index][$i];
                    if (!isset($translations[$orig])) {
                        $translations[$orig] = [
                            'translation' => '',
                            'context' => $f
                        ];
                    }
                }
            }
        }

        if ($toCSV) {
            $this->toCSV($translations);
        }

        return $response;
    }

    /**
     * @param string  $pattern The pattern to search.
     * @param integer $flags   The glob flags.
     * @return array
     * @see http://in.php.net/manual/en/function.glob.php#106595
     */
    public function globRecursive($pattern, $flags = 0): array|false
    {
        $max = $this->maxRecursiveLevel();
        $i = 1;
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', (GLOB_ONLYDIR | GLOB_NOSORT)) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir . '/' . basename($pattern), $flags));
            $i++;
            if ($i >= $max) {
                break;
            }
        }
        return $files;
    }

    /**
     * BASE URL
     * Realpath
     */
    public function base(): string
    {
        return realpath($this->app()->config()->get('base_path') . DIRECTORY_SEPARATOR . '../../../') . '/';
    }

    /**
     * ARGUMENTS
     * @return TranslateScript Chainable
     */
    public function getPath(): static
    {
        $path = $this->argOrInput('path');
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function path()
    {
        if (!$this->path) {
            $this->getPath();
        }
        return $this->path;
    }

    /**
     * @return TranslateScript Chainable
     */
    public function getFileType(): static
    {
        $type = $this->argOrInput('type');
        $this->fileType = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function fileType()
    {
        if (!$this->fileType) {
            $this->getFileType();
        }
        return $this->fileType;
    }

    /**
     * @return string
     */
    public function file()
    {
        if ($this->output) {
            return $this->output;
        }
        $locales = $this->locales();
        $this->output = $locales['file'];
        return $this->output;
    }

    /**
     * Returns associative array
     * 'original text' => [ 'translation' => 'translation text', 'context' => 'filename' ]
     */
    public function fromCSV(): array
    {
        $output = $this->file();
        $base = $this->base();
        $file = fopen($base . $output, 'r');

        if (!$file) {
            return [];
        }

        $results = [];
        $row = 0;
        while (($data = fgetcsv($file, 0, ',', escape: '\\')) !== false) {
            $row++;
            // Skip column names
            if ($row === 1) {
                continue;
            }
            /**
             * data[0] = ORIGINAL
             * data[1] = TRANSLATION
             * data[2] = CONTEXT
             */
            $translation = $this->translateCSV($data);
            if ($translation !== []) {
                $results[$translation[0]] = $translation[1];
            }
        }

        return $results;
    }

    /**
     * @param array $translations The translations to save in CSV.
     * @return TranslateScript Chainable
     */
    public function toCSV(array $translations): static
    {
        $base = $this->base();
        $output = $this->file();

        $separator = $this->separator();
        $enclosure = $this->enclosure();
        $columns = $this->columns();

        // Create / open the handle
        $dirname = dirname($base . $output);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $file = fopen($base . $output, 'w');
        if (!$file) {
            // Wtf happened?
            return $this;
        }
        fputcsv($file, $columns, $separator, $enclosure, escape: '\\');

        foreach ($translations as $orig => $translation) {
            $data = [ $orig, $translation['translation'], $translation['context'] ];
            fputcsv($file, $data, $separator, $enclosure, escape: '\\');
        }

        fclose($file);

        return $this;
    }

    /**
     * @param array $data The translation data.
     * @todo multiple langs
     * data[0] = ORIGINAL
     * data[1] = TRANSLATION
     * data[2] = CONTEXT
     */
    public function translateCSV(array $data): array
    {
        if (count($data) < 3) {
            return [];
        }

        return [
            $data[0],
            [
                'translation' => $data[1],
                'context' => $data[2]
            ]
        ];
    }

    /**
     * @todo make this optional
     * @return string lang ident
     */
    public function origLanguage(): string
    {
        return 'fr';
    }

    /**
     * Get opposite languages from DATABASE
     *
     * @return [type] [description]
     * @return mixed[]
     */
    public function oppositeLanguages(): array
    {
        $this->app()->config();
        $locales = $this->locales();
        $languages = $locales['languages'];

        $opposite = [];
        $orig = $this->origLanguage();

        foreach ($languages as $ident => $opts) {
            if ($ident != $orig) {
                $opposite[] = $ident;
            }
        }
        return $opposite;
    }

    /**
     * Locales set in config.json
     * Expects languages | file | default_language
     *
     * @return array
     */
    public function locales()
    {
        if ($this->locales) {
            return $this->locales;
        }

        $cfg = $this->app()->config();
        $locales = $cfg['locales'] ?? [];
        $languages = $locales['languages'] ?? [];
        $file = $locales['file'] ?? $this->argOrInput('output');
        // Default to FR
        $default = $locales['default_language'] ?? 'fr';

        $this->locales = [
            'languages' => $languages,
            'file' => $file,
            'default_language' => $default
        ];
        return $this->locales;
    }

    /**
     * Columns of CSV file
     * This is already built to take multiple languages
     */
    public function columns(): array
    {
        $orig = $this->origLanguage();
        $opposites = $this->oppositeLanguages();

        $columns = [ $orig ];

        foreach ($opposites as $lang) {
            $columns[] = $lang;
        }

        // Add context.
        $columns[] = 'context';

        return $columns;
    }

    public function enclosure(): string
    {
        return '"';
    }

    public function separator(): string
    {
        return ',';
    }

    public function maxRecursiveLevel(): int
    {
        return 4;
    }
}
