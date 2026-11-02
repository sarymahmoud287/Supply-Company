<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم خدمة عملاء أو مدير
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'support' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة خدمة العملاء - مصنع محمد كمال</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
        h2 { color: #8b0000; }
        .actions { margin-top: 30px; display: flex; justify-content: center; gap: 20px; }
        .btn { background: #8b0000; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: #a52a2a; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <h2>أهلاً بك يا باشمهندس <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h2>
    <p>من هنا يمكنك تسجيل الطلبات الجديدة ومتابعة بيانات العملاء بكل سهولة.</p>
    
    <div class="actions">
        <a href="add_order.php" class="btn">تسجيل طلب جديد للعميل</a>
        <a href="orders_list.php" class="btn">عرض ومتابعة الطلبات</a>
    </div>
</div>

</body>
</html>