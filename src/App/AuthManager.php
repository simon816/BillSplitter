<?php

namespace App;

class AuthManager {

    public static function isLoggedIn() {
        return self::getUserId() !== null;
    }

    public static function getUserId() {
        return SessionManager::getInstance()->get('auth.userId');
    }

    public static function login($email, $password) {
        $db = \Database::getInstance();
        $data = $db->selectSingle('id, pass_hash, salt', 'users', array('email' => $email));
        if (!$data) {
            return array('success' => false, 'error' => array('email' => 'Unknown email'));
        }
        $hash = hash('sha512', $password . $data['salt']);
        if ($hash !== $data['pass_hash']) {
            return array('success' => false, 'error' => array('password' => 'Incorrect password'));
        }
        SessionManager::getInstance()->put('auth.userId', $data['id']);
        return array('success' => true);
    }

    public static function register($name, $email, $password) {
        $db = \Database::getInstance();
        $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        $hash = hash('sha512', $password . $salt);
        if (!$db->insert('users', array('name' => $name, 'pass_hash' => $hash, 'salt' => $salt, 'email' => $email))) {
            if ($db->count('users', array('email' => $email)) !== 0) {
                return array('success' => false, 'error' => array('email' => 'Email address already exists'));
            }
            return array('success' => false, 'error' => array());
        }
        $userId = $db->asPDO()->lastInsertId('id');
        SessionManager::getInstance()->put('auth.userId', $userId);
        return array('success' => true);
    }

    public static function logout() {
        SessionManager::getInstance()->remove('auth.userId');
    }

}
