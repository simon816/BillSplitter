<?php

class PaymentModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getPendingForUser($userId) {
        $memCount = $this->db->count('household_member', array('user_id' => $userId));
        if (!$memCount) {
            return null;
        }
        $data = $this->db->query('SELECT id, total_payable, description, payable_to, qty_paid, proportion FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND payment_received = 0', array($userId));
        return array_map(function ($pending) {
            return array(
                'id' => $pending['id'],
                'total' => $pending['total_payable'],
                'description' => $pending['description'],
                'payableTo' => $pending['payable_to'],
                'quantityPaid' => $pending['qty_paid'],
                'proportion' => $pending['proportion']
            );
        }, $data);
    }

    public function makePayment($userId, $billId) {
        // TODO Improve
        $bill = $this->db->selectSingle('total_payable', 'bills', array('id' => $billId));
        $payment = $this->db->selectSingle('proportion, qty_paid', 'payments', array('user_id' => $userId, 'bill_id' => $billId));
        $affected = $this->db->update('payments',array(
            'payment_received' => 1,
            'qty_paid' => (float) $bill['total_payable'] * (float) $payment['proportion'] - (float) $payment['qty_paid']
        ), array('user_id' => $userId, 'bill_id' => $billId), true);
        if ($affected === false) {
            return false;
        }
        if ($affected == 0) {
            throw new Exception("No bill exists with that ID under your membership");
        }
        return true;
    }

    public function getHistoryForUser($userId) {
        $memCount = $this->db->count('household_member', array('user_id' => $userId));
        if (!$memCount) {
            return null;
        }
        $data = $this->db->query('SELECT total_payable, description, payable_to, qty_paid FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND payment_received = 1', array($userId));
        return array_map(function ($pending) {
            return array(
                'total' => $pending['total_payable'],
                'description' => $pending['description'],
                'payableTo' => $pending['payable_to'],
                'quantityPaid' => $pending['qty_paid']
            );
        }, $data);
    }

}
