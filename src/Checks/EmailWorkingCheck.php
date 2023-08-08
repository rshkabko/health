<?php

namespace Flamix\Health\Checks;

use Illuminate\Support\Facades\Http;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Exceptions\InvalidCheck;

class EmailWorkingCheck extends Check
{
    private string $email;

    public function email(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function run(): Result
    {
        $emailResult = $this->sendEmail();
        $result = Result::make();
        return str_contains($emailResult, '</html>') ? $result->ok() : $result->failed("Render error!");
    }

    protected function sendEmail(): string
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer phpunitbearerKEY',
        ])->post(config('app.url') . '/api/v1/mail/send/flamix.solutions/en/' . $this->email, [
            'template' => 'general',
            'subject' => '📧 Subject',
            'title' => '❤️‍🔥 Supper important title!',
            'body' => '<h1>Hello Jon,</h1><p>Test email!</p>',
        ])->body();
    }
}
