<?php

namespace Charcoal\App;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Charcoal\Factory\GenericFactory as Factory;
use Charcoal\App\ServiceProvider\AppServiceProvider;

/**
 * Charcoal App Container
 */
class AppContainer implements ContainerInterface
{
    /**
     * @var Container
     */
    private Container $container;

    /**
     * Create new container
     *
     * @param array $definitions The DI definitions (services, parameters, etc).
     */
    public function __construct(array $definitions = [])
    {
        $builder = new ContainerBuilder();
        if (!empty($definitions)) {
            $builder->addDefinitions($definitions);
        }
        $this->container = $builder->build();
        // Ensure config is set
        if (!$this->container->has('config')) {
            $this->container->set('config', new AppConfig());
        }

        (new AppServiceProvider())->register($this->container);

        $this->registerProviderFactory();
        $this->registerConfigProviders();
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function set(string $id, $value)
    {
        return $this->container->set($id, $value);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @return void
     */
    private function registerProviderFactory()
    {
        /**
        * @return Factory
        */
        if (!$this->has('provider/factory')) {
            $this->set('provider/factory', function () {
                return new Factory([
                    'resolver_options' => [
                        'suffix' => 'ServiceProvider'
                    ]
                ]);
            });
        }
    }

    /**
     * @return void
     */
    private function registerConfigProviders()
    {
        if (empty($this->get('config')['service_providers'])) {
            return;
        }

        $providers = $this->get('config')['service_providers'];

        foreach ($providers as $provider => $values) {
            if (false === $values) {
                continue;
            }

            if (!is_array($values)) {
                $values = [];
            }

            $provider = $this->get('provider/factory')->create($provider);

            $provider->register($this->container);
            //$this->register($provider, $values);
        }
    }
}
