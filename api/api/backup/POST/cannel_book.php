<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

try {
    header('Content-Type: application/json');
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_POST['booking_id'])) {
        echo json_encode(["message" => "ข้อมูลไม่ครบถ้วน"]);
        exit();
    }

    $booking_id = $_POST['booking_id'];

    $pdo->beginTransaction();

    $selectBooking = "SELECT booth_id, booking_status FROM bookings WHERE booking_id = :booking_id";
    $stmtSelect = $pdo->prepare($selectBooking);
    $stmtSelect->execute([':booking_id' => $booking_id]);
    $booking = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $pdo->rollBack();
        echo json_encode(["message" => "ไม่พบการจองที่ระบุ"]);
        exit();
    }

    if ($booking['booking_status'] === 'ยกเลิกการจอง') {
        $pdo->rollBack();
        echo json_encode(["message" => "การจองนี้ได้ถูกยกเลิกไปแล้ว"]);
        exit();
    }

    $booth_id = $booking['booth_id'];

    $updateBooking = "UPDATE bookings SET booking_status = 'ยกเลิกการจอง' WHERE booking_id = :booking_id";
    $stmtBooking = $pdo->prepare($updateBooking);
    if (!$stmtBooking->execute([':booking_id' => $booking_id])) {
        $pdo->rollBack();
        echo json_encode(["message" => "ไม่สามารถเปลี่ยนสถานะการจองได้"]);
        exit();
    }

    $updateBooth = "UPDATE booths SET booth_status = 'ว่าง' WHERE booth_id = :booth_id";
    $stmtBooth = $pdo->prepare($updateBooth);
    if (!$stmtBooth->execute([':booth_id' => $booth_id])) {
        $pdo->rollBack();
        echo json_encode(["message" => "ไม่สามารถเปลี่ยนสถานะบูธได้"]);
        exit();
    }

    $pdo->commit();
    echo json_encode(["message" => "ยกเลิกการจองและบูธเรียบร้อยแล้ว"]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
    exit();
}
?>
