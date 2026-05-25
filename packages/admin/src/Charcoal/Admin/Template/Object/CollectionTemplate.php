<?php

namespace Charcoal\Admin\Template\Object;

use Exception;
use InvalidArgumentException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Widget\SearchWidget;

/**
 * Object collection template (table with a list of objects).
 */
class CollectionTemplate extends AdminTemplate implements
    CollectionContainerInterface,
    DashboardContainerInterface
{
    use CollectionContainerTrait;
    use DashboardContainerTrait;

    /**
     * The search widget instance.
     *
     * @var SearchWidget
     */
    protected $searchWidget;

    /**
     * Tracks whether the search widget was processed.
     *
     * @var boolean
     */
    protected $didSearchWidget = false;

    /**
     * @param RequestInterface $request PSR-7 request.
     */
    #[\Override]
    public function init(RequestInterface $request): bool
    {
        parent::init($request);
        $this->createObjTable();

        return true;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    #[\Override]
    protected function validDataFromRequest(): array
    {
        return array_merge([
            'obj_type'
        ], parent::validDataFromRequest());
    }

    /**
     * Retrieve the search widget, if available,
     *
     * Uses the "default_search_list" ident that should point
     * on ident in the "lists".
     *
     * @return SearchWidget|null
     */
    public function searchWidget()
    {
        if ($this->searchWidget === null && $this->didSearchWidget === false) {
            $this->searchWidget    = $this->createSearchWidget();
            $this->didSearchWidget = true;
        }

        return $this->searchWidget;
    }

    /**
     * Create the search widget.
     *
     * @return SearchWidget|null
     */
    protected function createSearchWidget()
    {
        $config = $this->dashboardConfig();

        if (isset($config['search']) && $config['search'] === false) {
            return null;
        }

        if (isset($config['search'])) {
            if ($config['search'] === false) {
                return null;
            }

            $widgetData = $config['search'];
        } else {
            $widgetData = [];
        }

        $widgetType = ($widgetData['type'] ?? SearchWidget::class);

        $widget = $this->widgetFactory()->create($widgetType);
        $widget->setObjType($this->objType());
        $widget->setData($widgetData);

        // Note that if the ident doesn't match a list,
        // it will return basicly every properties of the object
        $widget->setCollectionIdent($this->metadataListIdent());

        return $widget;
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation
     */
    #[\Override]
    public function title()
    {
        if ($this->title !== null) {
            return $this->title;
        }

        $translator = $this->translator();

        try {
            $config = $this->dashboardConfig();

            if (isset($config['title'])) {
                $this->title = $translator->translation($config['title']);
                return $this->title;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $model    = $this->proto();
        $hasView  = $this->isObjRenderable($model);
        $metadata = $model->metadata();
        $objLabel = null;

        if (isset($metadata['admin']['lists'])) {
            $adminMetadata = $metadata['admin'];

            $listIdent = htmlspecialchars(trim($_GET['collection_ident'] ?? ''), ENT_QUOTES, 'UTF-8');
            if (!$listIdent) {
                $listIdent = $this->collectionIdent();
            }

            if (!$listIdent) {
                $listIdent = $this->collectionIdentFallback();
            }

            if ($listIdent && $hasView) {
                $listIdent = $model->render($listIdent);
            }

            if (isset($adminMetadata['lists'][$listIdent]['label'])) {
                $objLabel = $translator->translation($adminMetadata['lists'][$listIdent]['label']);
            }
        }

        if (!$objLabel && isset($metadata['labels']['all_items'])) {
            $objLabel = $translator->translation($metadata['labels']['all_items']);
        }

        if (!$objLabel) {
            $objType = (isset($metadata['labels']['name'])
                        ? $translator->translation($metadata['labels']['name'])
                        : null);

            $objLabel = $translator->translation('Collection: {{ objType }}');

            if ($objType) {
                $objLabel = strtr($objLabel, [
                    '{{ objType }}' => $objType
                ]);
            }
        }

        $this->title = $hasView ? $model->render((string)$objLabel, $model) : (string)$objLabel;

        return $this->title;
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required collection dependencies
        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);

        // Required dashboard dependencies.
        $this->setDashboardBuilder($container['dashboard/builder']);
    }

    /**
     * @throws Exception If the dashboard config can not be loaded.
     * @return array
     */
    protected function createDashboardConfig()
    {
        $adminMetadata  = $this->objAdminMetadata();
        $dashboardIdent = $this->dashboardIdent();

        if (!$dashboardIdent) {
            $dashboardIdent = $this->metadataDashboardIdent();
        }

        if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
            throw new Exception(
                'Dashboard config is not defined.'
            );
        }

        return $adminMetadata['dashboards'][$dashboardIdent];
    }

    private function createObjTable(): void
    {
        $obj = $this->proto();
        if (!$obj) {
            return;
        }

        if ($obj->source()->tableExists() === false) {
            $obj->source()->createTable();
            $msg = $this->translator()->translate('Database table created for "{{ objType }}".', [
                '{{ objType }}' => $obj->objType()
            ]);
            $this->addFeedback(
                'notice',
                '<span class="fa fa-asterisk" aria-hidden="true"></span><span>&nbsp; ' . $msg . '</span>'
            );
        }
    }

    /**
     * @return string
     */
    private function metadataListIdent()
    {
        $adminMetadata = $this->objAdminMetadata();

        if (isset($adminMetadata['defaultSearchList'])) {
            $listIdent = $adminMetadata['defaultSearchList'];
        } elseif (isset($adminMetadata['default_search_list'])) {
            $listIdent = $adminMetadata['default_search_list'];
        } elseif (isset($adminMetadata['defaultList'])) {
            $listIdent = $adminMetadata['defaultList'];
        } elseif (isset($adminMetadata['default_list'])) {
            $listIdent = $adminMetadata['default_list'];
        } else {
            $listIdent = 'default';
        }

        return $listIdent;
    }

    /**
     * @throws Exception If no default collection is defined.
     * @return string
     */
    private function metadataDashboardIdent()
    {
        $dashboardIdent = htmlspecialchars(trim($_GET['dashboard_ident'] ?? ''), ENT_QUOTES, 'UTF-8');
        if ($dashboardIdent) {
            return $dashboardIdent;
        }

        $adminMetadata = $this->objAdminMetadata();
        if (isset($adminMetadata['defaultCollectionDashboard'])) {
            return $adminMetadata['defaultCollectionDashboard'];
        } elseif (isset($adminMetadata['default_collection_dashboard'])) {
            return $adminMetadata['default_collection_dashboard'];
        }

        // You've reached error.
        throw new Exception(sprintf(
            'No default collection dashboard defined in admin metadata for %s.',
            $this->proto()::class
        ));
    }

    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    protected function objAdminMetadata()
    {
        $objMetadata = $this->proto()->metadata();
        return ($objMetadata['admin'] ?? []);
    }
}
