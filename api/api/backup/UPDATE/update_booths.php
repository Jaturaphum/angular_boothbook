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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบข้อมูลที่ได้รับ
    if (!isset($_POST['booth_id'], $_POST['booth_name'], $_POST['booth_size'], $_POST['booth_price']) ||
        empty($_POST['booth_id']) || empty($_POST['booth_name']) || empty($_POST['booth_size']) || empty($_POST['booth_price'])) {
        echo json_encode(["message" => "ข้อมูลที่ได้รับไม่ครบถ้วนหรือไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $booth_id = $_POST['booth_id'];
    $booth_name = $_POST['booth_name'];
    $booth_size = $_POST['booth_size'];
    $booth_price = $_POST['booth_price'];

    try {

        $stmt = $pdo->prepare("SELECT * FROM booths WHERE booth_id = :booth_id");
        $stmt->bindParam(':booth_id', $booth_id, PDO::PARAM_INT);
        $stmt->execute();
        $booth = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booth) {
            echo json_encode(["message" => "ไม่พบบูธที่มี ID นี้"], JSON_UNESCAPED_UNICODE);
            exit();
        }


        $sql = "UPDATE booths SET booth_name = :booth_name, booth_size = :booth_size, booth_price = :booth_price WHERE booth_id = :booth_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booth_name', $booth_name);
        $stmt->bindParam(':booth_size', $booth_size);
        $stmt->bindParam(':booth_price', $booth_price);
        $stmt->bindParam(':booth_id', $booth_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["message" => "ข้อมูลบูธอัพเดทเรียบร้อยแล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["message" => "เกิดข้อผิดพลาดในการอัพเดทข้อมูลบูธ"], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>
