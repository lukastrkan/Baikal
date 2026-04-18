<?php

declare(strict_types=1);

namespace BaikalAdmin\Route\Settings;

class Smtp extends \Flake\Core\Route {
    static function layout(\Flake\Core\Render\Container &$oRenderContainer) {
        $oRenderContainer->zone("Payload")->addBlock(new \BaikalAdmin\Controller\Settings\Smtp());
    }
}
