<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $cities = DB::table('country_state_cities')->get();
        foreach ($cities as $city) {

            $db_city = CountryStateCity::find($city->id);

            if ($city->state_code == null) {
                $db_city->update(['state_code' => $db_city->state->code]);
            }

            if ($city->default_name == null) {

                DB::table('country_state_cities')
                    ->where('id', $city->id)
                    ->update(['default_name' => $db_city->translations->first()->default_name]);
            }

            $db_city->update(['code' => strtolower(str_replace(' ', '_', $db_city->default_name))]);
        }

        $this->info('end!');
    }
}
