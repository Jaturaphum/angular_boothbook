<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

try {
    header('Content-Type: application/json');
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

$booking_id = $_POST['booking_id'] ?? null;

if ($booking_id) {
    try {
        $query = "SELECT booth_id FROM bookings WHERE booking_id = :booking_id AND booking_status = 'ชำระเงิน'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $booth_id = $booking['booth_id'];
            $updateBooking = $pdo->prepare("UPDATE bookings SET booking_status = 'อนุมัติแล้ว' WHERE booking_id = :booking_id");
            $updateBooking->execute([':booking_id' => $booking_id]);
            $updateBooth = $pdo->prepare("UPDATE booths SET booth_status = 'จองแล้ว' WHERE booth_id = :booth_id");
            $updateBooth->execute([':booth_id' => $booth_id]);
            echo json_encode(["message" => "การจองถูกอนุมัติและบูธถูกจองแล้ว"]);
        } else {
            echo json_encode(["message" => "ไม่มีการจองที่ชำระเงิน"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["message" => "ไม่มีข้อมูล"]);
}


?>
<?php
function adminApproveBooking() {
    // เนื้อหาการทำงานของฟังก์ชัน
    return ['status' => 'success', 'message' => 'Booking approved'];
}
