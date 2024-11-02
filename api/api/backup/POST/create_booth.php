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
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booth_name = $_POST['booth_name'];
    $booth_size = $_POST['booth_size'];
    $booth_price = $_POST['booth_price'];
    $zone_id = $_POST['zone_id'];
    $booth_status = "ว่าง";


    $stmt = $pdo->prepare("SELECT COUNT(*) as current_booth_count FROM booths WHERE zone_id = :zone_id");
    $stmt->bindParam(':zone_id', $zone_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_booth_count = $result['current_booth_count'];


    $stmt = $pdo->prepare("SELECT booth_count FROM zones WHERE zone_id = :zone_id");
    $stmt->bindParam(':zone_id', $zone_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_booth_count = $result['booth_count'];


    if ($current_booth_count >= $max_booth_count) {
        echo json_encode(["message" => "ไม่สามารถสร้างบูธได้: โซนนี้เต็มแล้ว"], JSON_UNESCAPED_UNICODE);
        exit();
    }


    $sql = "INSERT INTO booths (booth_name, booth_size, booth_price, booth_status, zone_id) 
            VALUES (:booth_name, :booth_size, :booth_price, :booth_status, :zone_id)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booth_name', $booth_name);
        $stmt->bindParam(':booth_size', $booth_size);
        $stmt->bindParam(':booth_price', $booth_price);
        $stmt->bindParam(':booth_status', $booth_status);
        $stmt->bindParam(':zone_id', $zone_id);

        $stmt->execute();
        echo json_encode(["message" => "เพิ่มบูธเรียบร้อยแล้ว!"], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
}
