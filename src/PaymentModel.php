<?php

class PaymentModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getPendingForUser($userId) {
        $memCount = $this->db->count('household_member', array('user_id' => $userId));
        if ($memCount === false || $memCount === 0) {
            return null;
        }
        $data = $this->db->query('SELECT * FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND payment_received = 0', array($userId));
//        var_dump($data);
        return array(); // TODO
    }

}
