<?php
// assets/function/add/add_account.php
function addAccount($conn, $data) {
    // Validate input
    $errors = [];
    
    if (empty($data['Ten_user'])) {
        $errors[] = "Tên người dùng không được để trống";
    }
    if (empty($data['username'])) {
        $errors[] = "Username không được để trống";
    }
    if (empty($data['password'])) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($data['password']) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    if (empty($data['email'])) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Check if username or email exists
    $stmt = $conn->prepare("SELECT idtk FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $data['username'], $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'errors' => ["Username hoặc Email đã tồn tại"]];
    }
    
    // Hash password
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Handle image upload
    $Anh_user = 'default-avatar.png'; // Default image
    if (!empty($_FILES['Anh_user']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['Anh_user']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('avatar_') . '.' . $ext;
            $upload_path = __DIR__ . '/../../../assets/img/' . $new_filename;
            
            if (move_uploaded_file($_FILES['Anh_user']['tmp_name'], $upload_path)) {
                $Anh_user = $new_filename;
            } else {
                return ['success' => false, 'errors' => ["Lỗi khi tải ảnh lên"]];
            }
        } else {
            return ['success' => false, 'errors' => ["Chỉ chấp nhận ảnh: jpg, jpeg, png, gif"]];
        }
    }
    
    // Insert into database
    $sql = "INSERT INTO users (Ten_user, username, password, email, Anh_user, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['Ten_user'], 
        $data['username'], 
        $password_hash, 
        $data['email'], 
        $Anh_user
    );
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => "Thêm tài khoản thành công!"];
    } else {
        return ['success' => false, 'errors' => ["Lỗi hệ thống: " . $stmt->error]];
    }
}
?>