<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\RawExpressionInterface;

/**
 * Represents a query expression.
 */
class Expression extends AbstractExpression implements
    RawExpressionInterface
{
    /**
     * Custom query expression.
     *
     * @var mixed
     */
    protected $condition;

    /**
     * Set the expression data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     */
    #[\Override]
    public function setData(array $data): static
    {
        parent::setData($data);

        if (isset($data['condition'])) {
            $this->setCondition($data['condition']);
        }

        return $this;
    }

    /**
     * Retrieve the default values.
     *
     * @return array<string,mixed> An associative array.
     */
    public function defaultData(): array
    {
        return [
            'condition' => null,
            'active'    => true,
            'name'      => null,
        ];
    }

    /**
     * Retrieve the expression data structure.
     *
     * @return array<string,mixed> An associative array.
     */
    public function data(): array
    {
        return [
            'condition' => $this->condition(),
            'active'    => $this->active(),
            'name'      => $this->name(),
        ];
    }

    /**
     * Set the custom query expression.
     *
     * @param  string|null $condition The custom query expression.
     * @throws InvalidArgumentException If the parameter is not a valid string expression.
     */
    public function setCondition($condition): static
    {
        if ($condition === null) {
            $this->condition = $condition;
            return $this;
        }

        if (!is_string($condition)) {
            throw new InvalidArgumentException(
                'Custom expression must be a string.'
            );
        }

        $condition = trim($condition);
        if ($condition === '') {
            $condition = null;
        }

        $this->condition = $condition;
        return $this;
    }

    /**
     * Determine if the expression has a custom condition.
     */
    public function hasCondition(): bool
    {
        return !(empty($this->condition) && !is_numeric($this->condition));
    }

    /**
     * Retrieve the custom query expression.
     *
     * @return mixed
     */
    public function condition()
    {
        return $this->condition;
    }
}
