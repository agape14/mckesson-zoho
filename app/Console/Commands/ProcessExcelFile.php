<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;


class ProcessExcelFile extends Command
{
    protected $signature = 'excel:process {path} {outputCsv} {outputTxt} {sheet=Sheet1} {columns=540}';
    protected $description = 'Process an Excel file and export the specified number of columns to a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '1G'); // Ajusta el límite de memoria según sea necesario

        try {
            $path = $this->argument('path');
            $outputCsv = $this->argument('outputCsv');
            $outputTxt = $this->argument('outputTxt');
            $sheet = $this->argument('sheet');
            $columns = (int) $this->argument('columns');

            // Verificar si el archivo existe
            if (!file_exists($path)) {
                $this->error("El archivo de entrada no existe en la ruta proporcionada.");
                return;
            }

            // Procesar el archivo en bloques
            $csvFile = fopen($outputCsv, 'w');
            $txtFile = fopen($outputTxt, 'w');

            Excel::import(new ExcelImport($csvFile, $txtFile, $sheet, $columns), $path);

            fclose($csvFile);
            fclose($txtFile);

            $this->info('El archivo Excel ha sido procesado y el archivo CSV se ha creado correctamente.');
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

class ExcelImport implements ToModel, WithHeadingRow, WithChunkReading, WithMultipleSheets
{
    protected $csvFile;
    protected $txtFile;
    protected $sheet;
    protected $columns;

    public function __construct($csvFile, $txtFile, $sheet, $columns)
    {
        $this->csvFile = $csvFile;
        $this->txtFile = $txtFile;
        $this->sheet = $sheet;
        $this->columns = $columns;
    }

    public function model(array $row)
    {
        $filteredRow = array_slice($row, 0, $this->columns);
        // Guardar en CSV
        fputcsv($this->csvFile, $filteredRow);

        // Guardar en TXT en formato JSON
        fwrite($this->txtFile, json_encode($filteredRow) . PHP_EOL);

        return null; // Return null since we're not saving models
    }

    public function chunkSize(): int
    {
        return 1000; // Número de filas por bloque
    }

    public function sheets(): array
    {
        return [
            $this->sheet => new SheetImport($this->csvFile, $this->txtFile, $this->columns),
        ];
    }
}

class SheetImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $csvFile;
    protected $txtFile;
    protected $columns;

    public function __construct($csvFile, $txtFile, $columns)
    {
        $this->csvFile = $csvFile;
        $this->txtFile = $txtFile;
        $this->columns = $columns;
    }

    public function model(array $row)
    {
        $filteredRow = array_slice($row, 0, $this->columns);
        
        // Guardar en CSV
        fputcsv($this->csvFile, $filteredRow);

        // Guardar en TXT en formato JSON
        fwrite($this->txtFile, json_encode($filteredRow) . PHP_EOL);
        
        return null; // Return null since we're not saving models
    }

    public function chunkSize(): int
    {
        return 1000; // Número de filas por bloque
    }
}