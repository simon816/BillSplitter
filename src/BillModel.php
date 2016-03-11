<?php

class BillModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    private function userIdToHhId($userId) {
        $hhIdEntry = $this->db->selectSingle('hh_id', 'household_member', array('user_id' => $userId));
        if (!$hhIdEntry) {
            throw new Exception("User doesn't belong to a household", 100);
        }
        return $hhIdEntry['hh_id'];
    }

    public function addNewBill($userId, $description, $totalPayable, $payableTo) {
        $hhId = $this->userIdToHhId($userId);
        if (!$this->db->insert('bills', array('total_payable' => $totalPayable, 'payable_to' => $payableTo, 'description' => $description))) {
            return false;
        }
        $billId = $this->db->lastId();
        if (!$this->db->insert('household_bill', array('hh_id' => $hhId, 'bill_id' => $billId))) {
            return false;
        }
        $members = $this->db->select('user_id', 'household_member', array('hh_id' => $hhId));
        if ($members === false) {
            return false;
        }
        foreach ($members as $member) {
            $this->db->insert('payments', array(
                'user_id' => $member['user_id'],
                'bill_id' => $billId,
                'qty_paid' => 0,
                'payment_received' => 0
            ));
        }
        return true;
    }

    public function getActiveBills($userId) {
        $hhId = $this->userIdToHhId($userId);
        $bills = $this->db->query('SELECT * FROM bills JOIN household_bill ON bills.id = household_bill.bill_id WHERE hh_id = ? AND paid = 0', array($hhId));
        if ($bills === false) {
            return false;
        }
        $db = $this->db;
        return array_map(function ($bill) use (&$db) {
            $payees = $db->query('SELECT users.name, qty_paid, proportion, payment_received FROM payments JOIN users on users.id = payments.user_id WHERE bill_id = ? ORDER BY payment_received ASC, name ASC', array($bill['id']));
            $payees = array_map(function ($payment) {
                return array(
                    'name' => $payment['name'],
                    'quantityPaid' => (float) $payment['qty_paid'],
                    'proportion' => (float) $payment['proportion'],
                    'received' => (int) $payment['payment_received'] != 0
                );
            }, $payees);
            return array(
                'total' => (float) $bill['total_payable'],
                'description' => $bill['description'],
                'payableTo' => $bill['payable_to'],
                'payees' => $payees
            );
        }, $bills);
    }

}
