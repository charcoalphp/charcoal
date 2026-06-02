<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;
use RuntimeException;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\MetadataInterface;
use Charcoal\Model\Service\MetadataLoader;
// From 'charcoal-property'
use Charcoal\Property\Structure\StructureMetadata;
// From 'charcoal-ui'
use Charcoal\Ui\Form\FormInterface;
use Charcoal\Ui\Form\FormTrait;
use Charcoal\Ui\Layout\LayoutAwareInterface;
use Charcoal\Ui\Layout\LayoutAwareTrait;
use Charcoal\Ui\PrioritizableInterface;
// From 'charcoal-cms'
use Charcoal\Cms\TemplateableInterface;

/**
 * Class TemplateAttachmentWidget
 */
class GroupAttachmentWidget extends AttachmentWidget implements
    FormInterface,
    LayoutAwareInterface
{
    use FormTrait;
    use LayoutAwareTrait;

    /**
     * Store the metadata loader instance.
     */
    private ?\Charcoal\Model\Service\MetadataLoader $metadataLoader = null;

    /**
     * @var boolean
     */
    protected $isAttachmentMetadataFinalized;

    /**
     * @var string
     */
    protected $controllerIdent;

    /**
     * Comparison function used by {@see uasort()}.
     *
     * @param  PrioritizableInterface $a Sortable entity A.
     * @param  PrioritizableInterface $b Sortable entity B.
     * @return integer Sorting value: -1 or 1.
     */
    protected function sortItemsByPriority(
        PrioritizableInterface $a,
        PrioritizableInterface $b
    ): int {
        $priorityA = $a->priority();
        $priorityB = $b->priority();
        return ($priorityA <=> $priorityB);
    }

    /**
     * @param  Container $container The DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setWidgetFactory($container['widget/factory']);

        // Satisfies FormInterface
        $this->setFormGroupFactory($container['form/group/factory']);
        $this->setMetadataLoader($container['metadata/loader']);
    }

    /**
     * Set a metadata loader.
     *
     * @param  MetadataLoader $loader The loader instance, used to load metadata.
     */
    protected function setMetadataLoader(MetadataLoader $loader): static
    {
        $this->metadataLoader = $loader;

        return $this;
    }

    /**
     * Retrieve the metadata loader.
     *
     * @throws RuntimeException If the metadata loader was not previously set.
     */
    protected function metadataLoader(): \Charcoal\Model\Service\MetadataLoader
    {
        if (!$this->metadataLoader instanceof \Charcoal\Model\Service\MetadataLoader) {
            throw new RuntimeException(sprintf(
                'Metadata Loader is not defined for "%s"',
                static::class
            ));
        }

        return $this->metadataLoader;
    }

    /**
     * Load a metadata file.
     *
     * @param  string $metadataIdent A metadata file path or namespace.
     * @return MetadataInterface
     */
    protected function loadMetadata($metadataIdent)
    {
        $metadataLoader = $this->metadataLoader();

        return $metadataLoader->load($metadataIdent, $this->createMetadata());
    }

    /**
     * @throws InvalidArgumentException If structureMetadata $data is note defined.
     * @return MetadataInterface
     */
    protected function createMetadata(): \Charcoal\Property\Structure\StructureMetadata
    {
        return new StructureMetadata();
    }

    /**
     * Sets data on this widget.
     *
     * @param  array $data Key-value array of data to append.
     */
    #[\Override]
    public function setData(array $data): static
    {
        parent::setData($data);

        $this->addAttachmentGroupFromMetadata();

        return $this;
    }

    /**
     * Load attachments group widget and them as form groups.
     *
     * @param boolean $reload Allows to reload the data.
     * @throws InvalidArgumentException If structureMetadata $data is note defined.
     * @throws RuntimeException If the metadataLoader is not defined.
     * @return void
     */
    protected function addAttachmentGroupFromMetadata($reload = false)
    {
        if ($this->obj() instanceof TemplateableInterface) {
            $this->setControllerIdent($this->obj()->templateIdentClass());
        }

        if ($reload || !$this->isAttachmentMetadataFinalized) {
            $obj                 = $this->obj();
            $controllerInterface = $this->controllerIdent();

            $interfaces = [$this->objType()];

            if ($controllerInterface) {
                $interfaces[] = $controllerInterface;
            }

            $structureMetadata = $this->createMetadata();

            if (count($interfaces)) {
                $controllerMetadataIdent = sprintf(
                    'widget/metadata/%s/%s',
                    $obj->objType(),
                    $obj->id()
                );
                $structureMetadata       = $this->metadataLoader()->load(
                    $controllerMetadataIdent,
                    $structureMetadata,
                    $interfaces
                );
            }

            $attachmentWidgets = $structureMetadata->get('attachments.widgets');
            foreach ((array)$attachmentWidgets as $ident => $metadata) {
                $this->addGroup($ident, $metadata);
            }

            $this->isAttachmentMetadataFinalized = true;
        }
    }

    /**
     * Set the form object's template controller identifier.
     *
     * @param  mixed $ident The template controller identifier.
     */
    public function setControllerIdent(string $ident): static
    {
        if (class_exists($ident)) {
            $this->controllerIdent = $ident;

            return $this;
        }

        if (!str_ends_with($ident, '-template')) {
            $ident .= '-template';
        }

        $this->controllerIdent = $ident;

        return $this;
    }

    /**
     * Retrieve the form object's template controller identifier.
     *
     * @return mixed
     */
    public function controllerIdent()
    {
        return $this->controllerIdent;
    }

    /**
     * Disable the pill nav if there is only one group.
     */
    public function displayPills(): bool
    {
        return $this->numGroups() > 1;
    }
}
