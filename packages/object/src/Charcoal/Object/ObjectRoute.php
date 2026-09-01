<?php

namespace Charcoal\Object;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Exception;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;
use Charcoal\Model\ModelFactoryTrait;
use Charcoal\Loader\CollectionLoaderAwareTrait;
// From 'charcoal-object'
use Charcoal\Object\ObjectRouteInterface;

/**
 * Represents a route to an object (i.e., a permalink).
 *
 * Intended to be used to collect all routes related to models
 * under a single source (e.g., database table).
 *
 * {@see \Charcoal\Object\ObjectRevision} for a similar model that aggregates data
 * under a common source.
 *
 * Requirements:
 *
 * - 'model/factory'
 * - 'model/collection/loader'
 */
class ObjectRoute extends AbstractModel implements
    ObjectRouteInterface
{
    use ModelFactoryTrait;
    use CollectionLoaderAwareTrait;

    /**
     * A route is active by default.
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * The route's URI.
     *
     * @var string
     */
    protected $slug;

    /**
     * The route's locale.
     *
     * @var string
     */
    protected $lang;

    /**
     * The creation timestamp.
     *
     * @var DateTime
     */
    protected $creationDate;

    /**
     * The last modification timestamp.
     *
     * @var DateTime
     */
    protected $lastModificationDate;

    /**
     * The foreign object type related to this route.
     *
     * @var string
     */
    protected $routeObjType;

    /**
     * The foreign object ID related to this route.
     *
     * @var mixed
     */
    protected $routeObjId;

    /**
     * The foreign object's template identifier.
     *
     * @var string
     */
    protected $routeTemplate;

    /**
     * Retrieve the foreign object's routes options.
     *
     * @var array
     */
    protected $routeOptions;

    /**
     * Retrieve the foreign object's routes options ident.
     *
     * @var string
     */
    protected $routeOptionsIdent;

    /**
     * Store a copy of the original—_preferred_—slug before alterations are made.
     *
     * @var string
     */
    private $originalSlug;

    /**
     * Store the increment used to create a unique slug.
     */
    private int $slugInc = 0;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * Event called before _creating_ the object.
     *
     * @see    \Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    #[\Override]
    protected function preSave()
    {
        $this->generateUniqueSlug();
        $this->setCreationDate('now');
        $this->setLastModificationDate('now');

        return parent::preSave();
    }

    /**
     * Event called before _updating_ the object.
     *
     * @see    \Charcoal\Source\StorableTrait::preUpdate() For the "update" Event.
     * @param  array $properties Optional. The list of properties to update.
     * @return boolean
     */
    #[\Override]
    protected function preUpdate(?array $properties = null)
    {
        $this->setCreationDate('now');
        $this->setLastModificationDate('now');

        return parent::preUpdate($properties);
    }

    /**
     * Determine if the current slug is unique.
     */
    public function isSlugUnique(): bool
    {
        $proto  = $this->modelFactory()->get(static::class);
        $loader = $this->collectionLoader();
        $loader
            ->reset()
            ->setModel($proto)
            ->addFilter('active', true)
            ->addFilter('slug', $this->getSlug())
            ->addFilter('lang', $this->getLang())
            ->addOrder('creation_date', 'desc')
            ->setPage(1)
            ->setNumPerPage(1);

        $routes = $loader->load()->values();
        if (!$routes) {
            return true;
        }
        $obj = reset($routes);
        if (!$obj->id()) {
            return true;
        }
        if ($obj->id() === $this->id()) {
            return true;
        }
        if (
            $obj->getRouteObjId() === $this->getRouteObjId() &&
            $obj->getRouteObjType() === $this->getRouteObjType() &&
            $obj->getLang() === $this->getLang()
        ) {
            $this->setId($obj->id());

            return true;
        }

        return false;
    }

    /**
     * Generate a unique URL slug for routable object.
     *
     * @return self
     */
    public function generateUniqueSlug()
    {
        if (!$this->isSlugUnique()) {
            if (!$this->originalSlug) {
                $this->originalSlug = $this->getSlug();
            }
            $this->slugInc++;
            $this->setSlug($this->originalSlug . '-' . $this->slugInc);

            return $this->generateUniqueSlug();
        }

        return $this;
    }

    /**
     * Set the object route URI.
     *
     * @param  string|null $slug The route.
     * @throws InvalidArgumentException If the slug argument is not a string.
     */
    public function setSlug($slug): static
    {
        if ($slug === null) {
            $this->slug = null;

            return $this;
        }
        if (!is_string($slug)) {
            throw new InvalidArgumentException(
                'Slug is not a string'
            );
        }
        $this->slug = $slug;

        return $this;
    }

    /**
     * Set the locale of the object route.
     *
     * @param  string $lang The route's locale.
     */
    public function setLang($lang): static
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Set the route's last creation date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @throws InvalidArgumentException If the date/time value is invalid.
     */
    public function setCreationDate($time): static
    {
        if (empty($time) && !is_numeric($time)) {
            $this->creationDate = null;

            return $this;
        }

        if (is_string($time)) {
            try {
                $time = new DateTime($time);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid Creation Date: %s',
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }

        if (!$time instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                'Creation Date must be a date/time string or an instance of DateTimeInterface'
            );
        }

        $this->creationDate = $time;

        return $this;
    }

    /**
     * Set the route's last modification date.
     *
     * @param  string|DateTimeInterface|null $time The date/time value.
     * @throws InvalidArgumentException If the date/time value is invalid.
     */
    public function setLastModificationDate($time): static
    {
        if (empty($time) && !is_numeric($time)) {
            $this->lastModificationDate = null;

            return $this;
        }

        if (is_string($time)) {
            try {
                $time = new DateTime($time);
            } catch (Exception $e) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid Updated Date: %s',
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
        }

        if (!$time instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                'Updated Date must be a date/time string or an instance of DateTimeInterface'
            );
        }

        $this->lastModificationDate = $time;

        return $this;
    }

    /**
     * Set the foreign object type related to this route.
     *
     * @param  string $type The object type.
     */
    public function setRouteObjType($type): static
    {
        $this->routeObjType = $type;

        return $this;
    }

    /**
     * Set the foreign object ID related to this route.
     *
     * @param  string $id The object ID.
     */
    public function setRouteObjId($id): static
    {
        $this->routeObjId = $id;

        return $this;
    }

    /**
     * Set the foreign object's template identifier.
     *
     * @param  string $template The template identifier.
     */
    public function setRouteTemplate($template): static
    {
        $this->routeTemplate = $template;

        return $this;
    }

    /**
     * Customize the template's options.
     *
     * @param  mixed $options Template options.
     */
    public function setRouteOptions($options): static
    {
        if (is_string($options)) {
            $options = json_decode($options, true);
        }

        $this->routeOptions = $options;

        return $this;
    }

    /**
     * @param string $routeOptionsIdent Template options ident.
     */
    public function setRouteOptionsIdent($routeOptionsIdent): static
    {
        $this->routeOptionsIdent = $routeOptionsIdent;

        return $this;
    }

    /**
     * Retrieve the object route.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Retrieve the locale of the object route.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Retrieve the route's creation date.
     *
     * @return DateTimeInterface|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Retrieve the route's last modification date.
     *
     * @return DateTimeInterface|null
     */
    public function getLastModificationDate()
    {
        return $this->lastModificationDate;
    }

    /**
     * Retrieve the foreign object type related to this route.
     *
     * @return string
     */
    public function getRouteObjType()
    {
        return $this->routeObjType;
    }

    /**
     * Retrieve the foreign object ID related to this route.
     *
     * @return string
     */
    public function getRouteObjId()
    {
        return $this->routeObjId;
    }

    /**
     * Retrieve the foreign object's template identifier.
     *
     * @return string
     */
    public function getRouteTemplate()
    {
        return $this->routeTemplate;
    }

    /**
     * Retrieve the template's customized options.
     *
     * @return array
     */
    public function getRouteOptions()
    {
        return $this->routeOptions;
    }

    /**
     * @return string
     */
    public function getRouteOptionsIdent()
    {
        return $this->routeOptionsIdent;
    }

    /**
     * Alias of {@see self::slug()}.
     */
    #[\Override]
    public function __toString(): string
    {
        return (string)$this->getSlug();
    }
}
