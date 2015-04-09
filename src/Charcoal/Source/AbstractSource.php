<?php

namespace Charcoal\Source;

use \Charcoal\Config\ConfigurableInterface as ConfigurableInterface;

use \Charcoal\Source\SourceInterface as SourceInterface;

abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface
{
    // ...
}
