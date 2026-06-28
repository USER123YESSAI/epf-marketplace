<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController
{
    /**
     * Health check endpoint for monitoring and load balancers
     */
    public function __invoke(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'up',
                'connection' => config('database.default'),
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        // Storage check
        try {
            $disk = \Storage::disk(config('filesystems.default'));
            $disk->exists('health_check');
            $checks['storage'] = [
                'status' => 'up',
                'disk' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['storage'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        // Cache check
        try {
            \Cache::put('health_check', true, 60);
            $cached = \Cache::get('health_check');
            $checks['cache'] = [
                'status' => $cached ? 'up' : 'down',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['cache'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        $httpStatus = $status === 'healthy' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'environment' => config('app.env'),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ], $httpStatus);
    }
}
