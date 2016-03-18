<?php

use App\AuthManager;

class NotificationsController extends App\Controller {

    public function __construct() {
        parent::__construct();
    }

    protected function authorizeRequest($request) {
        return AuthManager::isLoggedIn();
    }

    protected function handleDefaultGet() {
        $userId = AuthManager::getUserId();
        session_write_close();
        $lastId = 0;
        if (isset($_SERVER['HTTP_X_LAST_ID'])) {
            $lastId = (int) $_SERVER['HTTP_X_LAST_ID'];
        }
        $delay = 0;
        do {
            usleep($delay);
            $delay = 1000000;
            $notifications = Notifications::getNotificationsFor($userId, $lastId);
        } while (count($notifications) === 0 && connection_aborted() === 0);
        $this->outputJson($notifications);
    }

    protected function handleAction($action, $args) {
        if ($action === 'dismiss') {
            if (!ctype_digit($args)) {
                return false;
            }
            $this->dismiss((int) $args);
            return true;
        }
        if ($action == 'chat') {
            $this->doChat();
            return true;
        }
        return parent::handleAction($action, $args);
    }

    private function dismiss($notId) {
        Notifications::dismiss(AuthManager::getUserId(), $notId);
        http_response_code(204);
    }

    private function doChat() {
        // TODO This is a temporary last-minute addition to get chat working
        // Obviously the code is terrible
        $name = \UserManager::getName(AuthManager::getUserId());
        $hid = Database::getInstance()->selectSingle('hh_id', 'users', array('id' => AuthManager::getUserId()));
        foreach (Database::getInstance()->select('id', 'users', array('hh_id' => $hid['hh_id'])) as $user) {
            if ($user['id'] != AuthManager::getUserId()) {
                Notifications::pushNotification($user['id'], $name . ':' . $this->validatePost('msg'), Notifications::CHAT);
            }
        }
    }

}
