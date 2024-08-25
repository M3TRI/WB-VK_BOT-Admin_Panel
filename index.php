<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] == $_SERVER['REMOTE_ADDR']) {
    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $username = $_SESSION['username'];
        $password = $_SESSION['password']; 
        
        require("./data/db.php");
        global $pdo;
    
        // Подготовка и выполнение запроса
        $stmt = $pdo->prepare("SELECT password, ip_address FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Проверка пароля
    
        if ($user && password_verify($password, $user['password'])) {
            header('Location: /admin/index.php');
        }
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <form id="login_form" method="post">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label for="username">Имя пользователя:</label>
        <input type="text" id="username" name="username" placeholder="Введите логин" required>
        <br>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" placeholder="Введите пароль" required>
        <br>
        <button type="submit">Войти</button>
    </form>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const registrationForm = document.getElementById("login_form");
    
            if (registrationForm) {
                registrationForm.addEventListener("submit", function (event) {
                    event.preventDefault();
                    const username = document.getElementById("username").value;
                    const password = document.getElementById("password").value;
                    const csrf_token = document.getElementById("csrf_token").value;
                    const url = "/login/index.php";
                    const data = {
                        username: username,
                        password: password,
                        csrf_token: csrf_token
                    };
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: data,
                        success: function (data) {
                            try {
                                if (typeof data === 'string') {
                                    data = JSON.parse(data);
                                }
                                console.log(data);
                                if (data.success === 'True') {
                                    window.location.href = '/admin/index.php';
                                } else {
                                    alert(data.content);
                                }
                            } catch (e) {
                                console.log('Ошибка при парсинге JSON:', e);
                                alert("Ошибка");
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Произошла ошибка при отправке запроса:', error);
                            alert("Ошибка");
                        }
                    });

                });
            }
        });
    </script>
</body>
</html>
