<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Post(
 *     path="/api/process-excel",
 *     summary="Process an Excel file and export the specified number of columns to a CSV and a TXT file",
 *     tags={"Excel"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"path", "outputCsv", "outputTxt", "sheet", "columns"},
 *             @OA\Property(property="path", type="string", example="/ruta/al/archivo.xlsx"),
 *             @OA\Property(property="outputCsv", type="string", example="/ruta/al/output.csv"),
 *             @OA\Property(property="outputTxt", type="string", example="/ruta/al/output.txt"),
 *             @OA\Property(property="sheet", type="string", example="Sheet1"),
 *             @OA\Property(property="columns", type="integer", example=540)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="El archivo Excel ha sido procesado correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="El archivo Excel ha sido procesado correctamente.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Validation error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Error message")
 *         )
 *     )
 * )
 */


 class ExcelController extends Controller
{
    public function processExcel(Request $request)
    {
        // Validar la solicitud
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'outputCsv' => 'required|string',
            'outputTxt' => 'required|string',
            'sheet' => 'required|string',
            'columns' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $path = $request->input('path');
        $outputCsv = $request->input('outputCsv');
        $outputTxt = $request->input('outputTxt');
        $sheet = $request->input('sheet');
        $columns = $request->input('columns');

        // Crear un buffer para capturar la salida del comando
        $buffer = new BufferedOutput();

        // Ejecutar el comando Artisan y capturar la salida
        try {
            $exitCode = Artisan::call('excel:process', [
                'path' => $path,
                'outputCsv' => $outputCsv,
                'outputTxt' => $outputTxt,
                'sheet' => $sheet,
                'columns' => $columns,
            ], $buffer);

            // Verificar el código de salida
            if ($exitCode !== 0) {
                return response()->json(['error' => 'Hubo un problema al procesar el archivo Excel.'], 500);
            }

            // Capturar la salida del comando
            $output = $buffer->fetch();

            return response()->json(['message' => 'El archivo Excel ha sido procesado correctamente.', 'output' => $output], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
