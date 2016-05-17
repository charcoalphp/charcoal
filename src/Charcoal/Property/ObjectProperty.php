<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\Model\ModelInterface;
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\StorableInterface;
use \Charcoal\Translation\TranslationConfig;

use \Charcoal\Property\AbstractProperty;
use \Charcoal\Property\SelectablePropertyInterface;

/**
 * Object Property holds a reference to an external object.
 *
 * The object property implements the full `SelectablePropertyInterface` without using
 * its accompanying trait. (`set_choices`, `add_choice`, `choices`, `has_choice`, `choice`).
 */
class ObjectProperty extends AbstractProperty implements SelectablePropertyInterface
{
    /**
     * @var array $objectCache
     */
    static public $objectCache = [];

    /**
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * @var string $objType
     */
    private $objType;

    /**
     * @var string $pattern
     */
    private $pattern = '{{name}}';

    /**
     * The available selectable choices map.
     *
     * @var array $choices The internal choices
     */
    protected $choices = [];

    /**
     * @var ModelInterface $proto
     */
    private $proto;

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'object';
    }

    /**
     * @param FactoryInterface $factory The factory, to create model objects.
     * @return ObjectProperty Chainable
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface
     */
    private function modelFactory()
    {
        if ($this->modelFactory === null) {
            throw new Exception(
                sprintf('Model factory not set on object property "%s".')
            );
        }
        return $this->modelFactory;
    }

    /**
     * @param string $objType The object type.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return ObjectPropertyChainable
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Can not set property\'s object type: "Obj type" needs to be a string'
            );
        }
        $this->objType = $objType;
        return $this;
    }

    /**
     * @throws Exception If the object type was not previously set.
     * @return string
     */
    public function objType()
    {
        if ($this->objType === null) {
            throw new Exception(
                sprintf('No obj type defined. Invalid property "%s"', $this->ident())
            );
        }
        return $this->objType;
    }

    /**
     * @param string $pattern The render pattern.
     * @throws InvalidArgumentException If the pattern is not a string.
     * @return ObjectProperty Chainable
     */
    public function setPattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'Can not set property object pattern, needs to be a string.'
            );
        }
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * @return string
     */
    public function sqlType()
    {
        if ($this->multiple() === true) {
            return 'TEXT';
        } else {
            // Read from proto's key
            $proto = $this->proto();
            $key = $proto->p($proto->key());
            return $key->sqlType();
        }
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        // Read from proto's key
        $proto = $this->proto();
        $key = $proto->p($proto->key());
        return $key->sqlPdoType();
    }



    /**
     * At this point, does nothing but return
     * the actual value. Other properties could
     * parse values such as ObjectProperty who
     * could parse objects into object IDs.
     *
     * @param mixed $val Value to be parsed.
     * @return mixed
     */
    public function parseVal($val = null)
    {
        if ($val instanceof StorableInterface) {
            return $val->id();
        }

        if ($this->multiple()) {
            $out = [];
            foreach ($val as $i => $v) {
                if ($v instanceof StorableInterface) {
                    $out[] = $v->id();
                } else {
                    $out[] = $v;
                }
            }
            $val = $out;
        }

        return $val;
    }

    /**
     * Get the property's value in a format suitable for storage.
     *
     * @param mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val = null)
    {
        if ($val === null) {
            $this->logger->warning('Calling storageVal without argument.');
            $val = $this->val();
        }
        if ($val === null) {
            // Do not json_encode NULL values
            return null;
        }

        // Get parsedVal
        $val = $this->parseVal($val);

        if ($this->multiple()) {
            if (is_array($val)) {
                $val = implode($this->multipleSeparator(), $val);
            }
        }

        if (!is_scalar($val)) {
            return json_encode($val, true);
        }
        return $val;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->val();
    }

    /**
     * @return ModelInterface
     */
    public function proto()
    {
        if ($this->proto === null) {
            $this->proto = $this->modelFactory()->get($this->objType());
        }
        return $this->proto;
    }

    /**
     * @param mixed $val Optional. The value to display.
     * @return string
     */
    public function displayVal($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }

        if ($val === null) {
            return '';
        }

        $propertyValue = $val;

        if ($this->l10n() === true) {
            $translator = TranslationConfig::instance();

            $propertyValue = $propertyValue[$translator->currentLanguage()];
        }

        if ($this->multiple() === true) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($this->multipleSeparator(), $propertyValue);
            }
        } else {
            $propertyValue = [$propertyValue];
        }

        $names = [];
        foreach ($propertyValue as $objIdent) {
            $obj = $this->loadObject($objIdent);
            $names[] = $this->objPattern($obj);
        }
        return implode(', ', $names);
    }

    /**
     * Fulfills the SelectableProperty interface, but does nothing.
     *
     * @param array $choices The array of choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices)
    {
        unset($choices);
        $this->logger->debug('Choices can not be set for object properties. They are auto-generated from objects.');
        return $this;
    }

    /**
     * Add a choice to the available choices map.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice)
    {
        unset($choiceIdent, $choice);
        $this->logger->debug('Choices can not be added for object properties. They are auto-generated from objects.');
        return $this;
    }

    /**
     * Get the choices array map.
     *
     * @return array
     */
    public function choices()
    {
        $proto = $this->proto();
        $loader = new CollectionLoader([
            'logger'    => $this->logger,
            'factory'   => $this->modelFactory()
        ]);
        $loader->setModel($this->proto());

        if ($proto->hasProperty('active')) {
            $loader->addFilter('active', true);
        }

        $choices = [];
        $objects = $loader->load();
        foreach ($objects as $c) {
            $choice = [
                'value'   => $c->id(),
                'label'   => $this->objPattern($c),
                'title'   => $this->objPattern($c),
                'subtext' => ''
            ];

            if (is_callable([ $c, 'icon' ])) {
                $choice['icon'] = $c->icon();
            }

            $choices[$c->id()] = $choice;
        }

        return $choices;
    }

    /**
     * Returns wether a given choiceIdent exists or not.
     *
     * @param string $choiceIdent The choice identifier.
     * @return boolean True / false wether the choice exists or not.
     */
    public function hasChoice($choiceIdent)
    {
        $c = $this->loadObject($choiceIdent);
        return ($c->id() == $choiceIdent);
    }

    /**
     * Returns a choice structure for a given ident.
     *
     * @param string $choiceIdent The choice ident to load.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent)
    {
        $c = $this->loadObject($choiceIdent);

        $choice = [
            'value'   => $c->id(),
            'label'   => $this->objPattern($c),
            'title'   => $this->objPattern($c),
            'subtext' => '',
            'icon'    => $c->icon()
        ];

        return $choice;
    }

    /**
     * @param string $obj The object to "render".
     * @return string
     */
    protected function objPattern($obj)
    {
        $pattern = (string)$this->pattern();
        if ($obj instanceof Viewable && $obj->view() !== null) {
            return $obj->render($pattern);
        } else {
            $cb = function ($matches) use ($obj) {
                $method = trim($matches[1]);
                if (method_exists($obj, $method)) {
                    return call_user_func([$obj, $method]);
                } elseif (isset($obj[$method])) {
                    return $obj[$method];
                } else {
                    return '';
                }
            };
            return preg_replace_callback('~{{(.*?)}}~i', $cb, $pattern);
        }
    }

    /**
     * @param mixed $id Object id.
     * @return ModelInterface
     */
    private function loadObject($id)
    {
        $cached = $this->loadObjectFromCache($id);
        if ($cached !== null) {
            return $cached;
        }
        $obj = $this->modelFactory()->create($this->objType());
        $obj->load($id);
        $this->addObjectToCache($id, $obj);
        return $obj;
    }

    /**
     * @param mixed $id Object id.
     * @return null|ModelInterface
     */
    private function loadObjectFromCache($id)
    {
        $objType = $this->objType();
        if (isset(static::$objectCache[$objType][$id])) {
            return static::$objectCache[$objType][$id];
        } else {
            return null;
        }
    }

    /**
     * @param mixed          $id  Object id.
     * @param ModelInterface $obj Object to store.
     * @return void
     */
    private function addObjectToCache($id, ModelInterface $obj)
    {
        $objType = $this->objType();
        static::$objectCache[$objType][$id] = $obj;
    }
}
