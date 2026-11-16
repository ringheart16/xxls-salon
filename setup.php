<?php
require_once __DIR__ . '/includes/functions.php';
$done = false; $error = '';
try {
    $pdo = db();
    $count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($count > 0) $done = true;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$done) {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) throw new RuntimeException('Enter a name, valid email, and password with at least 8 characters.');
        $st = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'admin')");
        $st->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
        $done = true;
    }
} catch (Throwable $ex) { $error = $ex->getMessage(); }
$pageTitle = 'Initial Setup';
require __DIR__ . '/includes/header.php';
?>
<div class="form-shell narrow">
  <span class="eyebrow">ONE-TIME CONFIGURATION</span><h1>Set up your salon admin</h1>
  <?php if ($done): ?><div class="alert success">Admin account exists or was created successfully. You can now <a href="login.php">log in</a>. For security, delete or rename setup.php after setup.</div>
  <?php else: ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form-card">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label>Admin full name<input name="name" required></label>
      <label>Email address<input type="email" name="email" required></label>
      <label>Password (minimum 8 characters)<input type="password" name="password" minlength="8" required></label>
      <button class="btn btn-primary full">Create admin account</button>
    </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
