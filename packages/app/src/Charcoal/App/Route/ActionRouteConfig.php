<?php

declare(strict_types=1);

namespace Charcoal\App\Route;

// From 'charcoal-app'
use Charcoal\App\Route\RouteConfig;

/**
 *
 */
class ActionRouteConfig extends RouteConfig
{
    private array $actionData = [];

    /**
     * Set the action data.
     *
     * @param array $actionData The route data.
     * @return ActionRouteConfig Chainable
     */
    public function setActionData(array $actionData): static
    {
        $this->actionData = $actionData;
        return $this;
    }

    /**
     * Get the action data.
     */
    public function actionData(): array
    {
        return $this->actionData;
    }
}
