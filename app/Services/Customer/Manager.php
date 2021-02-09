<?php


namespace App\Services\Customer;


use Closure;
use InvalidArgumentException;
use Laravel\Lumen\Application;
use Illuminate\Config\Repository;
use Illuminate\Http\Client\Factory;
use Illuminate\Contracts\Container\Container;
use App\Services\Customer\Contracts\ManagerContract;

class Manager implements ManagerContract
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
     * Manager constructor.
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
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
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
    public function extend(string $driver, Closure $callback) : Manager
    {
        $this->customDrivers[$driver] = $callback->bindTo($this, $this);

        return $this;
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
     * @return RandomUserClient|mixed
     */
    private function resolveDriver(string $name)
    {
        $config = $this->getConfig($name);

        if ($config === null)
        {
            throw new InvalidArgumentException("Customer Importer client [${name}] is not defined.");
        }

        if (isset($this->customDrivers[$config['driver']]))
        {
            return $this->callCustomDriver($config);
        }

        return $this->beginClientImport($config);
    }

    /**
     * @param string|null $name
     * @return RandomUserClient|mixed
     */
    public function driver(?string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->drivers[$name] ??= $this->resolveDriver($name);
    }

    /**
     * @param array $config
     * @return RandomUserClient
     */
    private function beginClientImport(array $config)
    {
        return (new RandomUserClient(
            $this->factory->baseUrl($config['url']),
            $config
        ));
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
