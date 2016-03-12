<?php

class PaymentModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getPendingForUser($userId) {
        $household = $this->db->selectSingle('hh_id', 'users', array('id' => $userId));
        if (!$household || $household['hh_id'] == null) {
            return null; // No household
        }
        $data = $this->db->query('SELECT id, total_payable, description, payable_to, qty_paid, qty_owed FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND status = 0', array($userId));
        return array_map(function ($pending) {
            return array(
                'id' => (int) $pending['id'],
                'total' => (float) $pending['total_payable'],
                'description' => $pending['description'],
                'payableTo' => $pending['payable_to'],
                'quantityPaid' => (float) $pending['qty_paid'],
                'quantityOwed' => (float) $pending['qty_owed']
            );
        }, $data);
    }

    public function makePayment($userId, $billId) {
        $affected = $this->db->query('UPDATE payments SET qty_paid = qty_owed, paid_date = CURRENT_TIMESTAMP, status = 1 WHERE user_id = ? AND bill_id = ?', array($userId, $billId), true);
        if ($affected === false) {
            return false;
        }
        if ($affected == 0) {
            throw new Exception("No bill exists with that ID under your membership");
        }
        return true;
    }

    public function getHistoryForUser($userId) {
        $household = $this->db->selectSingle('hh_id', 'users', array('id' => $userId));
        if (!$household || $household['hh_id'] == null) {
            return null; // No household
        }
        $data = $this->db->query('SELECT total_payable, description, payable_to, qty_paid, payments.paid_date FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND status = 1', array($userId));
        return array_map(function ($pending) {
            return array(
                'total' => $pending['total_payable'],
                'description' => $pending['description'],
                'payableTo' => $pending['payable_to'],
                'quantityPaid' => $pending['qty_paid'],
                'date' => strtotime($pending['paid_date'])
            );
        }, $data);
    }

}
