<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Core\Models\CountryStateCity;

class Fix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('start!');

        $cities = CountryStateCity::all();
        foreach ($cities as $city) {
            dd($city->state);
            if ($city->state_code == null) {
                $city->update(['state_code' => $city->state->code]);
            }

            if ($city->default_name == null) {
                $city->update(['default_name' => $city->default_name]);
            }
        }

        $this->info('end!');
    }
}
