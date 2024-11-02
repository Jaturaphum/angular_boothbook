<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json; charset=utf-8');

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {

        $sql = "SELECT booth_id, booth_name, booth_size, booth_status, booth_price, zone_id FROM booths";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        

        $booths = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($booths) {
            echo json_encode([
                "status" => "success",
                "booths" => $booths
            ], JSON_UNESCAPED_UNICODE); 
        } else {
            echo json_encode(["message" => "ไม่พบข้อมูลบูธ"], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>
