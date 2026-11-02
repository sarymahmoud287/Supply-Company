<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم مسجل دخوله وصحيح
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// عند إرسال نموذج إضافة الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $governorate = trim($_POST['governorate']);
    $address = trim($_POST['address']);
    
    $brick_type = $_POST['brick_type'];
    $quantity = intval($_POST['quantity']);
    $price_per_1000 = floatval($_POST['price_per_1000']);
    $transport_cost = floatval($_POST['transport_cost']);
    $assigned_agent_id = !empty($_POST['assigned_agent_id']) ? intval($_POST['assigned_agent_id']) : null;
    $truck_number = trim($_POST['truck_number']);
    $notes = trim($_POST['notes']);

    // حساب إجمالي سعر الطوب
    $total_price = ($quantity / 1000) * $price_per_1000;

    try {
        $pdo->beginTransaction();

        // 1. إدخال أو تسجيل العميل الجديد
        $stmt_cust = $pdo->prepare("INSERT INTO customers (name, phone, governorate, address) VALUES (?, ?, ?, ?)");
        $stmt_cust->execute([$customer_name, $phone, $governorate, $address]);
        $customer_id = $pdo->lastInsertId();

        // 2. إدخال الطلب في جدول الطلبات
        $stmt_order = $pdo->prepare("INSERT INTO orders (customer_id, brick_type, quantity, factory_name, price_per_1000, total_price, transport_cost, assigned_agent_id, truck_number, status, notes) VALUES (?, ?, ?, 'مصنع محمد كمال', ?, ?, ?, ?, ?, 'new', ?)");
        
        $stmt_order->execute([
            $customer_id, 
            $brick_type, 
            $quantity, 
            $price_per_1000, 
            $total_price, 
            $transport_cost, 
            $assigned_agent_id, 
            $truck_number, 
            $notes
        ]);

        $pdo->commit();
        $success = "تم تسجيل الطلب وإضافة العميل بنجاح!";
    } catch (\Exception $e) {
        $pdo->rollBack();
        $error = "حدث خطأ أثناء حفظ الطلب: " . $e->getMessage();
    }
}

// جلب قائمة المناديب من جدول المستخدمين لتعيين أحدهم للطلب
$agents = $pdo->query("SELECT id, name FROM users WHERE role = 'agent'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل طلب توريد جديد - مصنع محمد كمال</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 700px; background: white; margin: 30px auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #8b0000; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; height: 80px; }
        .btn { background: #8b0000; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #a52a2a; }
        .success { color: green; background: #e6f4ea; padding: 10px; border: 1px solid #ceead6; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .error { color: red; background: #fdf7f7; padding: 10px; border: 1px solid #ebccd1; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<!-- استدعاء شريط التنقل الموحد -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2>تسجيل طلب توريد طوب جديد</h2>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <h3>بيانات العميل</h3>
        <div class="form-group">
            <label>اسم العميل / المقاول:</label>
            <input type="text" name="customer_name" required>
        </div>
        <div class="form-group">
            <label>رقم الهاتف:</label>
            <input type="text" name="phone" required>
        </div>
        <div class="form-group">
            <label>المحافظة:</label>
            <input type="text" name="governorate" required placeholder="مثال: الجيزة، القاهرة...">
        </div>
        <div class="form-group">
            <label>العنوان بالتفصيل:</label>
            <textarea name="address"></textarea>
        </div>

        <hr style="margin: 20px 0; border:0; border-top:1px solid #ddd;">

        <h3>تفاصيل توريد الطوب</h3>
        <div class="form-group">
            <label>نوع الطوب:</label>
            <select name="brick_type" required>
                <option value="طفلي مثقب">طوب طفلي مثقب</option>
                <option value="مفرغ رملي">طوب مفرغ</option>
                <option value="مصمت">طوب مصمت</option>
            </select>
        </div>
        <div class="form-group">
            <label>الكمية (بالألف طوبة):</label>
            <input type="number" name="quantity" required placeholder="مثال: 5000">
        </div>
        <div class="form-group">
            <label>سعر الألف طوبة (جنيه):</label>
            <input type="number" step="0.01" name="price_per_1000" required>
        </div>
        
        <div class="form-group">
            <label>المندوب المسؤول:</label>
            <select name="assigned_agent_id">
                <option value="">-- اختر المندوب --</option>
                <?php foreach($agents as $agent): ?>
                    <option value="<?php echo $agent['id']; ?>"><?php echo htmlspecialchars($agent['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>ملاحظات إضافية:</label>
            <textarea name="notes" placeholder="أي ملاحظات تخص الموقع أو التنزيل..."></textarea>
        </div>

        <button type="submit" class="btn">حفظ وتسجيل الطلب</button>
    </form>
</div>

</body>
</html>