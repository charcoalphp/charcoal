<?php

namespace Charcoal\Translator;

use Symfony\Component\Translation\TranslatorInterface;

interface TranslatableInterface
{
    /**
     * @param  TranslatorInterface $translator The translator.
     * @param  ?string             $locale     The locale.
     * @return mixed
     */
    public function trans(TranslatorInterface $translator, ?string $locale = null);
}
