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

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['zone_id'])) {
        $zone_id = $data['zone_id'];
        error_log("Attempting to delete zone with ID: $zone_id"); 
        $stmt = $pdo->prepare("DELETE FROM zones WHERE zone_id = :zone_id");
        $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(["message" => "ได้ลบข้อมูลของโซน ID $zone_id แล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Error deleting zone: " . print_r($errorInfo, true));
            echo json_encode(["message" => "ไม่สามารถลบข้อมูลได้", "error" => $errorInfo], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["message" => "ไม่มีข้อมูล zone_id"]);
    }
}