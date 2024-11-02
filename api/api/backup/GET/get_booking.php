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

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $stmt = $pdo->prepare("
        SELECT 
        bookings.booking_id, bookings.booking_date, bookings.booth_id, 
        bookings.member_id, bookings.event_id, bookings.status,
        members.title, members.first_name
        FROM bookings
        INNER JOIN members ON bookings.member_id = members.member_id
        WHERE bookings.booking_id = :booking_id
    ");
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(["message" => "ไม่พบข้อมูลการจองสำหรับ booking_id นี้"]);
    }
}
