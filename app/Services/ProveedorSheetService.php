<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ProveedorSheetService
{
    private $url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRfIjcYcPGY5vG_DBG8oWHyesBUeQlS0qiBDlM409Cm6TQ06MEwFBGUCm-z4AnB81T3eUubSMLWxddk/pub?gid=0&single=true&output=csv';

    public function obtenerPorProducto($productoId)
    {
        $response = Http::get($this->url);
        if (! $response->successful()) {
            return [];
        }

        $rows = array_map('str_getcsv', explode("\n", $response->body()));
        $headers = array_shift($rows);

        $relaciones = [];

        foreach ($rows as $row) {
            if (count($row) !== count($headers)) {
                continue;
            }

            $data = array_combine($headers, $row);

            if (
                $data['producto_id'] == $productoId &&
                $data['activo'] === 'SI'
            ) {
                $relaciones[] = $data;
            }
        }

        return $relaciones;
    }
}
