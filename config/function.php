<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'dbcon.php';

/* input field validation - must be declared early as other functions depend on it */
if (!function_exists('validated')) {
    function validated($inputData)
    {
        global $conn;
        $validatedData = mysqli_real_escape_string($conn, $inputData);
        return trim($validatedData);
    }
}

// Application-level defaults and settings
if (!defined('COD_DELIVERY_FEE')) {
    // Default cash-on-delivery fee (PHP) - used as fallback when DB setting missing
    define('COD_DELIVERY_FEE', 50.0);
}

/**
 * Get a setting value from the `settings` table. If the table does not exist
 * or the key is not found, return the provided default.
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
if (!function_exists('get_setting')) {
    function get_setting($key, $default = null)
    {
        global $conn;
        $k = validated($key);
        // Check if settings table exists
        $db = mysqli_real_escape_string($conn, mysqli_fetch_row(mysqli_query($conn, 'SELECT DATABASE()'))[0]);
        $check = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='settings'");
        if (!$check) {
            return $default;
        }
        $row = mysqli_fetch_assoc($check);
        if (!$row || $row['cnt'] == 0) {
            return $default;
        }

        $res = mysqli_query($conn, "SELECT value FROM settings WHERE `key` = '$k' LIMIT 1");
        if (!$res || mysqli_num_rows($res) == 0) {
            return $default;
        }
        $r = mysqli_fetch_assoc($res);
        return $r['value'];
    }
}

/**
 * Insert or update a setting in the `settings` table. Creates table if missing.
 * @param string $key
 * @param string $value
 * @return bool
 */
if (!function_exists('set_setting')) {
    function set_setting($key, $value)
    {
        global $conn;
        $k = validated($key);
        $v = validated($value);
        // create settings table if not exists
        $create = "CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(191) PRIMARY KEY,
            `value` TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        mysqli_query($conn, $create);

        // try update, otherwise insert
        $upd = mysqli_query($conn, "INSERT INTO settings (`key`, `value`) VALUES ('$k', '$v') ON DUPLICATE KEY UPDATE `value` = '$v'");
        return (bool) $upd;
    }
}

/**
 * Return the configured delivery fee for COD. Reads from settings table 'cod_delivery_fee'
 * and falls back to COD_DELIVERY_FEE constant.
 * @return float
 */
if (!function_exists('get_delivery_fee')) {
    function get_delivery_fee()
    {
        $val = get_setting('cod_delivery_fee', null);
        if ($val === null || $val === '') {
            return (float) COD_DELIVERY_FEE;
        }
        return (float) $val;
    }
}

/* redirect from 1 page to another page with message */
if (!function_exists('redirect')) {
    function redirect($url, $status, $type = 'success')
    {
        $_SESSION['status'] = $status;
        $_SESSION['status_type'] = $type;
        if (!headers_sent()) {
            header('Location: ' . $url);
            exit(0);
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit(0);
        }
    }
}

/* showing messages */
if (!function_exists('alertMessage')) {
    function alertMessage()
    {
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
                    background: ' .
                $bgColor .
                ';
                    color: white;
                    padding: 15px 20px;
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    transform: translateX(400px);
                    transition: all 0.5s ease;
                    max-width: 400px;
                    font-weight: 500;
                ">
                    <i class="fas fa-' .
                $icon .
                ' me-2"></i>
                    ' .
                $_SESSION['status'] .
                '
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
}

/* insert record */
if (!function_exists('insert')) {
    function insert($tableName, $data)
    {
        global $conn;

        $table = $tableName;

        $columns = implode(', ', array_keys($data));
        $values = implode("', '", array_map('validated', array_values($data)));

        $query = "INSERT INTO $table ($columns) VALUES ('$values')";
        $result = mysqli_query($conn, $query);
        return $result;
    }
}

/* update record */
if (!function_exists('update')) {
    function update($tableName, $id, $data)
    {
        global $conn;

        $table = validated($tableName);

        $updateData = '';
        foreach ($data as $column => $value) {
            $updateData .= "$column='" . validated($value) . "', ";
        }
        $finalUpdateData = substr(trim($updateData), 0, -1);

        $query = "UPDATE $table SET $finalUpdateData WHERE id='$id'";
        $result = mysqli_query($conn, $query);
        return $result;
    }
}

if (!function_exists('getAll')) {
    function getAll($tableName, $status = null)
    {
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
        } elseif (is_string($status)) {
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
}

/* get id */
if (!function_exists('getById')) {
    function getById($tableName, $id)
    {
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
                'data' => $data,
            ];
        } else {
            return [
                'status' => 404,
                'message' => 'No record found',
            ];
        }
    }
}

/* delete record */
if (!function_exists('delete')) {
    function delete($tableName, $id)
    {
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
}

if (!function_exists('checkParamId')) {
    function checkParamId($type)
    {
        if ($_GET[$type]) {
            if ($_GET[$type] != '') {
                return $_GET[$type];
            } else {
                return '<h5>no id given</h5>';
            }
        } else {
            return '<h5>no id found</h5>';
        }
    }
}

if (!function_exists('logoutSession')) {
    function logoutSession()
    {
        unset($_SESSION['loggedIn']);
        unset($_SESSION['loggedInUser']);
    }
}

/**
 * Simple audit logging helper. Creates table if not exists and inserts a log row.
 * @param string $action short action name (e.g. 'update', 'delete')
 * @param string $entity entity type (e.g. 'order', 'customer')
 * @param int $entityId primary id of the entity
 * @param string $details optional JSON or text details
 */
if (!function_exists('audit_log')) {
    function audit_log($action, $entity, $entityId, $details = '')
    {
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
        $entityIdEsc = (int) $entityId;
        $detailsEsc = validated($details);

        $insert = "INSERT INTO audit_logs (action, entity, entity_id, details) VALUES ('$actionEsc', '$entityEsc', '$entityIdEsc', '$detailsEsc')";
        mysqli_query($conn, $insert);
    }
}

/**
 * Check if a column exists in a table
 */
if (!function_exists('columnExists')) {
    function columnExists($tableName, $columnName)
    {
        global $conn;
        $table = validated($tableName);
        $column = validated($columnName);
        $db = mysqli_real_escape_string($conn, mysqli_fetch_row(mysqli_query($conn, 'SELECT DATABASE()'))[0]);
        $sql = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . $db . "' AND TABLE_NAME='" . $table . "' AND COLUMN_NAME='" . $column . "'";
        $res = mysqli_query($conn, $sql);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            return isset($row['cnt']) && $row['cnt'] > 0;
        }
        return false;
    }
}

/**
 * Map logical entity name to actual DB table name.
 */
if (!function_exists('getTableForEntity')) {
    function getTableForEntity($entity)
    {
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
            'admins' => 'admins',
        ];
        return $map[$entity] ?? $entity . 's';
    }
}

/**
 * Check if an entity is archived. Supports two strategies:
 * - If the table has an `is_archived` column, use it.
 * - Otherwise, use a central `archived_items` table (created on demand).
 */
if (!function_exists('isArchived')) {
    function isArchived($entity, $entityId)
    {
        global $conn;
        $entity = validated($entity);
        $entityId = (int) $entityId;

        // Determine table name for the entity
        $table = getTableForEntity($entity);
        if (columnExists($table, 'is_archived')) {
            $q = "SELECT is_archived FROM $table WHERE id = '$entityId' LIMIT 1";
            $r = mysqli_query($conn, $q);
            if ($r && mysqli_num_rows($r) > 0) {
                $row = mysqli_fetch_assoc($r);
                return isset($row['is_archived']) && (int) $row['is_archived'] === 1;
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
}

/**
 * Set or unset archived status for an entity. Returns true on success.
 */
if (!function_exists('setArchived')) {
    function setArchived($entity, $entityId, $archived = true)
    {
        global $conn;
        $entity = validated($entity);
        $entityId = (int) $entityId;

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
}

/**
 * Ensure the archived_items table exists. Safe to call repeatedly.
 */
if (!function_exists('ensureArchivedTableExists')) {
    function ensureArchivedTableExists()
    {
        global $conn;
        $createSql = "CREATE TABLE IF NOT EXISTS archived_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                entity VARCHAR(50) NOT NULL,
                entity_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return mysqli_query($conn, $createSql);
    }
}

/**
 * Ensure secret question/answer columns exist for a given table (customers/admins)
 */
if (!function_exists('ensureSecretColumns')) {
    function ensureSecretColumns($tableName)
    {
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
}
?>