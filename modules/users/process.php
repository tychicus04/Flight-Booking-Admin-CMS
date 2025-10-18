<?php
// ============================================
// FILE: modules/users/process.php
// ============================================

$errors = [];
$id = $_POST['id'] ?? null;

// Validation
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role_id = $_POST['role_id'] ?? '';
$status = $_POST['status'] ?? 'active';

// Validate required fields
if (empty($username)) {
    $errors['username'] = 'Username không được để trống!';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username phải có ít nhất 3 ký tự!';
}

if (empty($email)) {
    $errors['email'] = 'Email không được để trống!';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email không hợp lệ!';
}

if (empty($full_name)) {
    $errors['full_name'] = 'Họ và tên không được để trống!';
}

if (empty($role_id)) {
    $errors['role_id'] = 'Vui lòng chọn vai trò!';
}

// Password validation (chỉ khi thêm mới hoặc có nhập password)
if (!$id || !empty($password)) {
    if (empty($password)) {
        $errors['password'] = 'Mật khẩu không được để trống!';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Xác nhận mật khẩu không khớp!';
    }
}

// Check unique username
$checkUsernameQuery = "SELECT id FROM users WHERE username = ?" . ($id ? " AND id != ?" : "");
$stmt = $db->prepare($checkUsernameQuery);
$params = $id ? [$username, $id] : [$username];
$stmt->execute($params);
if ($stmt->fetch()) {
    $errors['username'] = 'Username đã tồn tại!';
}

// Check unique email
$checkEmailQuery = "SELECT id FROM users WHERE email = ?" . ($id ? " AND id != ?" : "");
$stmt = $db->prepare($checkEmailQuery);
$stmt->execute($params);
if ($stmt->fetch()) {
    $errors['email'] = 'Email đã tồn tại!';
}

// Handle avatar upload
$avatar = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = UPLOAD_PATH . 'avatars/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileInfo = pathinfo($_FILES['avatar']['name']);
    $extension = strtolower($fileInfo['extension']);
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($extension, $allowedExtensions)) {
        $errors['avatar'] = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!';
    } elseif ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        $errors['avatar'] = 'File ảnh không được vượt quá 2MB!';
    } else {
        $avatar = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $avatar;
        
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
            $errors['avatar'] = 'Lỗi khi upload file!';
            $avatar = null;
        }
    }
}

// If no errors, save to database
if (empty($errors)) {
    try {
        if ($id) {
            // Update
            $updateFields = [
                "username = ?",
                "email = ?",
                "full_name = ?",
                "role_id = ?",
                "status = ?"
            ];
            $params = [$username, $email, $full_name, $role_id, $status];
            
            if (!empty($password)) {
                $updateFields[] = "password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            if ($avatar) {
                $updateFields[] = "avatar = ?";
                $params[] = $avatar;
                
                // Delete old avatar
                $oldUser = $db->prepare("SELECT avatar FROM users WHERE id = ?");
                $oldUser->execute([$id]);
                $oldAvatar = $oldUser->fetch()['avatar'] ?? null;
                if ($oldAvatar && file_exists(UPLOAD_PATH . 'avatars/' . $oldAvatar)) {
                    unlink(UPLOAD_PATH . 'avatars/' . $oldAvatar);
                }
            }
            
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('Cập nhật user thành công!', 'success');
            
            // Update session if editing current user
            if ($id == $_SESSION['user_id']) {
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                if ($avatar) {
                    $_SESSION['avatar'] = $avatar;
                }
            }
        } else {
            // Insert
            $sql = "INSERT INTO users (username, password, full_name, email, role_id, status, avatar) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare($sql);
            $stmt->execute([$username, $hashedPassword, $full_name, $email, $role_id, $status, $avatar]);
            
            setFlashMessage('Thêm user mới thành công!', 'success');
            redirect('modules/users/index.php');
        }
    } catch (PDOException $e) {
        $errors['database'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
    }
}

// Display errors
if (!empty($errors)) {
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}

?>
