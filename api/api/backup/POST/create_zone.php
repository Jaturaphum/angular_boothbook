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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zone_name = $_POST['zone_name'];
    $zone_info = $_POST['zone_info'];
    $booth_count = $_POST['booth_count'];
    $event_id = $_POST['event_id'];

    $sql = "INSERT INTO zones (zone_name, zone_info, booth_count, event_id) 
            VALUES (:zone_name, :zone_info, :booth_count, :event_id)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        ':zone_name' => $zone_name,
        ':zone_info' => $zone_info,
        ':booth_count' => $booth_count,
        ':event_id' => $event_id
    ])) {
        echo json_encode(["message" => "Zone created successfully"]);
    } else {
        echo json_encode(["message" => "Failed to create zone"]);
    }
}