<?php

class Notifications {

    const REQ_JOIN_HOUSEHOLD = 1;
    const NEW_BILL = 2;
    const PAYMENT_MADE = 3;
    const PAYMENT_CANCELLED = 4;
    const PAYMENT_CONFIRMED = 5;
    const PAYMENT_DENIED = 6;
    const BILL_CONFIRMED = 7;

    public static function pushNotification($toUser, $message, $type) {
        $db = Database::getInstance();
        $db->insert('notifications', array(
            'receiver_id' => $toUser,
            'message' => $message,
            'type_id' => $type
        ));
        $email = $db->selectSingle('email', 'users', array('id' => $toUser));
        if ($email) {
            // Uncomment to enable
            // sendMail($email['email'], $message);
        }
    }

    private static function sendMail($address, $message, $headers = array()) {
        $headers = array_merge(array(
            'From' => 'notifications@billsplitter.dcs.warwick.ac.uk' // For example
        ), $headers);
        $headerString = '';
        foreach ($headers as $key=>$value) {
            $headerString .= "{$key}: {$value}\r\n";
        }
        $headerString .= "\r\n";
        return mail($address, 'Notification from Bill Splitter', $messaage, $headerString);
    }

    public static function getNotificationsFor($userId, $minId = 0) {
        $db = Database::getInstance();
        $data = $db->select('message, type_id, id', 'notifications', array('receiver_id' => $userId));
        if ($data === false) {
            $data = array();
        }
        $notifications = array();
        array_walk($data, function (&$notification) use ($minId, &$notifications) {
            $notification['type'] = (int) $notification['type_id'];
            unset($notification['type_id']);
            if (($notification['id'] = (int) $notification['id']) > $minId) {
                $notifications[] = $notification;
            }
        });
        return $notifications;
    }

    public static function dismiss($userId, $notId) {
        Database::getInstance()->delete('notifications', array('receiver_id' => $userId, 'id' => $notId));
    }

}
