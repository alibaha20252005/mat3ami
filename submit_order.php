<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// إعداد الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mat3ami";

$conn = new mysqli($host, $user, $password, $dbname);

// تحقق من الاتصال
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "فشل الاتصال بقاعدة البيانات: " . $conn->connect_error]);
    exit;
}

// استقبال البيانات بصيغة JSON
$data = json_decode(file_get_contents("php://input"), true);

// تأكد من وجود البيانات المطلوبة
$required_fields = ['full_name', 'address', 'phone', 'payment_method', 'cart'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["message" => "الرجاء ملء جميع الحقول المطلوبة: $field"]);
        exit;
    }
}

if (!is_array($data['cart']) || count($data['cart']) === 0) {
    http_response_code(400);
    echo json_encode(["message" => "السلة فارغة"]);
    exit;
}

// 1. إضافة الزبون
$stmt = $conn->prepare("INSERT INTO customers (full_name, address, phone, payment_method) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "فشل تحضير استعلام إضافة الزبون: " . $conn->error]);
    exit;
}
$stmt->bind_param("ssss", $data['full_name'], $data['address'], $data['phone'], $data['payment_method']);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "فشل تنفيذ استعلام إضافة الزبون: " . $stmt->error]);
    exit;
}
$customer_id = $stmt->insert_id;
$stmt->close();

// 2. حساب السعر الكلي
$total_price = 0;
foreach ($data['cart'] as $item) {
    if (!isset($item['price'], $item['quantity'])) continue;
    $total_price += floatval($item['price']) * intval($item['quantity']);
}

// 3. إنشاء الطلب
$stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price) VALUES (?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "فشل تحضير استعلام إنشاء الطلب: " . $conn->error]);
    exit;
}
$stmt->bind_param("id", $customer_id, $total_price);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["message" => "فشل تنفيذ استعلام إنشاء الطلب: " . $stmt->error]);
    exit;
}
$order_id = $stmt->insert_id;
$stmt->close();

// 4. إدخال تفاصيل العناصر
$stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, price, quantity) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "فشل تحضير استعلام إضافة عناصر الطلب: " . $conn->error]);
    exit;
}
foreach ($data['cart'] as $item) {
    if (!isset($item['name'], $item['price'], $item['quantity'])) continue;
    $stmt->bind_param("isdi", $order_id, $item['name'], $item['price'], $item['quantity']);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["message" => "فشل تنفيذ استعلام إضافة عنصر: " . $stmt->error]);
        exit;
    }
}
$stmt->close();

// إنهاء الاتصال
$conn->close();

// الرد النهائي
echo json_encode(["message" => "✅ تم إرسال طلبك بنجاح!"]);
