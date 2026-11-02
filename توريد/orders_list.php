<?php
session_start();
require_once 'db.php';

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// معالجة حذف طلب فردي (للمدير فقط)
if (isset($_GET['delete_order_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $del_id = intval($_GET['delete_order_id']);
    $stmt_del = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt_del->execute([$del_id]);
    header("Location: orders_list.php");
    exit();
}
// معالجة تغيير حالة الطلب لو المدير حب يحدثها
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    $stmt_update = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt_update->execute([$new_status, $order_id]);
    header("Location: orders_list.php");
    exit();
}

// جلب الطلبات مع بيانات العميل والمندوب المسؤول
$query = "SELECT orders.*, customers.name AS customer_name, customers.phone AS customer_phone, customers.governorate, users.name AS agent_name 
          FROM orders 
          LEFT JOIN customers ON orders.customer_id = customers.id 
          LEFT JOIN users ON orders.assigned_agent_id = users.id 
          ORDER BY orders.created_at DESC";
$orders = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة الطلبات - مصنع محمد كمال</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #8b0000; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #8b0000; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .status-new { color: #0275d8; font-weight: bold; }
        .status-loading { color: #f0ad4e; font-weight: bold; }
        .status-on_way { color: #5bc0de; font-weight: bold; }
        .status-delivered { color: #5cb85c; font-weight: bold; }
        .status-cancelled { color: #d9534f; font-weight: bold; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .btn-sm { background: #8b0000; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .btn-sm:hover { background: #a52a2a; }
    </style>
</head>
<body>

<!-- استدعاء شريط التنقل -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2>قائمة ومتابعة طلبات التوريد</h2>

    <table>
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>اسم العميل</th>
                <th>الهاتف والمحافظة</th>
                <th>نوع الطوب والكمية</th>
                <th>الإجمالي (جنيه)</th>
                <th>سيارة النقل</th>
                <th>المندوب</th>
                <th>الحالة</th>
                <th>تحديث الحالة</th>
                <th>الإجراءات</th>

<
            </tr>
        </thead>
        
        <tbody>
            <?php if(count($orders) > 0): ?>
                <?php foreach($orders as $order): ?>
                    <tr>
                        
                        <td>#<?php echo $order['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_phone']); ?><br><small><?php echo htmlspecialchars($order['governorate']); ?></small></td>
                        <td><?php echo htmlspecialchars($order['brick_type']); ?><br><strong><?php echo number_format($order['quantity']); ?> طوبة</strong></td>
                        <td><?php echo number_format($order['total_price'], 2); ?> ج.م</td>
                        <td><?php echo htmlspecialchars($order['truck_number'] ?? 'غير محدد'); ?></td>
                        <td><?php echo htmlspecialchars($order['agent_name'] ?? 'بدون مندوب'); ?></td>
                        <td>
                            <?php 
                                $status_labels = [
                                    'new' => '<span class="status-new">جديد</span>',
                                    'loading' => '<span class="status-loading">جاري التحميل</span>',
                                    'on_way' => '<span class="status-on_way">في الطريق</span>',
                                    'delivered' => '<span class="status-delivered">تم التوصيل</span>',
                                    'cancelled' => '<span class="status-cancelled">ملغي</span>'
                                ];
                                echo $status_labels[$order['status']] ?? $order['status'];
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 5px; justify-content: center; align-items: center;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status">
                                    <option value="new" <?php echo $order['status']=='new'?'selected':''; ?>>جديد</option>
                                    <option value="loading" <?php echo $order['status']=='loading'?'selected':''; ?>>تحميل</option>
                                    <option value="on_way" <?php echo $order['status']=='on_way'?'selected':''; ?>>طريق</option>
                                    <option value="delivered" <?php echo $order['status']=='delivered'?'selected':''; ?>>توصيل</option>
                                    <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>إلغاء</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-sm">حفظ</button>
                            </form>
                            <td>
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="orders_list.php?delete_order_id=<?php echo $order['id']; ?>" class="btn-del" onclick="return confirm('هل أنت متأكد من حذف هذا الطلب نهائياً؟');" style="background: #d9534f; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px;">حذف</a>
    <?php else: ?>
        <span style="color: #999; font-size: 12px;">غيرحساب المدير مطلوب</span>
    <?php endif; ?>
</td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="padding: 20px; color: #777;">لا توجد طلبات مسجلة حتى الآن.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>