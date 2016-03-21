<?php

namespace Charcoal\Ui;

use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractEntity;

// Module `charcoal-core` dependencies
use \Charcoal\Translation\TranslationString;

// Module `charcoal-view` dependencies
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\UiItemTrait;

/**
 *
 */
abstract class AbstractUiItem extends AbstractEntity implements UiItemInterface
{
    use ViewableTrait;
    use UiItemTrait;
}
