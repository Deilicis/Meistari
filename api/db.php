<?php
// api/db.php
$host = 'localhost';
$db   = 'meistari_db';
$user = 'root';     // Default XAMPP user
$pass = '';         // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, show error (remove this line in production!)
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>