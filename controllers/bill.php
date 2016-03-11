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
        if ($args !== '') {
            return false;
        }
        switch ($action) {
            case 'setup':
                $this->handleSetup();
                return true;
            case 'create':
                $this->handleCreate();
                return true;
            case 'list':
                $this->handleList();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleSetup() {
        $this->output($this->renderTemplate('bill/setup'));
    }

    private function handleCreate() {
        $desc = $this->validatePost('desc', 1);
        $total = $this->validatePost('total', 'float', 0.01);
        $payableTo = $this->validatePost('recipient', 1);
        $id = $this->loadModel('BillModel')->addNewBill(AuthManager::getUserId(), $desc, $total, $payableTo);
        $this->checkSuccessJson($id !== false, "Failed to create bill");
    }

    private function handleList() {
        try {
            $bills = $this->loadModel('BillModel')->getActiveBills(AuthManager::getUserId());
        } catch (Exception $e) {
            $this->failJson($e, 400);
        }
        $this->outputJson($bills);
    }
}
