<?php

namespace Charcoal\Model\Service;

use UnexpectedValueException;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\ModelMetadata;
use Charcoal\Model\Service\MetadataLoader;

/**
 *
 */
final class ModelBuilder
{
    public const DEFAULT_SOURCE_TYPE = 'database';

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var MetadataLoader
     */
    private $metadataLoader;

    /**
     * @var FactoryInterface
     */
    private $sourceFactory;

    /**
     * @param array $data Constructor dependencies.
     */
    public function __construct(array $data)
    {
        $this->setFactory($data['factory']);
        $this->setMetadataLoader($data['metadata_loader']);
        $this->setSourceFactory($data['source_factory']);
    }

    /**
     * Build a model, pre-initializing its metadata and its source.
     *
     * By default, the name of the "obj type" (the model class name) is used to determine the metadata ident to load.
     * To load a custom metadata for the object, use the `$metadataIdent` argument.
     *
     * By default, the object's _default_ source (from its metadata) is used as source.
     * To load a custom source for the object, use the `$sourceIdent` argument.
     *
     * @param string      $objType       The object type of the Model.
     * @param string|null $metadataIdent Optional. The metadata ident of the object.
     * @param string|null $sourceIdent   Optional. The custom source ident to load as source.
     * @return \Charcoal\Model\ModelInterface
     */
    public function build($objType, $metadataIdent = null, $sourceIdent = null)
    {
        $metadata = $this->createMetadata($objType, $metadataIdent);
        $source = $this->createSource($metadata, $sourceIdent);
        $args = array_merge($this->factory->arguments(), [
            'metadata'  => $metadata,
            'source'    => $source
        ]);
        $model = $this->factory->create($objType, $args);
        $model->source()->setModel($model);
        return $model;
    }

    /**
     * The builder can be invoked (used as function).
     *
     * @param string      $objType       The object type of the Model.
     * @param string|null $metadataIdent Optional. The metadata ident of the object.
     * @param string|null $sourceIdent   Optional. The custom source ident to load as source.
     * @return \Charcoal\Model\ModelInterface
     */
    public function __invoke($objType, $metadataIdent = null, $sourceIdent = null)
    {
        return $this->build($objType, $metadataIdent, $sourceIdent);
    }


    /**
     * @param FactoryInterface $factory The factory to use to create models.
     * @return void
     */
    private function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param MetadataLoader $loader The loader instance, used to load metadata.
     * @return void
     */
    private function setMetadataLoader(MetadataLoader $loader)
    {
        $this->metadataLoader = $loader;
    }

    /**
     * @param FactoryInterface $factory The factory to use to create models.
     * @return void
     */
    private function setSourceFactory(FactoryInterface $factory)
    {
        $this->sourceFactory = $factory;
    }

    /**
     * @param string      $objType       The type of object to load.
     *     (A class name or object identifier).
     * @param string|null $metadataIdent Optional. The metadata identifier to load.
     *     If NULL, it will be implied from objType.
     * @return \Charcoal\Model\MetadataInterface
     */
    private function createMetadata($objType, $metadataIdent = null)
    {
        $metadataIdent = ($metadataIdent !== null) ? $metadataIdent : $objType;
        return $this->metadataLoader->load($metadataIdent, ModelMetadata::class);
    }

    /**
     * @param ModelMetadata $metadata    The object metadata, where to find the object's
     *     source configuration.
     * @param string|null   $sourceIdent Optional. Custom source ident to load.
     *     If NULL, the default (from metadata) will be used.
     * @throws UnexpectedValueException If the source is not defined in the model's metadata.
     * @return \Charcoal\Source\SourceInterface
     */
    private function createSource(ModelMetadata $metadata, $sourceIdent = null)
    {
        if ($sourceIdent === null) {
            $sourceIdent = $metadata->defaultSource();
        }
        $sourceConfig = $metadata->source($sourceIdent);

        if (!$sourceConfig) {
            throw new UnexpectedValueException(
                sprintf('Can not create %s source: "%s" is not defined in metadata.', get_class($this), $sourceIdent)
            );
        }

        $sourceType = isset($sourceConfig['type']) ? $sourceConfig['type'] : self::DEFAULT_SOURCE_TYPE;
        $source = $this->sourceFactory->create($sourceType);
        $source->setData($sourceConfig);

        return $source;
    }
}
