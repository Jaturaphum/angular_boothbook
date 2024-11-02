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

if (isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];
    $sql = "SELECT booths.booth_name AS booth_name, zones.zone_name AS zone_name, bookings.booth_price, bookings.booking_status
        FROM bookings
        INNER JOIN booths ON bookings.booth_id = booths.booth_id
        INNER JOIN zones ON booths.zone_id = zones.zone_id
        WHERE bookings.member_id = :member_id";

    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($bookings) {
            echo json_encode($bookings);
        } else {
            echo json_encode(["message" => "ไม่พบรายการการจองสำหรับสมาชิกนี้"]);
        }
    } catch (PDOException $e) {
        // echo json_encode(["message" => "เกิดข้อผิดพลาดในการดึงข้อมูลการจอง: " . $e->getMessage()]);
    }
}
?>
