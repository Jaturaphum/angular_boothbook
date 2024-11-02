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
    $fields = ['booking_id', 'member_id', 'booth_id', 'booth_price', 'payment_slip'];
    
    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            exit(json_encode(["message" => "ข้อมูลไม่ครบ: $field"]));
        }
    }

    $stmt = $pdo->prepare("
        SELECT b.booking_status, b.booking_date, e.event_start_date
        FROM bookings b
        JOIN events e ON b.event_id = e.event_id
        WHERE b.booking_id = ? AND b.booth_id = ? AND b.member_id = ?
    ");
    $stmt->execute([$_POST['booking_id'], $_POST['booth_id'], $_POST['member_id']]);
    $booking = $stmt->fetch();

    if (!$booking || $booking['booking_status'] != 'จอง') {
        exit(json_encode(["message" => "ไม่พบการจองหรือสถานะการจอง"]));
    }

    $days_diff = (new DateTime())->diff(new DateTime($booking['event_start_date']))->days;
    $is_future_event = (new DateTime() < new DateTime($booking['event_start_date']));

    if ($days_diff < 5 && $is_future_event || $_POST['booth_price'] < 5) {
        $pdo->prepare("UPDATE bookings SET booking_status = 'ยกเลิกการจอง' WHERE booking_id = ? AND booth_id = ? AND member_id = ?")
            ->execute([$_POST['booking_id'], $_POST['booth_id'], $_POST['member_id']]);
        $pdo->prepare("UPDATE booths SET booth_status = 'ว่าง' WHERE booth_id = ?")
            ->execute([$_POST['booth_id']]);
        $message = $days_diff < 5 ? " ไม่สามารถชำรพได้ ต้องชำระอย่างน้อย 5 วันก่อนงาน" : "ชำระเงินไม่ถูกต้อง จองถูกยกเลิก";
        exit(json_encode(["message" => $message]));
    }

    if ($days_diff >= 5 && $is_future_event) {
        $pdo->prepare("UPDATE bookings SET booking_status = 'ชำระเงิน', payment_slip = ?, booth_price = ?, payment_date = NOW() WHERE booking_id = ? AND booth_id = ? AND member_id = ?")
            ->execute([$_POST['payment_slip'], $_POST['booth_price'], $_POST['booking_id'], $_POST['booth_id'], $_POST['member_id']]);
        exit(json_encode(["message" => "ชำระเงินสำเร็จ"]));
    }
}
