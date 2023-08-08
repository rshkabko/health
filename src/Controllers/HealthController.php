<?php

namespace Flamix\Health\Controllers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Health;
use Spatie\Health\ResultStores\ResultStore;
use Carbon\Carbon;

class HealthController extends Controller
{
    /**
     * Проверка состояния системы.
     *
     * Вручную можно запустить php artisan health:check
     *
     * @param Schedule $schedule
     * @return void
     */
    public static function schedule(Schedule $schedule): void
    {
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
        $schedule->command('health:check')->runInBackground()->everyFiveMinutes();
    }

    /**
     * Котроллер по выводу результатов тестирования.
     *
     * Важно! Рестарт теста не доступен через WEB интерфейс, потому что все тесты инициализируются только через cmd.
     * Чтобы его рестартануть, в консоле запустите "php artisan health:check"
     *
     * @param string $response
     * @param ResultStore $resultStore
     * @param Health $health
     * @return array|View
     */
    public static function show(string $response, ResultStore $resultStore, Health $health): array|View
    {
        $checkResults = $resultStore->latestResults();
        $time = new Carbon($checkResults?->finishedAt ?? now());

        switch ($response) {
            case 'json':
                foreach ($checkResults?->storedCheckResults ?? [] as $result) {
                    if ($result->status === 'ok') continue;

                    // Хотя бы один текст не прошел, сразу возвращаем результаты
                    return [
                        'status' => $result->status,
                        'key' => $result->name,
                        'value' => $result->shortSummary ?? '',
                        'name' => $result->label ?? '',
                        'message' => $result->notificationMessage ?? '',
                        'time' => $time->diffInSeconds(),
                    ];
                }

                return ['status' => 'success', 'time' => $time->diffInSeconds()];
        }

        return view('health::list', [
            'lastRanAt' => $time,
            'checkResults' => $checkResults,
            'assets' => $health->assets(),
            'theme' => config('health.theme', 'dark'),
        ]);
    }

    /**
     * Получаем статус ВСЕХ систем с https://status.flamix.solutions/?json
     *
     * @return string
     */
    public static function getAllSystemStatus(): string
    {
        return Cache::remember('system_status', 1800, function () {
            $status = Http::timeout(5)->get('https://status.flamix.solutions/?json');
            $status = $status->json('status', 'warning');
            return match (strtolower($status)) {
                'success' => $status,
                'failed' => 'error',
                'error' => 'error',
                default => 'warning'
            };
        });

    }
}
