<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??''); $pass=$_POST['password']??'';
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<8) $error='Please complete all fields and use a password with at least 8 characters.';
    else {
        try {
            $st=db()->prepare("INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?, 'customer')");
            $st->execute([$name,$email,$phone,password_hash($pass,PASSWORD_DEFAULT)]);
            flash('success','Account created! You can now log in.'); redirect('login.php');
        } catch (PDOException $ex) { $error = in_array((string)$ex->getCode(), ['23000','23505'], true) ? 'That email is already registered.' : 'Unable to create account. Please try again.'; }
    }
}
$pageTitle='Create Account'; require __DIR__.'/includes/header.php';
?>
<div class="form-shell narrow"><span class="eyebrow">JOIN OUR SALON</span><h1>Create your account</h1><p class="muted">Book appointments and keep track of your visits.</p>
<?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<form method="post" class="form-card"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Full name<input name="name" required value="<?=e($_POST['name']??'')?>"></label>
<label>Email address<input type="email" name="email" required value="<?=e($_POST['email']??'')?>"></label>
<label>Phone number<input name="phone" value="<?=e($_POST['phone']??'')?>"></label>
<label>Password<input type="password" name="password" minlength="8" required></label>
<button class="btn btn-primary full">Create account</button><p class="form-foot">Already registered? <a href="login.php">Log in</a></p></form></div>
<?php require __DIR__.'/includes/footer.php'; ?>
