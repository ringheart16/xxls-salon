<?php
require_once __DIR__.'/includes/functions.php';$admin=require_admin();$pdo=db();
$counts=[];foreach(['pending','approved','completed','declined','cancelled'] as $s){$q=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE status=?");$q->execute([$s]);$counts[$s]=(int)$q->fetchColumn();}
$customers=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$services=(int)$pdo->query("SELECT COUNT(*) FROM services WHERE is_active=1")->fetchColumn();
$sales=(float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='paid'")->fetchColumn();
$recent=$pdo->query("SELECT a.*,u.name customer_name,s.name service_name FROM appointments a JOIN users u ON u.id=a.user_id JOIN services s ON s.id=a.service_id ORDER BY a.created_at DESC LIMIT 8")->fetchAll();
$pageTitle='Admin Dashboard';require __DIR__.'/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">SALON OVERVIEW</span><h1>Dashboard</h1><p>Welcome back! Here’s what’s happening at XXL’s Salon.</p></div>
<div class="stats-grid"><div class="stat-card"><span>Pending bookings</span><strong><?=$counts['pending']?></strong><small>Need your attention</small></div><div class="stat-card"><span>Approved</span><strong><?=$counts['approved']?></strong><small>Upcoming visits</small></div><div class="stat-card"><span>Customers</span><strong><?=$customers?></strong><small>Registered accounts</small></div><div class="stat-card"><span>Active services</span><strong><?=$services?></strong><small>Available to book</small></div><div class="stat-card highlight"><span>Recorded payments</span><strong><?=money($sales)?></strong><small>Paid transactions</small></div></div>
<div class="section-heading"><div><span class="eyebrow">LATEST ACTIVITY</span><h2>Recent appointments</h2></div><a class="text-link" href="admin_appointments.php">Manage all →</a></div>
<div class="table-wrap"><table><thead><tr><th>Customer</th><th>Service</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody><?php foreach($recent as $r):?><tr><td><?=e($r['customer_name'])?></td><td><?=e($r['service_name'])?></td><td><?=date('M d, Y',strtotime($r['appointment_date']))?></td><td><?=money($r['total_amount'])?></td><td><?=status_badge($r['status'])?></td></tr><?php endforeach;?><?php if(!$recent):?><tr><td colspan="5" class="empty-cell">No appointments yet.</td></tr><?php endif;?></tbody></table></div>
<div class="quick-links"><a href="admin_services.php">＋ Manage services</a><a href="admin_appointments.php">▣ Review bookings</a><a href="admin_staff.php">✂ Manage staff</a><a href="admin_customers.php">♙ View customers</a><a href="admin_reports.php">↗ View reports</a></div>
<?php require __DIR__.'/includes/footer.php';?>
