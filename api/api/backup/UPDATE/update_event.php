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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_start_date = ?, event_end_date = ? WHERE event_id = ?");
            $stmt->execute([$event_name, $event_start_date, $event_end_date, $event_id]);
            echo json_encode(["message" => "อัปเดตอีเวนต์เรียบร้อย"]);
        } catch (PDOException $e) {
            echo json_encode(["message" => "Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["message" => "เกิดผิดพลาดในการเชื่อมต่อฐานข้อมูล"]);
    }
}
