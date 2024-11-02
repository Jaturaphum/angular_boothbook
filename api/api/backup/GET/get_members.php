<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT member_id, title, first_name, last_name, phone_number, email FROM `members`");
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($members, JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(["message" => "การดึงข้อมูลล้มเหลว: " . $e->getMessage()]);
    }
}