<?php

namespace App\Mail\Concerns;

trait LogsEmail
{
    public ?int $email_log_id = null;
    public ?int $recado_id = null;
    public ?int $triggered_by_user_id = null;
    public ?string $view_name = null;

    public function withEmailLog(int $logId): static
    {
        $this->email_log_id = $logId;
        return $this;
    }

    public function withRecado(?int $recadoId): static
    {
        $this->recado_id = $recadoId;
        return $this;
    }

    public function triggeredBy(?int $userId): static
    {
        $this->triggered_by_user_id = $userId;
        return $this;
    }

    public function usingViewName(?string $view): static
    {
        $this->view_name = $view;
        return $this;
    }
}
