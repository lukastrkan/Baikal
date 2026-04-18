<?php

declare(strict_types=1);

namespace BaikalAdmin\Controller\Settings;

class Smtp extends \Flake\Core\Controller {
    private \Baikal\Model\Config\Smtp $oModel;
    private \Formal\Form $oForm;

    function execute() {
        $this->oModel = new \Baikal\Model\Config\Smtp();

        if (!$this->oModel->writable()) {
            throw new \Exception("Config file is not writable;" . __FILE__ . " > " . __LINE__);
        }

        $this->oForm = $this->oModel->formForThisModelInstance([
            "close" => false,
        ]);

        if ($this->oForm->submitted()) {
            $this->oForm->execute();
        }
    }

    function render() {
        $oView = new \BaikalAdmin\View\Settings\Smtp();
        $oView->setData("form", $this->oForm->render());

        return $oView->render();
    }
}
