<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Core\Models\CountryState;
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
        $csvFile = 'CityDataGrid.csv';
        $csvData = $this->csvToArray($csvFile);
        foreach ($csvData as $row) {
            $city_en = DB::table('country_state_city_translations')->where('default_name', $row['name_en'])->first();
            if ($city_en) {
                $city_ar = DB::table('country_state_city_translations')->where('country_state_city_id', $city_en->country_state_city_id)->where('locale', 'ar')->first();
                $city_ar->update([
                    'default_name' => $row['name_ar']
                ]);
            }
        }

        $csvFile = 'StateDataGrid.csv';
        $csvData = $this->csvToArray($csvFile);
        foreach ($csvData as $row) {
            $state_en = DB::table('country_state_translations')->where('default_name', $row['name_en'])->first();
            if ($state_en) {
                $state_ar = DB::table('country_state_translations')->where('country_state_id', $state_en->country_state_id)->where('locale', 'ar')->first();
                $state_ar->update([
                    'default_name' => $row['name_ar']
                ]);
            }
        }
        $this->info('end!');
    }

    function csvToArray($filename, $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== false) {
            // Read the first line
            $firstLine = fgets($handle);
            // Check for BOM and remove it if present
            if (substr($firstLine, 0, 3) === "\u{FEFF}") {
                $firstLine = substr($firstLine, 3);
            }

            // Parse the first line as CSV
            $header = str_getcsv($firstLine, $delimiter);

            // Continue reading the rest of the file
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                // Convert all scientific notation numbers to strings
                foreach ($row as &$value) {
                    if (preg_match('/^\d+\.?\d*E[\+\-]?\d+$/i', $value)) {
                        $value = sprintf('%.0f', $value); // Convert to plain number string
                    }
                }
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
