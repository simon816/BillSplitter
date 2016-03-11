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
        return array(
            'user' => $details['name'],
            'email' => $details['email']
        );
    }

}
