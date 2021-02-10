<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\Models\CustomerImportModel;
use App\Services\Customer\Events\CustomerImportEvent;
use App\Services\Customer\Contracts\CustomerImporterContract;

class CustomerImportCommand extends Command
{
    /**
     * @var string
     */
    protected $description = 'Import users based on the given drivers';

    /**
     * @var string
     */
    protected $signature = 'customer:import
                            {--c|count=100 : Count of users to import}
                            {--d|driver=json : Driver to use}';

    /**
     * @param CustomerImporterContract $importer
     * @param Dispatcher $dispatcher
     */
    public function handle(CustomerImporterContract $importer, Dispatcher $dispatcher) : void
    {
        $count = $this->option('count');
        $driver = $this->option('driver');
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->advanceProgressBar($bar, $dispatcher);
        $importer->import(CustomerImportModel::class, compact('count', 'driver'));
        $bar->finish();
        $this->info(PHP_EOL . 'Successfully imported ' . $count . ' customer(s)');
    }

    /**
     * @param $bar
     * @param $dispatcher
     */
    protected function advanceProgressBar($bar, $dispatcher) : void
    {
        $dispatcher->listen(CustomerImportEvent::class, function () use ($bar) {
            $bar->advance();
        });
    }
}
