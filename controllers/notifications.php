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
        } while (count($notifications) === 0);
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
        return parent::handleAction($action, $args);
    }

    private function dismiss($notId) {
        Notifications::dismiss(AuthManager::getUserId(), $notId);
        http_response_code(204);
    }

}
