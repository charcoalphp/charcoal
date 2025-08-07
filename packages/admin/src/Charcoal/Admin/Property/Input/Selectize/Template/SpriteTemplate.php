<?php

namespace Charcoal\Admin\Property\Input\Selectize\Template;

use DI\Container;
// From 'charcoal-app'
use Charcoal\App\Template\AbstractTemplate;
// From 'charcoal-admin'
use Charcoal\Admin\Support\BaseUrlTrait;
use Psr\Container\ContainerInterface;

/**
 * Controller for selectize template
 * Controls the display of {@see Charcoal/Property/SpriteProperty} in the context of a selectize input
 *
 * Sprite Property Input Template
 */
class SpriteTemplate extends AbstractTemplate
{
    use BaseUrlTrait;

    /**
     * Show the sprite id besides the icon.
     *
     * @var boolean
     */
    protected $showSpriteId = true;

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        $this->setBaseUrl($container->get('base-url'));
    }

    /**
     * @return boolean
     */
    public function showSpriteId()
    {
        return $this->showSpriteId;
    }

    /**
     * @param boolean $flag Show the sprite id besides the icon.
     * @return self
     */
    public function setShowSpriteId($flag)
    {
        $this->showSpriteId = $flag;

        return $this;
    }
}
