<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Class HealthCheckController.
 */
final class HealthCheckController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $status = [
            'app' => 'ok',
            'database' => 'ok',
            'cache' => 'ok',
            'queue' => 'ok',
        ];

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $status['database'] = 'error';
        }

        try {
            Cache::put('health_check', 'ok', 1);

            if (Cache::get('health_check') !== 'ok') {
                throw new RuntimeException('Cache not returning expected value');
            }
        } catch (Throwable $e) {
            $status['cache'] = 'error';
        }

        try {
            /** @var QueueManager $queue */
            $queue = app(QueueManager::class);
            $defaultDriver = $queue->getDefaultDriver();

            $queue->connection($defaultDriver);
        } catch (Throwable $e) {
            $status['queue'] = 'error';
        }

        $hasErrors = in_array('error', $status, true);

        return response()->json($status, $hasErrors ? Response::HTTP_INTERNAL_SERVER_ERROR : Response::HTTP_OK);
    }
}

