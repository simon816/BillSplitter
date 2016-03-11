<?php

class HouseholdModel {

    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getDetails($userId) {
        $data = $this->db->query(
<<<SQL
SELECT
    id, name,
    (SELECT count(*) FROM bills JOIN household_bill ON hh_id = id WHERE paid = 1) AS paid,
    (SELECT count(*) FROM bills JOIN household_bill ON hh_id = id WHERE paid = 0) AS due
FROM households JOIN household_member ON household_member.hh_id = households.id WHERE user_id = ?
SQL
, array($userId));
        if ($data === false || count($data) == 0) {
            return null;
        }
        $members = $this->db->query(
<<<SQL
SELECT
    name, default_proportion,
    (SELECT count(*) FROM payments WHERE user_id = id AND payment_received = 1) AS payments_made,
    (SELECT count(*) FROM payments WHERE user_id = id AND payment_received = 0) AS payments_due
FROM users JOIN household_member ON user_id = id WHERE hh_id = ?
SQL
, array($data[0]['id']));
        if ($members === false) {
            $members = array();
        }
        array_walk($members, function (&$user) {
            $user['default_proportion'] = (float) $user['default_proportion'];
            $user['payments_made'] = (int) $user['payments_made'];
            $user['payments_due'] = (int) $user['payments_due'];
        });
        return array(
            'name' => $data[0]['name'],
            'members' => $members,
            'billsPaid' => (int) $data[0]['paid'],
            'billsDue' => (int) $data[0]['due']
        );
    }

    public function create($name, $ownerId) {
        $existing = $this->db->count('household_member', array('user_id' => $ownerId));
        if ($existing !== false && $existing > 0) {
            throw new Exception("User already has a membership with a household");
        }
        if (!$this->db->insert('households', array('name' => $name))) {
            return false;
        }
        $id = $this->db->lastId();
        if (!$this->db->insert('household_member', array('user_id' => $ownerId, 'hh_id' => $id))) {
            return false;
        }
        return $id;
    }

    public function requestJoin($userId, $groupEmail) {
        $user = $this->db->selectSingle('id', 'users', array('email' => $groupEmail));
        if ($user === false || $user === null) {
            throw new Exception("No user exists with that email address");
        }
        if ($user['id'] == $userId) {
            throw new Exception("That's your email!");
        }
        $householdId = $this->db->selectSingle('hh_id', 'household_member', array('user_id' => $user['id']));
        if ($householdId === false || $householdId === null) {
            throw new Exception("That user is not registered to a household");
        }
        return $this->db->insert('household_member', array('user_id' => $userId, 'hh_id' => $householdId['hh_id']));
    }

}
