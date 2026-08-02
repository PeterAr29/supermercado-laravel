<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ProveedorSheetController extends Controller
{
    private $sheetUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTEE6Fgx0mir4VGby8lt4KS3OGVqQXC1mJG2aQ6Q8GLw_cMFGMP7xN-l5ZbHaPLBbWo-KtZ8AZMGmsY/pub?gid=0&single=true&output=csv';

    public function index()
    {
        $response = Http::get($this->sheetUrl);

        if (!$response->successful()) {
            abort(500, 'No se pudo leer Google Sheets');
        }

        $rows = array_map('str_getcsv', explode("\n", $response->body()));
        $headers = array_shift($rows);

        $proveedores = [];

        foreach ($rows as $row) {
            if (count($row) === count($headers)) {
                $proveedores[] = array_combine($headers, $row);
            }
        }

        return view('proveedores.sheet', compact('proveedores'));
    }
}
