<?php
// إعدادات الاتصال بقاعدة البيانات لمصنع محمد كمال
$host = 'localhost';
$db   = 'توريد'; // اسم قاعدة البيانات اللي ظاهر عندك في phpMyAdmin
$user = 'root'; // اسم المستخدم الافتراضي
$pass = '';     // كلمة المرور (عادة بتكون فارغة في السيرفر المحلي)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // ضبط وضع الأخطاء ليكون استثناءات
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>