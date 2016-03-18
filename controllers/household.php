<?php

use App\AuthManager;

class HouseholdController extends App\Controller {

    public function __construct() {
        parent::__construct('household/index');
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function handleAction($action, $args) {
        if ($args !== '') {
            return false;
        }
        switch ($action) {
            case 'details':
                $this->handleDetails();
                return true;
            case 'create':
                $this->handleCreate();
                return true;
            case 'join':
                $this->handleJoin();
                return true;
            case 'members':
                $this->handleListMembers();
                return true;
            case 'leave':
                $this->handleLeave();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleDetails() {
        $details = $this->loadModel('HouseholdModel')->getDetails(AuthManager::getUserId());
        $this->outputJson($details);
    }

    private function handleCreate() {
        $name = $this->validatePost('name', 1);
        $id = $this->loadModel('HouseholdModel')->create($name, AuthManager::getUserId());
        $this->checkSuccessJson($id !== false, "Failed to create household");
        $this->outputJson(array('id' => $id));
    }

    private function handleJoin() {
        $reqEmail = $this->validatePost('email');
        if (!filter_var($reqEmail, FILTER_VALIDATE_EMAIL)) {
            $this->failJson(new InvalidArgumentException("Invalid email address"));
        }
        try {
            $success = $this->loadModel('HouseholdModel')->requestJoin(AuthManager::getUserId(), $reqEmail);
        } catch (Exception $e) {
            $this->failJson($e, 400);
        }
        $this->checkSuccessJson($success, "Failed to request joining that household");
        http_response_code(204);
    }

    private function handleListMembers() {
        $members = $this->loadModel('HouseholdModel')->getHousemates(AuthManager::getUserId());
        $this->outputJson($members);
    }

    private function handleLeave() {
        try {
            $success = $this->loadModel('HouseholdModel')->leaveHousehold(AuthManager::getUserId());
        } catch (Exception $e) {
            $this->failJson($e);
        }
        $this->checkSuccessJson($success, "Failed to leave household");
        http_response_code(204);
    }

}
