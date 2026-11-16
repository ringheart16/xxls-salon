<?php
require_once __DIR__.'/includes/functions.php';require_admin();$pdo=db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $staffId = ($_POST['staff_id'] ?? '') !== '' ? (int)$_POST['staff_id'] : null;
    $allowed = ['approved','declined','completed','cancelled'];
    if ($id > 0) {
        $st = $pdo->prepare("UPDATE appointments SET staff_id=? WHERE id=?");
        $st->execute([$staffId,$id]);
        if (in_array($status,$allowed,true)) {
            $st = $pdo->prepare("UPDATE appointments SET status=? WHERE id=?");
            $st->execute([$status,$id]);
            if ($status === 'completed') {
                $q=$pdo->prepare('SELECT total_amount FROM appointments WHERE id=?'); $q->execute([$id]); $amount=$q->fetchColumn();
                record_payment($id, (float)$amount);
            }
        }
        flash('success','Appointment and staff assignment updated.');
    }
    redirect('admin_appointments.php');
}
$staffOptions = $pdo->query("SELECT id,name FROM users WHERE role='staff' AND is_active=1 ORDER BY name")->fetchAll();
$filter=$_GET['status']??'all';
$sql="SELECT a.*,u.name customer_name,u.phone,s.name service_name,st.name staff_name FROM appointments a JOIN users u ON u.id=a.user_id JOIN services s ON s.id=a.service_id LEFT JOIN users st ON st.id=a.staff_id";
$params=[];
if(in_array($filter,['pending','approved','completed','declined','cancelled'],true)){$sql.=" WHERE a.status=?";$params[]=$filter;}
$sql.=" ORDER BY a.appointment_date DESC,a.appointment_time DESC";
$q=$pdo->prepare($sql);$q->execute($params);$rows=$q->fetchAll();
$pageTitle='Manage Appointments';require __DIR__.'/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">BOOKING MANAGEMENT</span><h1>Appointments</h1><p>Review requests and update appointment status.</p></div>
<div class="filter-tabs"><?php foreach(['all','pending','approved','completed','declined','cancelled'] as $f):?><a class="<?=$filter===$f?'active':''?>" href="?status=<?=$f?>"><?=ucfirst($f)?></a><?php endforeach;?></div>
<div class="table-wrap"><table><thead><tr><th>Customer</th><th>Service</th><th>Schedule</th><th>Staff</th><th>Total</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?=e($r['customer_name'])?></strong><br><small><?=e($r['phone'])?></small></td><td><?=e($r['service_name'])?></td><td><?=date('M d, Y',strtotime($r['appointment_date']))?><br><small><?=date('g:i A',strtotime($r['appointment_time']))?></small></td><td><?=e($r['staff_name']??'Any')?></td><td><?=money($r['total_amount'])?></td><td><?=status_badge($r['status'])?></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$r['id']?>"><select name="staff_id" aria-label="Assign staff"><option value="">Unassigned</option><?php foreach($staffOptions as $opt):?><option value="<?=$opt['id']?>" <?=((int)$r['staff_id']===(int)$opt['id']?'selected':'')?>><?=e($opt['name'])?></option><?php endforeach;?></select><select name="status" aria-label="New status"><option value="">Keep status</option><?php foreach(['approved','declined','completed','cancelled'] as $s):?><option value="<?=$s?>"><?=ucfirst($s)?></option><?php endforeach;?></select><button class="btn btn-small btn-primary">Save</button></form></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="7" class="empty-cell">No appointments found.</td></tr><?php endif;?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php';?>
