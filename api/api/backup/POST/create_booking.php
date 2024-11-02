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
    exit(json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $required_fields = ['member_id', 'booth_id', 'product_info', 'event_id'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) exit(json_encode(["message" => "ข้อมูลไม่ครบ"]));
    }
    
    $stmt = $pdo->prepare("SELECT event_start_date FROM events WHERE event_id = ?");
    $stmt->execute([$_POST['event_id']]);
    $event = $stmt->fetch();

    if (!$event) exit(json_encode(["message" => "ไม่พบข้อมูลงาน"]));

    $today = new DateTime();
    $event_date = new DateTime($event['event_start_date']);
    if ($today->diff($event_date)->days < 5) {
        exit(json_encode(["message" => "ต้องชำระเงินอย่างน้อย 5 วันก่อนวันงาน"]));
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE member_id = ? AND booking_status != 'ยกเลิกการจอง'");
$stmt->execute([$_POST['member_id']]);
if ($stmt->fetchColumn() >= 4) {
    exit(json_encode(["message" => "ไม่สามารถจองบูธเพิ่มได้"]));
}


    $stmt = $pdo->prepare("SELECT booth_status, booth_price FROM booths WHERE booth_id = ?");
    $stmt->execute([$_POST['booth_id']]);
    $booth = $stmt->fetch();

    if (!$booth || $booth['booth_status'] != 'ว่าง') {
        exit(json_encode(["message" => "บูธไม่ว่างหรือไม่สามารถจองได้"]));
    }

    $stmt = $pdo->prepare("INSERT INTO bookings (booth_id, product_info, member_id, event_id, booth_price, booking_status, booking_date) 
                           VALUES (?, ?, ?, ?, ?, 'จอง', NOW())");
    $stmt->execute([$_POST['booth_id'], $_POST['product_info'], $_POST['member_id'], $_POST['event_id'], $booth['booth_price']]);

    $stmt = $pdo->prepare("UPDATE booths SET booth_status = 'อยู่ระหว่างตรวจสอบ' WHERE booth_id = ?");
    $stmt->execute([$_POST['booth_id']]);

    exit(json_encode(["message" => "จองบูธสำเร็จ"]));
}
