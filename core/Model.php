<?php
// core/Model.php
abstract class Model
{
    /** @var PDO */
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection(); // assuming it returns a PDO connection
    }

    // Insert data into table
    public function insert($table, $data, $returnId = false)
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ':' . $f, $fields);

        $sql = "INSERT INTO `$table` (`" . implode('`,`', $fields) . "`) 
                VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);

        $result = $stmt->execute($data);
        
        if ($returnId && $result) {
            return $this->pdo->lastInsertId();
        }
        
        return $result;
    }
    
    // Insert data and return the inserted ID
    public function insertWithId($table, $data)
    {
        return $this->insert($table, $data, true);
    }

    // Select all data
    public function all_data($table, $where = null, $count = false)
    {
        $sql = "SELECT * FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        if ($count) {
            return $stmt->rowCount();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete by ID
    public function del_data($table, $id)
    {
        $sql = "DELETE FROM `$table` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // Delete by custom condition
    public function del_custom($table, $condition)
    {
        $sql = "DELETE FROM `$table` WHERE $condition";
        return $this->pdo->exec($sql);
    }

    // Update record
    public function update_data($table, $data, $where)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $fields[] = "`$key` = NULL";
            } else {
                $fields[] = "`$key` = :$key";
            }
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        
        // Filter out NULL values from the data array since they're handled in the SQL
        $dataWithoutNulls = array_filter($data, fn($value) => $value !== null);
        
        return $stmt->execute($dataWithoutNulls);
    }

    // Get single row
    public function single_data($table, $where = null)
    {
        $sql = "SELECT * FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Run custom query
    public function qry($query, $fetch = false, $all = false)
    {
        $stmt = $this->pdo->query($query);

        if ($fetch) {
            if ($all) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return $stmt;
    }
}
