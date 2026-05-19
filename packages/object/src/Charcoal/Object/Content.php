<?php

namespace Charcoal\Object;

use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
// From 'charcoal-object'
use Charcoal\Object\ContentInterface;
use Charcoal\Object\AuthorableInterface;
use Charcoal\Object\AuthorableTrait;
use Charcoal\Object\RevisionableInterface;
use Charcoal\Object\RevisionableTrait;
use Charcoal\Object\TimestampableInterface;
use Charcoal\Object\TimestampableTrait;

/**
 *
 */
class Content extends AbstractModel implements
    AuthorableInterface,
    ContentInterface,
    RevisionableInterface,
    TimestampableInterface
{
    use AuthorableTrait;
    use RevisionableTrait;
    use TranslatorAwareTrait;
    use TimestampableTrait;

    /**
     * Objects are active by default
     */
    private bool $active = true;

    /**
     * The position is used for ordering lists
     */
    private ?int $position = 0;


    private ?\Charcoal\Factory\FactoryInterface $modelFactory = null;

    /**
     * @var string[]
     */
    private array $requiredAclPermissions = [];

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setTranslator($container['translator']);
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory used to create models.
     * @return Content Chainable
     */
    protected function setModelFactory(FactoryInterface $factory): static
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface The model factory.
     */
    protected function modelFactory(): ?\Charcoal\Factory\FactoryInterface
    {
        return $this->modelFactory;
    }

    /**
     * @param boolean $active The active flag.
     * @return Content Chainable
     */
    public function setActive($active): static
    {
        $this->active = (bool) $active;
        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param integer $position The position (for ordering purpose).
     * @throws InvalidArgumentException If the position is not an integer (or numeric integer string).
     * @return Content Chainable
     */
    public function setPosition($position): static
    {
        if ($position === null) {
            $this->position = null;
            return $this;
        }

        if (!is_numeric($position)) {
            throw new InvalidArgumentException(sprintf(
                'Position must be an integer, received %s',
                get_debug_type($position)
            ));
        }

        $this->position = (int)$position;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }


    /**
     * @throws InvalidArgumentException If the ACL permissions are invalid.
     * @param  string|string[] $permissions The required ACL permissions.
     * @return Content Chainable
     */
    public function setRequiredAclPermissions($permissions): static
    {
        if ($permissions === null || !$permissions) {
            $this->requiredAclPermissions = [];
            return $this;
        }
        if (is_string($permissions)) {
            $permissions = explode(',', $permissions);
            $permissions = array_map(trim(...), $permissions);
        }
        if (!is_array($permissions)) {
            throw new InvalidArgumentException(
                sprintf('Invalid ACL permissions. Permissions need to be an array (%s given)', gettype($permissions))
            );
        }
        $this->requiredAclPermissions = $permissions;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRequiredAclPermissions(): array
    {
        return $this->requiredAclPermissions;
    }

    /**
     * StorableTrait > preSave(): Called automatically before saving the object to source.
     * For content object, set the `created` and `lastModified` properties automatically
     */
    #[\Override]
    protected function preSave(): bool
    {
        parent::preSave();

        // Timestampable properties
        $this->setCreated('now');
        $this->setLastModified('now');

        return true;
    }

    /**
     * StorableTrait > preUpdate(): Called automatically before updating the object to source.
     * For content object, set the `lastModified` property automatically.
     *
     * @param array $properties The properties (ident) set for update.
     */
    #[\Override]
    protected function preUpdate(?array $properties = null): bool
    {
        parent::preUpdate($properties);

        // Content is revisionable
        if ($this['revisionEnabled']) {
            $this->generateRevision();
        }

        // Timestampable propertiees
        $this->setLastModified('now');

        return true;
    }
}
