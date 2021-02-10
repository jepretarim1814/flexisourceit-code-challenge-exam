<?php

namespace App\Services\Customer;

use App\Services\Customer\Contracts\CustomerManagerContract;
use App\Services\Customer\Drivers\RandomUserJsonDriver;
use App\Services\Customer\Drivers\RandomUserXmlDriver;
use App\Services\Customer\Helpers\XmlParserHelper;
use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Laravel\Lumen\Application;

class CustomerManager implements CustomerManagerContract
{
    protected array $drivers = [];

    protected array $customDrivers = [];

    protected Application $app;

    protected ?Repository $config;

    protected ?Factory $factory;

    /**
     * CustomerManager constructor.
     *
     * @param Application|Container $app
     * @param Repository|null $config
     * @param Factory|null $factory
     */

    public function __construct($app, Repository $config = null, Factory $factory = null)
    {
        $this->app = $app;
        $this->config = $config ?? $this->app['config'];
        $this->factory = $factory ?? $this->app[Factory::class];
    }

    /**
     * @param string $driver
     * @param Closure $callback
     * @return $this
     */
    public function extend(string $driver, Closure $callback): CustomerManager
    {
        $this->customDrivers[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $driver = Arr::get($parameters, '0.driver', null);
        if ($driver !== null) {
            $this->setDefaultDriver($driver);
        }

        return $this->driver()->$method(...$parameters);
    }

    /**
     * @param string $name
     */
    public function setDefaultDriver(string $name): void
    {
        $this->config->set('customer.importer_default_driver', $name);
    }

    /**
     * @param string|null $name
     * @return mixed
     */
    public function driver(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] ??= $this->resolveDriver($name);
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('customer.importer_default_driver');
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function resolveDriver(string $name)
    {
        $config = $this->getConfig($name);

        if ($config === null) {
            throw new InvalidArgumentException("Driver [${name}] is not defined.");
        }

        if (isset($this->customDrivers[$config['driver']])) {
            return $this->callCustomDriver($config);
        }

        return $this->importUsingSelectedDriver($config);
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getConfig(string $name)
    {
        return $this->config->get("customer.importer_drivers.${name}");
    }

    /**
     * @param mixed[] $config
     * @return mixed
     */
    private function callCustomDriver(array $config)
    {
        return $this->customDrivers[$config['driver']]($this->app, $config);
    }

    /**
     * @param mixed[] $config
     * @return mixed
     */
    private function importUsingSelectedDriver(array $config)
    {
        $method = 'beginImportUsing' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException("Driver [${config['driver']}] is not supported");
    }

    /**
     * @param mixed[] $config
     * @return RandomUserJsonDriver
     */
    private function beginImportUsingJsonDriver(array $config): RandomUserJsonDriver
    {
        return new RandomUserJsonDriver(
            $config,
            $this->factory->baseUrl($config['url'])->asForm(),
        );
    }

    /**
     * @param mixed[] $config
     * @return RandomUserXmlDriver
     * @throws BindingResolutionException
     */
    private function beginImportUsingXmlDriver(array $config): RandomUserXmlDriver
    {
        return new RandomUserXmlDriver(
            $config,
            $this->factory->baseUrl($config['url'])->asForm(),
            $this->app->make(XmlParserHelper::class)
        );
    }
}
