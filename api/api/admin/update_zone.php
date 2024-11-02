<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $zone_id = $input['zone_id'];
    $zone_name = $input['zone_name'];
    $zone_info = $input['zone_info'];
    $booth_count = $input['booth_count'];
    $event_id = $input['event_id'];

    $sql = "UPDATE zones 
            SET zone_name = :zone_name, zone_info = :zone_info, booth_count = :booth_count, event_id = :event_id
            WHERE zone_id = :zone_id";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        ':zone_name' => $zone_name,
        ':zone_info' => $zone_info,
        ':booth_count' => $booth_count,
        ':event_id' => $event_id,
        ':zone_id' => $zone_id
    ])) {
        echo json_encode(["message" => "อัปเดตโซนสำเร็จ"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["message" => "เกิดข้อผิดพลาดในการอัปเดตโซน"], JSON_UNESCAPED_UNICODE);
    }
}
