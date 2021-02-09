<?php


namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\Models\CustomerImport;
use App\Services\Customer\Contracts\ImporterContract;

class ImportCommand extends Command
{
    /**
     * @var string
     */
    protected $description = 'Import users based on the given drivers';

    /**
     * @var string
     */
    protected $signature = 'customer:import {--c|count=100 : Count of users to import}';

    /**
     * @param ImporterContract $importer
     * @param Dispatcher $dispatcher
     */
    public function handle(ImporterContract $importer, Dispatcher $dispatcher)
    {
        $count = $this->option('count');
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        $this->advanceProgressBar($bar, $dispatcher);
        $importer->import(CustomerImport::class, compact('count'));
        $bar->finish();
        $this->info(PHP_EOL . 'Successfully imported ' . $count . ' customer(s)');
    }

    /**
     * @param $bar
     * @param $dispatcher
     */
    protected function advanceProgressBar($bar, $dispatcher)
    {
        $dispatcher->listen('customer.import', function () use ($bar) {
            $bar->advance();
        });
    }
}
