<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы оставить заявку, необходимо <a href="login.php">войти в аккаунт</a>.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $curses = $_POST['curses'];
    $payment = $_POST['payment'];
    $status = 'Новая';
    
    include('db.php');
    
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $curses = $con->real_escape_string($curses);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$curses', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на курс - Учусь.РФ</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 550px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
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

        .nav-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }

        .btn-nav {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-nav:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
            font-weight: 700;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        form button {
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

        form button:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #dc3545;
        }

        .success-message a, .error-message a {
            color: inherit;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav">🏠 Главная</a>
            <a href="history.php" class="btn-nav">📋 История заявок</a>
        </div>
        
        <h1>Запись на онлайн-курс</h1>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Заявка успешно отправлена!<br><br>
                <a href="history.php">→ Перейти к истории моих заявок →</a>
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                ❌ Ошибка при отправке заявки: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()">← Попробовать снова</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <label for="curses">📚 Выберите курс</label>
            <select id="curses" name="curses" required>
                <option value="Курсы повышения квалификации">Курсы повышения квалификации</option>
                <option value="Курсы профессиональной переподготовки">Курсы профессиональной переподготовки</option>
                <option value="Курсы по охране труда">Курсы по охране труда</option>
            </select>

            <label for="date">📅 Желаемая дата начала обучения</label>
            <input id="date" type="datetime-local" name="date" required>

            <label for="payment">💳 Способ оплаты</label>
            <select id="payment" name="payment" required>
                <option value="наличные">Наличные</option>
                <option value="перевод">Перевод по номеру карты</option>
                <option value="карта">Банковской картой онлайн</option>
            </select>

            <label for="review">📝 Дополнительная информация / Пожелания</label>
            <textarea id="review" name="review" placeholder="Опишите ваши пожелания или комментарий..."></textarea>
             
            <button type="submit">🚀 Отправить заявку</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>