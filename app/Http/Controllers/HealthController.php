<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $db = $this->databaseStatus();

        $healthy = $db === 'ok';

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'db' => $db,
        ], $healthy ? 200 : 503);
    }

    private function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (\Throwable) {
            return 'fail';
        }
    }
}
