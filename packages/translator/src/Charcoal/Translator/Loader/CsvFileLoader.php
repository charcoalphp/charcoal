<?php

namespace Charcoal\Translator\Loader;

use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\FileLoader as SymfonyLoader;

/**
 * CsvFileLoader loads translations from CSV files.
 *
 * Replaces Symfony's CSV file loader to resolve deprecations
 * in PHP 8.4 (escape parameter of {@see \SplFileObject::setCsvControl()})
 * and PHP 8.6 (CSV methods of {@see \SplFileObject}) and issues with
 * empty lines returning `[ null ]` instead of being skipped.
 *
 * Changes are based on {@link https://github.com/symfony/symfony/blob/v8.1.12/src/Symfony/Component/Translation/Loader/CsvFileLoader.php}.
 *
 * @todo Remove this class if Charcoal updates symfony/translation to v6.4.44+.
 */
class CsvFileLoader extends SymfonyLoader
{
    private string $delimiter = ';';
    private string $enclosure = '"';

    /**
     * @param  string $resource
     * @throws NotFoundResourceException if stream content has an invalid format
     * @return array
     */
    protected function loadResource($resource)
    {
        $messages = [];

        if (!$file = @fopen($resource, 'r')) {
            throw new NotFoundResourceException(sprintf('Error opening file "%s".', $resource));
        }

        try {
            while (false !== $data = fgetcsv($file, null, $this->delimiter, $this->enclosure, '')) {
                // empty lines are read as [null]
                if (isset($data[1]) && 2 === count($data) && '#' !== substr($data[0], 0, 1)) {
                    $messages[$data[0]] = $data[1];
                }
            }
        } finally {
            fclose($file);
        }

        return $messages;
    }

    /**
     * Sets the delimiter and enclosure character for CSV.
     */
    public function setCsvControl(string $delimiter = ';', string $enclosure = '"'): void
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }
}
