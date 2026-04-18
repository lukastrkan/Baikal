<?php

declare(strict_types=1);

namespace vhs;

use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;

class SentryPlugin extends ServerPlugin
{
    private string $failedAccessMessage;

    public function __construct(string $dsn, string $failedAccessMessage = '', float $tracesSampleRate = 0.0)
    {
        $options = ['dsn' => $dsn];
        if ($tracesSampleRate > 0.0) {
            $options['traces_sample_rate'] = $tracesSampleRate;
        }
        \Sentry\init($options);
        $this->failedAccessMessage = $failedAccessMessage;
    }

    public function initialize(Server $server): void
    {
        $server->on('exception', [$this, 'onException']);
    }

    public function getPluginName(): string
    {
        return 'sentry';
    }

    public function getPluginInfo(): array
    {
        return [
            'name'        => $this->getPluginName(),
            'description' => 'Captures exceptions and sends them to Sentry.',
        ];
    }

    public function onException(\Throwable $e): void
    {
        if ($e instanceof NotAuthenticated) {
            // Applications may make their first call without auth so don't log these attempts
            // Pattern from sabre/dav/lib/DAV/Auth/Backend/AbstractDigest.php
            if (
                $this->failedAccessMessage !== ''
                && !preg_match("/No 'Authorization: (Basic|Digest)' header found./", $e->getMessage())
            ) {
                $log_msg = str_replace('%u', '(name stripped-out)', $this->failedAccessMessage);
                error_log($log_msg, 4);
            }
        } else {
            \Sentry\captureException($e);
            error_log((string) $e);
        }
    }
}
