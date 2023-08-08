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
        $usedDiskSpacePercentage = $this->getLicenseResponse();
        $result = Result::make();
        return $usedDiskSpacePercentage ? $result->ok() : $result->failed("License check failed!");
    }

    protected function getLicenseResponse(): bool
    {
        if (empty($this->key))
            throw new InvalidCheck('Empty key!');

        $license = Http::get('https://cp.flamix.solutions/extranet/ajax/pub/license.php?action=checkKey&key=' . $this->key);
        return $license->ok() && $license->json('sStatus', 'ERROR') === "SUCCESS";
    }
}
