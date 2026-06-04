<?php
include('db.php');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$valid_statuses = ['Новая', 'Идет обучение', 'Обучение завершено'];
$status_updated = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }

    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);

    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Идет обучение' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'Обучение завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Учусь.РФ</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: #ffffff;
            padding: 25px 30px;
            border-bottom: 1px solid #dee2e6;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0d47a1;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
        }

        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-family: 'Inter', sans-serif;
        }

        .btn-outline {
            background: transparent;
            color: #007bff;
            border: 2px solid #007bff;
        }

        .btn-outline:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 25px 30px;
        }

        .stat-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #007bff;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .requests-container {
            padding: 0 30px 30px;
        }

        .request-item {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #dee2e6;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .user-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .user-info p {
            font-size: 14px;
            color: #666;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-new { background: #fff3cd; color: #856404; }
        .status-in-progress { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }

        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .detail-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 14px;
            color: #333;
            margin-top: 5px;
        }

        .status-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-save {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-save:hover {
            background: #218838;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-link {
            padding: 8px 16px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-decoration: none;
            color: #007bff;
            transition: all 0.3s ease;
        }

        .page-link:hover, .page-link.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: #28a745;
            color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-out 2.5s forwards;
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        @media (max-width: 768px) {
            .nav-bar {
                flex-direction: column;
                gap: 10px;
            }
            
            .request-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Панель администратора</h1>
            <p class="subtitle">Управление заявками на обучение</p>
        </div>

        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">🏠 Главная</a>
            <a href="?logout=1" class="btn btn-outline" onclick="return confirm('Выйти из аккаунта?')">🚪 Выход</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#ffc107;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#17a2b8;"><?= $stats['in_progress'] ?></div>
                <div class="stat-label">Идёт обучение</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#28a745;"><?= $stats['completed'] ?></div>
                <div class="stat-label">Завершено</div>
            </div>
        </div>

        <div class="requests-container">
            <?php if ($query->num_rows === 0): ?>
                <div class="empty-state" style="text-align:center; padding:60px;">
                    📭 Заявок пока нет
                </div>
            <?php else: ?>
                <?php while ($request = $query->fetch_assoc()): ?>
                    <?php
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Идет обучение' => 'status-in-progress',
                        'Обучение завершено' => 'status-completed',
                        default => 'status-new'
                    };
                    ?>
                    <div class="request-item">
                        <div class="request-header">
                            <div class="user-info">
                                <h3><?= htmlspecialchars($request['login']) ?></h3>
                                <p><?= htmlspecialchars($request['fullname']) ?></p>
                            </div>
                            <div>
                                <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                            </div>
                        </div>

                        <div class="request-details">
                            <div class="detail-item">
                                <div class="detail-label">📅 Дата подачи</div>
                                <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">📚 Курс</div>
                                <div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">💳 Оплата</div>
                                <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">📝 Комментарий</div>
                                <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                            </div>
                        </div>

                        <div class="status-form">
                            <form method="POST" class="status-update-form">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <div class="form-group">
                                    <label class="form-label">Изменить статус:</label>
                                    <select name="status" class="form-select">
                                        <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>🆕 Новая</option>
                                        <option value="Идет обучение" <?= $request['status'] == 'Идет обучение' ? 'selected' : '' ?>>📖 Идёт обучение</option>
                                        <option value="Обучение завершено" <?= $request['status'] == 'Обучение завершено' ? 'selected' : '' ?>>✅ Обучение завершено</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-save">💾 Сохранить изменения</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($status_updated): ?>
        <div class="notification">✅ Статус заявки успешно обновлён!</div>
    <?php endif; ?>
</body>
</html>