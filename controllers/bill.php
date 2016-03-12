<?php

use App\AuthManager;

class BillController extends App\Controller {

    public function __construct() {
        parent::__construct();
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function handleAction($action, $args) {
        if ($args !== '' && $action !== 'confirm') {
            return false;
        }
        switch ($action) {
            case 'active':
                $this->handleActive();
                return true;
            case 'setup':
                $this->handleSetup();
                return true;
            case 'create':
                $this->handleCreate();
                return true;
            case 'list':
                $this->handleList();
                return true;
            case 'confirm':
                if (!ctype_digit($args)) {
                    false;
                }
                $this->handleBillPaymentConfirmation((int) $args);
                return true;
            case 'history':
                $this->handleHistory();
                return true;
            case 'history.json':
                $this->handleHistoryJson();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleActive() {
        $this->output($this->renderTemplate('bill/active'));
    }

    private function handleSetup() {
        $this->output($this->renderTemplate('bill/setup'));
    }

    private function handleCreate() {
        $desc = $this->validatePost('desc', 1);
        $total = $this->validatePost('total', 'float', 0.01);
        $payableTo = $this->validatePost('payTo', 1);
        $splitwith = $this->validatePost('splitwith', 'array');
        $proportions = $this->validatePost('proportions', 'array');
        if (count($splitwith) !== count($proportions)) {
            $this->handleError(400, "User ID array to split with does not match the length of the proportions");
        }
        $splits = array();
        foreach ($proportions as $i => $proportion) {
            if (!ctype_digit($proportion)) {
                $this->handleError(400, "Proportion percentage in array is not a valid integer, found '{$proportion}'");
            }
            $proportion = (int) $proportion;
            if ($proportion < 1 || $proportion > 100) {
                $this->handleError(400, "Proportion must be between 1 and 100, found {$proportion}");
            }
            $userId = $splitwith[$i];
            if (!ctype_digit($userId)) {
                $this->handleError(400, "User ID in array is not a valid integer, found '{$userId}'");
            }
            $splits[(int) $userId] = $proportion / 100;
        }
        try {
            $id = $this->loadModel('BillModel')->addNewBill(AuthManager::getUserId(), $desc, $total, $payableTo, $splits);
        } catch (Exception $e) {
            $this->failJson($e);
        }
        $this->checkSuccessJson($id !== false, "Failed to create bill");
        http_response_code(201);
    }

    private function handleList() {
        try {
            $bills = $this->loadModel('BillModel')->getActiveBills(AuthManager::getUserId());
        } catch (Exception $e) {
            $this->failJson($e, 400);
        }
        $this->outputJson($bills);
    }

    private function handleBillPaymentConfirmation($billId) {
        $success = $this->loadModel('BillModel')->confirmPayment(AuthManager::getUserId(), $billId);
        $this->checkSuccessJson($success, "Failed to confirm payment");
        http_response_code(204);
    }

    private function handleHistory() {
        $this->output($this->renderTemplate('bill/history'));
    }

    private function handleHistoryJson() {
        try {
            $history = $this->loadModel('BillModel')->getHistoryForUserHousehold(AuthManager::getUserId());
        } catch (Exception $e) {
            $this->failJson($e, 400);
        }
        $this->outputJson($history);
    }
}
