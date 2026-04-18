<?php

declare(strict_types=1);

namespace vhs;

use Sabre\CalDAV\Schedule\IMipPlugin as SabreIMipPlugin;

class IMipPlugin extends SabreIMipPlugin
{
    protected function mail($to, $subject, $body, array $headers)
    {
        $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
        $headers[] = 'Content-Transfer-Encoding: base64';
        mail($to, $encodedSubject, base64_encode($body), implode("\r\n", $headers));
    }
}
