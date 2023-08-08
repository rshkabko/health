<?php

namespace Flamix\Health\Checks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\InvalidCheck;

class B24AppCheck extends Check
{
    private string $url;

    public function domain(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function run(): Result
    {
        $result = Result::make();

        try {
            $appStatus = $this->getAppStatus();
        } catch (\Exception $exception) {
            return $result->failed($exception->getMessage());
        }

        // 600 = 10 минут
        if ($appStatus['status'] === 'success' && isset($appStatus['time'])) {
            return $appStatus['time'] <= 600 ? $result->ok() : $result->warning("Checking a long time ago ({$appStatus['time']} seconds)");
        }

        if ($appStatus['status'] === 'warning')
            return $result->warning($appStatus['message'] ?? 'Empty message!');

        return $result->failed($appStatus['message'] ?? 'Empty message!');
    }

    /**
     * Получаем статус из ПРИЛОЖУХИ.
     *
     * На приложении делаются само тесты.
     *
     * @return array
     * @throws InvalidCheck
     */
    protected function getAppStatus(): array
    {
        if (empty($this->url))
            throw new InvalidCheck('Empty domain!');

        $response = Http::get($this->url);

        if (!$response->ok()) {
            return match ($response->status()) {
                404 => throw new \Exception('404 by url: ' . $this->url),
                503 => ['status' => 'warning', 'message' => 'Maintenance (503)'],
                default => throw new \Exception('Unknown status: ' . $response->status()),
            };
        }

        $json = Http::get($this->url)->json();

        if (is_array($json) && $json['status'] ?? false)
            return $json;

        throw new \Exception('Unknown error: ' . $response->body());
    }
}
