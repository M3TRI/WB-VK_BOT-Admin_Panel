<?php
namespace Admin;

use PDO;
use PDOException;

// User.php
class Database
{
    private PDO $pdo;
    public function __construct(DatabaseConfig $config)
    {

        try {
            $dsn = $config->getDsn();
            $user = $config->getUser();
            $password = $config->getPassword();
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }


    public function getAllUsers(): array
    {
        $users = [];

        try {
            $query = "SELECT * FROM subscriptions";
            $stmt = $this->pdo->query($query);
            $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($subscriptions as $row) {
                $userChannel = "";

                $stmt2 = $this->pdo->prepare("SELECT user_channel FROM users WHERE user_id = :user_id AND bot_name = :bot_name");
                $stmt2->execute([
                    ':user_id' => (int)$row['user_id'],
                    ':bot_name' => (string)$row['bot_name']
                ]);

                $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                if ($result2) {
                    $userChannel = (string)$result2['user_channel'];
                }

                $users[] = new User(
                    (int)$row['subscription_id'],
                    (int)$row['user_id'],
                    (string)$row['bot_name'],
                    $userChannel,
                    new \DateTime($row['subscription_end_date']),
                    (string)$row['subscription_issue']
                );
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        return $users;
    }
    public function createUserAndSubscription(int $userId, string $botName, string $subscriptionEndDate, string $subscriptionIssue, string $userChannel ): bool
    {
        try {
            // Начинаем транзакцию
            $this->pdo->beginTransaction();

            // Вставляем или обновляем данные в таблицу users
            $query1 = "INSERT INTO users (user_id, bot_name, user_channel)
                   VALUES (:user_id, :bot_name, :user_channel)
                   ON DUPLICATE KEY UPDATE
                       user_channel = VALUES(user_channel)";
            $stmt1 = $this->pdo->prepare($query1);
            $stmt1->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName,
                ':user_channel' => $userChannel
            ]);
            // Вставляем или обновляем данные в таблицу subscriptions
            $query2 = "INSERT INTO subscriptions (user_id, bot_name, subscription_end_date, subscription_issue)
                   VALUES (:user_id, :bot_name, :subscription_end_date, :subscription_issue)
                   ON DUPLICATE KEY UPDATE
                       subscription_end_date = VALUES(subscription_end_date),
                       subscription_issue = VALUES(subscription_issue)";
            $stmt2 = $this->pdo->prepare($query2);
            $stmt2->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName,
                ':subscription_end_date' => $subscriptionEndDate,
                ':subscription_issue' => $subscriptionIssue
            ]);
            // Подтверждаем транзакцию
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    public function updateUserAndSubscription(int $userId, string $botName, string $subscriptionEndDate, string $subscriptionIssue, string $userChannel): bool
    {
        try {
            // Начинаем транзакцию
            $this->pdo->beginTransaction();

            // Проверяем, существует ли запись в таблице users
            $checkUserQuery = "
            SELECT COUNT(*)
            FROM users
            WHERE user_id = :user_id AND bot_name = :bot_name";

            $checkStmt = $this->pdo->prepare($checkUserQuery);
            $checkStmt->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName
            ]);
            $userExists = $checkStmt->fetchColumn() > 0;

            // Обновляем данные в таблице users
            $updateUserQuery = "
            UPDATE users
            SET user_channel = :user_channel
            WHERE user_id = :user_id AND bot_name = :bot_name";

            $stmt1 = $this->pdo->prepare($updateUserQuery);
            $stmt1->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName,
                ':user_channel' => $userChannel
            ]);

            // Проверка после обновления
            $checkUserUpdatedQuery = "
            SELECT bot_name, user_channel
            FROM users
            WHERE user_id = :user_id";

            $checkUserUpdatedStmt = $this->pdo->prepare($checkUserUpdatedQuery);
            $checkUserUpdatedStmt->execute([
                ':user_id' => $userId
            ]);
            $updatedUser = $checkUserUpdatedStmt->fetch(PDO::FETCH_ASSOC);

            // Обновляем данные в таблице subscriptions
            $updateSubscriptionQuery = "
            UPDATE subscriptions
            SET subscription_end_date = :subscription_end_date,
                subscription_issue = :subscription_issue
            WHERE user_id = :user_id AND bot_name = :bot_name";

            $stmt2 = $this->pdo->prepare($updateSubscriptionQuery);
            $stmt2->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName,
                ':subscription_end_date' => $subscriptionEndDate,
                ':subscription_issue' => $subscriptionIssue
            ]);

            // Проверка после обновления
            $checkSubscriptionUpdatedQuery = "
            SELECT subscription_end_date, subscription_issue
            FROM subscriptions
            WHERE user_id = :user_id AND bot_name = :bot_name";

            $checkSubscriptionUpdatedStmt = $this->pdo->prepare($checkSubscriptionUpdatedQuery);
            $checkSubscriptionUpdatedStmt->execute([
                ':user_id' => $userId,
                ':bot_name' => $botName
            ]);
            $updatedSubscription = $checkSubscriptionUpdatedStmt->fetch(PDO::FETCH_ASSOC);

            // Подтверждаем транзакцию
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        } catch (Exception $e) {
            // Обработка других ошибок
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

}