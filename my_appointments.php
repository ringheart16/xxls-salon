<?php
require_once __DIR__.'/includes/functions.php';$user=require_login();if($user['role']!=='customer')redirect('dashboard.php');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$id=(int)($_POST['id']??0);$st=db()->prepare("UPDATE appointments SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");$st->execute([$id,$user['id']]);flash($st->rowCount()?'success':'error',$st->rowCount()?'Appointment cancelled.':'This appointment can no longer be cancelled.');redirect('my_appointments.php');}
$st=db()->prepare("SELECT a.*,s.name service_name,u.name staff_name FROM appointments a JOIN services s ON s.id=a.service_id LEFT JOIN users u ON u.id=a.staff_id WHERE a.user_id=? ORDER BY a.appointment_date DESC,a.appointment_time DESC");$st->execute([$user['id']]);$rows=$st->fetchAll();
$pageTitle='My Appointments';require __DIR__.'/includes/header.php';
?>
<div class="page-intro row-between"><div><span class="eyebrow">YOUR SALON VISITS</span><h1>My appointments</h1><p>Review your booking requests and appointment history.</p></div><a class="btn btn-primary" href="book.php">+ New booking</a></div>
<div class="table-wrap"><table><thead><tr><th>Service</th><th>Date & time</th><th>Staff</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?=e($r['service_name'])?></strong></td><td><?=date('M d, Y',strtotime($r['appointment_date']))?><br><small><?=date('g:i A',strtotime($r['appointment_time']))?></small></td><td><?=e($r['staff_name']??'Any available')?></td><td><?=money($r['total_amount'])?></td><td><?=status_badge($r['status'])?></td><td><?php if($r['status']==='pending'):?><form method="post" data-confirm="Cancel this appointment?"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-small btn-danger">Cancel</button></form><?php else:?>—<?php endif;?></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="6" class="empty-cell">You have no appointments yet.</td></tr><?php endif;?></tbody></table></div>
<?php require __DIR__.'/includes/footer.php';?>
