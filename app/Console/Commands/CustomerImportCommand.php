<?php

namespace App\Console\Commands;

use App\Services\Customer\Contracts\CustomerImporterContract;
use App\Services\Customer\Events\CustomerImportEvent;
use App\Services\Customer\Models\CustomerImportModel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Helper\ProgressBar;

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
    public function handle(CustomerImporterContract $importer, Dispatcher $dispatcher): void
    {
        $count = $this->option('count');
        $driver = $this->option('driver');
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->advanceProgressBar($dispatcher, $bar);
        $importer->import(CustomerImportModel::class, compact('count', 'driver'));
        $bar->finish();
        $this->info(PHP_EOL . 'Successfully imported ' . $count . ' customer(s)');
    }

    /**
     * @param Dispatcher $dispatcher
     * @param ProgressBar $bar
     */
    protected function advanceProgressBar(Dispatcher $dispatcher, ProgressBar $bar): void
    {
        $dispatcher->listen(CustomerImportEvent::class, function () use ($bar) {
            $bar->advance();
        });
    }
}
