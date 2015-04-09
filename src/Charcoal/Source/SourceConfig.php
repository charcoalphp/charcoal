<?php

namespace Charcoal\Source;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

class SourceConfig extends AbstractConfig
{
    public $type;

    public function default_data()
    {
        return [
            'type'=>null
        ];
    }
}
