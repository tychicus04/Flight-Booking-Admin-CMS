<?php
// ============================================
// FILE: modules/bookings/view_ticket.php
// Xem vé booking - Ticket View
// ============================================
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die('ID không hợp lệ!');
}

// Get booking details
$query = "SELECT b.*, c.*,
          c.full_name as customer_name, c.email as customer_email, 
          c.phone as customer_phone, c.date_of_birth, c.id_number
          FROM bookings b 
          JOIN customers c ON b.customer_id = c.id
          WHERE b.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    die('Booking không tồn tại!');
}

// Get flight details
$flightQuery = "SELECT * FROM booking_flights WHERE booking_id = ? ORDER BY departure_date, departure_time";
$stmt = $db->prepare($flightQuery);
$stmt->execute([$id]);
$flights = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé máy bay - <?= $booking['booking_code'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .ticket-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .ticket-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .airline-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .airline-logo img {
            height: 50px;
        }
        
        .airline-logo h3 {
            margin: 0;
            color: #004990;
            font-weight: 600;
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 8px 20px;
            border: 1px solid #004990;
            background: white;
            color: #004990;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-action:hover {
            background: #004990;
            color: white;
        }
        
        .btn-action.primary {
            background: #004990;
            color: white;
        }
        
        .btn-action.primary:hover {
            background: #003670;
        }
        
        .ticket-info-bar {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .ticket-dates {
            display: flex;
            gap: 30px;
            font-size: 14px;
        }
        
        .ticket-dates div {
            display: flex;
            gap: 10px;
        }
        
        .ticket-dates strong {
            color: #6c757d;
        }
        
        .reservation-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .reservation-header h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #6c757d;
            font-weight: normal;
        }
        
        .reservation-code {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .reservation-code strong {
            font-size: 18px;
            color: #212529;
        }
        
        .airline-badge {
            text-align: right;
        }
        
        .airline-badge img {
            height: 40px;
        }
        
        .flight-section {
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .flight-type {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .flight-type i {
            font-size: 24px;
        }
        
        .flight-type strong {
            font-size: 16px;
        }
        
        .flight-route {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 30px;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .route-point {
            text-align: center;
        }
        
        .route-point.departure {
            text-align: left;
        }
        
        .route-point.arrival {
            text-align: right;
        }
        
        .route-point .code {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 5px;
        }
        
        .route-point .city {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .route-arrow {
            text-align: center;
            color: #6c757d;
        }
        
        .route-arrow i {
            font-size: 32px;
        }
        
        .flight-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .flight-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-item label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .detail-item .value {
            font-size: 16px;
            color: #212529;
            font-weight: 600;
        }
        
        .detail-item .time {
            font-size: 20px;
        }
        
        .customer-section {
            padding: 30px;
            background: white;
        }
        
        .customer-section h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #212529;
        }
        
        .customer-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row .label {
            width: 200px;
            font-weight: 600;
            color: #6c757d;
        }
        
        .info-row .value {
            flex: 1;
            color: #212529;
        }
        
        .contact-section {
            padding: 30px;
            background: #f8f9fa;
            border-top: 1px solid #e5e7eb;
        }
        
        .contact-section h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #212529;
        }
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .contact-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .contact-item label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }
        
        .contact-item .value {
            font-size: 15px;
            color: #212529;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .ticket-actions {
                display: none;
            }
            
            .ticket-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            <div class="airline-logo">
                <i class="bi bi-airplane-fill" style="font-size: 40px; color: #004990;"></i>
                <h3>Flight Booking System</h3>
            </div>
            <div class="ticket-actions">
                <button onclick="window.print()" class="btn-action primary">
                    <i class="bi bi-printer"></i> In vé
                </button>
                <a href="index.php" class="btn-action">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
        
        <!-- Ticket Info Bar -->
        <div class="ticket-info-bar">
            <div class="ticket-dates">
                <div>
                    <strong>Ngày đặt:</strong>
                    <span><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></span>
                </div>
                <?php if (!empty($flights)): ?>
                <div>
                    <strong>TRIP TO</strong>
                    <span><?= strtoupper($flights[0]['arrival_city']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Reservation Code -->
        <div class="reservation-header">
            <h4>PREPARED FOR</h4>
            <div class="reservation-code">
                <div>
                    <strong>RESERVATION CODE</strong>
                    <h2 style="margin: 5px 0; color: #004990; font-size: 24px;"><?= $booking['booking_code'] ?></h2>
                </div>
                <div class="airline-badge">
                    <i class="bi bi-airplane-engines" style="font-size: 50px; color: #004990;"></i>
                </div>
            </div>
        </div>
        
        <!-- Flight Details -->
        <?php foreach ($flights as $index => $flight): ?>
        <div class="flight-section">
            <div class="flight-type">
                <i class="bi bi-airplane-fill" style="color: #004990;"></i>
                <strong><?= $index === 0 ? 'DEPARTURE' : 'RETURN' ?>: <?= date('d/m/Y', strtotime($flight['departure_date'])) ?></strong>
                <span style="color: #6c757d; font-size: 14px;">Please verify flight times prior to departure</span>
            </div>
            
            <div class="flight-route">
                <div class="route-point departure">
                    <div class="code"><?= substr($flight['departure_city'], 0, 3) ?></div>
                    <div class="city"><?= $flight['departure_city'] ?></div>
                </div>
                
                <div class="route-arrow">
                    <i class="bi bi-arrow-right"></i>
                </div>
                
                <div class="route-point arrival">
                    <div class="code"><?= substr($flight['arrival_city'], 0, 3) ?></div>
                    <div class="city"><?= $flight['arrival_city'] ?></div>
                </div>
            </div>
            
            <div class="flight-details">
                <div class="flight-details-grid">
                    <div class="detail-item">
                        <label>Departing At:</label>
                        <div class="value time"><?= date('H:i', strtotime($flight['departure_time'])) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Arriving At:</label>
                        <div class="value time"><?= date('H:i', strtotime($flight['arrival_time'])) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Date</label>
                        <div class="value"><?= date('d/m/Y', strtotime($flight['departure_date'])) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Date</label>
                        <div class="value"><?= date('d/m/Y', strtotime($flight['arrival_date'])) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Durations:</label>
                        <div class="value">
                            <?php
                            $departure = strtotime($flight['departure_date'] . ' ' . $flight['departure_time']);
                            $arrival = strtotime($flight['arrival_date'] . ' ' . $flight['arrival_time']);
                            $duration = ($arrival - $departure) / 3600;
                            echo floor($duration) . 'h' . (($duration - floor($duration)) * 60) . 'p';
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Aircraft:</label>
                        <div class="value"><?= $flight['airline'] ?> <?= $flight['flight_number'] ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Cabin:</label>
                        <div class="value">
                            <?php
                            $seatClass = [
                                'economy' => 'ECO',
                                'business' => 'BUSINESS',
                                'first' => 'FIRST CLASS'
                            ];
                            echo $seatClass[$flight['seat_class']] ?? 'ECO';
                            ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Status:</label>
                        <div class="value" style="color: <?= $booking['booking_status'] == 'confirmed' ? '#198754' : '#6c757d' ?>;">
                            <?= ucfirst($booking['booking_status']) ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Payment:</label>
                        <div class="value" style="color: <?= $booking['payment_status'] == 'paid' ? '#198754' : '#dc3545' ?>;">
                            <?= ucfirst($booking['payment_status']) ?>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <label>Number of passengers:</label>
                        <div class="value">1</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Customer Information -->
        <div class="customer-section">
            <h3>Thông tin khách hàng</h3>
            <div class="customer-info">
                <div class="info-row">
                    <div class="label">Hành khách:</div>
                    <div class="value"><?= strtoupper($booking['customer_name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="label">CCCD/Passport :</div>
                    <div class="value"><?= $booking['id_number'] ?? 'N/A' ?></div>
                </div>
                <?php if ($booking['date_of_birth']): ?>
                <div class="info-row">
                    <div class="label">Ngày hết hạn :</div>
                    <div class="value"><?= date('d/m/Y', strtotime($booking['date_of_birth'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="contact-section">
            <h3>Thông tin liên hệ</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <label>Họ tên :</label>
                    <div class="value"><?= $booking['customer_name'] ?></div>
                </div>
                <div class="contact-item">
                    <label>Số điện thoại :</label>
                    <div class="value"><?= $booking['customer_phone'] ?? 'N/A' ?></div>
                </div>
                <div class="contact-item">
                    <label>Email :</label>
                    <div class="value"><?= $booking['customer_email'] ?? 'N/A' ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
