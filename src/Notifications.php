<?php

class Notifications {

    const REQ_JOIN_HOUSEHOLD = 1;
    const NEW_BILL = 2;
    const PAYMENT_MADE = 3;
    const PAYMENT_CANCELLED = 4;
    const PAYMENT_CONFIRMED = 5;
    const PAYMENT_DENIED = 6;
    const BILL_CONFIRMED = 7;

    const CHAT = -1;

    public static function pushNotification($toUser, $message, $type) {
        $db = Database::getInstance();
        $db->insert('notifications', array(
            'receiver_id' => $toUser,
            'message' => $message,
            'type_id' => $type
        ));
        // TODO chat needs to be redone
        if ($type == self::CHAT) {
            return;
        }
        $userDetails = UserManager::getDetails($toUser);
        if ($userDetails) {
            if (self::prefAllowEmail($userDetails['prefs'], $type)) {
                self::sendMail($userDetails['email'], $message);
            }
        }
    }

    private static function prefAllowEmail($prefs, $type) {
        if (!isset($prefs['notifications'])) {
            return true; // Default enable
        }
        $n = $prefs['notifications'];
        switch ($type) {
            case self::REQ_JOIN_HOUSEHOLD:
                return isset($n['reqJoin']) && $n['reqJoin'];
            case self::NEW_BILL:
                return isset($n['newBill']) && $n['newBill'];
            case self::PAYMENT_MADE:
                return isset($n['pmtMade']) && $n['pmtMade'];
            case self::PAYMENT_CANCELLED:
                return isset($n['pmtCncl']) && $n['pmtCncl'];
            case self::PAYMENT_CONFIRMED:
                return isset($n['pmtAcpt']) && $n['pmtAcpt'];
            case self::PAYMENT_DENIED:
                return isset($n['pmtDeny']) && $n['pmtDeny'];
            case self::BILL_CONFIRMED:
                return isset($n['billCnf']) && $n['billCnf'];
            default:
                return true;
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
        return mail($address, 'Notification from Bill Splitter', $message, $headerString);
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
