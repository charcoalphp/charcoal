<?php

namespace Charcoal\Tests\Object\Mocks;

use Charcoal\Object\CategoryTrait;

class CategoryTraitTestDouble
{
    use CategoryTrait;

    public function loadCategoryItems(): array
    {
        return [];
    }
}
