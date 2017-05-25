<?php
/**
 * Charcoal Model class file
 * Part of the `charcoal-core` package.
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 */

namespace Charcoal\Tests\Property\Mocks;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From `charcoal-translation`
use Charcoal\Translator\TranslatorAwareTrait;

/**
 *
 */
class GenericModel extends AbstractModel
{
    use TranslatorAwareTrait;

    /**
     * @return Translation|string|null
     */
    private $name;

    /**
     * @param array $data Dependencies.
     */
    public function __construct(array $data = null)
    {
        $data['metadata'] = [
            'default_data' => [
                'active' => true
            ],
            'properties' => [
                'id' => [
                    'type' => 'id',
                    'mode' => 'uniqid'
                ],
                'name' => [
                    'type' => 'string',
                    'l10n' => true
                ],
                'active' => [
                    'type' => 'boolean'
                ],
                'position' => [
                    'type' => 'number'
                ]
            ],
            'sources' => [
                'default' => [
                    'table' => 'charcoal_tests_models'
                ]
            ],
            'default_source' => 'default'
        ];

        parent::__construct($data);
    }

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->setTranslator($container['translator']);
    }

    /**
     * @param  mixed $name The name of the model.
     * @return self
     */
    public function setName($name)
    {
        $this->name = $this->translator()->translation($name);

        return $this;
    }

    /**
     * @return Translation|string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function icon()
    {
        return '';
    }
}
