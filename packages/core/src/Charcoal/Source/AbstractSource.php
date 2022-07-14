<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
// From 'charcoal-core'
use Charcoal\Source\SourceConfig;
use Charcoal\Source\SourceInterface;
use Charcoal\Source\ModelAwareTrait;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\FilterCollectionTrait;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Source\OrderCollectionTrait;
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;

/**
 * Data Storage Source Handler.
 */
abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface,
    LoggerAwareInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;
    use ModelAwareTrait;
    use FilterCollectionTrait {
        FilterCollectionTrait::addFilter as pushFilter;
    }
    use OrderCollectionTrait {
        OrderCollectionTrait::addOrder as pushOrder;
    }

    /**
     * The {@see self::$model}'s properties.
     *
     * Stored as an associative array to maintain uniqueness.
     *
     * @var array<string,boolean>
     */
    private $properties = [];

    /**
     * Store the source query pagination.
     *
     * @var PaginationInterface
     */
    protected $pagination;

    /**
     * Create a new source handler.
     *
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->setLogger($data['logger']);
    }

    /**
     * Reset everything but the model.
     *
     * @return self
     */
    public function reset()
    {
        $this->properties = [];
        $this->filters    = [];
        $this->orders     = [];
        $this->pagination = null;

        return $this;
    }

    /**
     * Set the source's settings.
     *
     * @param  array $data Data to assign to the source.
     * @return self
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $val) {
            $setter = $this->setter($key);
            if (is_callable([ $this, $setter ])) {
                $this->{$setter}($val);
            } else {
                $this->{$key} = $val;
            }
        }

        return $this;
    }

    /**
     * Set the properties of the source to fetch.
     *
     * This method accepts an array of property identifiers
     * that will, if supported, be fetched from the source.
     *
     * If no properties are set, it is assumed that
     * all model propertiesare to be fetched.
     *
     * @param  (string|PropertyInterface)[] $properties One or more property keys to set.
     * @return self
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];
        $this->addProperties($properties);

        return $this;
    }

    /**
     * Determine if the source has any properties to fetch.
     *
     * @return boolean TRUE if properties are defined, otherwise FALSE.
     */
    public function hasProperties()
    {
        return !empty($this->properties);
    }

    /**
     * Get the properties of the source to fetch.
     *
     * @return string[]
     */
    public function properties()
    {
        return array_keys($this->properties);
    }

    /**
     * Add properties of the source to fetch.
     *
     * @param  (string|PropertyInterface)[] $properties One or more property keys to append.
     * @return self
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * Add a property of the source to fetch.
     *
     * @param  string|PropertyInterface $property A property key to append.
     * @return self
     */
    public function addProperty($property)
    {
        $property = $this->resolvePropertyName($property);
        $this->properties[$property] = true;
        return $this;
    }

    /**
     * Remove a property of the source to fetch.
     *
     * @param  string|PropertyInterface $property A property key.
     * @return self
     */
    public function removeProperty($property)
    {
        $property = $this->resolvePropertyName($property);
        unset($this->properties[$property]);
        return $this;
    }

    /**
     * Resolve the name for the given property, throws an Exception if not.
     *
     * @param  mixed $property Property to resolve.
     * @throws InvalidArgumentException If property is not a string, empty, or invalid.
     * @return string The property name.
     */
    protected function resolvePropertyName($property)
    {
        if ($property instanceof PropertyInterface) {
            $property = $property->ident();
        }

        if (!is_string($property)) {
            throw new InvalidArgumentException(sprintf(
                '[%s] property must be a string',
                $this->getModelClassForException()
            ));
        }

        if ($property === '') {
            throw new InvalidArgumentException(sprintf(
                '[%s] property can not be empty',
                $this->getModelClassForException()
            ));
        }

        return $property;
    }

    /**
     * Append a query filter on the source.
     *
     * There are 3 different ways of adding a filter:
     * - as a `Filter` object, in which case it will be added directly.
     *   - `addFilter($obj);`
     * - as an array of options, which will be used to build the `Filter` object
     *   - `addFilter(['property' => 'foo', 'value' => 42, 'operator' => '<=']);`
     * - as 3 parameters: `property`, `value` and `options`
     *   - `addFilter('foo', 42, ['operator' => '<=']);`
     *
     * @deprecated 0.3 To be replaced with FilterCollectionTrait::addFilter()
     *
     * @uses   self::parseFilterWithModel()
     * @uses   FilterCollectionTrait::processFilter()
     * @param  mixed $param   The property to filter by,
     *     a {@see FilterInterface} object,
     *     or a filter array structure.
     * @param  mixed $value   Optional value for the property to compare against.
     *     Only used if the first argument is a string.
     * @param  array $options Optional extra settings to apply on the filter.
     * @throws InvalidArgumentException If the $param argument is invalid.
     * @return self
     */
    public function addFilter($param, $value = null, array $options = null)
    {
        if (is_string($param) && $value !== null) {
            $expr = $this->createFilter();
            $expr->setProperty($param);
            $expr->setValue($value);
        } else {
            $expr = $param;
        }

        $expr = $this->processFilter($expr);

        /** @deprecated 0.3 */
        if (is_array($param) && isset($param['options'])) {
            $expr->setData($param['options']);
        }

        if (is_array($options)) {
            $expr->setData($options);
        }

        $this->filters[] = $this->parseFilterWithModel($expr);
        return $this;
    }

    /**
     * Process a query filter with the current model.
     *
     * @todo   If property is L10N, turn filter into group of filters for each language.
     * @param  FilterInterface $filter The expression object.
     * @return FilterInterface The parsed expression object.
     */
    protected function parseFilterWithModel(FilterInterface $filter)
    {
        if ($this->hasModel()) {
            if ($filter->hasProperty()) {
                $model    = $this->model();
                $property = $filter->property();
                if (is_string($property) && $model->hasProperty($property)) {
                    $property = $model->property($property);

                    if ($property['l10n']) {
                        $filter->setProperty($property->l10nIdent());
                    }

                    if ($property['multiple']) {
                        $filter->setOperator('FIND_IN_SET');
                    }
                }
            }

            if ($filter instanceof FilterCollectionInterface) {
                $filter->traverseFilters(function (FilterInterface $expr) {
                    $this->parseFilterWithModel($expr);
                });
            }
        }

        return $filter;
    }

    /**
     * Create a new query filter expression.
     *
     * @see    FilterCollectionTrait::createFilter()
     * @param  array $data Optional expression data.
     * @return FilterInterface A new filter expression object.
     */
    protected function createFilter(array $data = null)
    {
        $filter = new Filter();
        if ($data !== null) {
            $filter->setData($data);
        }
        return $filter;
    }

    /**
     * Append a query order on the source.
     *
     * @deprecated 0.3 To be replaced with OrderCollectionTrait::addOrder()
     *
     * @uses   self::parseOrderWithModel()
     * @uses   OrderCollectionTrait::processOrder()
     * @param  mixed  $param   The property to sort by,
     *     a {@see OrderInterface} object,
     *     or a order array structure.
     * @param  string $mode    Optional sorting mode.
     *     Defaults to ascending if a property is provided.
     * @param  array  $options Optional extra settings to apply on the order.
     * @throws InvalidArgumentException If the $param argument is invalid.
     * @return self
     */
    public function addOrder($param, $mode = 'asc', array $options = null)
    {
        if (is_string($param) && $mode !== null) {
            $expr = $this->createOrder();
            $expr->setProperty($param);
            $expr->setMode($mode);
        } else {
            $expr = $param;
        }

        $expr = $this->processOrder($expr);

        /** @deprecated 0.3 */
        if (is_array($param) && isset($param['options'])) {
            $expr->setData($param['options']);
        }

        if (is_array($options)) {
            $expr->setData($options);
        }

        $this->orders[] = $this->parseOrderWithModel($expr);
        return $this;
    }

    /**
     * Process a query order with the current model.
     *
     * @param  OrderInterface $order The expression object.
     * @return OrderInterface The parsed expression object.
     */
    protected function parseOrderWithModel(OrderInterface $order)
    {
        if ($this->hasModel()) {
            if ($order->hasProperty()) {
                $model    = $this->model();
                $property = $order->property();
                if (is_string($property) && $model->hasProperty($property)) {
                    $property = $model->property($property);

                    if ($property['l10n']) {
                        $order->setProperty($property->l10nIdent());
                    }
                }
            }

            if ($order instanceof OrderCollectionInterface) {
                $order->traverseOrders(function (OrderInterface $expr) {
                    $this->parseOrderWithModel($expr);
                });
            }
        }

        return $order;
    }

    /**
     * Create a new query order expression.
     *
     * @param  array $data Optional expression data.
     * @return OrderInterface
     */
    protected function createOrder(array $data = null)
    {
        $order = new Order();
        if ($data !== null) {
            $order->setData($data);
        }
        return $order;
    }

    /**
     * Set query pagination.
     *
     * @param  mixed        $param The pagination object or array.
     * @param  integer|null $limit The number of results to fetch if $param is a page number.
     * @throws InvalidArgumentException If the $param argument is invalid.
     * @return self
     */
    public function setPagination($param, $limit = null)
    {
        if ($param instanceof PaginationInterface) {
            $pager = $param;
        } elseif (is_numeric($param)) {
            $pager = $this->createPagination();
            $pager->setPage($param);
            $pager->setNumPerPage($limit);
        } elseif (is_array($param)) {
            $pager = $this->createPagination();
            $pager->setData($param);
        } else {
            throw new InvalidArgumentException(sprintf(
                '[%s] Can not set pagination, invalid argument',
                $this->getModelClassForException()
            ));
        }

        $this->pagination = $pager;

        return $this;
    }

    /**
     * Determine if the source has defined a query pagination.
     *
     * @return boolean
     */
    public function hasPagination()
    {
        return ($this->pagination !== null);
    }

    /**
     * Get query pagination.
     *
     * If the pagination wasn't previously define, a new Pagination object will be created.
     *
     * @return PaginationInterface
     */
    public function pagination()
    {
        if ($this->pagination === null) {
            $this->pagination = $this->createPagination();
        }

        return $this->pagination;
    }

    /**
     * Create a new pagination clause.
     *
     * @param  array $data Optional clause data.
     * @return PaginationInterface
     */
    protected function createPagination(array $data = null)
    {
        $pagination = new Pagination();
        if ($data !== null) {
            $pagination->setData($data);
        }
        return $pagination;
    }

    /**
     * Alias for {@see Pagination::setPage()}.
     *
     * @param  integer $page The current page.
     *     Pages should start at 1.
     * @return self
     */
    public function setPage($page)
    {
        $this->pagination()->setPage($page);
        return $this;
    }

    /**
     * Alias for {@see Pagination::page()}.
     *
     * @return integer
     */
    public function page()
    {
        return $this->pagination()->page();
    }

    /**
     * Alias for {@see Pagination::setNumPerPage()}.
     *
     * @param  integer $count The number of results to return, per page.
     *     Use 0 to request all results.
     * @return self
     */
    public function setNumPerPage($count)
    {
        $this->pagination()->setNumPerPage($count);
        return $this;
    }

    /**
     * Alias for {@see Pagination::numPerPage()}.
     *
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pagination()->numPerPage();
    }

    /**
     * Create a new database source config.
     *
     * @see    \Charcoal\Config\ConfigurableTrait
     * @param  array $data Optional data.
     * @return SourceConfig
     */
    public function createConfig(array $data = null)
    {
        $config = new SourceConfig($data);
        return $config;
    }

    /**
     * Load item by the primary key.
     *
     * @param  mixed             $ident Ident can be any scalar value.
     * @param  StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    abstract public function loadItem($ident, StorableInterface $item = null);

    /**
     * Load items for the given model.
     *
     * @param  StorableInterface|null $item Optional model.
     * @return StorableInterface[]
     */
    abstract public function loadItems(StorableInterface $item = null);

    /**
     * Save an item (create a new row) in storage.
     *
     * @param  StorableInterface $item The object to save.
     * @throws \Exception If a storage error occurs.
     * @return mixed The created item ID, otherwise FALSE.
     */
    abstract public function saveItem(StorableInterface $item);

    /**
     * Update an item in storage.
     *
     * @param  StorableInterface $item       The object to update.
     * @param  array             $properties The list of properties to update, if not all.
     * @return boolean TRUE if the item was updated, otherwise FALSE.
     */
    abstract public function updateItem(StorableInterface $item, array $properties = null);

    /**
     * Delete an item from storage.
     *
     * @param  StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @throws UnexpectedValueException If the item does not have an ID.
     * @return boolean TRUE if the item was deleted, otherwise FALSE.
     */
    abstract public function deleteItem(StorableInterface $item = null);

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param  string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param  string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_' . $key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param  string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    protected function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }

    /**
     * @return string
     */
    protected function getModelClassForException()
    {
        if ($this->hasModel()) {
            return get_class($this->model());
        }

        return 'Unknown Model';
    }
}
