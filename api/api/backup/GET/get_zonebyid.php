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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['zone_id']) && !empty($_POST['zone_id'])) {
        $zone_id = $_POST['zone_id'];

        try {
            $sql = "SELECT z.zone_id, z.zone_name, z.zone_info, COUNT(b.booth_id) AS booth_count 
                    FROM zones z
                    LEFT JOIN booths b ON z.zone_id = b.zone_id
                    WHERE z.zone_id = :zone_id
                    GROUP BY z.zone_id, z.zone_name, z.zone_info";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->execute();

            $zone = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($zone) {
                echo json_encode([
                    "status" => "success",
                    "zone" => $zone
                ], JSON_UNESCAPED_UNICODE); 
            } else {
                echo json_encode(["message" => "ไม่พบข้อมูลโซนที่มี ID นี้"], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            echo json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["message" => "กรุณาระบุ zone_id"], JSON_UNESCAPED_UNICODE);
    }
}
?>
