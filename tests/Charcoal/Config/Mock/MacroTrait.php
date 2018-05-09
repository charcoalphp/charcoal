<?php

namespace Charcoal\Tests\Config\Mock;

/**
 * Mock trait of {@see \Charcoal\Config\AbstractEntity}
 */
trait MacroTrait
{
    /**
     * @var integer
     */
    private $foo;

    /**
     * @var boolean
     */
    private $erd;

    /**
     * @param  integer $foo A number.
     * @return self
     */
    public function setFoo($foo)
    {
        $this->foo = ((int)$foo+10);
        return $this;
    }

    /**
     * @return string
     */
    public function foo()
    {
        return 'foo is '.$this->foo;
    }

    /**
     * @param  boolean $erd A boolean.
     * @return self
     */
    public function setErd($erd)
    {
        $this->erd = (bool)$erd;
        return $this;
    }

    /**
     * @return boolean
     */
    public function erd()
    {
        return $this->erd;
    }
}
