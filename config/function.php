<?php

    session_start();
    
    require ('dbcon.php');

    /* input field validation */
    function validated($inputData) {
        global $conn;
        $validatedData = mysqli_real_escape_string($conn, $inputData);
        return trim($validatedData);
    }

    /* redirect from 1 page to another page with message */
    function redirect($url, $status, $type = 'success') {
        $_SESSION['status'] = $status;
        $_SESSION['status_type'] = $type;
        header('Location: ' .$url);
        exit(0);
    }

    /* showing messages */
    function alertMessage() {
        if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
            $type = $_SESSION['status_type'] ?? 'success';
            $icon = $type == 'success' ? 'check-circle' : ($type == 'error' ? 'exclamation-triangle' : 'info-circle');
            $bgColor = $type == 'success' ? '#28a745' : ($type == 'error' ? '#dc3545' : '#17a2b8');
            
            echo '
            <div id="floatingAlert" class="floating-alert" style="
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                background: '.$bgColor.';
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                transform: translateX(400px);
                transition: all 0.5s ease;
                max-width: 400px;
                font-weight: 500;
            ">
                <i class="fas fa-'.$icon.' me-2"></i>
                '.$_SESSION['status'].'
                <button onclick="closeAlert()" style="
                    background: none;
                    border: none;
                    color: white;
                    float: right;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: 10px;
                ">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById("floatingAlert").style.transform = "translateX(0)";
                }, 100);
                setTimeout(() => {
                    closeAlert();
                }, 5000);
                function closeAlert() {
                    const alert = document.getElementById("floatingAlert");
                    if(alert) {
                        alert.style.transform = "translateX(400px)";
                        setTimeout(() => alert.remove(), 500);
                    }
                }
            </script>';

            unset($_SESSION['status']);
            unset($_SESSION['status_type']);
        }
    }

    /* insert record */
    function insert($tableName, $data) {
        global $conn;

        $table = $tableName;

        $columns = implode(", ", array_keys($data));
        $values = implode("', '", array_map('validated', array_values($data)));

        $query = "INSERT INTO $table ($columns) VALUES ('$values')";
        $result = mysqli_query($conn, $query);
        return $result;

    }

    /* update record */
    function update($tableName, $id, $data) {
        global $conn;

        $table = validated($tableName);
        /* $idKey = key($id); */

        $updateData = '';
        foreach ($data as $column => $value) {
            $updateData .= "$column='" .validated($value). "', ";
        }
        $finalUpdateData = substr(trim($updateData), 0, -1);

        $query = "UPDATE $table SET $finalUpdateData WHERE id='$id'";
        $result = mysqli_query($conn, $query);
        return $result;
    }
    function getAll($tableName, $status = null) {
        if (!isset($tableName) || !is_string($tableName)) {
            throw new InvalidArgumentException('Table name must be a string');
        }

        global $conn;

        if (!$conn) {
            throw new RuntimeException('Database connection is not established');
        }

        $table = validated($tableName);

        if (is_null($status)) {
            $query = "SELECT * FROM $table";
        } else if (is_string($status)) {
            $status = validated($status);
            $query = "SELECT * FROM $table WHERE status='$status'";
        } else {
            throw new InvalidArgumentException('Status must be a string or null');
        }

        $result = mysqli_query($conn, $query);

        if (!$result) {
            throw new RuntimeException(mysqli_error($conn));
        }

        return $result;

    }

    /* get id */
    function getById($tableName, $id) {
        
        if (!isset($tableName) || !is_string($tableName)) {
            throw new InvalidArgumentException('Table name must be a string');
        }

        if (!isset($id) || !is_numeric($id)) {
            throw new InvalidArgumentException('ID must be a numeric value');
        }

        global $conn;

        if (!$conn) {
            throw new RuntimeException('Database connection is not established');
        }

        $table = validated($tableName);
        $id = validated($id);

        $query = "SELECT * FROM $table WHERE id='$id' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            throw new RuntimeException(mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            return [
                'status' => 200,
                'data' => $data
            ];
        } else {
            return [
                'status' => 404,
                'message' => 'No record found'
            ];
        }

    }

    /* delete record */
    function delete($tableName, $id) {
        if (!isset($tableName) || !is_string($tableName)) {
            throw new InvalidArgumentException('Table name must be a string');
        }

        if (!isset($id) || !is_numeric($id)) {
            throw new InvalidArgumentException('ID must be a numeric value');
        }

        global $conn;

        if (!$conn) {
            throw new RuntimeException('Database connection is not established');
        }

        $table = validated($tableName);
        $id = validated($id);

        $query = "DELETE FROM $table WHERE id='$id'";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            throw new RuntimeException(mysqli_error($conn));
        }

        return $result;
    }

    function checkParamId($type) {
        if($_GET[$type]) {
            if ($_GET[$type] != '') {
            return $_GET[$type];

            } else {
                return '<h5>no id given</h5>';
            }
        } else {
            return '<h5>no id found</h5>';
        }
    }
    function logoutSession() {
        
        unset($_SESSION['loggedIn']);
        unset($_SESSION['loggedInUser']);
        
    }

    /**
     * Simple audit logging helper. Creates table if not exists and inserts a log row.
     * @param string $action short action name (e.g. 'update', 'delete')
     * @param string $entity entity type (e.g. 'order', 'customer')
     * @param int $entityId primary id of the entity
     * @param string $details optional JSON or text details
     */
    function audit_log($action, $entity, $entityId, $details = '') {
        global $conn;

        // Create table if not exists (id, action, entity, entity_id, details, created_at)
        $createSql = "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(50) NOT NULL,
            entity VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $createSql);

        $actionEsc = validated($action);
        $entityEsc = validated($entity);
        $entityIdEsc = (int)$entityId;
        $detailsEsc = validated($details);

        $insert = "INSERT INTO audit_logs (action, entity, entity_id, details) VALUES ('$actionEsc', '$entityEsc', '$entityIdEsc', '$detailsEsc')";
        mysqli_query($conn, $insert);
    }

    /**
     * Check if a column exists in a table
     */
    function columnExists($tableName, $columnName) {
        global $conn;
        $table = validated($tableName);
        $column = validated($columnName);
        $db = mysqli_real_escape_string($conn, mysqli_fetch_row(mysqli_query($conn, "SELECT DATABASE()"))[0]);
        $sql = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $db . "' AND TABLE_NAME='" . $table . "' AND COLUMN_NAME='" . $column . "'";
        $res = mysqli_query($conn, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            return isset($row['cnt']) && $row['cnt'] > 0;
        }
        return false;
    }

    /**
     * Map logical entity name to actual DB table name.
     */
    function getTableForEntity($entity) {
        $entity = strtolower(trim($entity));
        $map = [
            'category' => 'categories',
            'categories' => 'categories',
            'product' => 'products',
            'products' => 'products',
            'customer' => 'customers',
            'customers' => 'customers',
            'order' => 'orders',
            'orders' => 'orders',
            'admin' => 'admins',
            'admins' => 'admins'
        ];
        return $map[$entity] ?? ($entity . 's');
    }

    /**
     * Check if an entity is archived. Supports two strategies:
     * - If the table has an `is_archived` column, use it.
     * - Otherwise, use a central `archived_items` table (created on demand).
     */
    function isArchived($entity, $entityId) {
        global $conn;
        $entity = validated($entity);
        $entityId = (int)$entityId;

        // Determine table name for the entity
        $table = getTableForEntity($entity);
        if (columnExists($table, 'is_archived')) {
            $q = "SELECT is_archived FROM $table WHERE id = '$entityId' LIMIT 1";
            $r = mysqli_query($conn, $q);
            if ($r && mysqli_num_rows($r) > 0) {
                $row = mysqli_fetch_assoc($r);
                return isset($row['is_archived']) && (int)$row['is_archived'] === 1;
            }
            return false;
        }

        // fallback to archived_items table
        $createSql = "CREATE TABLE IF NOT EXISTS archived_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            entity VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $createSql);

        $entEsc = validated($entity);
        $q = "SELECT COUNT(*) as cnt FROM archived_items WHERE entity = '$entEsc' AND entity_id = '$entityId'";
        $r = mysqli_query($conn, $q);
        if ($r) {
            $row = mysqli_fetch_assoc($r);
            return isset($row['cnt']) && $row['cnt'] > 0;
        }
        return false;
    }

    /**
     * Set or unset archived status for an entity. Returns true on success.
     */
    function setArchived($entity, $entityId, $archived = true) {
        global $conn;
        $entity = validated($entity);
        $entityId = (int)$entityId;

        $table = getTableForEntity($entity);
        if (columnExists($table, 'is_archived')) {
            $val = $archived ? 1 : 0;
            $query = "UPDATE $table SET is_archived = '$val' WHERE id = '$entityId'";
            return mysqli_query($conn, $query);
        }

        // archived_items fallback
        $createSql = "CREATE TABLE IF NOT EXISTS archived_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            entity VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $createSql);

        $entEsc = validated($entity);
        if ($archived) {
            $insert = "INSERT INTO archived_items (entity, entity_id) VALUES ('$entEsc', '$entityId')";
            return mysqli_query($conn, $insert);
        } else {
            $delete = "DELETE FROM archived_items WHERE entity = '$entEsc' AND entity_id = '$entityId'";
            return mysqli_query($conn, $delete);
        }
    }

    /**
     * Ensure the archived_items table exists. Safe to call repeatedly.
     */
    function ensureArchivedTableExists() {
        global $conn;
        $createSql = "CREATE TABLE IF NOT EXISTS archived_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            entity VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return mysqli_query($conn, $createSql);
    }

    /**
     * Ensure secret question/answer columns exist for a given table (customers/admins)
     */
    function ensureSecretColumns($tableName) {
        global $conn;
        $table = validated($tableName);
        // Check and add secret_question
        $colCheck = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='secret_question'");
        $row = $colCheck ? mysqli_fetch_assoc($colCheck) : null;
        if (!$row || $row['cnt'] == 0) {
            mysqli_query($conn, "ALTER TABLE $table ADD COLUMN secret_question TEXT NULL");
        }

        // Check and add secret_answer_hash
        $colCheck2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table' AND COLUMN_NAME='secret_answer_hash'");
        $row2 = $colCheck2 ? mysqli_fetch_assoc($colCheck2) : null;
        if (!$row2 || $row2['cnt'] == 0) {
            mysqli_query($conn, "ALTER TABLE $table ADD COLUMN secret_answer_hash VARCHAR(255) NULL");
        }
    }
?>