<?php
declare(strict_types=1);

class EmailService {
    public static function send(string $to, string $subject, string $body): bool {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . Env::get('MAIL_FROM', 'no-reply@localhost'),
        ];
        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        if (AppConfig::isDebug()) {
            error_log("[mail] to={$to}; subject={$subject}; body={$body}");
        }
        return $sent;
    }
}