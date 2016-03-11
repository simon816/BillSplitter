<?php

class Database {

    private static $instance;

    public static final function getInstance() {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private $con;

    private function __construct() {
        $this->con = new PDO('sqlite:' . DB_FILE);
    }

    public function asPDO() {
        return $this->con;
    }

    private function failIfFalse($stmt) {
        if ($stmt === false) {
            throw new Exception($this->con->errorInfo()[2]);
        }
    }

    // WARNING! No validation is done here. Validation will be added in the future.
    public function insert($tableName, array $data) {
        $keys = array_keys($data);
        $querys = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->con->prepare($q="INSERT INTO {$tableName} (" . implode(', ', $keys) . ") VALUES ({$querys})");
        $this->failIfFalse($stmt);
        foreach ($keys as $i => $key) {
            $stmt->bindValue($i + 1, $data[$key]);
        }
        return $stmt->execute();
    }

    // WARNING! No validation is done here. Validation will be added in the future.
    public function select($columns, $table, array $where = null) {
        $sql = "SELECT {$columns} FROM {$table}";
        $stmt = $this->generateWhereStatement($sql, $where);
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectSingle($columns, $table, array $where = null) {
        $result = $this->select($columns, $table, $where);
        if ($result === false) {
            return false;
        }
        if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }

    // WARNING! No validation is done here. Validation will be added in the future.
    public function update($table, array $newValues, array $where = null, $affectedRows = false) {
        $keys = implode(' = ?, ', array_keys($newValues)) . ' = ?';
        $values = array_values($newValues);
        $stmt = $this->generateWhereStatement("UPDATE {$table} SET {$keys}", $where, count($newValues));
        foreach ($values as $i => $value) {
            $stmt->bindValue($i + 1, $value);
        }
        $success = $stmt->execute();
        return !$success ? false : ($affectedRows ? $stmt->rowCount() : true);
    }

    // WARNING! No validation is done here. Validation will be added in the future.
    public function delete($table, array $where) {
        $sql = "DELETE FROM {$table}";
        $stmt = $this->generateWhereStatement($sql, $where);
        return $stmt->execute();
    }

    private function generateWhereStatement($sql, array $where = null, $whereOffset = 0) {
        $whereKeys = array();
        if ($where != null && count($where) > 0) {
            $whereKeys = array_keys($where);
            $sql .= ' WHERE ' . implode(' = ? AND ', $whereKeys) . ' = ?';
        }
        $stmt = $this->con->prepare($sql);
        $this->failIfFalse($stmt);
        foreach ($whereKeys as $i => $key) {
            $stmt->bindValue($whereOffset + $i + 1, $where[$key]);
        }
        return $stmt;
    }

    // WARNING! This method is unsafe as no validation is applied to the query
    public function query($query, array $data = array()) {
        $stmt = $this->con->prepare($query);
        $this->failIfFalse($stmt);
        foreach ($data as $i => $d) {
            $stmt->bindValue($i + 1, $d);
        }
        if (!$stmt->execute()) {
            return false;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // WARNING! No validation is done here. Validation will be added in the future.
    public function count($table, array $where) {
        $stmt = $this->generateWhereStatement("SELECT count(*) FROM {$table}", $where);
        if (!$stmt->execute()) {
            return false;
        }
        return (int) $stmt->fetchColumn();
    }

    public function lastId($colName = 'id') {
        return (int) $this->con->lastInsertId($colName);
    }

}
