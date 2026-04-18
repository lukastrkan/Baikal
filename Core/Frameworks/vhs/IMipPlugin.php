<?php

declare(strict_types=1);

namespace vhs;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Sabre\CalDAV\Schedule\IMipPlugin as SabreIMipPlugin;

/**
 * PHPMailer subclass that injects the iCalendar `method=` parameter into the
 * body Content-Type without affecting subject encoding (which also uses CharSet).
 */
class CalendarMailer extends PHPMailer
{
    public string $calendarMethod = 'REQUEST';

    public function createBody(): string
    {
        $saved = $this->CharSet;
        $this->CharSet = PHPMailer::CHARSET_UTF8 . '; method=' . $this->calendarMethod;
        $result = parent::createBody();
        $this->CharSet = $saved;
        return $result;
    }
}

class IMipPlugin extends SabreIMipPlugin
{
    private array $smtpConfig;

    public function __construct(string $senderEmail, array $smtpConfig = [])
    {
        parent::__construct($senderEmail);
        $this->smtpConfig = $smtpConfig;
    }

    private function parseAddress(string $value): array
    {
        if (preg_match('/^"?([^"<]*?)"?\s*<([^>]+)>$/', $value, $m)) {
            return [trim($m[2]), trim($m[1])];
        }
        return [trim($value), ''];
    }

    protected function mail($to, $subject, $body, array $headers)
    {
        $mail = new CalendarMailer(true);

        [$fromEmail, $fromName] = [$this->senderEmail, ''];
        [$replyToEmail, $replyToName] = ['', ''];
        $method = 'REQUEST';

        foreach ($headers as $header) {
            [$name, $value] = explode(':', $header, 2);
            $value = trim($value);
            switch (strtolower(trim($name))) {
                case 'from':
                    [$fromEmail, $fromName] = $this->parseAddress($value);
                    break;
                case 'reply-to':
                    [$replyToEmail, $replyToName] = $this->parseAddress($value);
                    break;
                case 'content-type':
                    if (preg_match('/method=(\w+)/i', $value, $m)) {
                        $method = strtoupper($m[1]);
                    }
                    break;
            }
        }

        if (!empty($this->smtpConfig['host'])) {
            $mail->isSMTP();
            $mail->Host = $this->smtpConfig['host'];
            $mail->Port = (int) ($this->smtpConfig['port'] ?? 587);
            if (!empty($this->smtpConfig['username'])) {
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpConfig['username'];
                $mail->Password = $this->smtpConfig['password'] ?? '';
            }
            $encryption = $this->smtpConfig['encryption'] ?? 'tls';
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
        }

        $mail->setFrom($fromEmail, $fromName);
        if ($replyToEmail) {
            $mail->addReplyTo($replyToEmail, $replyToName);
        }
        [$toEmail, $toName] = $this->parseAddress($to);
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->ContentType = 'text/calendar';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->calendarMethod = $method;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->Body = $body;

        try {
            $mail->send();
            error_log(sprintf('IMipPlugin: sent %s to <%s> subject "%s"', $method, $to, $subject));
        } catch (PHPMailerException $e) {
            error_log(sprintf('IMipPlugin: failed to send %s to <%s>: %s', $method, $to, $e->getMessage()));
            \Sentry\captureException($e);
            throw $e;
        }
    }
}
