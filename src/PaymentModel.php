<?php
/*
The STATUS flag:

0 = no payment
1 = request sent
2 = Rejected
3 = confirmed
*/

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
        $yours = $this->db->query('SELECT id, total_payable, description, payable_to, qty_paid, qty_owed, status FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND status <> 3', array($userId));
        $others = $this->db->query('SELECT name AS user_name, users.id AS user_id, bill_id, description, qty_paid FROM payments JOIN users ON payments.user_id = users.id JOIN bills ON payments.bill_id = bills.id WHERE status = 1 AND collector = ?', array($userId));
        $yours = array_map(function (&$pending) {
            return array(
                'id' => (int) $pending['id'],
                'total' => (float) $pending['total_payable'],
                'description' => $pending['description'],
                'payableTo' => $pending['payable_to'],
                'quantityPaid' => (float) $pending['qty_paid'],
                'quantityOwed' => (float) $pending['qty_owed'],
                'status' => (int) $pending['status']
            );
        }, $yours);
        $others = array_map(function (&$payment) {
            return array(
                'user' => array('name' => $payment['user_name'], 'id' => (int) $payment['user_id']),
                'amount' => (float) $payment['qty_paid'],
                'billId' => (int) $payment['bill_id'],
                'description' => $payment['description']
            );
        }, $others);
        return array(
            'yours' => $yours,
            'others' => $others
        );
    }

    private function updatePayment($isCancelled, $status, $userId, $billId, Closure $getMessage, $notificationType) {
        $affected = $this->db->query('UPDATE payments SET qty_paid = ' . ($isCancelled ? '0' : 'qty_owed') . ', paid_date = ' . ($isCancelled ? 'NULL' : 'CURRENT_TIMESTAMP') . ', status = ? WHERE user_id = ? AND bill_id = ?', array($status, $userId, $billId), true);
        if ($affected === false) {
            return false;
        }
        if ($affected == 0) {
            throw new Exception("No bill exists with that ID under your membership");
        }
        $result = $this->db->query('SELECT collector, (SELECT name FROM users WHERE users.id = ?) as from_user FROM bills WHERE id = ?', array($userId, $billId));
        if ($result !== false) {
            Notifications::pushNotification((int) $result[0]['collector'], $getMessage($result[0]['from_user']), $notificationType);
        }
        return true;
    }

    public function makePayment($userId, $billId) {
        return $this->updatePayment(false, 1, $userId, $billId, function ($fromUser) {
            $date = date('Y-m-d H:i:s');
            return "{$fromUser} has made a payment on {$date}. Please confirm that you have received this payment.";
        }, Notifications::PAYMENT_MADE);
    }

    public function cancelPayment($userId, $billId) {
        return $this->updatePayment(true, 0, $userId, $billId, function ($fromUser) {
            $date = date('Y-m-d H:i:s');
            return "{$fromUser} has cancelled their payment on {$date}";
        }, Notifications::PAYMENT_CANCELLED);
    }

    private function updatePaymentResponse($isCancelled, $status, $collectorUserId, $fromUserId, $billId, $notificationMessage, $notificationType) {
        $count = $this->db->count('bills', array('id' => $billId, 'collector' => $collectorUserId));
        if (!$count) {
            throw new Exception("You are not the collector of this bill");
        }
        $affected = $this->db->query('UPDATE payments SET qty_paid = ' . ($isCancelled ? '0' : 'qty_owed') . ', paid_date = ' . ($isCancelled ? 'NULL' : 'paid_date') . ', status = ? WHERE user_id = ? AND bill_id = ?', array($status, $fromUserId, $billId), true);
        if ($affected === false) {
            return false;
        }
        if ($affected == 0) {
            throw new Exception("No bill exists with that ID under your membership");
        }
        Notifications::pushNotification($fromUserId, $notificationMessage, $notificationType);
        return true;
    }

    public function confirmPayment($collectorUserId, $fromUserId, $billId) {
        $message = "Your payment has been confirmed";
        return $this->updatePaymentResponse(false, 3, $collectorUserId, $fromUserId, $billId, $message, Notifications::PAYMENT_CONFIRMED);
    }

    public function denyPayment($collectorUserId, $fromUserId, $billId) {
        $message = "Your payment has been denied";
        return $this->updatePaymentResponse(true, 2, $collectorUserId, $fromUserId, $billId, $message, Notifications::PAYMENT_DENIED);
    }

    public function getHistoryForUser($userId) {
        $household = $this->db->selectSingle('hh_id', 'users', array('id' => $userId));
        if (!$household || $household['hh_id'] == null) {
            return null; // No household
        }
        $data = $this->db->query('SELECT total_payable, description, payable_to, qty_paid, payments.paid_date FROM bills JOIN payments ON payments.bill_id = bills.id WHERE user_id = ? AND status = 3', array($userId));
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
