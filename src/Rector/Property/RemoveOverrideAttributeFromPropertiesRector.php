<?php

namespace Charcoal\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveOverrideAttributeFromPropertiesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove #[Override] attribute from class properties', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class ChildClass extends ParentClass {
    #[Background]
    #[\Override]
    public string $name;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ChildClass extends ParentClass {
    #[Background]
    public string $name;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attrKey => $attribute) {
                // Check if the attribute name matches "Override"
                if ($this->isName($attribute->name, 'Override')) {
                    unset($attrGroup->attrs[$attrKey]);
                    $hasChanged = true;
                }
            }

            // Clean up the parent group if it contains no more attributes
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attrGroupKey]);
            } else {
                // Reindex array keys
                $attrGroup->attrs = array_values($attrGroup->attrs);
            }
        }

        if ($hasChanged) {
            $node->attrGroups = array_values($node->attrGroups);
            return $node;
        }

        return null;
    }
}
