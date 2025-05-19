<?php
// إعداد الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mat3ami";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب كل الطلبات مع بيانات الزبائن
$sql = "SELECT o.id AS order_id, o.total_price, o.created_at, 
               c.full_name, c.address, c.phone, c.payment_method
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطلبات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="text-center mb-4">طلبات الزبائن</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4 shadow">
                <div class="card-header bg-primary text-white">
                    <strong>طلب رقم: <?= $row['order_id'] ?></strong> - <?= $row['created_at'] ?>
                </div>
                <div class="card-body">
                    <p><strong>الاسم:</strong> <?= $row['full_name'] ?></p>
                    <p><strong>العنوان:</strong> <?= $row['address'] ?></p>
                    <p><strong>الهاتف:</strong> <?= $row['phone'] ?></p>
                    <p><strong>طريقة الدفع:</strong> <?= $row['payment_method'] ?></p>
                    <p><strong>السعر الكلي:</strong> <?= $row['total_price'] ?> دج</p>

                    <h5 class="mt-4">تفاصيل الطلب:</h5>
                    <ul class="list-group">
                        <?php
                        $order_id = $row['order_id'];
                        $items_sql = "SELECT item_name, price, quantity FROM order_items WHERE order_id = $order_id";
                        $items_result = $conn->query($items_sql);
                        while ($item = $items_result->fetch_assoc()):
                        ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?= $item['item_name'] ?> (x<?= $item['quantity'] ?>)</span>
                                <span><?= $item['price'] * $item['quantity'] ?> دج</span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">لا توجد طلبات حالياً.</div>
    <?php endif; ?>

</div>
</body>
</html>

<?php $conn->close(); ?>
