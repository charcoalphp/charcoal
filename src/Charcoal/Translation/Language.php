<?php

namespace Charcoal\Translation;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Translation\LanguageInterface as LanguageInterface;
use \Charcoal\Translation\TranslationString as TranslationString;

/**
*
*/
class Language implements LanguageInterface
{
    /**
    * The language identifier, as 2-chacters ISO code
    * @var string $ident
    */
    private $ident;
    /**
    * The language
    * @var TranslationString $name;
    */
    private $name;

    public function set_data(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['name']) && $data['name'] !== null) {
            $this->set_name($data['name']);
        }
        return $this;
    }

    public function set_ident($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->ident;
    }

    public function set_name($name)
    {
        $this->name = new TranslationString($name);
        return $this;
    }

    public function name()
    {
        return $this->name;
    }
}
