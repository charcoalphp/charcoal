<?php

namespace Charcoal\Admin\Widget;

// From Pimple
use Charcoal\Ui\Layout\LayoutBuilder;
use Charcoal\Ui\Layout\LayoutInterface;
use Pimple\Container;
// form 'charcoal-user'
use Charcoal\User\AuthAwareInterface;
use Charcoal\User\UserInterface;
// From 'charcoal-ui'
use Charcoal\Ui\Dashboard\DashboardInterface;
use Charcoal\Ui\Dashboard\DashboardTrait;
use Charcoal\Ui\UiItemTrait;
use Charcoal\Ui\UiItemInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Decorator\GridStackWidgetDecorator;

/**
 * The dashboard widget is a simple dashboard interface / layout aware object.
 */
class GridStackDashboardWidget extends AdminWidget implements
    DashboardInterface
{
    use DashboardTrait;
    use UiItemTrait;

    /**
     * @var array
     */
    protected $gridStack;

    /**
     * @var UserInterface|null
     */
    protected $adminUser;

    /**
     * @param Container $container The DI container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies DashboardInterface dependencies
        $this->setWidgetBuilder($container['widget/builder']);
    }

    /**
     * Retrieve the dashboard's widgets.
     *
     * @param callable $widgetCallback A callback applied to each widget.
     * @return UiItemInterface[]|\Generator
     */
    public function widgets(?callable $widgetCallback = null)
    {
        $widgets = $this->widgets;

        $gridStack = $this->gridStack() ?: [];
        $parsedGridStack = [];

        array_walk($gridStack, function (array $item) use (&$parsedGridStack): void {
            $parsedGridStack[$item['id']] = $item;
        });

        // Load gridStack from user preferences
        $user = $this->adminUser();
        $userGridStack = json_decode((string)$user->preferences(), true)['grid_stack'] ?: [];
        $parsedUserGridStack = [];

        array_walk($userGridStack, function (array $item) use (&$parsedUserGridStack): void {
            $parsedUserGridStack[$item['id']] = $item;
        });

        $widgetCallback ??= $this->widgetCallback;
        foreach ($widgets as $widget) {
            if (isset($widget['permissions']) && $this instanceof AuthAwareInterface) {
                $widget->setActive($this->hasPermissions($widget['permissions']));
            }

            if ((bool)count($parsedUserGridStack)) {
                if (isset($parsedUserGridStack[$widget->ident()])) {
                    $widget->setData([
                        'grid_stack' => $parsedUserGridStack[$widget->ident()]
                    ]);
                }
            } elseif (isset($parsedGridStack[$widget->ident()])) {
                $gridStack  = array_replace_recursive(
                    $parsedGridStack[$widget->ident()],
                    $widget['grid_stack'] ?: []
                );
                $widget->setData([
                    'grid_stack' => $gridStack
                ]);
            }

            $gridStackDeco = new GridStackWidgetDecorator($widget);
            $widget['grid_stack'] = $gridStackDeco->gridStack();

            if (!$widget->active()) {
                continue;
            }

            if ($widgetCallback) {
                $widgetCallback($widget);
            }

            $this->setDynamicTemplate('widget_template', $widget->template());
            yield $widget;
        }
    }

    /**
     * @return mixed
     */
    public function gridStack()
    {
        return $this->gridStack;
    }

    /**
     * @param mixed $gridStack GridStack for AdvancedDashboardWidget.
     */
    public function setGridStack($gridStack): static
    {
        $this->gridStack = $gridStack;

        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function adminUser()
    {
        if ($this->adminUser) {
            return $this->adminUser;
        }

        $this->adminUser = $this->authenticator()->user();

        return $this->adminUser;
    }

    // ==========================================================================
    // LayoutInterface is not needed.
    // ==========================================================================

    /**
     * @param LayoutBuilder $builder The layout builder, to create customized layout object(s).
     * @return \Charcoal\Ui\Layout\DashboardInterface Chainable
     */
    public function setLayoutBuilder(LayoutBuilder $builder): null
    {
        return null;
    }

    /**
     * @param LayoutInterface|array $layout The layout object or structure.
     * @return \Charcoal\Ui\Layout\DashboardInterface Chainable
     */
    public function setLayout($layout): null
    {
        return null;
    }

    /**
     * @return LayoutInterface
     */
    public function layout(): null
    {
        return null;
    }
}
