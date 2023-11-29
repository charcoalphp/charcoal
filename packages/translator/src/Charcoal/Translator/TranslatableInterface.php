<?php

namespace Charcoal\Translator;

use Symfony\Component\Translation\TranslatorInterface;

interface TranslatableInterface
{
    /**
     * @param TranslatorInterface $translator The $translator to use.
     * @return mixed
     */
    public function trans(TranslatorInterface $translator);
}
