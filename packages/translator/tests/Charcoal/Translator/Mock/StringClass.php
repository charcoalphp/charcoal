<?php

namespace Charcoal\Tests\Translator\Mock;

/**
 * {@link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php}
 */
class StringClass
{
    /**
     * @var string
     */
    protected $str;

    /**
     * @param string $str A string.
     */
    public function __construct($str)
    {
        $this->str = $str;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->str;
    }
}
