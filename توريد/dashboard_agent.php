<?php
session_start();

// بيانات الاتصال بقاعدة البيانات مباشرة لضمان عدم وجود أخطاء في الـ include
$host = 'localhost';
$dbname = 'توريد'; // أو اسم قاعدة البيانات عندك بالإنجليزية لو قاعدة بياناتك بالإنجليزية
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // محاولة الاتصال باسم قاعدة البيانات الإنجليزي الاحتياطي لو اسم المجلد بالعربي عامل مشكلة
    try {
        $conn = new PDO("mysql:host=$host;dbname=torid;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $ex) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $ex->getMessage());
    }
}

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// تحديث حالة الطلب لو المندوب ضغط "تم التسليم"
if (isset($_POST['update_status']) && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'تم التسليم' WHERE id = ?");
    $stmt->execute([$order_id]);
    header("Location: dashboard_agent.php");
    exit();
}

// جلب الطلبات المسندة للتوصيل
$stmt = $conn->prepare("SELECT * FROM orders WHERE status IN ('جاري التوصيل', 'تم التجهيز') ORDER BY id DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة متابعة المناديب - مصنع محمد كمال</title>
    <style>
        .container { width: 90%; margin: 30px auto; background: rgba(255, 255, 255, 0.92); padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h2 { color: #8b0000; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; font-size: 14px; }
        th { background-color: #8b0000; color: white; }
        .btn-deliver { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-deliver:hover { background: #218838; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>🚚 طلبات التوريد والشحن المتاحة للتوصيل</h2>
        <table>
            <tr>
                <th>رقم الطلب</th>
                <th>اسم العميل</th>
                <th>الهاتف والعنوان</th>
                <th>نوع الطوب والكمية</th>
                <th>سيارة النقل</th>
                <th>الحالة الحالية</th>
                <th>إجراء المندوب</th>
            </tr>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone'] . ' - ' . $order['address']); ?></td>
                    <td><?php echo htmlspecialchars($order['brick_type'] . ' (' . $order['quantity'] . ' طوبة)'); ?></td>
                    <td><?php echo htmlspecialchars($order['truck_number']); ?></td>
                    <td><strong style="color: #d9534f;"><?php echo $order['status']; ?></strong></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" name="update_status" class="btn-deliver" onclick="return confirm('هل تأكيد تسليم الشحنة للعميل بنجاح؟');">✅ تم التسليم</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="padding: 20px; color: #777;">لا توجد طلبات جديدة معلقة للتوصيل حالياً.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>