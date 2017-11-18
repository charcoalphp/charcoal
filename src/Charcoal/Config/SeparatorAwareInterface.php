<?php

namespace Charcoal\Config;

/**
 *
 */
interface SeparatorAwareInterface
{
    /**
     * @param string $separator The separator character.
     * @return EntityInterface Chainable
     */
    public function setSeparator($separator);
}
