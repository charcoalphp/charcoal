<?php

namespace Charcoal\Admin\Widget;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Support\HttpAwareTrait;
use Charcoal\Admin\Ui\ActionContainerTrait;
use Charcoal\Admin\Ui\SecondaryMenu\SecondaryMenuGroupInterface;

/**
 * Admin Secondary Menu Widget
 */
class SecondaryMenuWidget extends AdminWidget implements
    SecondaryMenuWidgetInterface
{
    use ActionContainerTrait;
    use HttpAwareTrait;

    public $isCurrent;

    /**
     * Default sorting priority for an action.
     *
     * @const integer
     */
    public const DEFAULT_ACTION_PRIORITY = 10;

    /**
     * Store the secondary menu actions.
     *
     * @var array|null
     */
    protected $secondaryMenuActions;

    /**
     * Store the default list actions.
     *
     * @var array|null
     */
    protected $defaultSecondaryMenuActions;

    /**
     * Keep track if secondary menu actions are finalized.
     *
     * @var boolean
     */
    protected $parsedSecondaryMenuActions = false;

    /**
     * The secondary menu's display type.
     *
     * @var string
     */
    protected $displayType;

    /**
     * The secondary menu's display options.
     *
     * @var array
     */
    protected $displayOptions;

    /**
     * Whether the group is collapsed or not.
     */
    private bool $collapsed = false;

    /**
     * Whether the group has siblings or not.
     */
    private bool $parented = false;

    /**
     * The title is displayed by default.
     */
    private bool $showTitle = true;

    /**
     * The description is displayed by default.
     */
    private bool $showDescription = true;

    /**
     * The currently highlighted item.
     *
     * @var mixed
     */
    protected $currentItem;

    /**
     * The admin's current route.
     *
     * @var UriInterface
     */
    protected $adminRoute;

    /**
     * The secondary menu's title.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    protected $title;

    /**
     * The secondary menu's links.
     *
     * @var array
     */
    protected $links;

    /**
     * The secondary menu's groups.
     *
     * @var SecondaryMenuGroupInterface[]
     */
    protected $groups;

    /**
     * The secondary menu's description.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    protected $description;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $secondaryMenu;

    /**
     * @param  array $data Class data.
     */
    #[\Override]
    public function setData(array $data): static
    {

        if (isset($data['actions'])) {
            $this->setSecondaryMenuActions($data['actions']);
            unset($data['actions']);
        }

        if (isset($data['current_item'])) {
            $this->setCurrentItem($data['current_item']);
            unset($data['current_item']);
        }

        if (isset($data['is_current'])) {
            $this->setIsCurrent($data['is_current']);
            unset($data['is_current']);
        }

        parent::setData($data);

        return $this;
    }

    /**
     * Determine if the secondary menu has anything.
     *
     * @return boolean
     */
    public function hasSecondaryMenu()
    {
        $ident    = $this->ident();
        $metadata = $this->adminSecondaryMenu();

        if (isset($metadata[$ident])) {
            return $this->hasLinks() ||
                   $this->hasGroups() ||
                   $this->hasActions() ||
                   $this->showTitle() ||
                   $this->showDescription();
        }

        return false;
    }

    /**
     * Determine if the secondary menu is accessible via a tab.
     *
     * @return boolean
     */
    public function isTabbed()
    {
        $ident    = $this->ident();
        $metadata = $this->adminSecondaryMenu();

        if (isset($metadata[$ident])) {
            return $this->hasLinks() ||
                   $this->hasGroups() ||
                   $this->hasActions();
        }

        return false;
    }

    /**
     * Retrieve the metadata for the secondary menu.
     *
     * @return array
     */
    public function adminSecondaryMenu()
    {
        return $this->adminConfig('secondary_menu', []);
    }

    /**
     * Retrieve the current route path.
     *
     * @return string|null
     */
    public function adminRoute()
    {
        if ($this->adminRoute === null) {
            $requestUri = (string)$this->httpRequest()->getUri();
            $requestUri = str_replace($this->adminUrl(), '', $requestUri);

            $this->adminRoute = $requestUri;
        }

        return $this->adminRoute;
    }

    /**
     * @param  string $ident The ident for the current item to highlight.
     */
    public function setCurrentItem($ident): static
    {
        $this->currentItem = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function currentItem()
    {
        return $this->currentItem;
    }

    /**
     * Computes the intersection of values to determine if the link is the current item.
     *
     * @param  mixed $linkIdent The link's value(s) to check.
     */
    public function isCurrentItem($linkIdent): bool
    {
        $context = array_filter([
            $this->currentItem(),
            $this->objType(),
            $this->adminRoute(),
        ]);

        $matches = array_intersect((array)$linkIdent, $context);

        return (bool)$matches;
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType()
    {
        return $this->httpRequest()->getParam('obj_type');
    }

    /**
     * Show/hide the widget's title.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the title.
     */
    public function setShowTitle($show): static
    {
        $this->showTitle = (bool)$show;

        return $this;
    }

    /**
     * Determine if the title is to be displayed.
     *
     * @return boolean If TRUE or unset, check if there is a title.
     */
    public function showTitle()
    {
        if ($this->showTitle === false) {
            return false;
        } else {
            return (bool)$this->title();
        }
    }

    /**
     * Set the title of the secondary menu.
     *
     * @param  mixed $title A title for the secondary menu.
     */
    public function setTitle($title): static
    {
        $this->title = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the title of the secondary menu.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->title = '';

            if (isset($metadata[$ident]['title'])) {
                $this->setTitle($metadata[$ident]['title']);
            }
        }

        return $this->title;
    }

    /**
     * Set the secondary menu links.
     *
     * @param  array $links A collection of link objects.
     */
    public function setLinks(array $links): static
    {
        $this->links = new ArrayIterator();

        foreach ($links as $linkIdent => $link) {
            $this->addLink($linkIdent, $link);
        }

        return $this;
    }

    /**
     * Set the secondary menu links.
     *
     * @param  string       $linkIdent The link identifier.
     * @param  array|object $link      The link object or structure.
     * @throws InvalidArgumentException If the link is invalid.
     */
    public function addLink($linkIdent, $link): static
    {
        if (!is_string($linkIdent) && !is_numeric($linkIdent)) {
            throw new InvalidArgumentException(
                'Link identifier must be a string or '
            );
        }

        if (is_array($link)) {
            $active = true;
            $name   = null;
            $url    = null;
            $permissions = [];

            if (isset($link['ident'])) {
                $linkIdent = $link['ident'];
            } else {
                $link['ident'] = $linkIdent;
            }

            if (isset($link['active'])) {
                $active = (bool)$link['active'];
            }

            if (isset($link['name'])) {
                $name = $this->translator()->translation($link['name']);
            }

            if (isset($link['url'])) {
                $url = $this->translator()->translation($link['url']);
            }

            if (isset($link['required_acl_permissions'])) {
                $permissions = $link['required_acl_permissions'];
            }

            if ($name === null && $url === null) {
                return $this;
            }

            $this->links[$linkIdent] = [
                'active'   => $active,
                'name'     => $name,
                'url'      => $url,
                'selected' => $this->isCurrentItem([ $linkIdent, (string)$url ]),
                'required_acl_permissions' => $permissions
            ];
        } else {
            throw new InvalidArgumentException(sprintf(
                'Link must be an associative array, received %s',
                (get_debug_type($link))
            ));
        }

        return $this;
    }

    /**
     * Retrieve the secondary menu links.
     *
     * @return array
     */
    public function links()
    {
        if ($this->links === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->links = [];
            if (isset($metadata[$ident]['links'])) {
                $links = $metadata[$ident]['links'];

                if (is_array($links)) {
                    $this->setLinks($links);
                }
            }
        }

        $out = [];

        foreach ($this->links as $link) {
            if (isset($link['active']) && !$link['active']) {
                continue;
            }

            if (isset($link['required_acl_permissions'])) {
                $link['permissions'] = $link['required_acl_permissions'];
                unset($link['required_acl_permissions']);
            }

            if (isset($link['permissions']) && $this->hasPermissions($link['permissions']) === false) {
                continue;
            }

            $out[] = $link;
        }

        $this->links = $out;
        return $this->links;
    }

    /**
     * Set the display type of the secondary menu's contents.
     *
     * @param  mixed $type The display type.
     * @throws InvalidArgumentException If the display type is invalid.
     */
    public function setDisplayType($type): static
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('The display type must be a string.');
        }

        $this->displayType = $type;

        return $this;
    }

    /**
     * Retrieve the display type of the secondary menu's contents.
     *
     * @return string|null
     */
    public function displayType()
    {
        if ($this->displayType === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            if (isset($metadata[$ident]['display_type'])) {
                $this->setDisplayType($metadata[$ident]['display_type']);
            } else {
                $this->displayType = '';
            }
        }

        return $this->displayType;
    }

    /**
     * Determine if the secondary menu groups should be displayed as panels.
     */
    public function displayAsPanel(): bool
    {
        return in_array($this->displayType(), [ 'panel', 'collapsible' ]);
    }

    /**
     * Determine if the display type is "collapsible".
     */
    public function collapsible(): bool
    {
        return ($this->displayType() === 'collapsible');
    }

    /**
     * Set the display options for the secondary menu.
     *
     * @param  array $options Display configuration.
     * @throws InvalidArgumentException If the display options are not an associative array.
     */
    public function setDisplayOptions(array $options): static
    {
        $this->displayOptions = array_replace($this->defaultDisplayOptions(), $options);

        return $this;
    }

    /**
     * Retrieve the display options for the secondary menu.
     *
     * @throws RuntimeException If the display options are not an associative array.
     * @return array
     */
    public function displayOptions()
    {
        if ($this->displayOptions === null) {
            $this->setDisplayOptions($this->defaultDisplayOptions());

            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            if (isset($metadata[$ident]['display_options'])) {
                $options = $metadata[$ident]['display_options'];

                if (!is_array($options)) {
                    throw new RuntimeException('The display options must be an associative array.');
                }

                $this->setDisplayOptions(array_merge($this->displayOptions, $options));
            }
        }

        return $this->displayOptions;
    }

    /**
     * Retrieve the default display options for the secondary menu.
     */
    public function defaultDisplayOptions(): array
    {
        return [
            'parented'  => false,
            'collapsed' => $this->collapsible()
        ];
    }

    /**
     * @return mixed
     */
    public function parented()
    {
        if ($this->parented) {
            return $this->parented;
        }

        return $this->displayOptions()['parented'];
    }

    /**
     * @return mixed
     */
    public function collapsed()
    {
        if ($this->collapsed) {
            return $this->collapsed;
        }

        return $this->displayOptions()['collapsed'];
    }

    /**
     * Set the secondary menu's groups.
     *
     * @param  array $groups A collection of group structures.
     */
    public function setGroups(array $groups): static
    {
        $this->groups = [];

        foreach ($groups as $groupIdent => $group) {
            $this->addGroup($groupIdent, $group);
        }

        uasort($this->groups, \Charcoal\Admin\Support\Sorter::sortByPriority(...));

        // Remove items that are not active and reset keys.
        $this->groups = array_values(array_filter($this->groups, fn($item) => $item->active()));

        return $this;
    }

    /**
     * Add a secondary menu group.
     *
     * @param  string                            $groupIdent The group identifier.
     * @param  array|SecondaryMenuGroupInterface $group      The group object or structure.
     * @throws InvalidArgumentException If the identifier is not a string or the group is invalid.
     */
    public function addGroup($groupIdent, $group): static
    {
        if (!is_string($groupIdent)) {
            throw new InvalidArgumentException(
                'Group identifier must be a string'
            );
        }

        if ($group instanceof SecondaryMenuGroupInterface) {
            $group->setSecondaryMenu($this);
            $group->setIdent($groupIdent);

            $this->groups[] = $group;
        } elseif (is_array($group)) {
            if (isset($group['ident'])) {
                $groupIdent = $group['ident'];
            } else {
                $group['ident'] = $groupIdent;
            }

            $displayOptions = $this->displayOptions();
            if (isset($group['display_options'])) {
                $displayOptions = array_replace($displayOptions, $group['display_options']);
            }

            $group['collapsed'] = $displayOptions['collapsed'];
            $group['parented']  = $displayOptions['parented'];

            if (!isset($group['display_type'])) {
                $group['display_type'] = $this->displayType();
            }

            $collapsible = ($group['display_type'] === 'collapsible');

            if ($collapsible) {
                $group['group_id'] = uniqid('collapsible_');
            }

            $g = $this->secondaryMenu()->create($this->defaultGroupType());
            $g->setSecondaryMenu($this);
            $g->setData($group);

            $group = $g;
        } elseif ($group === false || $group === null) {
            return $this;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Group must be an instance of %s or an array of form group options, received %s',
                'SecondaryMenuGroupInterface',
                (get_debug_type($group))
            ));
        }

        if ($g->isAuthorized() === false) {
            return $this;
        }

        $this->groups[] = $g;

        return $this;
    }

    /**
     * Retrieve the secondary menu groups.
     *
     * @return array
     */
    public function groups()
    {
        if ($this->groups === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->groups = [];
            if (isset($metadata[$ident]['groups'])) {
                $groups = $metadata[$ident]['groups'];

                if (is_array($groups)) {
                    $this->setGroups($groups);
                }
            }
        }

        return $this->groups;
    }

    /**
     * Retrieve the default secondary menu group class name.
     */
    public function defaultGroupType(): string
    {
        return 'charcoal/ui/secondary-menu/generic';
    }

    /**
     * Determine if the secondary menu has any links.
     */
    public function hasLinks(): bool
    {
        return (bool)$this->numLinks();
    }

    /**
     * Count the number of secondary menu links.
     */
    public function numLinks(): int
    {
        if (!is_array($this->links()) && !($this->links() instanceof \Traversable)) {
            return 0;
        }

        $links = array_filter($this->links, function (array $link): bool {
            if (isset($link['active']) && !$link['active']) {
                return false;
            }

            if (isset($link['required_acl_permissions'])) {
                $link['permissions'] = $link['required_acl_permissions'];
                unset($link['required_acl_permissions']);
            }
            return !(isset($link['permissions']) && $this->hasPermissions($link['permissions']) === false);
        });

        return count($links);
    }

    /**
     * Determine if the secondary menu has any groups of links.
     */
    public function hasGroups(): bool
    {
        return (bool)$this->numGroups();
    }

    /**
     * Count the number of secondary menu groups.
     */
    public function numGroups(): int
    {
        return count($this->groups());
    }

    /**
     * Alias for {@see self::showSecondaryMenuActions()}
     */
    public function hasActions(): int
    {
        return $this->showSecondaryMenuActions();
    }

    /**
     * Determine if the secondary menu's actions should be shown.
     */
    public function showSecondaryMenuActions(): int
    {
        $actions = $this->secondaryMenuActions();

        return count($actions);
    }

    /**
     * Retrieve the secondary menu's actions.
     *
     * @return array
     */
    public function secondaryMenuActions()
    {
        if ($this->secondaryMenuActions === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();
            $actions = ($metadata[$ident]['actions'] ?? []);
            $this->setSecondaryMenuActions($actions);
        }

        if ($this->parsedSecondaryMenuActions === false) {
            $this->parsedSecondaryMenuActions = true;
            $this->secondaryMenuActions = $this->createSecondaryMenuActions($this->secondaryMenuActions);
        }

        return $this->secondaryMenuActions;
    }

    /**
     * Set the description of the secondary menu.
     *
     * @param  mixed $description A description for the secondary menu.
     */
    public function setDescription($description): static
    {
        $this->description = $this->translator()->translation($description);

        return $this;
    }

    /**
     * Retrieve the description of the secondary menu.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function description()
    {
        if ($this->description === null) {
            $ident    = $this->ident();
            $metadata = $this->adminSecondaryMenu();

            $this->description = '';

            if (isset($metadata[$ident]['description'])) {
                $this->setDescription($metadata[$ident]['description']);
            }
        }

        return $this->description;
    }

    /**
     * Determine if the description is to be displayed.
     *
     * @param  boolean $show Show (TRUE) or hide (FALSE) the description.
     */
    public function setShowDescription($show): static
    {
        $this->showDescription = (bool)$show;
        return $this;
    }

    /**
     * Show/hide the widget's description.
     *
     * @return boolean If TRUE or unset, check if there is a description.
     */
    public function showDescription()
    {
        if ($this->showDescription === false) {
            return false;
        } else {
            return (bool)$this->description();
        }
    }

    public function jsActionPrefix(): string
    {
        return 'js-secondary-menu';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies HttpAwareTrait dependencies
        $this->setHttpRequest($container['request']);

        $this->setSecondaryMenuGroupFactory($container['secondary-menu/group/factory']);
    }

    /**
     * Retrieve the widget's display state.
     *
     * @return boolean
     */
    public function isCurrent()
    {
        return $this->isCurrent;
    }

    /**
     * Set the widget's display state.
     *
     * @param  boolean $flag A truthy state.
     */
    protected function setIsCurrent($flag): static
    {
        $this->isCurrent = boolval($flag);

        return $this;
    }

    /**
     * Retrieve the secondary menu group factory.
     *
     * @throws RuntimeException If the secondary menu group factory was not previously set.
     * @return FactoryInterface
     */
    protected function secondaryMenu()
    {
        if ($this->secondaryMenu === null) {
            throw new RuntimeException(sprintf(
                'Secondary Menu Group Factory is not defined for "%s"',
                static::class
            ));
        }

        return $this->secondaryMenu;
    }

    /**
     * Set the secondary menu's actions.
     *
     * @param  array $actions One or more actions.
     */
    protected function setSecondaryMenuActions(array $actions): static
    {
        $this->parsedSecondaryMenuActions = false;

        $this->secondaryMenuActions = $this->mergeActions($this->defaultSecondaryMenuActions(), $actions);

        return $this;
    }

    /**
     * Build the secondary menu's actions.
     *
     * Secondary menu actions should come from the form settings defined by the "secondary menus".
     * It is still possible to completly override those externally by setting the "actions"
     * with the {@see self::setSecondaryMenuActions()} method.
     *
     * @param  array $actions Actions to resolve.
     * @return array Secondary menu actions.
     */
    protected function createSecondaryMenuActions(array $actions): array
    {
        return $this->parseActions($actions);
    }

    /**
     * Retrieve the secondary menu's default actions.
     *
     * @return array
     */
    protected function defaultSecondaryMenuActions()
    {
        if ($this->defaultSecondaryMenuActions === null) {
            $this->defaultSecondaryMenuActions = [];
        }

        return $this->defaultSecondaryMenuActions;
    }

    /**
     * Set a secondary menu group factory.
     *
     * @param FactoryInterface $factory The group factory, to create objects.
     */
    private function setSecondaryMenuGroupFactory(FactoryInterface $factory): void
    {
        $this->secondaryMenu = $factory;
    }
}
