<?php

namespace Charcoal\Translator;

interface TranslatableInterface
{
    /**
     * @param Translator  $translator The translator.
     * @param string|null $locale     The locale.
     * @return string
     */
    public function trans(Translator $translator, ?string $locale = null): string;
}
