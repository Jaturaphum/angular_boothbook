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
    // เชื่อมต่อฐานข้อมูล
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // ตรวจสอบว่ามีพารามิเตอร์ booth_id ในคำขอแบบ POST หรือไม่
        if (isset($_POST['booth_id']) && !empty($_POST['booth_id']) && is_numeric($_POST['booth_id'])) {
            $booth_id = intval($_POST['booth_id']); // แปลงค่าเป็นตัวเลขเพื่อความปลอดภัย

            // ดึงข้อมูลบูธตาม booth_id
            $sql = "SELECT booth_id, booth_name, booth_size, booth_status, booth_price, zone_id 
                    FROM booths 
                    WHERE booth_id = :booth_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':booth_id', $booth_id, PDO::PARAM_INT);

            $stmt->execute();
            $booth = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booth) {
                // ถ้าพบข้อมูลบูธ
                echo json_encode([
                    "booth" => $booth
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // ไม่พบข้อมูลบูธตาม booth_id
                echo json_encode([
                    "status" => "error",
                    "message" => "ไม่พบข้อมูลบูธที่มี ID นี้"
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // กรณีไม่มี booth_id หรือ booth_id ไม่ถูกต้อง
            echo json_encode([
                "status" => "error",
                "message" => "กรุณาระบุ booth_id ที่ถูกต้อง"
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        // ดักจับข้อผิดพลาดที่เกิดจากการ query ฐานข้อมูล
        echo json_encode([
            "status" => "error",
            "message" => "Error: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
