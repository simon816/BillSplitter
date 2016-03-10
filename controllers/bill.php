<?php

use App\AuthManager;

class BillController extends App\Controller {

    public function __construct() {
        parent::__construct('bill/index');
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function handleAction($action, $args) {
        switch ($action) {
            case 'setup':
                $this->handleSetup();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleSetup() {
        $this->output($this->renderTemplate('bill/setup'));
    }
}
