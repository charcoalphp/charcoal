<?php

declare(strict_types=1);

namespace Charcoal\Tests\Translator\Mock;

/**
 * {@link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php}
 */
class StringClass implements \Stringable
{
    /**
     * @param string $str A string.
     */
    public function __construct(protected $str)
    {
    }

    public function __toString(): string
    {
        return $this->str;
    }
}
