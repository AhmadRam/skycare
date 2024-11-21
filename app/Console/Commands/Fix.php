<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Core\Models\CountryState;
use Webkul\Core\Models\CountryStateCity;
use Webkul\Sales\Jobs\CreateOdooOrder;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

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
