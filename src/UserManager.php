<?php

class UserManager {

    public static function get($id) {
        $db = Database::getInstance();
        $data = $db->selectSingle('name', 'users', array('id' => $id));
        return $data ? $data['name'] : null;
    }

}
