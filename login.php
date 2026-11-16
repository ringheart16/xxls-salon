<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) redirect('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $email=strtolower(trim($_POST['email']??'')); $pass=$_POST['password']??'';
 $st=db()->prepare("SELECT id,name,email,phone,password,role FROM users WHERE email=? AND is_active=1 LIMIT 1"); $st->execute([$email]); $u=$st->fetch();
 if($u && password_verify($pass,$u['password'])){session_regenerate_id(true); unset($u['password']); $_SESSION['user']=$u; redirect($u['role']==='admin' ? 'dashboard.php' : ($u['role']==='staff' ? 'staff_dashboard.php' : 'index.php'));}
 $error='Incorrect email or password.';
}
$pageTitle='Login'; require __DIR__.'/includes/header.php';
?>
<div class="form-shell narrow"><span class="eyebrow">WELCOME BACK</span><h1>Log in to your account</h1><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<form method="post" class="form-card"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Email address<input type="email" name="email" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-primary full">Log in</button><p class="form-foot">New to XXL’s Salon? <a href="register.php">Create an account</a></p></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
