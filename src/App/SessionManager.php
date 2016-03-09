<?php

namespace App;

class SessionManager {

    private static $instance;

    public static final function getInstance() {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct() {
        $this->startSession();
    }

    private function startSession() {
        ini_set('session.use_only_cookies', 1);
        $cookie = session_get_cookie_params();
        $cookie['domain'] = $cookie['domain'] ? $cookie['domain'] : '.' . $_SERVER['SERVER_NAME'];
        session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], false, true);
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function put($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $fallback = null) {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $fallback;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

}
