<?php
// Выход пользователя
session_destroy();
header("Location: index.php");
exit();
?>