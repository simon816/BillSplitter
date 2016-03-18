<?php

use App\AuthManager;

class SettingsController extends App\Controller {

    private static $prefNames = array('reqJoin', 'newBill', 'pmtMade', 'pmtCncl', 'pmtAcpt', 'pmtDeny', 'billCnf');

    public function __construct() {
        parent::__construct('settings/index');
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function getGlobalVars() {
        $details = \UserManager::getDetails(AuthManager::getUserId());
        $error = App\SessionManager::getInstance()->get('settings.error');
        App\SessionManager::getInstance()->remove('settings.error');
        if (isset($details['prefs']['notifications'])) {
            $prefs = $details['prefs']['notifications'];
        } else {
            $prefs = array();
            foreach (self::$prefNames as $pref) {
                $prefs[$pref] = true;
            }
        }
        $notifications = array_map(function ($emailPref) {
            return $emailPref ? 'checked' : '';
        }, $prefs);
        return array(
            'error' => $error,
            'user' => $details['name'],
            'email' => $details['email'],
            'notification' => $notifications
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
            $emailPrefs = array();
            foreach (self::$prefNames as $field) {
                $emailPrefs[$field] = isset($_POST[$field]) && filter_var($_POST[$field], FILTER_VALIDATE_BOOLEAN);
            }
            AuthManager::changeDetails($name, $email);
            \UserManager::updatePreferences(AuthManager::getUserId(), array('notifications' => $emailPrefs));
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
