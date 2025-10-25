<?php
require_once 'config.php';
require_once 'includes/functions.php';

session_start();

$auth = new Auth();
$auth->logout();

showMessage('Вы успешно вышли из системы', 'success');
redirect('/');
?>
