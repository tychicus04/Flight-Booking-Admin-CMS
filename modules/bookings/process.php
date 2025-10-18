
<?php
// ============================================
// FILE: modules/bookings/process.php
// ============================================

$errors = [];

// Validate
$booking_code = trim($_POST['booking_code'] ?? '');
$booking_date = $_POST['booking_date'] ?? '';
$payment_status = $_POST['payment_status'] ?? 'unpaid';
$booking_status = $_POST['booking_status'] ?? 'pending';
$total_amount = $_POST['total_amount'] ?? 0;

if (empty($booking_code)) {
    $errors['booking_code'] = 'Mã đặt chỗ không được để trống!';
}

if (empty($booking_date)) {
    $errors['booking_date'] = 'Ngày đặt không được để trống!';
}

// Check if booking code exists
$checkQuery = "SELECT id FROM bookings WHERE booking_code = ?";
$stmt = $db->prepare($checkQuery);
$stmt->execute([$booking_code]);
if ($stmt->fetch()) {
    $errors['booking_code'] = 'Mã đặt chỗ đã tồn tại!';
}

// Handle customer
$customer_id = $_POST['customer_id'] ?? null;

if (!$customer_id) {
    // Create new customer
    $customer_name = trim($_POST['customer_name'] ?? '');
    if (empty($customer_name)) {
        $errors['customer_name'] = 'Tên khách hàng không được để trống!';
    } else {
        $insertCustomer = "INSERT INTO customers (full_name, email, phone, id_number) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($insertCustomer);
        $stmt->execute([
            $customer_name,
            $_POST['customer_email'] ?? null,
            $_POST['customer_phone'] ?? null,
            $_POST['customer_id_number'] ?? null
        ]);
        $customer_id = $db->lastInsertId();
        
        // Create wallet for new customer
        $insertWallet = "INSERT INTO wallets (customer_id, balance) VALUES (?, 0)";
        $stmt = $db->prepare($insertWallet);
        $stmt->execute([$customer_id]);
    }
}

// Validate flights
$flights = $_POST['flights'] ?? [];
if (empty($flights)) {
    $errors['flights'] = 'Phải có ít nhất một chuyến bay!';
}

if (empty($errors)) {
    try {
        $db->beginTransaction();
        
        // Insert booking
        $insertBooking = "INSERT INTO bookings 
                         (booking_code, customer_id, total_amount, payment_status, booking_status, created_by, booking_date) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insertBooking);
        $stmt->execute([
            $booking_code,
            $customer_id,
            $total_amount,
            $payment_status,
            $booking_status,
            $_SESSION['user_id'],
            $booking_date
        ]);
        
        $booking_id = $db->lastInsertId();
        
        // Insert flights
        $insertFlight = "INSERT INTO booking_flights 
                        (booking_id, flight_number, departure_city, arrival_city, departure_date, 
                         departure_time, arrival_date, arrival_time, airline, seat_class, price) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        foreach ($flights as $flight) {
            $stmt = $db->prepare($insertFlight);
            $stmt->execute([
                $booking_id,
                $flight['flight_number'],
                $flight['departure_city'],
                $flight['arrival_city'],
                $flight['departure_date'],
                $flight['departure_time'],
                $flight['arrival_date'],
                $flight['arrival_time'],
                $flight['airline'],
                $flight['seat_class'],
                $flight['price']
            ]);
        }
        
        $db->commit();
        setFlashMessage('Tạo booking thành công!', 'success');
        
    } catch (Exception $e) {
        $db->rollBack();
        $errors['database'] = 'Lỗi: ' . $e->getMessage();
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        setFlashMessage($error, 'danger');
    }
}

?>

