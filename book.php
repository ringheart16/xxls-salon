<?php
require_once __DIR__.'/includes/functions.php'; $user=require_login();
if($user['role']!=='customer') redirect('dashboard.php');
$services=db()->query("SELECT id,name,price,duration_minutes FROM services WHERE is_active=1 ORDER BY name")->fetchAll();
$staff=db()->query("SELECT id,name FROM users WHERE role='staff' AND is_active=1 ORDER BY name")->fetchAll();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $sid=(int)($_POST['service_id']??0); $date=$_POST['appointment_date']??''; $time=$_POST['appointment_time']??''; $staffId=($_POST['staff_id']??'')!==''?(int)$_POST['staff_id']:null; $notes=trim($_POST['notes']??'');
 $serviceStmt=db()->prepare("SELECT id,price FROM services WHERE id=? AND is_active=1");$serviceStmt->execute([$sid]);$service=$serviceStmt->fetch();
 $dt=DateTime::createFromFormat('Y-m-d H:i',"$date $time");
 if(!$service || !$dt || $dt->format('Y-m-d H:i')!=="$date $time" || $dt < new DateTime('now')) $error='Please choose a valid service, date, and time in the future.';
 else {
   $date=$dt->format('Y-m-d'); $time=$dt->format('H:i');
   if($staffId===null){
     $check=db()->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date=? AND appointment_time=? AND status IN ('pending','approved') AND staff_id IS NULL");
     $check->execute([$date,$time]);
   } else {
     $check=db()->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date=? AND appointment_time=? AND status IN ('pending','approved') AND staff_id=?");
     $check->execute([$date,$time,$staffId]);
   }
   if((int)$check->fetchColumn()>0) $error='That time slot is already reserved. Please choose another time.';
   else {
    $ins=db()->prepare("INSERT INTO appointments(user_id,service_id,staff_id,appointment_date,appointment_time,notes,total_amount,status) VALUES(?,?,?,?,?,?,?,'pending')");
    $ins->execute([$user['id'],$sid,$staffId,$date,$time,$notes,$service['price']]);
    flash('success','Your appointment request has been submitted. Please wait for salon confirmation.');redirect('my_appointments.php');
   }
 }
}
$pageTitle='Book Appointment';require __DIR__.'/includes/header.php';
?>
<div class="form-shell"><span class="eyebrow">RESERVE YOUR TIME</span><h1>Book an appointment</h1><p class="muted">Choose a service and preferred schedule. Your booking will be pending until approved by the salon.</p>
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<?php if(!$services):?><div class="alert error">There are no active services available for booking yet.</div><?php else:?>
<form method="post" class="form-card"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="form-grid"><label>Choose service<select name="service_id" required><option value="">Select a service</option><?php foreach($services as $s):?><option value="<?=$s['id']?>" <?=((int)($_GET['service']??0)===(int)$s['id']?'selected':'')?>><?=e($s['name'])?> — <?=money($s['price'])?> (<?=$s['duration_minutes']?> min)</option><?php endforeach;?></select></label>
<label>Preferred staff member<select name="staff_id"><option value="">Any available staff</option><?php foreach($staff as $st):?><option value="<?=$st['id']?>"><?=e($st['name'])?></option><?php endforeach;?></select></label>
<label>Appointment date<input type="date" name="appointment_date" min="<?=date('Y-m-d')?>" required></label>
<label>Appointment time<select name="appointment_time" required><option value="">Select a time</option><?php foreach(['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'] as $t):?><option value="<?=$t?>"><?=date('g:i A',strtotime($t))?></option><?php endforeach;?></select></label></div>
<label>Notes (optional)<textarea name="notes" rows="3" placeholder="Tell us anything we should know..."></textarea></label>
<button class="btn btn-primary">Submit appointment request</button></form><?php endif;?></div>
<?php require __DIR__.'/includes/footer.php';?>
