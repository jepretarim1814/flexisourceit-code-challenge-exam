<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Customer\CustomerManager;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\CustomerImporter;
use App\Services\Customer\Helpers\XmlParserHelper;
use Illuminate\Contracts\Support\DeferrableProvider;
use App\Services\Customer\Contracts\CustomerManagerContract;
use App\Services\Customer\Contracts\CustomerImporterContract;

class CustomerServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        if ($this->isLumen()) {
            $this->app->configure('customer');
        }
        $this->app->singleton(CustomerManagerContract::class, function ($app) {
            return new CustomerManager($app, $app->make('config'), $app->make(Factory::class));
        });
        $this->app->bind(CustomerImporterContract::class, function ($app) {
            return new CustomerImporter(
                $app->make(CustomerManagerContract::class),
                $app->make(EntityManagerInterface::class),
                $app->make(Dispatcher::class)
            );
        });

        $this->app->singleton(XmlParserHelper::class, fn() => new XmlParserHelper());
    }

    /**
     * @return bool
     */
    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }

    public function provides()
    {
        return [
            CustomerManagerContract::class
        ];
    }
}
