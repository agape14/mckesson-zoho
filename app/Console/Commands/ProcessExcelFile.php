<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProcessExcelFile extends Command
{
    protected $signature = 'excel:process {path} {output}';
    protected $description = 'Process an Excel file and export the first 540 columns to a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '1G'); // Ajusta el límite de memoria según sea necesario

        try {
            $path = $this->argument('path');
            $output = $this->argument('output');

            // Verificar si el archivo existe
            if (!file_exists($path)) {
                $this->error("El archivo de entrada no existe en la ruta proporcionada.");
                return;
            }

            // Procesar el archivo en bloques
            $csvFile = fopen($output, 'w');

            Excel::import(new ExcelImport($csvFile), $path);

            fclose($csvFile);

            $this->info('El archivo Excel ha sido procesado y el archivo CSV se ha creado correctamente.');
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

class ExcelImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $csvFile;

    public function __construct($csvFile)
    {
        $this->csvFile = $csvFile;
    }

    public function model(array $row)
    {
        $filteredRow = array_slice($row, 0, 540);
        fputcsv($this->csvFile, $filteredRow);
        return null; // Return null since we're not saving models
    }

    public function chunkSize(): int
    {
        return 1000; // Número de filas por bloque
    }
}