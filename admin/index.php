<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Управление Подписками</title>
        <link rel="stylesheet" href="../css/styles.css" type="text/css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <?php
}
ini_set('max_execution_time', 600);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
function check()
{
    if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] == $_SERVER['REMOTE_ADDR']) {
        if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
            $username = $_SESSION['username'];
            $password = $_SESSION['password'];

            require("/var/www/monahat/data/db.php");

            try {
                $pdo = new PDO($dsn, $DBUSER, $DBPASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                exit();
            }

            try {
                $stmt = $pdo->prepare("SELECT password, ip_address FROM admin WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    return true;
                }
                header('Location: /');
                exit();
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
require_once './class/DatabaseConfig.php';
require_once './class/Database.php';
require_once './class/User.php';
require_once './functions/searchUsers.php';
use Admin\DatabaseConfig;
use Admin\Database;

$config = new DatabaseConfig("localhost", "sudo", '', "monahat", "utf8mb4");
$database = new Database($config);
$users = $database->getAllUsers();
$action = $_POST['action'] ?? '1';
$action_get = $_GET['action'] ?? '1';
if ($action === 'adduser') {
    $userId = $_POST['userId'] ?? '';
    $botName = $_POST['botname'] ?? '';
    $subscriptionEndDate = $_POST['subsenddate'] ?? '';
    $subscriptionIssue = $_POST['subsissue'] ?? '';
    $userChannel = $_POST['userchannel'] ?? '';
    $usercreate_out = $database->createUserAndSubscription($userId, $botName, $subscriptionEndDate, $subscriptionIssue, $userChannel);
    if ($usercreate_out) {
        $out_success = 'true';
        $out_message = "Пользователь успешно создан";
    } else {
        $out_success = 'false';
        $out_message = "Произошла ошибка!";
    }
    echo(json_encode(array("success" => $out_success, "content" => $out_message), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
if ($action === 'getuserlist') {
    echo(json_encode(array("success" => 'true', "content" => $users), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
if ($action === 'search') {
    $search_in = [
        $_POST['search_key'] => $_POST['search_request'],
    ];
    $results = searchUsers($users, $search_in);
    if ($results) {
        echo(json_encode(array("success" => 'true', "content" => $results), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

}
if (!$_SESSION['page']) {
    $_SESSION['page'] = '0';
}
if ($action_get === 'page') {
    if ($_GET['page'] === 'next_page') {
        ++$_SESSION['page'];
        header('Location: /admin');
        exit;
    }
    if ($_GET['page'] === 'prev_page') {
        if ($_SESSION['page'] !== '0') {
            --$_SESSION['page'];
            header('Location: /admin');
            exit;
        }
    }
    if ($_GET['page'] === 'main') {
        $_SESSION['page'] = '0';
        header('Location: /admin');
        exit;
    }
}

if ($action === "updateuser") {
    $userId = $_POST['userId'];
    $botName = $_POST['botName'];
    $subscriptionEndDate = $_POST['subscriptionEndDate'];
    $subscriptionIssue = $_POST['subscriptionIssue'];
    $userChannel = $_POST['userChannel'];
    $userupdate = $database->updateUserAndSubscription($userId, $botName, $subscriptionEndDate, $subscriptionIssue, $userChannel);

    if ($userupdate) {
        echo(json_encode(array("success" => 'true', "content" => 'Данные обновлены!'), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    } else {
        echo(json_encode(array("success" => 'false', "content" => 'Что-то пошло не так!'), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
if ($action === "getuser") {
    $search_in = [
        "subscriptionId" => $_POST['userId'],
    ];
    $results = searchUsers($users, $search_in);
    if ($results) {
        echo(json_encode(array("success" => 'true', "content" => $results), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <body>
    <header>
        <a href="/" class="title">TG_ADMINER</a>
        <div class="button-block">
            <a href="/logout" role="button" id="logoutButton">Выход</a>
        </div>
    </header>
    <main>
        <div class="headerbuttons">
            <button id="createUserButton">Создать</button>
            <button id="searchUserBtn">Поиск</button>
            <button id="go_main">На главную</button>
        </div>
        <!--Возврат на главную по нажатию кнопки-->
        <script>document.getElementById('go_main').addEventListener('click', function () {
                location.reload();
            });</script>
        <div class="main-block">
            <h2>Пользователи</h2>
            <div class="table-user">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>USER ID</th>
                        <th>Имя бота</th>
                        <th>Канал</th>
                        <th>Дата окончания</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody id="userTableBody">
                    <?php
                    $strnum = $_SESSION['page'];
                    $start = $strnum * 10;
                    $end = min($start + 10, count($users));
                    for ($i = $start; $i < $end; $i++) {
                        // Проверяем и выводим поля объекта
                        echo '<tr>';
                        echo '<td>' . ($users[$i]->subscriptionId ?? '') . '</td>';
                        echo '<td>' . ($users[$i]->userId ?? '') . '</td>';
                        echo '<td>' . ($users[$i]->botName ?? '') . '</td>';
                        echo '<td>' . ($users[$i]->userChannel ?? '') . '</td>';
                        // Проверяем и выводим дату, если она существует
                        if ($users[$i]->subscriptionEndDate instanceof DateTime) {
                            echo "<th>" . $users[$i]->subscriptionEndDate->format('Y-m-d') . "</th>";
                        } else {
                            echo "</th>";
                        }
                        echo "<td><button class='editUserButton' data-id='" . ($users[$i]->subscriptionId ?? '') . "' id='edit-" . ($users[$i]->subscriptionId ?? '') . "'>Редактировать</button></td>";
                        echo "</tr>";
                    }
                    ?>
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
                        <label>ID пользователя:</label>
                        <input name="userId" placeholder="ID пользователя" required>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Имя бота:</label>
                        <select name="botname" required>
                            <option value="WbParser">WbParser</option>
                            <option value="VkParser">VkParser</option>
                        </select>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Канал пользователя:</label>
                        <input name="userchannel" placeholder="Канал пользователя" required>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Подписка до:</label>
                        <input type="date" name="subsenddate" min="2023-01-01" max="2024-12-31" required>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Причина выдачи:</label>
                        <input name="subsissue" placeholder="Причина выдачи" required>
                    </div>
                    <button type="button" id="confirmSaveUserButton">Сохранить</button>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('confirmSaveUserButton').addEventListener('click', function () {
                            var formData = new FormData(document.getElementById('createUserForm'));

                            formData.append('action', 'adduser');

                            var xhr = new XMLHttpRequest();

                            xhr.open('POST', '/admin/index.php', true);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                            xhr.onload = function () {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    // Успешный ответ
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.success === 'true') {
                                        alert('Успех: ' + response.content);
                                        location.reload()
                                    } else {
                                        alert('Ошибка: ' + response.content);
                                    }
                                } else {
                                    // Ошибка при запросе
                                    alert('Ошибка при отправке данных.');
                                }
                            };

                            // Отправляем запрос
                            xhr.send(formData);
                        });
                    });
                </script>
            </div>
        </div>

        <div id="deleteUserModal" class="modal" style="z-index: 15;">
            <div class="modal-content deleteUser">
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
                <form id="search_form" method="post">
                    <select id="search_key">
                        <option value="subscriptionId">ID</option>
                        <option value="userId">USER ID</option>
                        <option value="botName">Имя бота</option>
                        <option value="userChannel">Канал</option>
                        <option value="subscriptionEndDate">Дата окончания</option>
                    </select>
                    <input class="searchInput" id="search_request" placeholder="Введите запрос">
                    <button type="submit">Найти</button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('search_form').addEventListener('submit', function (event) {
                event.preventDefault();
                var search_Key = document.getElementById('search_key').value;
                var search_request = document.getElementById('search_request').value;
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/admin/index.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const result = JSON.parse(xhr.responseText);
                        searchresult_out(result);
                    }
                }

                xhr.send(`action=search&search_key=${encodeURIComponent(search_Key)}&search_request=${encodeURIComponent(search_request)}`);
            });

            function searchresult_out(result) {
                const result_out = document.getElementById('userTableBody');
                result_out.innerHTML = '';
                if (result.success === "true" && Array.isArray(result.content)) {
                    result.content.forEach(user => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${user.subscriptionId ?? ''}</td>
                        <td>${user.userId ?? ''}</td>
                        <td>${user.botName ?? ''}</td>
                        <td>${user.userChannel ?? ''}</td>
                        <th>${user.subscriptionEndDate.date.split(' ')[0]}</th>
                        <td><button class='editUserButton' data-id='${user.subscriptionId ?? ''}' id='edit-${user.subscriptionId ?? ''}'>Редактировать</button></td>
                    `;
                        result_out.appendChild(tr);

                        document.getElementById(`edit-${user.subscriptionId ?? ''}`).addEventListener('click', function () {
                            openModal('EditUserModal');
                        });
                    });
                }
                closeModal('searchUserModal');
            }
        </script>
        <div id="EditUserModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('EditUserModal')">&times;</span>
                <h2>Редактирование</h2>
                <form id="createUserForm">
                    <div class="userFormCreateInput">
                        <label>ID пользователя:</label>
                        <input id="input_userId" placeholder="" disabled>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Имя бота:</label>
                        <select id="select_botName">
                            <option>WbParser</option>
                            <option>VkParser</option>
                        </select>
                    </div>
                    <div class="userFormCreateInput">
                        <label>Канал пользователя:</label>
                        <input id="userChannel" placeholder="Канал пользователя">
                    </div>
                    <div class="userFormCreateInput">
                        <label>Подписка до:</label>
                        <input id="input_date" type="date">
                    </div>
                    <div class="userFormCreateInput">
                        <label>Причина выдачи:</label>
                        <input id="input_issue" placeholder="Причина выдачи">
                    </div>
                </form>
                <button id="updateuser">Сохранить</button>
                <button id="DeleteUserModalButton">Удалить пользователя</button>
            </div>
        </div>
        <script>
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "block";
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = "none";
                }
            }

            const createUserButton = document.getElementById('createUserButton');
            if (createUserButton) {
                createUserButton.addEventListener('click', () => {
                    openModal('createUserModal');
                });
            }

            const searchUserBtn = document.getElementById('searchUserBtn');
            if (searchUserBtn) {
                searchUserBtn.addEventListener('click', () => {
                    openModal('searchUserModal');
                });
            }

            const deleteUserModalButton = document.getElementById('DeleteUserModalButton');
            if (deleteUserModalButton) {
                deleteUserModalButton.addEventListener('click', () => {
                    openModal('deleteUserModal');
                });
            }

            const confirmSaveUserButton = document.getElementById('confirmsaveUserButton');
            if (confirmSaveUserButton) {
                confirmSaveUserButton.addEventListener('click', () => {
                    openModal('saveUserModal');
                });
            }

            const deleteSubscriptionButton = document.getElementById('deleteSubscriptionButton');
            if (deleteSubscriptionButton) {
                deleteSubscriptionButton.addEventListener('click', () => {
                    openModal('deleteSubscriptionModal');
                });
            }
        </script>

        <div class="navigation">

            <a class="null-page" style="text-decoration: none" href="/admin/index.php?action=page&page=main">
                На начало
            </a>

            <?php
            if ($_SESSION['page'] > 0) {
                ?>
                <a class="prev-page" style="text-decoration: none" href="/admin/index.php?action=page&page=prev_page">
                    <span><svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="48" d="M328 112L184 256l144 144"></path></svg></span>
                </a>
            <?php } ?>
            <?php
            $max_page = ceil(count($users) / 11) - 1;
            if ($_SESSION['page'] >= 0 && $_SESSION['page'] <= $max_page) {
                ?>
                <a class="next-page" href="/admin/index.php?action=page&page=next_page">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="48" d="M184 112l144 144-144 144"></path></svg>
                </a>
            <?php } ?>
        </div>
    </main>
    <footer>
        <p class="footer-first">&copy; 2024 Разработано командой&nbsp;<a class="animiku" href="https://animiku.cc">ANIMIKU</a>.
        </p>
        <p class="footer-second">Совместно с&nbsp;<a href="https://github.com/duckpicker">duckpicker</a>.</p>
        <p></p>
    </footer>
    <script>
        document.querySelectorAll('.editUserButton').forEach(function(button) {
            button.addEventListener('click', function() {

                var userId = this.getAttribute('data-id');

                var postData = new URLSearchParams();
                postData.append('action', 'getuser');
                postData.append('userId', userId);

                fetch('/admin/index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: postData.toString()
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);

                        var userData = data.content[0];

                        // Найти форму
                        var form = document.getElementById('createUserForm');

                        // Убедимся, что форма найдена
                        if (!form) {
                            console.error('Форма не найдена!');
                            return;
                        }

                        // Найти и заполнить поля формы
                        var userIdInput = document.getElementById('input_userId');
                        if (userIdInput) {
                            userIdInput.value = userData.userId;
                            userIdInput.disabled = true;
                        }

                        var botNameSelect = document.getElementById('select_botName');
                        if (botNameSelect) botNameSelect.value = userData.botName;

                        var userChannelInput = document.getElementById('userChannel');
                        if (userChannelInput) userChannelInput.value = userData.userChannel;

                        var subscriptionEndDateInput = document.getElementById('input_date');
                        if (subscriptionEndDateInput) subscriptionEndDateInput.value = userData.subscriptionEndDate.date.split(' ')[0];

                        var subscriptionIssueInput = document.getElementById('input_issue');
                        if (subscriptionIssueInput) subscriptionIssueInput.value = userData.subscriptionIssue;

                        // Открыть модальное окно
                        openModal('EditUserModal');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });

        // Обработка кнопки "Сохранить"
        document.getElementById('updateuser').addEventListener('click', function() {
            // Найти форму
            var form = document.getElementById('createUserForm');

            // Собрать данные из формы
            var postData = new URLSearchParams();
            postData.append('action', 'updateuser');
            postData.append('userId', document.getElementById('input_userId').value);
            postData.append('botName', document.getElementById('select_botName').value);
            postData.append('subscriptionEndDate', document.getElementById('input_date').value);
            postData.append('subscriptionIssue', document.getElementById('input_issue').value);
            postData.append('userChannel', document.getElementById('userChannel').value);

            // Отправить POST запрос на сервер
            fetch('/admin/index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: postData.toString()
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success === 'true') {
                        alert('Данные обновлены!');
                        location.reload();
                    } else {
                        alert('Что-то пошло не так: ' + data.content);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка при отправке данных.');
                });
        });

    </script>
    </body>
    </html>
<?php } ?>