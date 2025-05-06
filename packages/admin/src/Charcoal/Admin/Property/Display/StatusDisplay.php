<?php

namespace Charcoal\Admin\Property\Display;

// from 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyDisplay;
// from 'pimple'
use Pimple\Container;
use UnexpectedValueException;

/**
 * Textual Display Property with status indicator
 *
 * The default display for most properties; only output {@see AbstractProperty::displayVal()}.
 */
class StatusDisplay extends AbstractPropertyDisplay
{
    public const STATE_PRIMARY = 'primary';
    public const STATE_SUCCESS = 'success';
    public const STATE_INFO = 'info';
    public const STATE_WARNING = 'warning';
    public const STATE_DANGER = 'danger';
    public const STATE_DEFAULT = 'default';

    public const SUPPORTED_STATES = [
        self::STATE_PRIMARY,
        self::STATE_SUCCESS,
        self::STATE_INFO,
        self::STATE_WARNING,
        self::STATE_DANGER,
        self::STATE_DEFAULT,
    ];

    public const SUPPORTED_OPERATOR = [
        '===',
        '!==',
        '==',
        '!=',
        '>',
        '<',
        '<=',
        '>='
    ];

    /**
     * @var array|callable|null
     */
    private $state;

    /**
     * Wether to show or not the default property value as well as the status indicator.
     *
     * @var boolean
     */
    public $showPropertyVal = true;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
    }

    /**
     * Provides a default calculation method to determine the state.
     *
     * @return string
     */
    private function fallbackStatus()
    {
        return !!$this->propertyVal() ? static::STATE_SUCCESS : static::STATE_DEFAULT;
    }

    /**
     * @param string $condition The condition to test.
     * @throws UnexpectedValueException When an unsupported operator is used.
     * @return boolean
     */
    private function testConditionWithOperator($condition)
    {
        $value = $condition;
        $operator = null;

        if (is_array($condition)) {
            extract($condition);
        }

        if (is_string($operator) && !in_array($operator, static::SUPPORTED_OPERATOR)) {
            throw new UnexpectedValueException(sprintf(
                'The operator [%s] is not supported in [%s]',
                $operator,
                get_class($this)
            ));
        }

        switch ($operator) {
            default:
            case '===':
                return $value === $this->propertyVal();
            case '!==':
                return $value !== $this->propertyVal();
            case '==':
                return $value == $this->propertyVal();
            case '!=':
                return $value != $this->propertyVal();
            case '>':
                return $value > $this->propertyVal();
            case '<':
                return $value < $this->propertyVal();
            case '>=':
                return $value >= $this->propertyVal();
            case '<=':
                return $value <= $this->propertyVal();
        }
    }

    /**
     * @TODO Allow the state to be a string and render it on a supplied template controller.
     * @return boolean|string
     */
    private function calculateState()
    {
        $state = $this->state();
        if (!$state) {
            return false;
        }

        if (is_array($state)) {
            foreach ($state as $stateIdent => $conditions) {
                $result = is_string($conditions) ?
                    $result = $this->testConditionWithOperator($conditions) : false;

                $result = !$result && is_array($conditions) ?
                    !!count(array_filter($conditions, [$this, 'testConditionWithOperator'])) : $result;

                if (!!$result && in_array($stateIdent, static::SUPPORTED_STATES)) {
                    return $stateIdent;
                }
            };
        }

        return static::STATE_DEFAULT;
    }

    /**
     * The actual status state for chip display.
     *
     * @return string
     */
    public function statusState()
    {
        return $this->calculateState() ?: $this->fallbackStatus();
    }

    // GETTERS & SETTERS
    // ==========================================================================

    /**
     * @return array|callable
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * @param array|callable $state State for StatusDisplay.
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
