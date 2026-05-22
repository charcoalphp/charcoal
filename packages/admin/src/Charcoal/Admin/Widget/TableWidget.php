<?php

namespace Charcoal\Admin\Widget;

use RuntimeException;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\ModelInterface;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * Displays a collection of models in a tabular (table) format.
 */
class TableWidget extends AdminWidget implements CollectionContainerInterface
{
    use ActionContainerTrait {
        ActionContainerTrait::resolveActionType as resolveDefaultActionType;
    }
    use CollectionContainerTrait {
        CollectionContainerTrait::configureCollectionLoader as configureCollectionLoaderFromTrait;
        CollectionContainerTrait::parsePropertyCell as parseCollectionPropertyCell;
        CollectionContainerTrait::parseObjectRow as parseCollectionObjectRow;
    }
    use HttpAwareTrait;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    public const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * @var boolean $parsedProperties
     */
    protected $parsedProperties = false;

    /**
     * @var array $propertiesOptions
     */
    protected $propertiesOptions;

    /**
     * @var boolean $sortable
     */
    protected $sortable;

    /**
     * @var boolean $showTableHeader
     */
    protected $showTableHeader = true;

    /**
     * @var boolean $showTableHead
     */
    protected $showTableHead = true;

    /**
     * @var boolean $showTableFoot
     */
    protected $showTableFoot = false;

    /**
     * Store the factory instance for the current class.
     */
    private ?\Charcoal\Factory\FactoryInterface $widgetFactory = null;

    private ?\Charcoal\Factory\FactoryInterface $propertyFactory = null;

    /**
     * @var mixed $adminMetadata
     */
    private $adminMetadata;

    /**
     * List actions ars displayed by default.
     */
    private bool $showListActions = true;

    /**
     * Store the list actions.
     *
     * @var array|null
     */
    protected $listActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultListActions;

    /**
     * Keep track if list actions are finalized.
     *
     * @var boolean
     */
    protected $parsedListActions = false;

    /**
     * Keep track if list actions are being parsed.
     *
     * @var boolean
     */
    protected $parsingListActions = false;

    /**
     * Object actions ars displayed by default.
     */
    private bool $showObjectActions = true;

    /**
     * Store the object actions.
     *
     * @var array|null
     */
    protected $objectActions;

    /**
     * Store the default object actions.
     *
     * @var array|null
     */
    protected $defaultObjectActions;

    /**
     * Keep track if object actions are finalized.
     *
     * @var boolean
     */
    protected $parsedObjectActions = false;

    /**
     * Keep track if object actions are being parsed.
     *
     * @var boolean
     */
    protected $parsingObjectActions = false;

    /**
     * Set the widget data.
     *
     * This method prioritizes specific data in order for the widget
     * to properly merge and process. To bypass a bug in Charcoal's logic,
     * any "collection_config" values are assigned after data-sources are merged.
     *
     * @param  array $data The widget data.
     * @return TableWidget Chainable
     */
    #[\Override]
    public function setData(array $data): static
    {
        if (isset($data['obj_type'])) {
            $this->setObjType($data['obj_type']);
            unset($data['obj_type']);
        }

        if (isset($data['data_sources'])) {
            $this->setDataSources($data['data_sources']);
            unset($data['data_sources']);
        }

        if (isset($data['collection_config'])) {
            $collectionConfig = $data['collection_config'];
            unset($data['collection_config']);
        } else {
            $collectionConfig = null;
        }

        parent::setData($data);

        if (!$this->mergedDataSources) {
            $this->mergeDataSources($data);
            $this->mergedDataSources = true;
        }

        if ($collectionConfig !== null) {
            $this->mergeCollectionConfig($collectionConfig);
        }

        return $this;
    }

    /**
     * Fetch metadata from the current request.
     *
     * @return array
     */
    public function dataFromRequest()
    {
        return $this->httpRequest()->getParams($this->acceptedRequestData());
    }

    /**
     * Retrieve the accepted metadata from the current request.
     */
    public function acceptedRequestData(): array
    {
        return [
            'obj_type',
            'obj_id',
            'collection_ident',
            'sortable',
            'template',
        ];
    }

    /**
     * Fetch metadata from the current object type.
     *
     * @return array
     */
    public function dataFromObject()
    {
        $proto = $this->proto();
        $objMetadata = $proto->metadata();
        $adminMetadata = ($objMetadata['admin'] ?? null);

        if (empty($adminMetadata['lists'])) {
            return [];
        }

        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = $this->collectionIdentFallback();
        }

        if ($collectionIdent && $this->isObjRenderable($proto)) {
            $collectionIdent = $proto->render($collectionIdent);
        }

        if (!$collectionIdent) {
            return [];
        }

        $objListData = $adminMetadata['lists'][$collectionIdent] ?? [];

        $collectionConfig = [];

        if (isset($objListData['list_actions']) && isset($adminMetadata['list_actions'])) {
            $extraListActions = array_intersect(
                array_keys($adminMetadata['list_actions']),
                array_keys($objListData['list_actions'])
            );
            foreach ($extraListActions as $listIdent) {
                $objListData['list_actions'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_actions'][$listIdent],
                    $objListData['list_actions'][$listIdent]
                );
            }
        }

        if (isset($objListData['object_actions']) && isset($adminMetadata['list_object_actions'])) {
            $extraObjectActions = array_intersect(
                array_keys($adminMetadata['list_object_actions']),
                array_keys($objListData['object_actions'])
            );
            foreach ($extraObjectActions as $listIdent) {
                $objListData['object_actions'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_object_actions'][$listIdent],
                    $objListData['object_actions'][$listIdent]
                );
            }
        }

        if (isset($objListData['orders']) && isset($adminMetadata['list_orders'])) {
            $extraOrders = array_intersect(
                array_keys($adminMetadata['list_orders']),
                array_keys($objListData['orders'])
            );
            foreach ($extraOrders as $listIdent) {
                $collectionConfig['orders'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_orders'][$listIdent],
                    $objListData['orders'][$listIdent]
                );
            }
        }

        if (isset($objListData['filters']) && isset($adminMetadata['list_filters'])) {
            $extraFilters = array_intersect(
                array_keys($adminMetadata['list_filters']),
                array_keys($objListData['filters'])
            );
            foreach ($extraFilters as $listIdent) {
                $collectionConfig['filters'][$listIdent] = array_replace_recursive(
                    $adminMetadata['list_filters'][$listIdent],
                    $objListData['filters'][$listIdent]
                );
            }
        }

        if ($collectionConfig !== []) {
            $this->mergeCollectionConfig($collectionConfig);
        }

        return $objListData;
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     */
    #[\Override]
    public function widgetDataForJs(): array
    {
        return [
            'obj_type'         => $this->objType(),
            'template'         => $this->template(),
            'collection_ident' => $this->collectionIdent(),
            'properties'       => $this->propertiesIdents(),
            'filters'          => $this->filters(),
            'orders'           => $this->orders(),
            'pagination'       => $this->pagination(),
            'sortable'         => $this->sortable(),
            'list_actions'     => $this->listActions(),
            'object_actions'   => $this->rawObjectActions(),
        ];
    }

    /**
     * Sets and returns properties
     *
     * Manages which to display, and their order, as set in object metadata
     *
     * @return FormPropertyWidget[]
     */
    public function properties()
    {
        if ($this->properties === null || $this->parsedProperties === false) {
            $this->parsedProperties = true;

            $model = $this->proto();
            $properties = $model->metadata()->properties();

            $listProperties = null;
            if ($this->properties === null) {
                $collectionConfig = $this->collectionConfig();
                if (isset($collectionConfig['properties'])) {
                    $listProperties = array_flip($collectionConfig['properties']);
                }
            } else {
                $listProperties = array_flip($this->properties);
            }

            if ($listProperties) {
                $props = [];
                foreach ($listProperties as $k => $v) {
                    $k = lcfirst(implode('', array_map(ucfirst(...), explode('_', (string) $k))));
                    $props[$k] = $v;
                }
                // Replacing values of listProperties from index to actual property values
                $properties = array_replace($props, $properties);
                // Get only the keys that are in listProperties from props
                $properties = array_intersect_key($properties, $props);
            }
            $this->properties = $properties;
        }

        return $this->properties;
    }

    /**
     * Retrieve the property keys shown in the collection.
     *
     * @return array
     */
    public function propertiesIdents()
    {
        $collectionConfig = $this->collectionConfig();

        return $collectionConfig['properties'] ?? [];
    }

    /**
     * Retrieve the property customizations for the collection.
     *
     * @return array|null
     */
    public function propertiesOptions()
    {
        if ($this->propertiesOptions === null) {
            $this->propertiesOptions = $this->defaultPropertiesOptions();
        }

        return $this->propertiesOptions;
    }

    /**
     * Retrieve the view options for the given property.
     *
     * @param  string $propertyIdent The property identifier to lookup.
     * @return array
     */
    public function viewOptions($propertyIdent)
    {
        if (!$propertyIdent) {
            return [];
        }

        if ($propertyIdent instanceof PropertyInterface) {
            $propertyIdent = $propertyIdent->ident();
        }

        $options = $this->propertiesOptions();

        if (isset($options[$propertyIdent]['view_options'])) {
            return $options[$propertyIdent]['view_options'];
        } else {
            return [];
        }
    }

    /**
     * Properties to display in collection template, and their order, as set in object metadata
     *
     * @throws RuntimeException If the property is invalid or fails internally.
     * @return array|Generator
     */
    public function collectionProperties()
    {
        $props = $this->properties();

        foreach ($props as $propertyIdent => $property) {
            $propertyMetadata = $props[$propertyIdent];

            try {
                $p = $this->propertyFactory()->create($propertyMetadata['type']);
                $p->setIdent($propertyIdent);
                $p->setData($propertyMetadata);
            } catch (\Exception $e) {
                throw new RuntimeException(sprintf(
                    '[%s] Can not create property "%s"',
                    $this->objType(),
                    $propertyIdent
                ), 0, $e);
            }

            $options = $this->viewOptions($propertyIdent);
            $classes = $this->parsePropertyCellClasses($p);

            $label = isset($options['label']) ? $this->translator()->translate($options['label']) : strval($p['label']);

            $column = [
                'label' => trim($label)
            ];

            if (!isset($column['attr'])) {
                $column['attr'] = [];
            }

            if (isset($options['attr'])) {
                $column['attr'] = array_merge($column['attr'], $options['attr']);
            }
            if (isset($column['attr']['class'])) {
                if (is_string($classes)) {
                    $classes = explode(' ', $column['attr']['class']);
                }

                if (is_string($column['attr']['class'])) {
                    $column['attr']['class'] = explode(' ', $column['attr']['class']);
                }

                $column['attr']['class'] = array_unique(array_merge($column['attr']['class'], $classes));
            } else {
                $column['attr']['class'] = $classes;
            }
            unset($classes);

            $column['attr'] = html_build_attributes($column['attr']);

            yield $propertyIdent => $column;
        }
    }

    /**
     * Show/hide the table's object actions.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the actions.
     * @return TableWidget Chainable
     */
    public function setShowObjectActions($show): static
    {
        $this->showObjectActions = (bool) $show;

        return $this;
    }

    /**
     * Determine if the table's object actions should be shown.
     */
    public function showObjectActions(): false|int
    {
        if ($this->showObjectActions === false) {
            return false;
        } else {
            return count($this->objectActions());
        }
    }

    /**
     * Retrieve the table's object actions.
     */
    public function objectActions(): array
    {
        $this->rawObjectActions();

        $objectActions = [];
        if (is_array($this->objectActions)) {
            $this->parsingObjectActions = true;
            $objectActions = $this->parseAsObjectActions($this->objectActions);
            $this->parsingObjectActions = false;
        }

        return $objectActions;
    }

    /**
     * Retrieve the table's object actions without rendering it.
     *
     * @return array
     */
    public function rawObjectActions()
    {
        if ($this->objectActions === null) {
            $parsed = $this->parsedObjectActions;

            $collectionConfig = $this->collectionConfig();
            $actions = $collectionConfig['object_actions'] ?? [];

            $this->setObjectActions($actions);

            $this->parsedObjectActions = $parsed;
        }

        if ($this->parsedObjectActions === false) {
            $this->parsedObjectActions = true;
            $this->objectActions = $this->createObjectActions($this->objectActions);
        }

        return $this->objectActions;
    }

    /**
     * Set the table's object actions.
     *
     * @param  array $actions One or more actions.
     * @return TableWidget Chainable.
     */
    public function setObjectActions(array $actions): static
    {
        $this->parsedObjectActions = false;

        $this->parsingObjectActions = true;
        $actions = $this->mergeActions($this->defaultObjectActions(), $actions);
        $this->parsingObjectActions = false;

        $this->objectActions = $actions;

        return $this;
    }

    /**
     * Build the table's object actions (row).
     *
     * Object actions should come from the collection settings defined by the "collection_ident".
     * It is still possible to completly override those externally by setting the "object_actions"
     * with the {@see self::setObjectActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Object actions.
     */
    public function createObjectActions(array $actions): array
    {
        $this->parsingObjectActions = true;
        $objectActions = $this->parseActions($actions);
        $this->parsingObjectActions = false;

        return $objectActions;
    }

    /**
     * Parse the given actions as (row) object actions.
     *
     * @param  array $actions Actions to resolve.
     */
    protected function parseAsObjectActions(array $actions): array
    {
        $objectActions = [];
        foreach ($actions as $action) {
            $action = $this->parseActionRenderables($action, true);

            if (isset($action['ident'])) {
                if ($action['ident'] === 'view' && !$this->isObjViewable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'create' && !$this->isObjCreatable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'edit' && !$this->isObjEditable()) {
                    $action['active'] = false;
                } elseif ($action['ident'] === 'delete' && !$this->isObjDeletable()) {
                    $action['active'] = false;
                }
            }

            if ($action['actions']) {
                $action['actions']    = $this->parseAsObjectActions($action['actions']);
                $action['hasActions'] = (bool) array_filter($action['actions'], fn(array $action): mixed => $action['active']);
            }

            $objectActions[] = $action;
        }

        return $objectActions;
    }

    /**
     * Determine if the table's empty collection actions should be shown.
     */
    public function showEmptyListActions(): int
    {
        $actions = $this->emptyListActions();

        return count($actions);
    }

    /**
     * Retrieve the table's empty collection actions.
     */
    public function emptyListActions(): array
    {
        $actions = $this->listActions();

        $filteredArray = array_filter($actions, fn(array $action): bool => $action['empty'] && $action['active']);

        return array_values($filteredArray);
    }

    /**
     * Show/hide the table's collection actions.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the actions.
     * @return TableWidget Chainable
     */
    public function setShowListActions($show): static
    {
        $this->showListActions = (bool) $show;

        return $this;
    }

    /**
     * Determine if the table's collection actions should be shown.
     */
    public function showListActions(): false|int
    {
        if ($this->showListActions === false) {
            return false;
        } else {
            return count($this->listActions());
        }
    }

    /**
     * Retrieve the table's collection actions.
     *
     * @return array
     */
    public function listActions()
    {
        if ($this->listActions === null) {
            $collectionConfig = $this->collectionConfig();
            $actions = $collectionConfig['list_actions'] ?? [];
            $this->setListActions($actions);
        }

        if ($this->parsedListActions === false) {
            $this->parsedListActions = true;
            $this->listActions = $this->createListActions($this->listActions);
        }

        return $this->listActions;
    }

    /**
     * @return PaginationWidget
     */
    public function paginationWidget()
    {
        $pagination = $this->widgetFactory()->create(PaginationWidget::class);
        $pagination->setData([
            'page'         => $this->page(),
            'num_per_page' => $this->numPerPage(),
            'num_total'    => $this->numTotal(),
            'label'        => $this->translator()->translation('Objects list navigation')
        ]);

        return $pagination;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableHeader($show): static
    {
        $this->showTableHeader = (bool) $show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableHeader()
    {
        return $this->showTableHeader;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableHead($show): static
    {
        $this->showTableHead = (bool) $show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableHead()
    {
        return $this->showTableHead;
    }

    /**
     * @param boolean $show The show flag.
     * @return TableWidget Chainable
     */
    public function setShowTableFoot($show): static
    {
        $this->showTableFoot = (bool) $show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showTableFoot()
    {
        return $this->showTableFoot;
    }

    /**
     * @param boolean $sortable The sortable flag.
     * @return TableWidget Chainable
     */
    public function setSortable($sortable): static
    {
        $this->sortable = (bool) $sortable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function sortable()
    {
        return $this->sortable;
    }

    /**
     * Resolve the action's type.
     *
     * @param  mixed   $action The action structure.
     * @param  boolean $row    Whether to resolve action type for a row.
     */
    protected function resolveActionType(array $action, $row = false): string
    {
        if ($row || $this->parsingObjectActions) {
            return match ($action['ident']) {
                'reset' => 'warning',
                'delete' => 'danger',
                default => 'seamless',
            };
        }

        return $this->resolveDefaultActionType($action);
    }

    public function jsActionPrefix(): string
    {
        return ($this->currentObj) ? 'js-obj' : 'js-list';
    }

    /**
     * Generate URL for editing an object
     * @return string
     */
    public function objectEditUrl()
    {
        $model = $this->proto();
        $url   = 'object/edit?main_menu={{ main_menu }}&obj_type=' . $this->objType();

        if ($this->isObjRenderable($model)) {
            $url = $model->render($url);
        } else {
            $url = preg_replace('~{{\s*id\s*}}~', (string) $this->currentObjId, $url);
        }

        return $url;
    }

    /**
     * Generate URL for creating an object
     * @return string
     */
    public function objectCreateUrl()
    {
        $actions = $this->listActions();
        if ($actions) {
            foreach ($actions as $action) {
                if (isset($action['ident']) && $action['ident'] === 'create' && isset($action['url'])) {
                    $model = $this->proto();
                    if ($this->isObjRenderable($model)) {
                        $action['url'] = $model->render((string)$action['url']);
                    } else {
                        $action['url'] = preg_replace('~{{\s*id\s*}}~', (string) $this->currentObjId, $action['url']);
                    }
                    return $action['url'];
                }
            }
        }

        return $this->objectEditUrl();
    }

    /**
     * Determine if the object is active.
     *
     * @param  ModelInterface|null $object The object to test.
     * @return boolean
     */
    public function isObjActive(?ModelInterface $object = null)
    {
        if (!$object instanceof \Charcoal\Model\ModelInterface) {
            $object = $this->getCurrentObjOrProto();
        }

        $method = [ $object, 'isActiveTableRow' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        if (isset($object['active'])) {
            return (bool)$object['active'];
        }

        return false;
    }

    /**
     * Determine if the object can be created.
     *
     * If TRUE, the "Create" button is shown. Objects can still be
     * inserted programmatically or via direct action on the database.
     *
     * @param  ModelInterface|null $object The object to test.
     * @return boolean
     */
    public function isObjCreatable(?ModelInterface $object = null)
    {
        if (!$object instanceof \Charcoal\Model\ModelInterface) {
            $object = $this->proto();
        }

        $method = [ $object, 'isCreatable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be modified.
     *
     * If TRUE, the "Modify" button is shown. Objects can still be
     * updated programmatically or via direct action on the database.
     *
     * @param  ModelInterface|null $object The object to test.
     * @return boolean
     */
    public function isObjEditable(?ModelInterface $object = null)
    {
        if (!$object instanceof \Charcoal\Model\ModelInterface) {
            $object = $this->getCurrentObjOrProto();
        }

        $method = [ $object, 'isEditable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be deleted.
     *
     * If TRUE, the "Delete" button is shown. Objects can still be
     * deleted programmatically or via direct action on the database.
     *
     * @param  ModelInterface|null $object The object to test.
     * @return boolean
     */
    public function isObjDeletable(?ModelInterface $object = null)
    {
        if (!$object instanceof \Charcoal\Model\ModelInterface) {
            $object = $this->getCurrentObjOrProto();
        }

        $method = [ $object, 'isDeletable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * Determine if the object can be viewed (on the front-end).
     *
     * If TRUE, any "View" button is shown. The object can still be
     * saved programmatically.
     *
     * @param  ModelInterface|null $object The object to test.
     * @return boolean
     */
    public function isObjViewable(?ModelInterface $object = null)
    {
        if (!$object instanceof \Charcoal\Model\ModelInterface) {
            $object = $this->getCurrentObjOrProto();
        }

        if (!$object->id()) {
            return false;
        }

        $method = [ $object, 'isViewable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        return true;
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setView($container['view']);
        $this->setCollectionLoader($container['model/collection/loader']);
        $this->setWidgetFactory($container['widget/factory']);
        $this->setPropertyFactory($container['property/factory']);
        $this->setPropertyDisplayFactory($container['property/display/factory']);
    }

    /**
     * Configure the collection loader.
     *
     * @see \Charcoal\Admin\Ui\CollectionContainerTrait::configureCollectionLoader()
     *     Overrides the method to assign the current main menu identifier to each object.
     *
     * @param  CollectionLoader $loader The collection loader to prepare.
     * @param  array|null       $data   Optional collection data.
     * @return void
     */
    protected function configureCollectionLoader(CollectionLoader $loader, ?array $data = null)
    {
        $this->configureCollectionLoaderFromTrait($loader, $data);

        if (!property_exists($loader, 'hasMainMenuCallback') || $loader->hasMainMenuCallback === null) {
            $mainMenu = filter_input(INPUT_GET, 'main_menu', FILTER_SANITIZE_STRING);
            if ($mainMenu) {
                $fn = function (array &$obj) use ($mainMenu): void {
                    if (!$obj['main_menu']) {
                        $obj['main_menu'] = $mainMenu;
                    }
                };

                $callback = $loader->callback();
                $callback = $callback === null ? $fn : function (&$obj) use ($fn): void {
                    $fn($obj);
                };

                $loader->setCallback($callback);
                $loader->hasMainMenuCallback = true;
            }
        }
    }

    /**
     * Retrieve the widget factory.
     *
     * @throws RuntimeException If the widget factory was not previously set.
     */
    protected function widgetFactory(): \Charcoal\Factory\FactoryInterface
    {
        if (!$this->widgetFactory instanceof \Charcoal\Factory\FactoryInterface) {
            throw new RuntimeException(
                sprintf('Widget Factory is not defined for "%s"', static::class)
            );
        }

        return $this->widgetFactory;
    }

    /**
     * @throws RuntimeException If the property factory was not previously set / injected.
     */
    protected function propertyFactory(): \Charcoal\Factory\FactoryInterface
    {
        if (!$this->propertyFactory instanceof \Charcoal\Factory\FactoryInterface) {
            throw new RuntimeException(
                'Property factory is not set for table widget'
            );
        }

        return $this->propertyFactory;
    }

    /**
     * Retrieve the default data source filters (when setting data on an entity).
     *
     * Note: Adapted from {@see \Slim\CallableResolver}.
     *
     * @link   https://github.com/slimphp/Slim/blob/3.x/Slim/CallableResolver.php
     * @param  mixed $toResolve A callable used when merging data.
     * @return callable|null
     */
    #[\Override]
    protected function resolveDataSourceFilter($toResolve)
    {
        if (is_string($toResolve)) {
            $model = $this->proto();

            $resolved = [ $model, $toResolve ];

            // Check for Slim callable
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

                if ($class === 'parent') {
                    $resolved = [ $model, $class . '::' . $method ];
                }
            }

            $toResolve = $resolved;
        }

        return parent::resolveDataSourceFilter($toResolve);
    }

    /**
     * Set the table's collection actions.
     *
     * @param  array $actions One or more actions.
     * @return TableWidget Chainable.
     */
    protected function setListActions(array $actions): static
    {
        $this->parsedListActions = false;

        $this->parsingListActions = true;
        $this->listActions = $this->mergeActions($this->defaultListActions(), $actions);
        $this->parsingListActions = false;

        return $this;
    }

    /**
     * Build the table collection actions.
     *
     * List actions should come from the collection settings defined by the "collection_ident".
     * It is still possible to completly override those externally by setting the "list_actions"
     * with the {@see self::setListActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array List actions.
     */
    protected function createListActions(array $actions): array
    {
        $this->actionsPriority = $this->defaultActionPriority();

        $this->parsingListActions = true;
        $listActions = $this->parseAsListActions($actions);
        $this->parsingListActions = false;

        return $listActions;
    }

    /**
     * Parse the given actions as collection actions.
     *
     * @param  array $actions Actions to resolve.
     */
    protected function parseAsListActions(array $actions): array
    {
        $listActions = [];
        foreach ($actions as $ident => $action) {
            $ident  = $this->parseActionIdent($ident, $action);
            $action = $this->parseActionItem($action, $ident, true);

            if (!isset($action['priority'])) {
                $action['priority'] = $this->actionsPriority++;
            }

            if ($action['ident'] === 'create') {
                $action['empty'] = true;

                if (!$this->isObjCreatable()) {
                    $action['active'] = false;
                }
            } else {
                $action['empty'] = (isset($action['empty']) && boolval($action['empty']));
            }

            if (is_array($action['actions'])) {
                $action['actions']    = $this->parseAsListActions($action['actions']);
                $action['hasActions'] = (bool) array_filter($action['actions'], fn(array $action): mixed => $action['active']);
            }

            if (isset($listActions[$ident])) {
                $hasPriority = ($action['priority'] > $listActions[$ident]['priority']);
                if ($hasPriority || $action['isSubmittable']) {
                    $listActions[$ident] = array_replace($listActions[$ident], $action);
                } else {
                    $listActions[$ident] = array_replace($action, $listActions[$ident]);
                }
            } else {
                $listActions[$ident] = $action;
            }
        }

        usort($listActions, \Charcoal\Admin\Support\Sorter::sortByPriority(...));

        while (($first = reset($listActions)) && $first['isSeparator']) {
            array_shift($listActions);
        }

        while (($last = end($listActions)) && $last['isSeparator']) {
            array_pop($listActions);
        }

        return $listActions;
    }

    /**
     * Retrieve the table's default collection actions.
     *
     * @return array
     */
    protected function defaultListActions()
    {
        if ($this->defaultListActions === null) {
            $this->defaultListActions = [];
        }

        return $this->defaultListActions;
    }

    /**
     * Retrieve the table's default object actions.
     *
     * @return array
     */
    protected function defaultObjectActions()
    {
        if ($this->defaultObjectActions === null) {
            $edit = [
                'label'    => $this->translator()->translation('Modify'),
                'url'      => $this->objectEditUrl() . '&obj_id={{id}}',
                'ident'    => 'edit',
                'priority' => 1
            ];
            $this->defaultObjectActions = [ $edit ];
        }

        return $this->defaultObjectActions;
    }

    /**
     * Retrieve the default property customizations.
     *
     * The default configset is determined by the collection ident and object type, if assigned.
     *
     * @return array|null
     */
    protected function defaultPropertiesOptions()
    {
        $collectionConfig = $this->collectionConfig();

        if (empty($collectionConfig['properties_options'])) {
            return [];
        }

        return $collectionConfig['properties_options'];
    }

    /**
     * Filter the property before its assigned to the object row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface    $object        The current row's object.
     * @param  PropertyInterface $property      The current property.
     * @param  string            $propertyValue The property $key's display value.
     */
    protected function parsePropertyCell(
        ModelInterface $object,
        PropertyInterface $property,
        string $propertyValue
    ): array {
        $cell    = $this->parseCollectionPropertyCell($object, $property, $propertyValue);
        $ident   = $property->ident();
        $options = $this->viewOptions($ident);
        $classes = $this->parsePropertyCellClasses($property, $object);

        $cell['truncate'] = (isset($options['truncate']) && boolval($options['truncate']));

        if (!isset($cell['attr'])) {
            $cell['attr'] = [];
        }

        if (isset($options['attr'])) {
            unset($options['attr']['width']);
            $cell['attr'] = array_merge($cell['attr'], $options['attr']);
        }
        if (isset($cell['attr']['class'])) {
            if (is_string($classes)) {
                $classes = explode(' ', $cell['attr']['class']);
            }

            if (is_string($cell['attr']['class'])) {
                $cell['attr']['class'] = explode(' ', $cell['attr']['class']);
            }

            $cell['attr']['class'] = array_unique(array_merge($cell['attr']['class'], $classes));
        } else {
            $cell['attr']['class'] = $classes;
        }
        unset($classes);

        $cell['attr'] = html_build_attributes($cell['attr']);

        return $cell;
    }

    /**
     * Filter the table cell's CSS classes before the property is assigned
     * to the object row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  PropertyInterface   $property The current property.
     * @param  ModelInterface|null $object   Optional. The current row's object.
     */
    protected function parsePropertyCellClasses(
        PropertyInterface $property,
        ?ModelInterface $object = null
    ): array {
        unset($object);

        $ident = $property->ident();
        $classes = [ sprintf('property-%s', $ident) ];
        $options = $this->viewOptions($ident);

        if (isset($options['classes'])) {
            if (is_array($options['classes'])) {
                $classes = array_merge($classes, $options['classes']);
            } else {
                $classes[] = $options['classes'];
            }
        }

        return $classes;
    }

    /**
     * Filter the object before its assigned to the row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface $object           The current row's object.
     * @param  array          $objectProperties The $object's display properties.
     */
    protected function parseObjectRow(ModelInterface $object, array $objectProperties): array
    {
        $row = $this->parseCollectionObjectRow($object, $objectProperties);
        $row['objectActions'] = $this->objectActions();
        $row['showObjectActions'] = ($this->showObjectActions() === false) ? false : (bool) $row['objectActions'];

        $row['attr'] = [
            'class' => []
        ];

        if ($this->isObjActive($object)) {
            $row['attr']['class'][] = 'active';
        }

        $row['attr']['class'][] = 'js-table-row';

        $row['attr'] = html_build_attributes($row['attr']);

        return $row;
    }

    /**
     * Set an widget factory.
     *
     * @param FactoryInterface $factory The factory to create widgets.
     */
    private function setWidgetFactory(FactoryInterface $factory): void
    {
        $this->widgetFactory = $factory;
    }

    /**
     * @param FactoryInterface $factory The property factory, to create properties.
     * @return TableWidget Chainable
     */
    private function setPropertyFactory(FactoryInterface $factory): static
    {
        $this->propertyFactory = $factory;

        return $this;
    }
}
