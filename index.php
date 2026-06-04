<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Проверяем, установлен ли ключ admin в сессии
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Учусь.РФ - онлайн курсы дистанционного обучения</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #ced4da;
      color: #333;
      line-height: 1.5;
    }

    /* Шапка сайта */
    .header {
      background: #0d47a1;
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .logo {
      color: #ffffff;
      font-size: 24px;
      font-weight: 700;
      text-decoration: none;
      transition: opacity 0.3s ease;
    }

    .logo:hover {
      opacity: 0.9;
    }

    .nav-buttons a {
      margin-left: 15px;
      padding: 10px 20px;
      border: 2px solid #ffffff;
      border-radius: 8px;
      color: #ffffff;
      text-decoration: none;
      transition: all 0.3s ease;
      font-weight: 500;
      font-size: 14px;
    }

    .nav-buttons a:hover {
      background-color: #ffffff;
      color: #0d47a1;
      transform: translateY(-2px);
    }

    /* Слайдер */
    .slideshow-container {
      max-width: 1200px;
      position: relative;
      margin: 40px auto;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .mySlides {
      display: none;
    }

    .fade {
      animation: fadeIn 1.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }

    .mySlides img {
      width: 100%;
      height: 500px;
      object-fit: cover;
    }

    .text {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: rgba(13, 71, 161, 0.9);
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 500;
      color: #ffffff;
    }

    /* Стрелки */
    .prev, .next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(13, 71, 161, 0.8);
      color: #ffffff;
      border: none;
      cursor: pointer;
      padding: 12px 18px;
      font-size: 18px;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .prev { left: 10px; }
    .next { right: 10px; }

    .prev:hover, .next:hover {
      background-color: #007bff;
      transform: translateY(-50%) scale(1.05);
    }

    /* Точки навигации */
    .dot-container {
      text-align: center;
      padding: 20px 0;
    }

    .dot {
      cursor: pointer;
      height: 12px;
      width: 12px;
      margin: 0 5px;
      background-color: #dee2e6;
      border-radius: 50%;
      display: inline-block;
      transition: background-color 0.3s ease;
    }

    .dot.active, .dot:hover {
      background-color: #007bff;
    }

    /* Секции */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .section-title {
      font-size: 36px;
      font-weight: 700;
      color: #0d47a1;
      text-align: center;
      margin-bottom: 40px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin: 60px 0;
    }

    .feature-card {
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      transition: transform 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
    }

    .feature-card h3 {
      font-size: 24px;
      font-weight: 600;
      color: #007bff;
      margin-bottom: 15px;
    }

    .feature-card p {
      font-size: 16px;
      color: #666;
      line-height: 1.6;
    }

    .btn-more {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background: #007bff;
      color: #ffffff;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      transition: background 0.3s ease;
    }

    .btn-more:hover {
      background: #0d47a1;
    }

    /* Адаптивность */
    @media (max-width: 768px) {
      .nav {
        flex-direction: column;
        gap: 15px;
      }
      
      .mySlides img {
        height: 300px;
      }
      
      .text {
        font-size: 14px;
        bottom: 10px;
        left: 10px;
        padding: 8px 16px;
      }
      
      .prev, .next {
        padding: 8px 12px;
        font-size: 14px;
      }

      .section-title {
        font-size: 28px;
      }
    }
  </style>
</head>
<body>
<header class="header">
  <div class="nav">
    <a href="index.php" class="logo">Учусь.РФ</a>
    <div class="nav-buttons">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php">Войти</a>
        <a href="register.php">Регистрация</a>
      <?php elseif ($is_admin): ?>
        <a href="admin.php">Панель администратора</a>
        <a href="?logout=1">Выход</a>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <a href="history.php">Мои заявки</a>
        <a href="create.php">Новая заявка</a>
        <a href="?logout=1">Выход</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Слайдер -->
<div class="slideshow-container">
  <div class="mySlides fade">
    <img src="slide1.jpg" alt="Курсы повышения квалификации">
    <div class="text">Курсы повышения квалификации</div>
  </div>
  <div class="mySlides fade">
    <img src="slide2.jpg" alt="Курсы переподготовки">
    <div class="text">Курсы профессиональной переподготовки</div>
  </div>
  <div class="mySlides fade">
    <img src="slide3.jpg" alt="Курсы по охране труда">
    <div class="text">Курсы по охране труда</div>
  </div>

  <a class="prev" onclick="plusSlides(-1)">❮</a>
  <a class="next" onclick="plusSlides(1)">❯</a>
</div>

<div class="dot-container">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
</div>

<!-- Основной контент -->
<div class="container">
  <h2 class="section-title">Почему выбирают Учусь.РФ?</h2>
  
  <div class="features-grid">
    <div class="feature-card">
      <h3>🎓 Опытные преподаватели</h3>
      <p>Все преподаватели — практикующие эксперты с многолетним опытом.</p>
      <a href="create.php" class="btn-more">Записаться →</a>
    </div>
    
    <div class="feature-card">
      <h3>💻 Современные технологии</h3>
      <p>Удобная платформа для дистанционного обучения в любое время.</p>
      <a href="create.php" class="btn-more">Записаться →</a>
    </div>
    
    <div class="feature-card">
      <h3>📜 Документы гособразца</h3>
      <p>После обучения выдается удостоверение или диплом установленного образца.</p>
      <a href="create.php" class="btn-more">Записаться →</a>
    </div>
  </div>
</div>

<script>
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }

  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}

let slideInterval = setInterval(function() {
  plusSlides(1);
}, 4000);

const slideshowContainer = document.querySelector('.slideshow-container');
if (slideshowContainer) {
  slideshowContainer.addEventListener('mouseenter', function() {
    clearInterval(slideInterval);
  });
  slideshowContainer.addEventListener('mouseleave', function() {
    slideInterval = setInterval(function() {
      plusSlides(1);
    }, 4000);
  });
}
</script>
</body>
</html>