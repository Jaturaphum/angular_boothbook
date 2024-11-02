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
    // รับค่า event_id
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);

    // ตรวจสอบว่า event_id เป็นตัวเลข
    if (empty($event_id) || !is_numeric($event_id)) {
        echo json_encode(["message" => "ID อีเวนต์ไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($pdo) {
        try {
            // ลบข้อมูลที่มี event_id ที่ระบุ
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
            $stmt->execute([$event_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "ลบอีเวนต์เรียบร้อย"], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["message" => "ไม่พบอีเวนต์ที่ต้องการลบ"], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            echo json_encode(["message" => "ข้อผิดพลาด: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["message" => "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล"], JSON_UNESCAPED_UNICODE);
    }
}
?>
