<?php

namespace Flamix\Health\Checks;

use Carbon\Carbon;
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
        $result = Result::make();

        if ($sslExpireDays > 5)
            return $result->ok();

        if ($sslExpireDays > 1)
            return $result->warning("Ssl Expire days left: {$sslExpireDays}");

        return $result->failed("Ssl expire!");
    }

    protected function getSSLExpireDays(): int
    {
        if (empty($this->domain))
            throw new InvalidCheck('Empty domain');

        $orignal_parse = parse_url($this->domain, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate'] ?? time());

        return intval(Carbon::createFromTimestamp($certinfo['validTo_time_t'])?->toDateTimeString() ?? 0);
    }
}
