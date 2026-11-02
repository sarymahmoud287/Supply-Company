<?php
// تحديد الصفحة الرئيسية المناسبة حسب دور المستخدم
$home_page = 'dashboard_admin.php';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'support') {
        $home_page = 'dashboard_support.php';
    } elseif ($_SESSION['role'] == 'operations') {
        $home_page = 'dashboard_operations.php';
    } elseif ($_SESSION['role'] == 'agent') {
        $home_page = 'dashboard_agent.php';
    }
}
?>




<div class="navbar">
    <!-- اسم المصنع بوضوح تام مع الأيقونة -->
    <div class="brand-title">
        <span>مصنع محمد كمال 🧱</span>
    </div>

    <div class="nav-links">
        <a href="<?php echo $home_page; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $home_page ? 'active' : ''; ?>">الرئيسية</a>
        <a href="add_order.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_order.php' ? 'active' : ''; ?>">تسجيل طلب جديد</a>
        <a href="orders_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders_list.php' ? 'active' : ''; ?>">قائمة الطلبات</a>
        
        <?php if(isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'operations')): ?>
            <a href="dashboard_operations.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_operations.php' ? 'active' : ''; ?>">حركة الشحن</a>
        <?php endif; ?>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
    <a href="users_management.php">إدارة المستخدمين</a>
    <a href="add_agent.php">➕ إضافة مندوب</a>
    <a href="reset_system.php" onclick="return confirm('تحذير خطير...');">⚠️ تصفير السيستم</a>
<?php endif; ?>
        <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
    </div>
</div>

<style>
    /* خلفية صورة مصنع طوب أحمر حقيقي لكل صفحات السيستم مع تغميق خفيف لوضوح النصوص */
    body { 
        font-family: Tahoma, sans-serif; 
        background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), url('https://images.unsplash.com/photo-1590069261209-f8e9b8642343?q=80&w=1600&auto=format&fit=crop') no-repeat center center fixed;
        background-size: cover;
        margin: 0; 
        padding: 0; 
        direction: rtl; 
        min-height: 100vh;
    }

    .navbar { 
        background: rgba(139, 0, 0, 0.95); 
        color: white; 
        padding: 12px 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.3); 
        direction: rtl; 
        font-family: Tahoma, sans-serif; 
    }
    
    .brand-title {
        font-size: 18px;
        font-weight: bold;
        white-space: nowrap;
        color: #fff;
    }

    .nav-links { 
        display: flex; 
        gap: 10px; 
        align-items: center; 
        flex-wrap: wrap; 
    }

    .nav-links a { 
        color: white; 
        text-decoration: none; 
        font-weight: bold; 
        padding: 7px 10px; 
        border-radius: 4px; 
        transition: 0.3s; 
        font-size: 13px; 
        white-space: nowrap;
    }

    .nav-links a:hover, .nav-links a.active { 
        background: rgba(255, 255, 255, 0.2); 
    }

    .logout-btn { 
        background: #d9534f !important; 
    }
    
</style>