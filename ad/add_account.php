<?php
// ad/add_account.php
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
    
    // Check if username already exists in taikhoan
    $stmt_check_user = $conn->prepare("SELECT idtk FROM taikhoan WHERE username = ?");
    $stmt_check_user->bind_param("s", $data['username']);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        return ['success' => false, 'errors' => ["Username đã tồn tại"]];
    }
    $stmt_check_user->close();

    // Check if email already exists in users
    $stmt_check_email = $conn->prepare("SELECT iduser FROM users WHERE email = ?");
    $stmt_check_email->bind_param("s", $data['email']);
    $stmt_check_email->execute();
    if ($stmt_check_email->get_result()->num_rows > 0) {
        return ['success' => false, 'errors' => ["Email đã tồn tại"]];
    }
    $stmt_check_email->close();
    
    // Handle image upload
    $Anh_user = 'user.jpg'; // Default image
    if (!empty($_FILES['Anh_user']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['Anh_user']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('avatar_') . '.' . $ext;
            $upload_path = __DIR__ . '/../assets/img/' . $new_filename;
            
            if (move_uploaded_file($_FILES['Anh_user']['tmp_name'], $upload_path)) {
                $Anh_user = $new_filename;
            } else {
                return ['success' => false, 'errors' => ["Lỗi khi tải ảnh lên"]];
            }
        } else {
            return ['success' => false, 'errors' => ["Chỉ chấp nhận ảnh: jpg, jpeg, png, gif"]];
        }
    }
    
    // Insert into taikhoan first
    $roleId = 2; // Default User
    $status = $data['status'];
    $stmt_tk = $conn->prepare("INSERT INTO taikhoan (username, password, roleId, trangthai, thoigiantao) VALUES (?, ?, ?, ?, NOW())");
    $stmt_tk->bind_param("ssii", $data['username'], $data['password'], $roleId, $status);
    
    if ($stmt_tk->execute()) {
        $idtk = $conn->insert_id;
        
        // Insert into users
        $stmt_user = $conn->prepare("INSERT INTO users (idtk, Ten_user, Anh_user, sdt, email, diachi, ngaysinh) VALUES (?, ?, ?, ?, ?, '', NULL)");
        $stmt_user->bind_param("issss", $idtk, $data['Ten_user'], $Anh_user, $data['phone'], $data['email']);
        
        if ($stmt_user->execute()) {
            $stmt_user->close();
            $stmt_tk->close();
            return ['success' => true, 'message' => "Thêm tài khoản thành công!"];
        } else {
            $err = $stmt_user->error;
            $stmt_user->close();
            $stmt_tk->close();
            return ['success' => false, 'errors' => ["Lỗi thêm thông tin người dùng: " . $err]];
        }
    } else {
        $err = $stmt_tk->error;
        $stmt_tk->close();
        return ['success' => false, 'errors' => ["Lỗi hệ thống khi thêm tài khoản: " . $err]];
    }
}
?>