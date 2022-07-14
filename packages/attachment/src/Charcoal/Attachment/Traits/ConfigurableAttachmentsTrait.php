<?php

namespace Charcoal\Attachment\Traits;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\ConfigInterface;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Ui\ObjectContainerInterface;
// From 'charcoal-attachment'
use Charcoal\Attachment\AttachmentsConfig;

/**
 * Configurable Attachments Trait
 *
* An implementation, as Trait, of the {@see \Charcoal\Config\ConfigurableInterface}.
*/
trait ConfigurableAttachmentsTrait
{
    /**
     * The attachments configset.
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Set the object's configuration container.
     *
     * @param  ConfigInterface|array $config The datas to set.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return ConfigurableInterface Chainable
     */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $this->config = $this->createConfig($config);
        } elseif ($config instanceof ConfigInterface) {
            $this->config = $config;
        } else {
            throw new InvalidArgumentException(
                'Configuration must be an array or a ConfigInterface object.'
            );
        }
        return $this;
    }

    /**
     * Retrieve the object's configuration container, or one of its entry.
     *
     * If the object has no existing config, create one.
     *
     * If a key is provided, return the configuration key value instead of the full object.
     *
     * @param  string $key Optional. If provided, the config key value will be returned, instead of the full object.
     * @return ConfigInterface
     */
    public function config($key = null)
    {
        if ($this->config === null) {
            $this->config = $this->createConfig();
        }

        if ($key !== null) {
            return $this->config->get($key);
        } else {
            return $this->config;
        }
    }

    /**
     * Retrieve a new AttachmentsConfig instance for the class.
     *
     * @param  array|null $data Optional data to pass to the new configset.
     * @return ConfigInterface
     */
    protected function createConfig($data = null)
    {
        return new AttachmentsConfig($data);
    }

    /**
     * Parse the given data and recursively merge presets from attachments config.
     *
     * @param  array $data The widget data.
     * @return array Returns the merged widget data.
     */
    protected function mergePresets(array $data)
    {
        if (isset($data['attachable_objects'])) {
            $data['attachable_objects'] = $this->mergePresetAttachableObjects($data['attachable_objects']);
        }

        if (isset($data['default_attachable_objects'])) {
            $data['attachable_objects'] = $this->mergePresetAttachableObjects($data['default_attachable_objects']);
        }

        if (isset($data['preset'])) {
            $data = $this->mergePresetWidget($data);
        }

        return $data;
    }

    /**
     * Parse the given data and merge the widget preset.
     *
     * @param  array $data The widget data.
     * @return array Returns the merged widget data.
     */
    private function mergePresetWidget(array $data)
    {
        if (!isset($data['preset']) || !is_string($data['preset'])) {
            return $data;
        }

        $widgetIdent = $data['preset'];

        if ($this instanceof ObjectContainerInterface) {
            if ($this->hasObj()) {
                $widgetIdent = $this->obj()->render($widgetIdent);
            }
        } elseif ($this instanceof ModelInterface) {
            $widgetIdent = $this->render($widgetIdent);
        }

        $presetWidgets = $this->config('widgets');
        if (!isset($presetWidgets[$widgetIdent])) {
            return $data;
        }

        $widgetData = $presetWidgets[$widgetIdent];
        if (isset($widgetData['attachable_objects'])) {
            $widgetData['attachable_objects'] = $this->mergePresetAttachableObjects(
                $widgetData['attachable_objects']
            );
        }

        if (isset($widgetData['default_attachable_objects'])) {
            $widgetData['attachable_objects'] = $this->mergePresetAttachableObjects(
                $widgetData['default_attachable_objects']
            );
        }

        return array_replace_recursive($widgetData, $data);
    }

    /**
     * Parse the given data and merge the preset attachment types.
     *
     * @param  string|array $data The attachable objects data.
     * @throws InvalidArgumentException If the attachment type or structure is invalid.
     * @return array Returns the merged attachable objects data.
     */
    private function mergePresetAttachableObjects($data)
    {
        if (is_string($data)) {
            $groupIdent = $data;
            if ($this instanceof ObjectContainerInterface) {
                if ($this->hasObj()) {
                    $groupIdent = $this->obj()->render($groupIdent);
                }
            } elseif ($this instanceof ModelInterface) {
                $groupIdent = $this->render($groupIdent);
            }

            $presetGroups = $this->config('groups');
            if (isset($presetGroups[$groupIdent])) {
                $data = $presetGroups[$groupIdent];
            }
        }

        if (is_array($data)) {
            $presetTypes = $this->config('attachables');
            $attachables = [];
            foreach ($data as $attType => $attStruct) {
                if (is_string($attStruct)) {
                    $attType   = $attStruct;
                    $attStruct = [];
                }

                if (!is_string($attType)) {
                    throw new InvalidArgumentException(
                        'The attachment type must be a string'
                    );
                }

                if (!is_array($attStruct)) {
                    throw new InvalidArgumentException(sprintf(
                        'The attachment structure for "%s" must be an array',
                        $attType
                    ));
                }

                if (isset($presetTypes[$attType])) {
                    $attStruct = array_replace_recursive(
                        $presetTypes[$attType],
                        $attStruct
                    );
                }

                $attachables[$attType] = $attStruct;
            }

            $data = $attachables;
        }

        return $data;
    }
}
