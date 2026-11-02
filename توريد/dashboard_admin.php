<?php
session_start();
require_once 'db.php';

// التأكد من أن المستخدم مدير (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// 1. حساب الإحصائيات المالية وحركة الطلبات
// إجمالي عدد الطلبات
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// إجمالي المبيعات (قيمة الطلبات كلها)
$total_sales = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;

// إجمالي تكاليف النقل
$total_transport = $pdo->query("SELECT SUM(transport_cost) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;

// عدد الطلبات الجديدة والجارية
$new_orders_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
$delivered_orders_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();

// 2. جلب آخر 5 مبيعات/طلبات مسجلة لعرضها في الجدول الأخير
$latest_orders = $pdo->query("SELECT orders.*, customers.name AS customer_name, customers.governorate 
                              FROM orders 
                              LEFT JOIN customers ON orders.customer_id = customers.id 
                              ORDER BY orders.created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم الرئيسية - مصنع محمد كمال</title>
    <!-- مكتبة الرسوم البيانية Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; margin: 0; padding: 0; direction: rtl; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        
        /* كروت الإحصائيات */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #8b0000; text-align: center; }
        .card h3 { margin: 0 0 10px 0; color: #555; font-size: 15px; }
        .card .number { font-size: 24px; font-weight: bold; color: #8b0000; }
        
        /* قسم الرسم البياني والجدول */
        .section-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .section-box h3 { margin-top: 0; color: #8b0000; border-bottom: 2px solid #f4f7f6; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #8b0000; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        
        .chart-container { position: relative; width: 100%; max-width: 600px; margin: 0 auto; height: 300px; }
    </style>
</head>
<body>

<!-- استدعاء شريط التنقل الموحد -->
<?php include 'navbar.php'; ?>

<div class="container">
    
    <!-- كروت الإحصائيات السريعة -->
    <div class="stats-grid">
        <div class="card">
            <h3>إجمالي الطلبات</h3>
            <div class="number"><?php echo number_format($total_orders); ?></div>
        </div>
        <div class="card">
            <h3>إجمالي الإيرادات (المبيعات)</h3>
            <div class="number"><?php echo number_format($total_sales, 2); ?> ج.م</div>
        </div>
        <div class="card">
            <h3>إجمالي تكاليف النقل</h3>
            <div class="number"><?php echo number_format($total_transport, 2); ?> ج.م</div>
        </div>
        <div class="card">
            <h3>طلبات تم توصيلها</h3>
            <div class="number"><?php echo number_format($delivered_orders_count); ?></div>
        </div>
    </div>

    <!-- قسم الرسم البياني لحالة الطلبات -->
    <div class="section-box">
        <h3>مؤشرات وحركة الطلبات</h3>
        <div class="chart-container">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>

    <!-- جدول آخر المبيعات والطلبات -->
    <div class="section-box">
        <h3>آخر المبيعات المسجلة</h3>
        <table>
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>اسم العميل</th>
                    <th>المحافظة</th>
                    <th>نوع الطوب والكمية</th>
                    <th>الإجمالي (جنيه)</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($latest_orders) > 0): ?>
                    <?php foreach($latest_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['governorate']); ?></td>
                            <td><?php echo htmlspecialchars($order['brick_type']); ?> (<?php echo number_format($order['quantity']); ?>)</td>
                            <td><?php echo number_format($order['total_price'], 2); ?> ج.م</td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 15px; color: #777;">لا توجد مبيعات مسجلة حتى الآن.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- كود تفعيل الرسم البياني عبر Chart.js -->
<script>
    const ctx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['إجمالي الطلبات', 'الطلبات الجديدة', 'تم التوصيل'],
            datasets: [{
                label: 'عدد الطلبات',
                data: [
                    <?php echo $total_orders; ?>, 
                    <?php echo $new_orders_count; ?>, 
                    <?php echo $delivered_orders_count; ?>
                ],
                backgroundColor: [
                    'rgba(139, 0, 0, 0.7)',
                    'rgba(2, 117, 216, 0.7)',
                    'rgba(92, 184, 92, 0.7)'
                ],
                borderColor: [
                    'rgba(139, 0, 0, 1)',
                    'rgba(2, 117, 216, 1)',
                    'rgba(92, 184, 92, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

</body>
</html>