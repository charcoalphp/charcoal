<?php

namespace Charcoal\Admin\Docs\Template\Object;

use Exception;
// From Pimple
use Pimple\Container;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Object Edit Template
 */
class DocTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    public $headerMenu;

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
     * Retrieve the header menu.
     *
     * @return array
     */
    public function headerMenu()
    {
        if ($this->headerMenu === null) {
            $dashboardConfig = $this->dashboardConfig();

            if (isset($dashboardConfig['secondary_menu'])) {
                $this->headerMenu = $this->createHeaderMenu($dashboardConfig['secondary_menu']);
            } else {
                $this->headerMenu = $this->createHeaderMenu();
            }
        }

        return $this->headerMenu;
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

        try {
            $config = $this->dashboardConfig();

            if (isset($config['title'])) {
                $this->title = $this->translator()->translation($config['title']);

                return $this->title;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $obj = $this->obj();
        $metadata = $obj->metadata();
        $objLabel = null;

        if (isset($metadata['admin']['forms'])) {
            $adminMetadata = $metadata['admin'];

            $formIdent = htmlspecialchars(trim(($_GET['form_ident'] ?? '')), ENT_QUOTES, 'UTF-8');
            if (!$formIdent) {
                if (isset($adminMetadata['defaultForm'])) {
                        $fomIdent = $adminMetadata['defaultForm'];
                } elseif (isset($adminMetadata['default_form'])) {
                    $formIdent = $adminMetadata['default_form'];
                } else {
                    $formIdent = '';
                }
            }

            if (isset($adminMetadata['forms'][$formIdent]['label'])) {
                $objLabel = $this->translator()->translation($adminMetadata['forms'][$formIdent]['label']);
            }
        }

        if (!$objLabel) {
            $objType = (isset($metadata['labels']['singular_name']) ?
                $this->translator()->translation($metadata['labels']['singular_name']) : null);

            $objLabel = $this->translator()->translation('Documentation: {{ objType }}');

            if ($objType) {
                $objLabel = strtr($objLabel, [
                    '{{ objType }}' => $objType
                ]);
            }
        }

        $this->title = $this->isObjRenderable($obj) ? $obj->render((string)$objLabel, $obj) : (string)$objLabel;

        return $this->title;
    }

    /**
     * @param Container $container DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);

        // Required dependencies.
        $this->dashboardBuilder = $container['dashboard/builder'];
    }

    /**
     * @throws Exception If the object's dashboard config can not be loaded.
     * @return array
     */
    protected function createDashboardConfig()
    {
        $adminMetadata  = $this->objAdminMetadata();
        $dashboardIdent = $this->dashboardIdent();

        if (empty($dashboardIdent)) {
            $dashboardIdent = htmlspecialchars(trim(($_GET['dashboard_ident'] ?? '')), ENT_QUOTES, 'UTF-8');
        }

        if (empty($dashboardIdent) && isset($adminMetadata['default_doc_dashboard'])) {
            $dashboardIdent = $adminMetadata['default_doc_dashboard'];
        }

        $overrideType = false;

        if (empty($dashboardIdent)) {
            if (!isset($adminMetadata['default_edit_dashboard'])) {
                throw new Exception(sprintf(
                    'No default doc dashboard defined in admin metadata for %s',
                    $this->obj()::class
                ));
            }
            $overrideType = true;
            $dashboardIdent = $adminMetadata['default_edit_dashboard'];
        }

        if (!isset($adminMetadata['dashboards']) || !isset($adminMetadata['dashboards'][$dashboardIdent])) {
            throw new Exception(
                'Dashboard config is not defined.'
            );
        }

        $dashboardConfig = $adminMetadata['dashboards'][$dashboardIdent];

        if ($overrideType) {
            $widgets = $dashboardConfig['widgets'];
            foreach ($widgets as $ident => $widget) {
                $dashboardConfig['widgets'][$ident]['type'] = 'charcoal/admin/widget/doc';
                $dashboardConfig['widgets'][$ident]['show_header'] = true;
                $dashboardConfig['widgets'][$ident]['show_title'] = true;
            }
        }

        return $dashboardConfig;
    }

    /**
     * @throws Exception If the object's admin metadata is not set.
     * @return \ArrayAccess
     */
    protected function objAdminMetadata()
    {
        $obj = $this->obj();

        $objMetadata = $obj->metadata();

        $adminMetadata = ($objMetadata['admin'] ?? null);
        if ($adminMetadata === null) {
            throw new Exception(sprintf(
                'The object %s does not have an admin metadata.',
                $obj::class
            ));
        }

        return $adminMetadata;
    }
}
