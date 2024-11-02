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

        $sql = "SELECT zone_id, zone_name, zone_info, booth_count FROM zones";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        

        $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($zones) {
            echo json_encode([
                "status" => "success",
                "zones" => $zones
            ], JSON_UNESCAPED_UNICODE); 
        } else {
            echo json_encode(["message" => "ไม่พบข้อมูลโซน"], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>
