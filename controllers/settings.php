<?php

use App\AuthManager;

class SettingsController extends App\Controller {

    public function __construct() {
        parent::__construct('settings/index');
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function getGlobalVars() {
        $details = \UserManager::getDetails(AuthManager::getUserId());
        $error = App\SessionManager::getInstance()->get('settings.error');
        var_dump($error);
        App\SessionManager::getInstance()->remove('settings.error');
        return array(
            'error' => $error,
            'user' => $details['name'],
            'email' => $details['email']
        );
    }

    protected function handleAction($action, $args) {
        if ($args !== '') {
            return false;
        }
        switch ($action) {
            case 'details':
                $this->handleDetails();
                return true;
            case 'password':
                $this->handlePassword();
                return true;
        }
        return parent::handleAction($action, $args);
    }

    private function handleDetails() {
        $name = $this->validatePost('name', 2);
        $email = $this->validatePost('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = App\SessionManager::getInstance()->get('settings.error', array());
            $error['email'] = 'Invalid email address';
            App\SessionManager::getInstance()->put('settings.error', $error);
        } else {
            AuthManager::changeDetails($name, $email);
        }
        $this->redirect('/settings', 303);
    }

    private function handlePassword() {
        $oldPass = $this->validatePost('oldpass', 6);
        $password = $this->validatePost('password', 6);
        $result = AuthManager::changePassword($oldPass, $password);
        if (is_string($result)) {
            $error = App\SessionManager::getInstance()->get('settings.error', array());
            $error['oldPassword'] = $result;
            App\SessionManager::getInstance()->put('settings.error', $error);
        }
        $this->redirect('/settings', 303);
    }

}
