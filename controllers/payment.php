<?php

use App\AuthManager;

class PaymentController extends App\Controller {

    public function __construct() {
        parent::__construct();
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function handleAction($action, $args) {
        if ($args !== '') {
            return false;
        }
        switch ($action) {
            case 'history':
                $this->handleHistory();
                return true;
            case 'pending':
                $this->handlePending();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleHistory() {
        $this->output($this->renderTemplate('payment/history'));
    }

    private function handlePending() {
        $pending = $this->loadModel('PaymentModel')->getPendingForUser(AuthManager::getUserId());
        $this->outputJson($pending);
    }

}
