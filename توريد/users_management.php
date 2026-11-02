<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم مدير فقط (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// معالجة إضافة مستخدم جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // كلمة المرور مباشرة أو مشفرة حسب الرغبة
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, username, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $username, $password, $role]);
        $success = "تم إضافة المستخدم الجديد بنجاح!";
    } catch (\Exception $e) {
        $error = "خطأ: اسم المستخدم موجود مسبقاً أو حدث مشكلة في الإضافة.";
    }
}

// معالجة حذف مستخدم
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // منع المدير من حذف نفسه بالخطأ
    if ($delete_id != $_SESSION['user_id']) {
        $stmt_del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt_del->execute([$delete_id]);
        header("Location: users_management.php");
        exit();
    } else {
        $error = "لا يمكنك حذف حسابك الحالي المدخل به!";
    }
}

// جلب كل المستخدمين المسجلين في النظام
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين والصلاحيات - مصنع محمد كمال</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 1100px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #8b0000; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { background: #8b0000; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #a52a2a; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #8b0000; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn-del { background: #d9534f; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-del:hover { background: #c9302c; }
        .success { color: green; background: #e6f4ea; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .error { color: red; background: #fdf7f7; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<!-- استدعاء شريط التنقل -->
<?php include 'navbar.php'; ?>

<div class="container">
    <h2>إدارة المستخدمين وصلاحيات النظام</h2>

    <?php if($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
    <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>

    <!-- نموذج إضافة مستخدم جديد -->
    <div style="background: #fafafa; padding: 20px; border-radius: 6px; border: 1px solid #eee; margin-bottom: 30px;">
        <h3>إضافة مستخدم جديد للنظام</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>الاسم بالكامل:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>رقم الهاتف:</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>اسم المستخدم (للدخول):</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور:</label>
                    <input type="text" name="password" required>
                </div>
                <div class="form-group">
                    <label>الصلاحية / الدور:</label>
                    <select name="role" required>
    <option value="admin">مدير (Admin - صلاحيات كاملة)</option>
    <option value="support">موظف خدمة العملاء (Customer Support)</option>
    <option value="operations">مشرف حركة (Operations)</option>
    <option value="agent">مندوب شحن وتوصيل (Agent)</option>
</select>
                </div>
            </div>
            <button type="submit" name="add_user" class="btn">حفظ وإضافة المستخدم</button>
        </form>
    </div>

    <!-- جدول عرض المستخدمين -->
    <h3>قائمة المستخدمين الحاليين</h3>
    <table>
        <thead>
            <tr>
                <th>#ID</th>
                <th>الاسم</th>
                <th>اسم المستخدم</th>
                <th>الهاتف</th>
                <th>الدور والصلاحية</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                    <td><?php echo htmlspecialchars($u['phone']); ?></td>
                    <td>
                        <?php 
    if($u['role'] == 'admin') echo '<span style="color:red; font-weight:bold;">مدير (Admin)</span>';
    elseif($u['role'] == 'support') echo '<span style="color:purple; font-weight:bold;">خدمة عملاء</span>';
    elseif($u['role'] == 'operations') echo '<span style="color:blue; font-weight:bold;">مشرف حركة</span>';
    else echo '<span style="color:green; font-weight:bold;">مندوب</span>';
?>                    </td>
                    <td>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <a href="users_management.php?delete_id=<?php echo $u['id']; ?>" class="btn-del" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">حذف</a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">حسابك الحالي</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>