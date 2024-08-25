<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Подписками</title>
    <link rel="stylesheet" href="https://monahat.animiku.cc/css/styles.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

function check() {
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] == $_SERVER['REMOTE_ADDR']) {
        if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
            $username = $_SESSION['username'];
            $password = $_SESSION['password'];

            // Подключение файла с базой данных
            require("/var/www/monahat/data/db.php");

            try {
                $pdo = new PDO($dsn, $DBUSER, $DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                exit();
            }

            // Подготовка и выполнение запроса
            try {
                $stmt = $pdo->prepare("SELECT password, ip_address FROM admin WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    return true;
                } else {
                    header('Location: /');
                    exit();
                }
            } catch (PDOException $e) {
                echo 'Query failed: ' . $e->getMessage();
                exit();
            }
        } else {
            header('Location: /');
            exit();
        }
    } else {
        header('Location: /');
        exit();
    }
}

if (check()) {
    require("/var/www/monahat/data/db.php");
    try {
        $pdo = new PDO($dsn, $DBUSER, $DBPASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        exit();
    }
}
?>






<body>
<header>
        <div class="title">TEST</div>
        <div class="button-block">
            <a href="/logout" role="button" id="logoutButton">Выход</a>
        </div>
    </header>

    <div class="headbutton">
            <button id="createUserButton">Создать</button>
            <button id="searchUserBtn">Поиск</button>
        </div>

    <div class="main-block">
        <h2>Пользователи</h2>
        <di class="table-user">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>USER ID</th>
                        <th>Имя бота</th>
                        <th>Канал </th>
                        <th>Дата окончания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr>
                        <td>1</td>
                        <td>89411574198</td>
                        <td>Бот_Иван</td>
                        <td>@канал_ивана</td>
                        <th>Дата окончания</th>
                        <td><button class="editUserButton" id="EditUserModalBtn">Редактировать</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>89411578198</td>
                        <td>Бот_Мария</td>
                        <td>@канал_марии</td>
                        <th>Дата окончания</th>
                        <td><button class="editUserButton" id="EditUserModalBtn">Редактировать</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>89411578198</td>
                        <td>Бот_Мария</td>
                        <td>@канал_марии</td>
                        <th>Дата окончания</th>
                        <td><button class="editUserButton" id="EditUserModalBtn">Редактировать</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>89411578198</td>
                        <td>Бот_Мария</td>
                        <td>@канал_марии</td>
                        <th>Дата окончания</th>
                        <td><button class="editUserButton" id="EditUserModalBtn">Редактировать</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
        <div id="createUserModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('createUserModal')">&times;</span>
                <h2>Создать пользователя</h2>
                <form id="createUserForm">
                    <div class="userFormCreateInput">
                        <label>USER ID:</label>
                        <input placeholder="ID пользователя">
                    </div>
                    <div class="userFormCreateInput">
                        <label>Имя бота:</label>
                        <select>
                            <option>WbParser</option>
                            <option>VkParser</option>
                        </select>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Канал пользователя:</label>
                        <input placeholder="Канал пользователя">
                    </div>
                    <div class="userFormCreateInput">
                        <label>Подписка до:</label>
                        <input type="date" min="2023-01-01" max="2024-12-31">
                    </div>
                    <div class="userFormCreateInput">
                        <label>Причина выдачи:</label>
                        <input placeholder="Причина выдачи">
                    </div>
                    <button id="confirmSaveUserButton">Сохранить</button>
                </form>
            </div>
        </div>

        <div id="deleteUserModal" class="modal" style="z-index: 15;">
            <div class="modal-content deleteUser" >
                <span class="close" onclick="closeModal('deleteUserModal')">&times;</span>
                <h2>Удалить пользователя</h2>
                <p>Вы уверены, что хотите удалить этого пользователя?</p>
                <button>Удалить</button>
                <button onclick="closeModal('deleteUserModal')">Отмена</button>
            </div>
        </div>

        <div id="saveUserModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('saveUserModal')">&times;</span>
                <h2>Сохранить?</h2>
                <p>Вы уверены, что хотите сохранить?</p>
                <button id="confirmSaveUserButton">Сохранить</button>
                <button onclick="closeModal('saveUserModal')">Отмена</button>
            </div>
        </div>

        <div id="searchUserModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('searchUserModal')">&times;</span>
                <h2>Поиск</h2>
                <select>
                    <option>ID</option>
                    <option>USER ID</option>
                    <option>Имя бота</option>
                    <option>Канал</option>
                    <option>Дата окончания</option>
                </select>
                <input class="searchInput" placeholder="Введите запрос">
                <button>Найти</button>
            </div>
        </div>

        <div id="EditUserModal" class="modal">
            <div class="modal-content">
                    <span class="close" onclick="closeModal('EditUserModal')">&times;</span>
                    <h2>Редектирование</h2>
                    <form id="createUserForm">
                        <div class="userFormCreateInput">
                            <label>ID пользователя:</label>
                            <input placeholder="874719849" disabled>
                        </div>
                        <div class="userFormCreateInput">
                            <label>Имя бота:</label>
                            <select>
                                <option>WbParser</option>
                                <option>VkParser</option>
                            </select>
                        </div>
                        <div class="userFormCreateInput">
                            <label>Канал пользователя:</label>
                            <input placeholder="Канал пользователя">
                        </div>
                        <div class="userFormCreateInput">
                            <label>Подписка до:</label>
                            <input type="date" min="2023-01-01" max="2024-12-31">
                        </div>
                        <div class="userFormCreateInput">
                            <label>Причина выдачи:</label>
                            <input placeholder="Причина выдачи">
                        </div>
                    </form>
                    <button id="deleteUserModalbtn">Сохранить</button>
                    <button id="DeleteUserModalButton">Удалить пользователя</button>
            </div>
        </div>



        <script>
            function openModal(modalId) {
                document.getElementById(modalId).style.display = "block";
            }
            function closeModal(modalId) {
                document.getElementById(modalId).style.display = "none";
            }
            document.getElementById('createUserButton').addEventListener('click', () => {
                openModal('createUserModal');
            });

            document.getElementById('EditUserModalBtn').addEventListener('click', () => {
                openModal('EditUserModal');
            });
            
            document.getElementById('searchUserBtn').addEventListener('click', () => {
                openModal('searchUserModal');
            });

            document.getElementById('DeleteUserModalButton').addEventListener('click', () => {
                openModal('deleteUserModal');
            });
            document.getElementById('saveUserButton').addEventListener('click', () => {
                openModal('saveUserModal');
            });
            document.getElementById('deleteSubscriptionButton').addEventListener('click', () => {
                openModal('deleteSubscriptionModal');
            });
        </script>

</body>
</html>