<?php
// auth/callback_google.php
session_start();

// 1. Gọi file cấu hình và database
// Lưu ý đường dẫn: dùng __DIR__ để lùi ra thư mục cha cho chính xác
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google_config.php';

if (isset($_GET['code'])) {
    // 2. Lấy Token từ Google
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // 3. Lấy thông tin người dùng từ Google
        $google_oauth = new Google\Service\Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;

        // 4. Kiểm tra Email trong Database (LOGIC KẾT NỐI)
        // Bảng users của bạn dùng cột: full_name, email, google_id
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // === TRƯỜNG HỢP 1: TÀI KHOẢN ĐÃ TỒN TẠI ===
            
            // Logic kết nối: Nếu user cũ chưa có google_id -> Cập nhật thêm vào
            if (empty($user['google_id'])) {
                $update = $conn->prepare("UPDATE users SET google_id = :gid WHERE id = :id");
                $update->execute([
                    ':gid' => $google_id, 
                    ':id' => $user['id']
                ]);
            }

            // Đăng nhập (Set Session theo cột trong DB của bạn)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name']; // Cột full_name
            $_SESSION['user_role'] = $user['role'];      // Cột role

        } else {
            // === TRƯỜNG HỢP 2: TÀI KHOẢN MỚI TINH ===
            
            // Insert vào bảng users
            // password để NULL, role mặc định là 0
            $sql = "INSERT INTO users (full_name, email, google_id, role, password) VALUES (:name, :email, :gid, 0, NULL)";
            $stmt = $conn->prepare($sql);
            
            // Nếu Google không trả về tên, lấy tạm phần đầu email
            $real_name = !empty($name) ? $name : explode('@', $email)[0];

            $stmt->execute([
                ':name' => $real_name, 
                ':email' => $email, 
                ':gid' => $google_id
            ]);

            // Lấy ID vừa tạo để đăng nhập
            $new_user_id = $conn->lastInsertId();
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $real_name;
            $_SESSION['user_role'] = 0;
        }

        // 5. Chuyển hướng về trang chủ
        header('Location: ../index.php');
        exit();
    }
}

// Nếu lỗi -> Quay về login
header('Location: ../login.php');
exit();
?>