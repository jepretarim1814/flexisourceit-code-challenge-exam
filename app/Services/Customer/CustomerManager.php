<?php

namespace App\Services\Customer;

use Closure;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Laravel\Lumen\Application;
use Illuminate\Config\Repository;
use Illuminate\Http\Client\Factory;
use Illuminate\Contracts\Container\Container;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Drivers\RandomUserXmlDriver;
use App\Services\Customer\Drivers\RandomUserJsonDriver;
use App\Services\Customer\Contracts\CustomerManagerContract;

class CustomerManager implements CustomerManagerContract
{
    protected ?Application $app;

    protected array $drivers = [];

    protected array $customDrivers = [];

    protected ?Repository $config;

    /**
     * @var Factory
     */
    protected $factory;

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
     * @param $name
     */
    public function setDefaultDriver(string $name): void
    {
        $this->config->set('customer.importer_default_driver', $name);
    }

    /**
     * @return string
     */
    public function getDefaultDriver() : string
    {
        return $this->config->get('customer.importer_default_driver');
    }

    /**
     * @param $driver
     * @param Closure $callback
     * @return $this
     */
    public function extend(string $driver, Closure $callback) : CustomerManager
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
     * @param $name
     * @return array|mixed
     */
    private function getConfig(string $name)
    {
        return $this->config->get("customer.importer_drivers.${name}");
    }

    /**
     * @param string $name
     * @return RandomUserJsonDriver|mixed
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
     * @param string|null $name
     * @return RandomUserJsonDriver|mixed
     */
    public function driver(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->drivers[$name] ??= $this->resolveDriver($name);
    }

    /**
     * @param array $config
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
     * @param array $config
     * @return RandomUserJsonDriver
     */
    private function beginImportUsingJsonDriver(array $config) : RandomUserJsonDriver
    {
        return new RandomUserJsonDriver(
            $this->factory->baseUrl($config['url'])->asForm(),
            $config
        );
    }

    /**
     * @param array $config
     * @return RandomUserXmlDriver
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function beginImportUsingXmlDriver(array $config) : RandomUserXmlDriver
    {
        return new RandomUserXmlDriver(
            $this->factory->baseUrl($config['url'])->asForm(),
            $config,
            $this->app->make(XmlParserHelper::class)
        );
    }

    /**
     * @param array $config
     * @return mixed
     */
    private function callCustomDriver(array $config)
    {
        return $this->customDrivers[$config['driver']]($this->app, $config);
    }

}
