<?php
// logout.php - выход из системы
require_once 'session.php';
require_once 'auth.php';

Auth::logout();
header('Location: login.php');
exit;
?>