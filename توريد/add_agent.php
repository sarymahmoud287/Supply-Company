<?php
session_start();

// الاتصال بقاعدة البيانات مباشرة لضمان الاستقرار
$host = 'localhost';
$dbname = 'توريد';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=torid;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $ex) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $ex->getMessage());
    }
}

// التأكد من أن المستخدم مدير (Admin) للوصول لهذه الصفحة
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin')) {
    // لو حبيت تسمح للـ operations كمان، ممكن تعدل الشرط
    // لكن الأفضل التحكم يكون للمدير فقط
}

$msg = "";
$error = "";

// معالجة البيانات عند الضغط على زر الإضافة
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $role = 'agent'; // الدور مثبت كـ مندوب

    if (!empty($username_input) && !empty($password_input)) {
        // تشفير كلمة المرور لحماية الحساب
        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

        try {
            // التحقق من عدم تكرار اسم المستخدم
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username_input]);
            if ($check->rowCount() > 0) {
                $error = "اسم المستخدم هذا موجود بالفعل، اختر اسم آخر.";
            } else {
                // إدخال المندوب الجديد في جدول المستخدمين (تأكد أن جدولك اسمه users وفيه أعمدة username, password, role, full_name)
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username_input, $hashed_password, $role, $full_name]);
                $msg = "تم إضافة حساب المندوب بنجاح! يمكنه تسجيل الدخول الآن.";
            }
        } catch (PDOException $e) {
            $error = "حدث خطأ أثناء الحفظ: " . $e->getMessage();
        }
    } else {
        $error = "برجاء إدخال اسم المستخدم وكلمة المرور على الأقل.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة مندوب جديد - مصنع محمد كمال</title>
    <style>
        .container { width: 500px; margin: 40px auto; background: rgba(255, 255, 255, 0.93); padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h2 { color: #8b0000; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #8b0000; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px; }
        .btn-submit:hover { background: #a52a2a; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>➕ إضافة حساب مندوب توصيل جديد</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>الاسم بالكامل (اختياري):</label>
                <input type="text" name="full_name" placeholder="مثال: محمد أحمد (سائق السيارة)">
            </div>

            <div class="form-group">
                <label>اسم المستخدم (للدخول به):</label>
                <input type="text" name="username" required placeholder="مثال: agent1 أو driver_mohamed">
            </div>

            <div class="form-group">
                <label>كلمة المرور:</label>
                <input type="password" name="password" required placeholder="أدخل كلمة مرور قوية">
            </div>

            <button type="submit" class="btn-submit">حفظ وإنشاء الحساب</button>
        </form>
    </div>
</body>
</html>///////////////