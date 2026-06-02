<?php

namespace Charcoal\User\Acl;

use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
// From 'charcoal-object'
use Charcoal\Object\CategorizableInterface;
use Charcoal\Object\CategorizableTrait;

/**
 * A permission is a simple string, that can be read with additional data (name + category) from storage.
 */
class Permission extends AbstractModel implements CategorizableInterface
{
    use CategorizableTrait;
    use TranslatorAwareTrait;

    private ?string $ident = null;

    /**
     * @var \Charcoal\Translator\Translation|null
     */
    private $name;

    /**
     * Permission can be used as a string (ident).
     */
    #[\Override]
    public function __toString(): string
    {
        if ($this->ident === null) {
            return '';
        }
        return $this->ident;
    }

    #[\Override]
    public function key(): string
    {
        return 'ident';
    }

    /**
     * @param string $ident The permission identifier.
     * @throws InvalidArgumentException If the ident is not a string.
     */
    public function setIdent($ident): static
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Permission ident needs to be a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    /**
     * @param mixed $name The permission name / label.
     */
    public function setName($name): static
    {
        $this->name = $this->translator()->translation($name);
        return $this;
    }

    /**
     * @return \Charcoal\Translator\Translation|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setTranslator($container['translator']);
    }
}
