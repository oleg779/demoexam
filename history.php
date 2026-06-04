<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, необходимо <a href="login.php">войти в аккаунт</a>.');
include('db.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $con->query("UPDATE users SET review='$review' WHERE id='$user_id'");
    echo '<div style="color:green; padding:10px; background:#e6ffe6; margin-bottom:10px;">✓ Отзыв успешно сохранён</div>';
}

$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('Ошибка запроса: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История заявок - Учусь.РФ</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
            font-weight: 700;
        }

        .btn-home {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .request {
            border: 1px solid #dee2e6;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .request h2 {
            font-size: 18px;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 15px;
        }

        .request p {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }

        .review-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #dee2e6;
        }

        .review-form input {
            width: 70%;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }

        .review-form button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .review-form button:hover {
            background: #218838;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-home">🏠 На главную</a>
        
        <h1>История заявок</h1>
        
        <?php
        $i = 0;
        if($query->num_rows == 0) {
            echo '<div class="empty-state">📭 У вас пока нет заявок.<br><br><a href="create.php" style="color:#007bff;">Создать первую заявку →</a></div>';
        }
        while($request = $query->fetch_assoc()) {
            $i++; 
            echo '
            <div class="request">
                <h2>Заявка №' . $i . '</h2>
                <p><strong>📅 Дата:</strong> ' . htmlspecialchars($request['date']) . '</p>
                <p><strong>📚 Курс:</strong> ' . htmlspecialchars($request['curses']) . '</p>
                <p><strong>💳 Оплата:</strong> ' . htmlspecialchars($request['payment']) . '</p>
                <p><strong>📌 Статус:</strong> ' . htmlspecialchars($request['status']) . '</p>';
                
            if($request['status'] === 'Обучение завершено') {
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="text" name="review" placeholder="Оставьте отзыв о курсе" value="' . htmlspecialchars($request['review']) . '">
                        <button type="submit">✍️ Оставить отзыв</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>