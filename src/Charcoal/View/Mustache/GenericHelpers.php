<?php

namespace Charcoal\View\Mustache;

use \Mustache_LambdaHelper as LambdaHelper;

/**
 * Mustache Helpers
 */
class GenericHelpers implements HelpersInterface
{
    /**
     * Retrieve the collection of helpers.
     *
     * @todo Move the '_t' helper to 'charcoal-translation' once that package is gold.
     * @return array
     */
    public function toArray()
    {
        return [
            '_t' => function($text, LambdaHelper $helper) {
                return $helper->render($text);
            }
        ];
    }
}
