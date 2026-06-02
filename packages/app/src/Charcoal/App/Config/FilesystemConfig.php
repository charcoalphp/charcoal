<?php

namespace Charcoal\App\Config;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 *
 */
class FilesystemConfig extends AbstractConfig
{
    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var string
     */
    public $defaultConnection;

    public function defaultConnections(): array
    {
        return [
            'public' => [
                'public'    => true,
                'type'      => 'local',
                'path'      => './',
                'label'     => 'Public',
            ],
            'private' => [
                'public'    => false,
                'type'      => 'local',
                'path'      => '../',
                'label'     => 'Private',
            ],
        ];
    }

    /**
     * Ensure connections always return the default connections.
     */
    public function connections(): array
    {
        return array_merge($this->defaultConnections(), $this->connections);
    }
}
