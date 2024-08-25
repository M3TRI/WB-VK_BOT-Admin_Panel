<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("../data/db.php");

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка наличия CSRF токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $out_status = "False";
        $out_message =  'CSRF токен не совпадает.';
    }

    // Проверка наличия имени пользователя и пароля
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $out_status = "False";
        $out_message =  'Имя пользователя или пароль не были отправлены.';
    }

    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    global $pdo;

    // Подготовка и выполнение запроса
    $stmt = $pdo->prepare("SELECT password, ip_address FROM admin WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Проверка пароля
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $out_status = "True";
        $out_message = "Успех!";
    } else {
        $out_status = "False";
        $out_message =  'Неверный логин или пароль.';
    }
} else {
    $out_status = "False";
    $out_message = 'Неправильный метод запроса.';
}
die (json_encode(array("success" => $out_status, "content" => $out_message), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ));
?>
