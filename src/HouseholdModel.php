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
    households.id, households.name,
    (SELECT count(*) FROM bills WHERE paid_date IS NOT NULL AND hh_id = households.id) AS paid,
    (SELECT count(*) FROM bills WHERE paid_date IS NULL AND hh_id = households.id) AS due
FROM households JOIN users ON users.hh_id = households.id WHERE users.id = ?
SQL
, array($userId));
        if ($data === false || count($data) == 0) {
            return null;
        }
        $members = $this->db->query(
<<<SQL
SELECT
    users.name,
    (SELECT count(*) FROM payments WHERE user_id = users.id AND status = 1) AS payments_made,
    (SELECT count(*) FROM payments WHERE user_id = users.id AND status = 0) AS payments_due
FROM users JOIN households ON households.id = users.hh_id WHERE hh_id = ?
SQL
, array($data[0]['id']));
        if ($members === false) {
            $members = array();
        }
        array_walk($members, function (&$user) {
            $user['paymentsMade'] = (int) $user['payments_made'];
            $user['paymentsDue'] = (int) $user['payments_due'];
            unset($user['payments_made'], $user['payments_due']);
        });
        return array(
            'name' => $data[0]['name'],
            'members' => $members,
            'billsPaid' => (int) $data[0]['paid'],
            'billsDue' => (int) $data[0]['due']
        );
    }

    public function create($name, $ownerId) {
        $existing = $this->db->selectSingle('hh_id', 'users', array('id' => $ownerId));
        if ($existing && $existing['hh_id'] != null) {
            throw new Exception("User already has a membership with a household");
        } elseif ($existing == null) {
            throw new Exception("Unknown user ID");
        }
        if (!$this->db->insert('households', array('name' => $name))) {
            return false;
        }
        $id = $this->db->lastId();
        if (!$this->db->update('users', array('hh_id' => $id), array('id' => $ownerId))) {
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
        $householdId = $this->db->selectSingle('hh_id', 'users', array('id' => $user['id']));
        if (!$householdId) {
            throw new Exception("That user is not registered to a household");
        }
        return $this->db->update('users', array('hh_id' => (int) $householdId['hh_id']), array('id' => $userId));
    }

    public function getHousemates($userId) {
        $householdId = $this->db->selectSingle('hh_id', 'users', array('id' => $userId));
        if (!$householdId || $householdId['hh_id'] == null) {
            return null;
        }
        $members = $this->db->query('SELECT id, name FROM users WHERE hh_id = ?', array($householdId['hh_id']));
        return array_map(function ($member) {
            return array(
                'id' => (int) $member['id'],
                'name' => $member['name']
            );
        }, $members);
    }

}
