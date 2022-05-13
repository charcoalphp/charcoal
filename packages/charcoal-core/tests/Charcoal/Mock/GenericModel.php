<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 *
 */
class GenericModel extends AbstractModel
{
    /**
     * @var Translation|string|null
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
                    'type' => 'string'
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
     * @param  mixed $name The name of the model.
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
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
