<?php

namespace Flamix\Health\Checks;

use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Is Bitrix24 app installing working?
 * Some time when I change password it's not work, and we loose client.
 */
class B24AppInstallCheck extends Check
{
    private string $portal;
    private string $app;

    public function portal(string $portal): self
    {
        $this->portal = $portal;
        return $this;
    }

    public function app(string $app): self
    {
        $this->app = $app;
        return $this;
    }

    public function run(): Result
    {
        $result = Result::make();

        $request = Http::withOptions(['allow_redirects' => false])->get("https://en.flamix.solutions/install/", [
            'CODE' => $this->app,
            'DOMAIN' => $this->portal,
        ]);

        if ($request->status() === 301) {
            return $result->ok('Installed correctly');
        }

        return $result->failed("Installiation dosen't work, check access. Http status code: {$request->status()}!");
    }
}
