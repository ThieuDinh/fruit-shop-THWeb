<?php
session_start();
require_once 'config/database.php';
require_once 'config/google_config.php';
$login_url = $client->createAuthUrl();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ email và mật khẩu!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu nhập lại không khớp!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email này đã được đăng ký!';
        } else {
            $email_parts = explode('@', $email);
            $temp_name = $email_parts[0]; 

            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // lưu db
            $sql = "INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':full_name', $temp_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php?status=success");
                exit();
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Đăng nhập / Đăng ký - Fruitify</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Tùy chỉnh CSS để giống thiết kế gốc và phù hợp với template nông sản */
        :root {
            --primary-color: #81c408;
            /* Màu xanh lá chuẩn của các template Fruitables */
            --secondary-color: #45595b;
            --light-bg: #f6f8f6;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--light-bg);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Raleway', sans-serif;
            font-weight: 800;
        }

        /* Layout chia đôi màn hình */
        .login-wrapper {
            min-height: 100vh;
        }

        /* Phần hình ảnh bên trái */
        .bg-image-side {
            background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuATMqmE0trsYRQnB1EWXeZlmUZTrDewBEqEz-kbTCCTf4fY6P_K1amZ8520vQKOQmdsrAYLEFlM1wf0hhS4zOlfxAJ-81utydfguEqmxRj12-hmD7JsTFJ6Nvmio7gpiIX9TdkiW-tf4fPAZP2FNa7IB7ytVTtS28NITBFdoxXHjkt6HM2Q0GiPlo1lc7k-BF-UjyeEVGAxREOq0tgkkCl36TcvWiXB1OyDCwgIfD5JNwTOfCrS2LiG8bRC1gcBo6TD96bmZBEQtJsh");
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .bg-overlay {
            background-color: rgba(0, 0, 0, 0.3);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Phần Form bên phải */
        .form-side {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-content {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }

        /* Custom Toggle Switch (Đăng nhập/Đăng ký) */
        .auth-toggle {
            background-color: #f0f2f0;
            padding: 5px;
            border-radius: 10px;
            display: flex;
            margin-bottom: 2rem;
        }

        .auth-toggle .btn {
            flex: 1;
            border-radius: 8px;
            font-weight: 600;
            color: #666;
            border: none;
        }

        .auth-toggle .btn.active {
            background-color: #fff;
            color: #000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Custom Input */
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(129, 196, 8, 0.25);
            border-color: var(--primary-color);
        }

        /* Nút Submit */
        .btn-submit {
            background-color: var(--primary-color);
            /* Màu xanh lá */
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #6ea806;
            color: #fff;
        }

        /* Custom Checkbox color */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #aaa;
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #eee;
        }

        .divider span {
            padding: 0 10px;
        }

        /* Social Buttons */
        .btn-social {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #555;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-social:hover {
            background-color: #f8f9fa;
        }

        .input-group .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .input-group .btn {

            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;

        }

        .input-group .btn:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: var(--primary-color);
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0">
        <div class="row g-0 login-wrapper">

            <div class="col-lg-5 d-none d-lg-block bg-image-side">
                <div class="bg-overlay">
                    <div class="text-center text-white p-4" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border-radius: 15px;">
                        <h1 class="display-4 text-white mb-2">Fruitify</h1>
                        <p class="lead mb-0">Sự tươi mới từ vườn đến nhà bạn.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 form-side">
                <div class="form-content">

                    <h2 class="text-dark mb-2">Chào mừng đến với Farm2Home</h2>
                    <p class="text-secondary mb-4">Vui lòng đăng ký để tiếp tục</p>

                    <div class="auth-toggle">
                        <button class="btn active" id="btn-login-toggle">Đăng nhập</button>
                        <button class="btn" id="btn-register-toggle">Đăng ký</button>
                    </div>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold text-secondary small">Email</label>
                            <input type="email" name="email" class="form-control" id="email" placeholder="Nhập email của bạn" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold text-secondary small">Mật khẩu</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" placeholder="Nhập mật khẩu" required>

                                <button class="btn btn-outline-secondary " type="button" id="togglePassword" style=" border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label fw-bold text-secondary small">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" id="confirmPassword" placeholder="Nhập lại mật khẩu" required>

                                <button class="btn btn-outline-secondary " type="button" id="toggleConfirmPassword" style=" border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-submit">Đăng ký</button>

                        <div class="divider">
                            <span>hoặc đăng nhập với</span>
                        </div>

                        <div class="row g-3 justify-content-center">
                            <div class="col-6">
                               <a href="<?php echo $login_url; ?>" class="btn btn-social w-100">
                                    <i class="fab fa-google text-danger"></i> Google
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.auth-toggle .btn').click(function() {
                $('.auth-toggle .btn').removeClass('active');
                $(this).addClass('active');
                // Tại đây bạn có thể thêm logic để ẩn/hiện form đăng ký nếu muốn
                if ($(this).attr('id') === 'btn-login-toggle') {
                    window.location.href = 'login.php';
                }
            });
        });
    </script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            // Kiểm tra loại hiện tại
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Đổi icon (Mắt mở <-> Mắt gạch chéo)
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');

            // Kiểm tra loại hiện tại
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);

            // Đổi icon (Mắt mở <-> Mắt gạch chéo)
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>