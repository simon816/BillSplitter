<?php

class BillModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    private function userIdToHhId($userId) {
        $hhIdEntry = $this->db->selectSingle('hh_id', 'users', array('id' => $userId));
        if (!$hhIdEntry || $hhIdEntry['hh_id'] == null) {
            throw new Exception("User doesn't belong to a household", 100);
        }
        return $hhIdEntry['hh_id'];
    }

    public function addNewBill($userId, $description, $totalPayable, $payableTo, array $split) {
        $hhId = $this->userIdToHhId($userId);
        $propTotal = 0.0;
        foreach ($split as $id => $prop) {
            $propTotal += $prop;
        }
        if ($propTotal !== 1.0) {
            throw new Exception('Percentages don\'t add up to 100%');
        }
        if (!$this->db->insert('bills', array('hh_id' => $hhId, 'total_payable' => $totalPayable, 'payable_to' => $payableTo, 'description' => $description, 'collector' => $userId))) {
            return false;
        }
        $billId = $this->db->lastId();
        $members = $this->db->select('id', 'users', array('hh_id' => $hhId));
        if ($members === false) {
            return false;
        }
        $membersById = array();
        foreach ($members as $member) {
            $membersById[(int) $member['id']] = $member;
        }
        foreach ($split as $userId => $proportion) {
            if (!array_key_exists($userId, $membersById)) {
                throw new Exception("User ID {$userId} does not exist or is not part of the household");
            }
            $qty = $totalPayable * $proportion;
            $this->db->insert('payments', array(
                'user_id' => $userId,
                'bill_id' => $billId,
                'qty_owed' => $qty
            ));
            Notifications::pushNotification($userId, "A new bill has been added, you owe £{$qty}", Notifications::NEW_BILL);
        }
        return true;
    }

    public function getActiveBills($userId) {
        $hhId = $this->userIdToHhId($userId);
        $bills = $this->db->query('SELECT bills.id, total_payable, description, payable_to, collector, users.name FROM bills JOIN users ON users.id = collector WHERE bills.hh_id = ? AND paid_date IS NULL', array($hhId));
        if ($bills === false) {
            return false;
        }
        $db = $this->db;
        return array_map(function ($bill) use (&$db, $userId) {
            $payees = $db->query('SELECT users.name, qty_paid, qty_owed, status FROM payments JOIN users on users.id = payments.user_id WHERE bill_id = ? ORDER BY status ASC, name ASC', array($bill['id']));
            $payees = array_map(function ($payment) {
                return array(
                    'name' => $payment['name'],
                    'quantityPaid' => (float) $payment['qty_paid'],
                    'quantityOwed' => (float) $payment['qty_owed'],
                    'confirmed' => ((int) $payment['status']) === 3
                );
            }, $payees);
            return array(
                'id' => (int) $bill['id'],
                'total' => (float) $bill['total_payable'],
                'description' => $bill['description'],
                'payableTo' => $bill['payable_to'],
                'payees' => $payees,
                'collector' => array('name' => $bill['name'], 'id' => (int) $bill['collector'], 'isCurrentUser' => (int) $bill['collector'] === $userId)
            );
        }, $bills);
    }

    public function confirmPayment($userId, $billId) {
        $hhId = $this->userIdToHhId($userId);
        $statuses = $this->db->select('status, user_id', 'payments', array('bill_id' => $billId));
        if ($statuses === false) {
            return false;
        }
        $userIds = array();
        foreach ($statuses as $userStatus) {
            if ((int) $userStatus['status'] !== 3) {
                throw new Exception("A user has not been confirmed to have paid.");
            }
            $userIds[] = (int) $userStatus['user_id'];
        }
        $affected = $this->db->query('UPDATE bills SET paid_date = CURRENT_TIMESTAMP WHERE hh_id = ? AND id = ? AND collector = ?', array($hhId, $billId, $userId), true);
        if ($affected === false) {
            return false;
        }
        if ($affected < 1) {
            throw new Exception("Either this bill does not exist, or you're not the collector of the money for it");
        }
        $billDesc = $this->db->selectSingle('description', 'bills', array('id' => $billId));
        foreach ($userIds as $participantId) {
            Notifications::pushNotification($participantId, "Payment for the bill '{$billDesc['description']}' has been confirmed", Notifications::BILL_CONFIRMED);
        }
        return true;
    }

    public function getHistoryForUserHousehold($userId) {
        $hhId = $this->userIdToHhId($userId);
        $bills = $this->db->query('SELECT total_payable, description, payable_to, paid_date, users.name FROM bills JOIN users ON users.id = collector WHERE bills.hh_id = ? AND paid_date IS NOT NULL', array($hhId));
        if ($bills === false) {
            return false;
        }
        return array_map(function ($bill) {
            return array(
                'total' => (float) $bill['total_payable'],
                'description' => $bill['description'],
                'payableTo' => $bill['payable_to'],
                'date' => strtotime($bill['paid_date']),
                'collectorName' => $bill['name']
            );
        }, $bills);
    }

}
