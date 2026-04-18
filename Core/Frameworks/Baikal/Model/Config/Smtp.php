<?php

declare(strict_types=1);

namespace Baikal\Model\Config;

class Smtp extends \Baikal\Model\Config {
    protected $aData = [
        "host"       => "",
        "port"       => 587,
        "username"   => "",
        "password"   => "",
        "encryption" => "tls",
    ];

    function __construct() {
        parent::__construct("smtp");
    }

    function formMorphologyForThisModelInstance() {
        $oMorpho = new \Formal\Form\Morphology();

        $oMorpho->add(new \Formal\Element\Text([
            "prop"  => "host",
            "label" => "SMTP host",
            "help"  => "Leave empty to use PHP mail() fallback",
        ]));

        $oMorpho->add(new \Formal\Element\Text([
            "prop"  => "port",
            "label" => "SMTP port",
        ]));

        $oMorpho->add(new \Formal\Element\Listbox([
            "prop"    => "encryption",
            "label"   => "Encryption",
            "options" => ["tls", "ssl", "none"],
        ]));

        $oMorpho->add(new \Formal\Element\Text([
            "prop"  => "username",
            "label" => "SMTP username",
            "help"  => "Leave empty if authentication is not required",
        ]));

        $oMorpho->add(new \Formal\Element\Password([
            "prop"        => "password",
            "label"       => "SMTP password",
            "placeholder" => "-- Leave empty to keep current password --",
        ]));

        return $oMorpho;
    }

    function set($sProp, $sValue) {
        if ($sProp === "password" && $sValue === "") {
            return $this;
        }

        parent::set($sProp, $sValue);
    }

    function get($sProp) {
        if ($sProp === "password") {
            return "";
        }

        return parent::get($sProp);
    }

    function label() {
        return "Baïkal SMTP Settings";
    }
}
