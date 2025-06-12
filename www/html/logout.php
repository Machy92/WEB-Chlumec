<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['user_email']);
unset($_SESSION['pozice']);

$_SESSION['flash_message'] = "Byl jste úspěšně odhlášen.";
$_SESSION['flash_type'] = "info";

header("Location: index.php");
exit;
?>