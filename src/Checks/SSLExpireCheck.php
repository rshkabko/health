<?php

namespace Flamix\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\InvalidCheck;

class SSLExpireCheck extends Check
{
    private string $domain;

    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function run(): Result
    {
        $sslExpireDays = $this->getSSLExpireDays();
        $result = Result::make()->meta(['days_left' => $sslExpireDays]);

        if ($sslExpireDays > 5)
            return $result->ok("Ssl expire days left: {$sslExpireDays}");

        if ($sslExpireDays > 1)
            return $result->warning("Ssl expire days left: {$sslExpireDays}");

        return $result->failed("Ssl expire days left: {$sslExpireDays}");
    }

    protected function getSSLExpireDays(): int
    {
        if (empty($this->domain))
            throw new InvalidCheck('Empty domain');

        $host = parse_url($this->domain, PHP_URL_HOST) ?: $this->domain;
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $client = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

        if ($client === false)
            throw new InvalidCheck("Could not connect to {$host}:443 — {$errstr} ({$errno})");

        $params = stream_context_get_params($client);
        $certificate = $params['options']['ssl']['peer_certificate'] ?? null;

        if ($certificate === null)
            throw new InvalidCheck("Could not capture SSL certificate for {$host}");

        $certinfo = openssl_x509_parse($certificate);

        if ($certinfo === false || empty($certinfo['validTo_time_t']))
            throw new InvalidCheck("Could not parse SSL certificate for {$host}");

        // Whole days remaining until expiry (negative if already expired).
        return (int) floor(($certinfo['validTo_time_t'] - time()) / 86400);
    }
}
