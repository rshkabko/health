<?php

namespace Flamix\Health\Checks;

use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\InvalidCheck;

class LicenseWorkingCheck extends Check
{
    private string $key;

    public function key(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function run(): Result
    {
        $error = $this->getLicenseError();
        $result = Result::make();
        return $error === null ? $result->ok() : $result->failed($error);
    }

    /**
     * Проверяем лицензию в биллинге.
     *
     * @return string|null null если лицензия валидна, иначе текст ошибки
     */
    protected function getLicenseError(): ?string
    {
        if (empty($this->key))
            throw new InvalidCheck('Empty key!');

        $license = Http::get('https://billing.flamix.info/rr/v1/license/' . $this->key);

        if (!$license->ok())
            return 'License server unavailable! HTTP ' . $license->status();

        if ($license->json('success') === true)
            return null;

        return $license->json('message') ?? 'License check failed!';
    }
}
