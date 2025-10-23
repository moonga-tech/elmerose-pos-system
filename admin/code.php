<?php 

include ('../config/function.php');

if(isset($_POST['saveAdmin'])){
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    $password = validated($_POST['password']);
    $phone = validated($_POST['phone']);
    $is_ban = isset($_POST['is_ban']) ? 1 : 0;

    if($name != '' && $email != '' && $password != ''){
        $emailCheck = mysqli_query($conn, "SELECT * FROM admins WHERE email='$email'");
        if($emailCheck) {
            if(mysqli_num_rows($emailCheck) > 0){
                redirect("admin-create.php", "Email already exists", "error");
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // ensure secret columns exist
                ensureSecretColumns('admins');
                $secret_question = validated($_POST['secret_question'] ?? '');
                $secret_answer_hash = password_hash(validated($_POST['secret_answer'] ?? ''), PASSWORD_BCRYPT);

                $insertQuery = mysqli_query($conn, "INSERT INTO admins (name, email, password, phone, is_ban, secret_question, secret_answer_hash) VALUES ('$name', '$email', '$hashedPassword', '$phone', '$is_ban', '".mysqli_real_escape_string($conn, $secret_question)."', '".mysqli_real_escape_string($conn, $secret_answer_hash)."')");
                if($insertQuery){
                    redirect("admins.php", "Admin added successfully", "success");
                } else {
                    redirect("admin-create.php", "Failed to add admin", "error");
                }
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'phone' => $phone,
                'is_ban' => $is_ban,
                'secret_question' => $secret_question,
                'secret_answer_hash' => $secret_answer_hash,
            ];

            $result = insert("admins", $data);

                if($result) {
                    redirect("admins.php", "Admin added successfully", "success");
                } else {
                    redirect("admin-create.php", "Failed to add admin", "error");
                }
            

        } else {
            redirect("admin-create.php", "Database query failed", "error");
        }
    } else {
        redirect("admin-create.php", "All fields are required", "error");
    }
}

if(isset($_POST['updateAdmin'])){
    $adminId = validated($_POST['adminId']);
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    /* $password = validated($_POST['password']); */
    $phone = validated($_POST['phone']);
    $is_ban = isset($_POST['is_ban']) ? 1 : 0;

    if($adminId != '' && $name != '' && $email != ''){
        $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $adminCheck = $stmt->get_result();
        if($adminCheck) {
            if(mysqli_num_rows($adminCheck) > 0){
                /* $hashedPassword = password_hash($password, PASSWORD_BCRYPT); */

                $updateStmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, is_ban = ? WHERE id = ?");
                $updateStmt->bind_param("sssii", $name, $email, $phone, $is_ban, $adminId);
                $updateQuery = $updateStmt->execute();
                if($updateQuery){
                    redirect("admins.php", "Admin updated successfully", "success");
                } else {
                    redirect("admin-edit.php?id=$adminId", "Failed to update admin", "error");
                }
            } else {
                redirect("admins.php", "No such admin found", "error");
            }
        } else {
            redirect("admin-edit.php?id=$adminId", "Database query failed", "error");
        }
    } else {
        redirect("admin-edit.php?id=$adminId", "All fields are required", "error");
    }
}

if(isset($_POST['saveCategory'])){
    $name = validated($_POST['name']);
    $description = validated($_POST['description']);
    $status = isset($_POST['status']) == true ? 1 : 0;

    if($name != '' && $description != ''){
        $data = [
            'name' => $name,
            'description' => $description,
            'status' => $status,
        ];

        $result = insert("categories", $data);

        if($result) {
            redirect("categories.php", "Category added successfully", "success");
        } else {
            redirect("category-create.php", "Failed to add category", "error");
        }
    } else {
        redirect("category-create.php", "All fields are required", "error");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateCategory'])) {
    $categoryId = $_POST['categoryId'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Prepare the SQL query using prepared statements
    $query = "UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $name, $description, $status, $categoryId);

    // Execute the query
    if ($stmt->execute()) {
        // Update successful
        redirect("categories.php", "Category updated successfully", "success");
    } else {
        // Update failed
        redirect("categories-edit.php?id=$categoryId", "Error updating category", "error");
    }
}

if(isset($_POST['saveProduct'])){
    $category_id = validated($_POST['category_id']);
    $name = validated($_POST['name']);
    $description = validated($_POST['description']);
    $price = validated($_POST['price']);
    $quantity = validated($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1 : 0;
    $has_expiry = isset($_POST['has_expiry']) ? 1 : 0;
    $expiry_date = $has_expiry && !empty($_POST['expiry_date']) ? validated($_POST['expiry_date']) : null;
    $expiry_alert_days = $has_expiry ? validated($_POST['expiry_alert_days']) : 30;
    $color_id = !empty($_POST['color_id']) ? validated($_POST['color_id']) : null;
    $variant_size = validated($_POST['variant_size']);
    $variant_unit = validated($_POST['variant_unit']);

    if($_FILES['image']['size'] > 0) {
        $path = "../assets/uploads/products";
        $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $filename = time().'.'.$image_ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $path."/".$filename);
        $final_image = "assets/uploads/products/".$filename;
    } else {
        $final_image = "";
    }

    $data = [
        'category_id' => $category_id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'quantity' => $quantity,
        'image' => $final_image,
        'status' => $status,
        'has_expiry' => $has_expiry,
        'expiry_date' => $expiry_date,
        'expiry_alert_days' => $expiry_alert_days,
        'color_id' => $color_id,
    ];

    $result = insert("products", $data);

    if($result) {
        $productId = mysqli_insert_id($conn);
        
        // Create product variant if size/unit specified
        if(!empty($variant_size) && !empty($variant_unit)) {
            $volume_liters = null;
            $weight_kg = null;
            
            // Calculate volume or weight based on unit
            if(in_array($variant_unit, ['ml', 'l'])) {
                $volume_liters = $variant_unit == 'ml' ? $variant_size / 1000 : $variant_size;
            } elseif(in_array($variant_unit, ['g', 'kg'])) {
                $weight_kg = $variant_unit == 'g' ? $variant_size / 1000 : $variant_size;
            }
            
            $variantData = [
                'product_id' => $productId,
                'size' => $variant_size,
                'unit' => $variant_unit,
                'volume_liters' => $volume_liters,
                'weight_kg' => $weight_kg,
                'stock_quantity' => $quantity
            ];
            
            insert("product_variants", $variantData);
        }
        
        redirect("products.php", "Product added successfully", "success");
    } else {
        redirect("products-create.php", "Failed to add product", "error");
    }
}

if(isset($_POST['updateProduct'])){
    $productId = validated($_POST['productId']);
    $productData = getById("products", $productId);
    if(!$productData){
        redirect("products.php", "Product not found", "error");
        return;
    }

    $category_id = validated($_POST['category_id']);
    $name = validated($_POST['name']);
    $description = validated($_POST['description']);
    $price = validated($_POST['price']);
    $quantity = validated($_POST['quantity']);
    $status = isset($_POST['status']) == true ? 1 : 0;

    if(empty($_FILES['image']['name'])){
        $final_image = $productData['data']['image'];
    } else {
        $path = "../assets/uploads/products";
        $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $filename = time().'.'.$image_ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $path."/".$filename);
        $final_image = "assets/uploads/products/".$filename;

        $delete_old_image = "../".$productData['data']['image'];
        if(file_exists($delete_old_image)){
            unlink($delete_old_image);
        }
    }

    $has_expiry = isset($_POST['has_expiry']) ? 1 : 0;
    $expiry_date = $has_expiry && !empty($_POST['expiry_date']) ? validated($_POST['expiry_date']) : null;
    $expiry_alert_days = $has_expiry ? validated($_POST['expiry_alert_days']) : 30;

    $data = [
        'category_id' => $category_id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'quantity' => $quantity,
        'image' => $final_image,
        'status' => $status,
        'has_expiry' => $has_expiry,
        'expiry_date' => $expiry_date,
        'expiry_alert_days' => $expiry_alert_days,
    ];

    $result = update("products", $productId, $data);

    if($result) {
        redirect("products-edit.php?id=$productId", "Product updated successfully", "success");
    } else {
        redirect("products-edit.php?id=$productId", "Failed to update product", "error");
    }
}

if(isset($_POST['order_status'])){
    $order_id = validated($_POST['order_id']);
    $order_status = validated($_POST['order_status']);

    $query = "UPDATE orders SET status = '$order_status' WHERE id = '$order_id'";
    $result = mysqli_query($conn, $query);

    if($result){
        // Audit log
        audit_log('status_change', 'order', $order_id, json_encode(['new_status' => $order_status, 'by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect("orders.php", "Order status updated successfully", "success");
    } else {
        redirect("orders.php", "Failed to update order status", "error");
    }
}

if (isset($_POST['updateOrder'])) {
    $orderId = validated($_POST['orderId']);
    $status = validated($_POST['status']);
    $totalAmount = validated($_POST['total_amount']);

    if ($orderId == '' || $status == '' || $totalAmount == '') {
        redirect("order-edit.php?id=$orderId", "All fields are required", "error");
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ?, total_amount = ? WHERE id = ?");
    $stmt->bind_param("sdi", $status, $totalAmount, $orderId);
    $updateQuery = $stmt->execute();

    if ($updateQuery) {
        audit_log('update', 'order', $orderId, json_encode(['status' => $status, 'total' => $totalAmount, 'by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect("orders.php", "Order updated successfully", "success");
    } else {
        redirect("order-edit.php?id=$orderId", "Failed to update order: " . $stmt->error, "error");
    }
}

if(isset($_POST['deleteOrder'])){
    $order_id = validated($_POST['order_id']);

    $order_items_query = "DELETE FROM order_items WHERE order_id = '$order_id'";
    $order_items_result = mysqli_query($conn, $order_items_query);

    $order_query = "DELETE FROM orders WHERE id = '$order_id'";
    $order_result = mysqli_query($conn, $order_query);

    if($order_result && $order_items_result){
        audit_log('delete', 'order', $order_id, json_encode(['by' => $_SESSION['loggedInUser']['name'] ?? 'system']));
        redirect("orders.php", "Order deleted successfully", "success");
    } else {
        redirect("orders.php", "Failed to delete order", "error");
    }
}

if(isset($_POST['updateCustomer'])){
    $customerId = validated($_POST['customerId']);
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    $phone = validated($_POST['phone']);
    $address = validated($_POST['address']);
    $status = validated($_POST['status']);

    if($customerId != '' && $name != '' && $email != ''){
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $name, $email, $phone, $address, $status, $customerId);
        $updateQuery = $stmt->execute();
        
        if($updateQuery){
            redirect("customers.php", "Customer updated successfully", "success");
        } else {
            redirect("customer-edit.php?id=$customerId", "Failed to update customer", "error");
        }
    } else {
        redirect("customer-edit.php?id=$customerId", "All required fields must be filled", "error");
    }
}

if(isset($_POST['updateProfile'])){
    $profileId = validated($_POST['profileId']);
    $name = validated($_POST['name']);
    $email = validated($_POST['email']);
    $phone = validated($_POST['phone']);
    $currentPassword = validated($_POST['current_password']);
    $newPassword = validated($_POST['new_password']);
    $confirmPassword = validated($_POST['confirm_password']);

    if($profileId != '' && $name != '' && $email != ''){
        // Check if password change is requested
        if(!empty($newPassword)) {
            if($newPassword !== $confirmPassword) {
                redirect("profile.php", "New passwords do not match", "error");
                exit();
            }
            
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->bind_param("i", $profileId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if(!password_verify($currentPassword, $admin['password'])) {
                    redirect("profile.php", "Current password is incorrect", "error");
                    exit();
                }
                
                // Update with new password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $hashedPassword, $profileId);
            } else {
                redirect("profile.php", "Admin not found", "error");
                exit();
            }
        } else {
            // Update without password change
            $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $phone, $profileId);
        }
        
        if($stmt->execute()){
            // Update session data
            $_SESSION['loggedInUser']['name'] = $name;
            $_SESSION['loggedInUser']['email'] = $email;
            redirect("profile.php", "Profile updated successfully", "success");
        } else {
            redirect("profile.php", "Failed to update profile", "error");
        }
    } else {
        redirect("profile.php", "Name and email are required", "error");
    }
}

// --- Suppliers CRUD handlers ---
// Ensure suppliers table exists (minimal schema)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(100) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if(isset($_POST['saveSupplier'])){
    $name = validated($_POST['name'] ?? '');
    $contact = validated($_POST['contact_person'] ?? '');
    $phone = validated($_POST['phone'] ?? '');
    $email = validated($_POST['email'] ?? '');
    $address = validated($_POST['address'] ?? '');

    if($name == ''){
        redirect('supplier-create.php', 'Name is required', 'error');
    }

    $data = [
        'name' => $name,
        'contact_person' => $contact,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
        'status' => 1,
    ];

    $result = insert('suppliers', $data);
    if($result) redirect('suppliers.php', 'Supplier added successfully', 'success');
    else redirect('supplier-create.php', 'Failed to add supplier', 'error');
}

if(isset($_POST['updateSupplier'])){
    $supplierId = validated($_POST['supplierId'] ?? '');
    $name = validated($_POST['name'] ?? '');
    $contact = validated($_POST['contact_person'] ?? '');
    $phone = validated($_POST['phone'] ?? '');
    $email = validated($_POST['email'] ?? '');
    $address = validated($_POST['address'] ?? '');

    if($supplierId == '' || $name == ''){
        redirect('supplier-edit.php?id=' . $supplierId, 'All required fields must be filled', 'error');
    }

    $data = [
        'name' => $name,
        'contact_person' => $contact,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
    ];

    $result = update('suppliers', $supplierId, $data);
    if($result) redirect('supplier-edit.php?id=' . $supplierId, 'Supplier updated successfully', 'success');
    else redirect('supplier-edit.php?id=' . $supplierId, 'Failed to update supplier', 'error');
}

if(isset($_POST['deleteSupplier'])){
    $supplierId = validated($_POST['supplier_id'] ?? '');
    if($supplierId == '') redirect('suppliers.php', 'Invalid supplier', 'error');

    $result = delete('suppliers', $supplierId);
    if($result) redirect('suppliers.php', 'Supplier deleted', 'success');
    else redirect('suppliers.php', 'Failed to delete supplier', 'error');
}


?>