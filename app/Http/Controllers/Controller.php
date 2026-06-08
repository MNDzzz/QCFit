<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success'=> true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse( $code, $message = null)
    {
        return response()->json([
            'success'=> false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    /**
     * Obtenemos y validamos la columna de ordenación para las tablas.
     * Por defecto usamos 'created_at'.
     *
     * @param array $allowedColumns Columnas por las que se permite ordenar
     * @param string $default Columna por defecto
     * @return string
     */
    protected function getOrderColumn(array $allowedColumns = ['id', 'name', 'created_at'], string $default = 'created_at'): string
    {
        $column = request('order_column', $default);
        return in_array($column, $allowedColumns) ? $column : $default;
    }

    /**
     * Obtenemos y validamos la dirección de ordenación (asc o desc)
     *
     * @param string $default Dirección por defecto
     * @return string
     */
    protected function getOrderDirection(string $default = 'desc'): string
    {
        $direction = strtolower(request('order_direction', $default));
        return in_array($direction, ['asc', 'desc']) ? $direction : $default;
    }
}
