<?php

class UserManager {

    public static function getName($id) {
        $db = Database::getInstance();
        $data = $db->selectSingle('name', 'users', array('id' => $id));
        return $data ? $data['name'] : null;
    }

    public static function getDetails($id) {
        $db = Database::getInstance();
        $data = $db->selectSingle('name, email', 'users', array('id' => $id));
        return $data ?: null;
    }

}
