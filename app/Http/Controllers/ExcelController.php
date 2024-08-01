<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Post(
 *     path="/api/process-excel",
 *     summary="Process an Excel file and export the first 540 columns to a CSV file",
 *     tags={"Excel"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"path","output"},
 *             @OA\Property(property="path", type="string", example="/ruta/al/archivo.xlsx"),
 *             @OA\Property(property="output", type="string", example="/ruta/al/output.csv")
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
            'output' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $path = $request->input('path');
        $output = $request->input('output');

        // Ejecutar el comando Artisan
        try {
            Artisan::call('excel:process', [
                'path' => $path,
                'output' => $output,
            ]);

            return response()->json(['message' => 'El archivo Excel ha sido procesado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
