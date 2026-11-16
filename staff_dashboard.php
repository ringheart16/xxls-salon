<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_staff();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($status === 'completed') {
        $st = $pdo->prepare("UPDATE appointments SET status='completed' WHERE id=? AND staff_id=? AND status='approved'");
        $st->execute([$id,$user['id']]);
        if ($st->rowCount()) {
            $q=$pdo->prepare('SELECT total_amount FROM appointments WHERE id=?'); $q->execute([$id]); $amount=$q->fetchColumn();
            record_payment($id, (float)$amount);
            flash('success','Appointment marked as completed.');
        } else { flash('error','Only your approved appointments can be marked completed.'); }
    }
    redirect('staff_dashboard.php');
}
$sql = "SELECT a.*,u.name customer_name,u.phone,s.name service_name FROM appointments a JOIN users u ON u.id=a.user_id JOIN services s ON s.id=a.service_id WHERE a.staff_id=? ORDER BY a.appointment_date ASC,a.appointment_time ASC";
$st=$pdo->prepare($sql); $st->execute([$user['id']]); $rows=$st->fetchAll();
$upcoming=0; $done=0; foreach($rows as $r){ if($r['status']==='approved' && $r['appointment_date']>=date('Y-m-d')) $upcoming++; if($r['status']==='completed') $done++; }
$pageTitle='My Staff Schedule'; require __DIR__ . '/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">STAFF WORKSPACE</span><h1>My schedule</h1><p>Welcome, <?=e($user['name'])?>. Here are the appointments assigned to you.</p></div>
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)"><div class="stat-card"><span>Assigned appointments</span><strong><?=count($rows)?></strong></div><div class="stat-card"><span>Upcoming approved</span><strong><?=$upcoming?></strong></div><div class="stat-card highlight"><span>Completed</span><strong><?=$done?></strong></div></div>
<div class="table-wrap"><table><thead><tr><th>Customer</th><th>Service</th><th>Date & time</th><th>Notes</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><strong><?=e($r['customer_name'])?></strong><br><small><?=e($r['phone'] ?? '')?></small></td><td><?=e($r['service_name'])?></td><td><?=date('M d, Y',strtotime($r['appointment_date']))?><br><small><?=date('g:i A',strtotime($r['appointment_time']))?></small></td><td><?=e($r['notes'] ?: '—')?></td><td><?=status_badge($r['status'])?></td><td><?php if($r['status']==='approved'): ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="status" value="completed"><button class="btn btn-small btn-primary">Mark completed</button></form><?php else: ?><small>—</small><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6" class="empty-cell">No appointments have been assigned to you yet. Please ask the admin to assign bookings to your account.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
