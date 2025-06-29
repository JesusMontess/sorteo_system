<?php
class Database {
    protected $connection;
    
    public function __construct() {
        $this->connection = DatabaseConfig::getConnection();
    }
    
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }
    
    protected function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    protected function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
