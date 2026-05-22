<?php

namespace Charcoal\Admin\Template\System\Object;

use Charcoal\Model\Service\CollectionLoader;
use Charcoal\Model\Service\MetadataLoader;
use Exception;
use ReflectionClass;
use ReflectionObject;
// From Pimple
use Pimple\Container;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Object Info Template
 */
class InfoTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    public $metadataLoader;
    public $collectionLoader;
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    private ?array $metadataFiles = null;

    /**
     * @return \Charcoal\Admin\Translation|\Charcoal\Translator\Translation|string|null
     */
    #[\Override]
    public function title()
    {
        return $this->objType();
    }

    public function objProperties(): array
    {
        $ret = [];
        $properties = $this->obj()->metadata()->properties();
        foreach ($properties as $ident => $property) {
            $prop = $this->obj()->p($ident);
            $allSources = $this->getAllFiles($ident);
            $property['ident'] = $ident;
            $property['metadataSource'] = $this->getFirstFile($ident);
            $property['allSources'] = $allSources;
            $property['hasMoreSource'] = (count($allSources) > 1);
            $propertyProperties = $prop->metadata()->properties();
            $property['propertyProperties'] = [[
                'ident' => 'type',
                'val'   => $property['type'],
                'type'  => '',
                'label' => 'Type'
            ]];
            foreach ($propertyProperties as $propIdent => $propProperty) {
                $property['propertyProperties'][] = array_merge($propProperty, [
                    'ident' => $propIdent,
                    'val'   => $prop->p($propIdent)->displayVal($prop[$propIdent]),
                    'label' => isset($propProperty['label']) ? (string)$propProperty['label'] : null,
                    'propDescription' => isset($propProperty['description']) ? (string)$propProperty['description'] : null
                ]);
            }


            $ret[] = $property;
        }
        usort($ret, function (array $a, array $b): int {
            $ret = strcmp((string)$a['metadataSource'], (string)$b['metadataSource']);
            if ($ret === 0) {
                return strcmp((string)$a['ident'], (string)$b['ident']);
            } else {
                return $ret;
            }
        });
        return $ret;
    }

    public function className(): string
    {
        return $this->obj()::class;
    }

    public function classHierarchy(): array
    {
        $ret = [];
        $ret = array_merge($ret, array_keys(class_parents($this->obj())));
        return array_reverse($ret);
    }

    public function classTraits(): array
    {
        $traits = [];
        $hierarchy = $this->classHierarchy();
        foreach ($hierarchy as $className) {
            $reflection = new ReflectionClass($className);
            $traits = array_merge($traits, array_keys($reflection->getTraits()));
        }
        sort($traits);
        return $traits;
    }

    public function classInterfaces(): array
    {
        $reflection = new ReflectionClass($this->obj()::class);
        $interfaces = array_keys($reflection->getInterfaces());
        sort($interfaces);
        return $interfaces;
    }

    public function metadataFiles(): array
    {
        if ($this->metadataFiles === null) {
            $files = [];
            $reflector = new ReflectionObject($this->metadataLoader);
            $method = $reflector->getMethod('hierarchy');
            $hierarchy = $method->invoke($this->metadataLoader, $this->objType());

            $method2 = $reflector->getMethod('loadMetadataFromSource');
            foreach ($hierarchy as $source) {
                $ret = $method2->invoke($this->metadataLoader, $source);
                if (!empty($ret)) {
                    $files[] = [
                        'name' => $source,
                        'metadata' => $ret
                    ];
                }
            }
            $this->metadataFiles = $files;
        }
        return $this->metadataFiles;
    }

    /**
     * @return string
     */
    public function sourceType()
    {
        return $this->obj()->source()->config()->type();
    }

    /**
     * @return string
     */
    public function sourceTable()
    {
        return $this->obj()->source()->table();
    }

    /**
     * @return string
     */
    public function sourceEntries()
    {
        $this->collectionLoader->setModel($this->obj()::class);
        return $this->collectionLoader->loadCount();
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
            'obj_type', 'obj_id'
        ], parent::validDataFromRequest());
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
        $this->metadataLoader = $container['metadata/loader'];

        $this->dashboardBuilder = $container['dashboard/builder'];

        $this->collectionLoader = $container['model/collection/loader'];
    }

    protected function createDashboardConfig(): array
    {
        return [];
    }

    /**
     * @param string $propertyIdent The property ident to retrieve.
     * @return string
     */
    private function getFirstFile($propertyIdent)
    {
        $all = $this->getAllFiles($propertyIdent);
        if (isset($all[0])) {
            return $all[0];
        } else {
            return '';
        }
    }

    /**
     * @param string $propertyIdent The property ident to retrieve.
     */
    private function getAllFiles($propertyIdent): array
    {
        $ret = [];
        $files = $this->metadataFiles();
        foreach ($files as $val) {
            if (isset($val['metadata']['properties'][$propertyIdent])) {
                $ret[] = $val['name'];
            }
        }
        return $ret;
    }
}
