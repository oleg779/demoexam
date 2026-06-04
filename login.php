<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        include('db.php');
        
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            
            if ($password !== $user['password']) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Учусь.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #007bff 0%, #0d47a1 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            animation: slideInUp 0.5s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #0d47a1;
        }

        .logo p {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            color: #888;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #dc3545;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .form-footer p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .register-link, .back-home {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-link:hover, .back-home:hover {
            color: #0d47a1;
        }

        .back-home {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Учусь.РФ</h1>
            <p>Онлайн-курсы дистанционного обучения</p>
        </div>

        <div class="form-header">
            <h2>Добро пожаловать!</h2>
            <p>Войдите в свой аккаунт</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="login">👤 Логин</label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="Введите ваш логин" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">🔒 Пароль</label>
                <input type="password" id="password" name="password" 
                       placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="btn-login">Войти</button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>