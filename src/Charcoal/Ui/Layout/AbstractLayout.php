<?php

namespace Charcoal\Ui\Layout;

// Depdencies from `charcoal-config`
use \Charcoal\Config\AbstractEntity;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\Layout\LayoutInterface;
use \Charcoal\Ui\Layout\LayoutTrait;

/**
 * Default implementation of the LayoutInterface, as an abstract class.
 */
abstract class AbstractLayout extends AbstractEntity implements LayoutInterface
{
    use LayoutTrait;
}
