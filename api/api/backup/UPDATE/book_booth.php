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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['member_id'], $_POST['booth_id'], $_POST['booth_price'], $_POST['product_info'], $_POST['event_id'])) {
        echo json_encode(["message" => "ข้อมูลไม่ครบถ้วน"]);
        exit;
    }

    $member_id = $_POST['member_id'];
    $booth_id = $_POST['booth_id'];
    $price = $_POST['booth_price'];
    $sale_information = $_POST['product_info'];
    $event_id = $_POST['event_id'];
    $stmt = $pdo->prepare("SELECT booth_status FROM booths WHERE booth_id = ?");
    $stmt->execute([$booth_id]);
    $booth = $stmt->fetch();

    if ($booth && $booth['booth_status'] == 'ว่าง') {
        $sql = "INSERT INTO bookings (booking_date, booth_id, booth_price, product_info, member_id, event_id, status) 
        VALUES (NOW(), ?, ?, ?, ?, ?, 'จอง')";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$booth_id, $price, $sale_information, $member_id, $event_id])) {
            $updateBooth = $pdo->prepare("UPDATE booths SET booth_status = 'อยู่ระหว่างตรวจสอบ' WHERE booth_id = ?");
            $updateBooth->execute([$booth_id]);
    
            echo json_encode(["message" => "จองบูธสำเร็จ"]);
        } else {
            echo json_encode(["message" => "เกิดข้อผิดพลาดในการจองบูธ"]);
        }
    }
}
