<?php

class UserManager {

    public static function getName($id) {
        $db = Database::getInstance();
        $data = $db->selectSingle('name', 'users', array('id' => $id));
        return $data ? $data['name'] : null;
    }

    public static function getDetails($id) {
        $db = Database::getInstance();
        $data = $db->selectSingle('name, email, prefs', 'users', array('id' => $id));
        if (!$data) {
            return null;
        }
        $data['prefs'] = json_decode($data['prefs'], true);
        return $data;
    }

    public static function updatePreferences($userId, array $prefs) {
        $db = Database::getInstance();
        return $db->update('users', array('prefs' => json_encode($prefs)), array('id' => $userId));
    }

}
