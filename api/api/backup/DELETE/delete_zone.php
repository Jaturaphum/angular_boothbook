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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['zone_name'])) {
        $zone_name = $_POST['zone_name'];
        $stmt = $pdo->prepare("DELETE FROM zones WHERE zone_name = :zone_name");
        $stmt->bindParam(':zone_name', $zone_name, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo json_encode(["message" => "ได้ลบข้อมูลของโซนชื่อ $zone_name แล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode(["message" => "ไม่สามารถลบข้อมูลได้", "error" => $errorInfo], JSON_UNESCAPED_UNICODE);
        }
    }
}
