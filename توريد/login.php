<?php
session_start();
require_once 'db.php'; // استدعاء ملف الاتصال بقاعدة البيانات

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // البحث عن المستخدم في القاعدة
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // التحقق من كلمة المرور
    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // توجيه المستخدم حسب دوره وصلاحيته
        if ($user['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        } elseif ($user['role'] == 'support') {
            header("Location: dashboard_support.php");
        } elseif ($user['role'] == 'operations') {
            header("Location: dashboard_operations.php");
        } elseif ($user['role'] == 'agent') {
            header("Location: dashboard_agent.php");
        }
        exit();
    } else {
        $error = "";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - مصنع محمد كمال لتوريدات الطوب</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 360px; text-align: center; }
        .login-card h2 { color: #8b0000; margin-bottom: 5px; }
        .login-card p { color: #666; margin-bottom: 20px; font-size: 14px; }
        .form-group { margin-bottom: 15px; text-align: right; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { background: #8b0000; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #a52a2a; }
        .error { color: #d9534f; background: #fdf7f7; padding: 10px; border: 1px solid #ebccd1; border-radius: 4px; margin-bottom: 15px; font-size: 13px; }
body { 
        font-family: Tahoma, sans-serif; 
        background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url('https://images.unsplash.com/photo-1590069261209-f8e9b8642343?q=80&w=1600&auto=format&fit=crop') no-repeat center center fixed;
        background-size: cover;
        margin: 0; 
        padding: 0; 
        direction: rtl; 
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }    
    </style>
</head>
<body>

<div class="login-card">
    <h2>مصنع محمد كمال</h2>
    <p>نظام إدارة التوريدات والشحن</p>

    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>اسم المستخدم:</label>
            <input type="text" name="username" required autocomplete="off">
        </div>
        <div class="form-group">
            <label>كلمة المرور:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">دخول للنظام</button>
    </form>
</div>

</body>
</html>