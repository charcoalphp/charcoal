<?php

namespace Charcoal\Translator;

use RuntimeException;
// From 'charcoal-translator'
use Charcoal\Translator\Translator;

/**
 * The Translator Aware Trait provides the methods necessary for an object
 * to use a "Translator" service.
 */
trait TranslatorAwareTrait
{
    /**
     * The Translator service.
     *
     * @var Translator
     */
    private $translator;

    /**
     * Set the translator service.
     *
     * @param  Translator $translator The Translator service.
     * @return void
     */
    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Retrieve the translator service.
     *
     * @throws RuntimeException If the translator is accessed before having been set.
     * @return Translator
     */
    protected function translator()
    {
        if ($this->translator === null) {
            throw new RuntimeException(
                'Translator has not been set on this object.'
            );
        }
        return $this->translator;
    }
}
