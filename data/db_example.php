<?php
$DBHOST = "localhost";
$DBUSER = "CHANGE_ME";
$DBPASS = 'CHANGE_ME';
$DBNAME = "CHANGE_ME";
$dsn = "mysql:host=$DBHOST;dbname=$DBNAME;charset=utf8";
try {
    $pdo = new PDO($dsn, $DBUSER, $DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>
