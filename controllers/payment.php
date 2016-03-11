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
        if ($args !== '' && $action !== 'make') {
            return false;
        }
        switch ($action) {
            case 'history':
                $this->handleHistoryHtml();
                return true;
            case 'history.json':
                $this->handleHistoryJson();
                return true;
            case 'pending':
                $this->handlePending();
                return true;
            case 'make':
                if (!ctype_digit($args)) {
                    return false;
                }
                $this->handleMakePayment((int) $args);
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleHistoryHtml() {
        $this->output($this->renderTemplate('payment/history'));
    }

    private function handleHistoryJson() {
        $history = $this->loadModel('PaymentModel')->getHistoryForUser(AuthManager::getUserId());
        $this->outputJson($history);
    }

    private function handlePending() {
        $pending = $this->loadModel('PaymentModel')->getPendingForUser(AuthManager::getUserId());
        $this->outputJson($pending);
    }

    private function handleMakePayment($billId) {
        $success = $this->loadModel('PaymentModel')->makePayment(AuthManager::getUserId(), $billId);
        $this->checkSuccessJson($success, "Failed to make payment");
        http_response_code(204);
    }

}
