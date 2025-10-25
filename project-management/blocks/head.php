<?php
// blocks/head.php - общий заголовок для всех страниц

// Определяем базовый путь
$basePath = dirname(__DIR__);

// Подключаем необходимые файлы с абсолютными путями
require_once $basePath . '/session.php';
require_once $basePath . '/auth.php';

$currentUser = Auth::getUser();
$currentPage = basename($_SERVER['PHP_SELF']);

// Определяем заголовок страницы
$pageTitles = [
    'index.php' => 'Ростелеком - Управление проектами',
    'projects.php' => 'Реестр проектов - Ростелеком',
    'project-create.php' => 'Создание проекта - Ростелеком',
    'project-card.php' => 'Карточка проекта - Ростелеком',
    'analytics.php' => 'Аналитика - Ростелеком',
    'reports.php' => 'Конструктор отчетов - Ростелеком',
    'login.php' => 'Вход - Ростелеком'
];

$pageTitle = $pageTitles[$currentPage] ?? 'Ростелеком - Управление проектами';

// Определяем базовый URL для ссылок
$baseUrl = ''; // Оставляем пустым для относительных путей
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Дополнительные стили для header */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }
        
.logo-image {
    height: 100px; /* было 40px */
    width: auto;
    flex-shrink: 0;
    object-fit: contain;
}

/* Адаптивность для логотипа */
@media (max-width: 1024px) {
    .logo-image {
        height: 45px; /* добавлен промежуточный размер */
    }
}

@media (max-width: 768px) {
    .logo-image {
        height: 40px; /* было 35px */
    }
}

@media (max-width: 480px) {
    .logo-image {
        height: 35px; /* было 30px */
    }
}

@media (max-width: 480px) {
    .logo-image {
        height: 50px;
    }
}
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            white-space: nowrap;
            font-size: 0.95rem;
        }
        
        nav a:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        nav a.active {
            background: rgba(255, 255, 255, 0.25);
            position: relative;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: white;
            color: #7700FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            flex-shrink: 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        .user-dropdown {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }
        
        .user-dropdown span:first-child {
            font-weight: 600;
            color: white;
            font-size: 0.95rem;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .user-dropdown a {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }
        
        .user-dropdown a:hover {
            opacity: 1;
            text-decoration: underline;
        }
        
        /* Адаптивность */
        @media (max-width: 1024px) {
            nav ul {
                gap: 1rem;
            }
            
            nav a {
                padding: 0.4rem 0.6rem;
                font-size: 0.9rem;
            }
            
            .logo {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .logo {
                order: 1;
            }
            
            nav {
                order: 3;
                width: 100%;
            }
            
            nav ul {
                justify-content: center;
                gap: 0.5rem;
            }
            
            .user-menu {
                order: 2;
                margin-left: auto;
            }
            
            .user-dropdown {
                display: none; /* Скрываем подробности на мобильных */
            }
        }
        
        @media (max-width: 480px) {
            .logo span {
                display: none; /* Скрываем текст лого на очень маленьких экранах */
            }
            
            nav a {
                padding: 0.3rem 0.5rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="img/RGB_RT_logo-vertical_black_en.svg" alt="Ростелеком" class="logo-image">
                    <span>Управление проектами</span>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Дашборд</a></li>
                        <li><a href="projects.php" class="<?= $currentPage === 'projects.php' ? 'active' : '' ?>">Проекты</a></li>
                        <li><a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">Отчеты</a></li>
                        <li><a href="analytics.php" class="<?= $currentPage === 'analytics.php' ? 'active' : '' ?>">Аналитика</a></li>
                        <?php if (Auth::isLoggedIn() && Auth::hasRole('Администратор')): ?>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="user-menu">
                    <?php if (Auth::isLoggedIn()): ?>
                        <div class="user-avatar" title="<?= $currentUser['full_name'] ?? 'Гость' ?>">
                            <?= substr($currentUser['full_name'] ?? 'Г', 0, 2) ?>
                        </div>
                        <div class="user-dropdown">
                            <span><?= $currentUser['full_name'] ?? 'Гость' ?></span>
                            <span class="user-role">
                                <?php 
                                $roles = [1 => 'Администратор', 2 => 'Аналитик', 3 => 'Пользователь'];
                                echo $roles[$currentUser['role_id'] ?? 3] ?? 'Пользователь';
                                ?>
                            </span>
                            <a href="logout.php">Выйти</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary" style="
                            padding: 0.5rem 1rem;
                            background: rgba(255,255,255,0.2);
                            color: white;
                            border: 1px solid rgba(255,255,255,0.3);
                            border-radius: 6px;
                            text-decoration: none;
                            font-weight: 500;
                            transition: all 0.3s ease;
                            white-space: nowrap;
                        ">Войти</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
