<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BookCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'book:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To Cancell booking which are not paid after given time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Cron job running" . now());
    }
}
