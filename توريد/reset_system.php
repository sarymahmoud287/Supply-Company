<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم مدير فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// تنفيذ التصفير (حذف الطلبات أو تفريغ الجدول حسب الرغبة)
try {
    // تفريغ جدول الطلبات والعملاء المرتبطين بها
    $pdo->exec("DELETE FROM orders");
    $pdo->exec("DELETE FROM customers");
    header("Location: dashboard_admin.php?reset=success");
    exit();
} catch (\Exception $e) {
    echo "حدث خطأ أثناء التصفير: " . $e->getMessage();
}
?>