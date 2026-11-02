<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم مشرف حركة أو مدير
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'operations' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// معالجة تعيين المندوب ورقم العربية وحالة الطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_order'])) {
    $order_id = intval($_POST['order_id']);
    $assigned_agent_id = !empty($_POST['assigned_agent_id']) ? intval($_POST['assigned_agent_id']) : null;
    $truck_number = trim($_POST['truck_number']);
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET assigned_agent_id = ?, truck_number = ?, status = ? WHERE id = ?");
        $stmt->execute([$assigned_agent_id, $truck_number, $status, $order_id]);
        $success = "تم تحديث وتعيين حركة الطلب #$order_id بنجاح!";
    } catch (\Exception $e) {
        $error = "حدث خطأ أثناء التحديث: " . $e->getMessage();
    }
}

// جلب قائمة المناديب المتاحين (دور agent)
$agents = $pdo->query("SELECT id, name FROM users WHERE role = 'agent'")->fetchAll();

// جلب الطلبات غير التابعة أو التي تحتاج لمتابعة حركة
$orders = $pdo->query("SELECT orders.*, customers.name AS customer_name, customers.governorate, customers.phone AS customer_phone, users.name AS agent_name 
                       FROM orders 
                       LEFT JOIN customers ON orders.customer_id = customers.id 
                       LEFT JOIN users ON orders.assigned_agent_id = users.id 
                       ORDER BY orders.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة مشرف الحركة - مصنع محمد كمال</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #8b0000; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #8b0000; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        select, input[type="text"] { padding: 6px; border-radius: 4px; border: 1px solid #ccc; width: 100%; box-sizing: border-box; margin-bottom: 5px; }
        .btn-assign { background: #8b0000; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-assign:hover { background: #a52a2a; }
        .success { color: green; background: #e6f4ea; padding: 10px; border: 1px solid #ceead6; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .error { color: red; background: #fdf7f7; padding: 10px; border: 1px solid #ebccd1; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<!-- استدعاء شريط التنقل الموحد -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2>إدارة الأسطول وحركة الشحن (مشرف الحركة)</h2>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل والمحافظة</th>
                <th>نوع الطوب والكمية</th>
                <th>رقم العربية الحالية</th>
                <th>المندوب الحالي</th>
                <th>تغيير الحالة</th>
                <th>إدارة وتعشيق الحركة</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($orders) > 0): ?>
                <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br><small><?php echo htmlspecialchars($order['governorate']); ?></small></td>
                        <td><?php echo htmlspecialchars($order['brick_type']); ?><br><strong><?php echo number_format($order['quantity']); ?> طوبة</strong></td>
                        
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <td>
                                <input type="text" name="truck_number" value="<?php echo htmlspecialchars($order['truck_number']); ?>" placeholder="رقم العربية">
                            </td>
                            <td>
                                <select name="assigned_agent_id">
                                    <option value="">-- اختر المندوب --</option>
                                    <?php foreach($agents as $agent): ?>
                                        <option value="<?php echo $agent['id']; ?>" <?php echo $order['assigned_agent_id'] == $agent['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($agent['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="status">
                                    <option value="new" <?php echo $order['status']=='new'?'selected':''; ?>>جديد</option>
                                    <option value="loading" <?php echo $order['status']=='loading'?'selected':''; ?>>جاري التحميل</option>
                                    <option value="on_way" <?php echo $order['status']=='on_way'?'selected':''; ?>>في الطريق</option>
                                    <option value="delivered" <?php echo $order['status']=='delivered'?'selected':''; ?>>تم التوصيل</option>
                                    <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>ملغي</option>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="assign_order" class="btn-assign">تحديث وإرسال</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="padding: 20px; color: #777;">لا توجد طلبات لإدارة حركتها حالياً.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>