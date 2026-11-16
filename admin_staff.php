<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();
$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'create';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'toggle' && $id > 0) {
        $st = $pdo->prepare("UPDATE users SET is_active = CASE WHEN is_active=1 THEN 0 ELSE 1 END WHERE id=? AND role='staff'");
        $st->execute([$id]);
        flash('success', 'Staff account status updated.');
        redirect('admin_staff.php');
    }
    if ($action === 'update' && $id > 0) {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a name and valid email address.';
        } else {
            try {
                if ($password !== '') {
                    if (strlen($password) < 8) throw new RuntimeException('Password must contain at least 8 characters.');
                    $st = $pdo->prepare("UPDATE users SET name=?,email=?,phone=?,password=? WHERE id=? AND role='staff'");
                    $st->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT),$id]);
                } else {
                    $st = $pdo->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=? AND role='staff'");
                    $st->execute([$name,$email,$phone,$id]);
                }
                flash('success', 'Staff details updated.');
                redirect('admin_staff.php');
            } catch (PDOException $ex) { $error = 'That email address is already in use.'; }
              catch (RuntimeException $ex) { $error = $ex->getMessage(); }
        }
    }
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error = 'Enter a name, valid email, and password with at least 8 characters.';
        } else {
            try {
                $st = $pdo->prepare("INSERT INTO users (name,email,phone,password,role,is_active) VALUES (?,?,?,?, 'staff',1)");
                $st->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT)]);
                flash('success', 'Staff account created successfully.');
                redirect('admin_staff.php');
            } catch (PDOException $ex) { $error = 'That email address is already registered.'; }
        }
    }
}
$editId = (int)($_GET['edit'] ?? 0);
$edit = null;
if ($editId) { $st=$pdo->prepare("SELECT id,name,email,phone FROM users WHERE id=? AND role='staff'"); $st->execute([$editId]); $edit=$st->fetch(); }
$rows = $pdo->query("SELECT u.id,u.name,u.email,u.phone,u.is_active,COUNT(a.id) assigned_count FROM users u LEFT JOIN appointments a ON a.staff_id=u.id WHERE u.role='staff' GROUP BY u.id ORDER BY u.name")->fetchAll();
$pageTitle = 'Staff Management';
require __DIR__ . '/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">YOUR TEAM</span><h1>Staff management</h1><p>Create staff logins, update details, and control account access.</p></div>
<?php if ($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<div class="admin-columns">
<form method="post" class="form-card">
<h2><?=$edit ? 'Edit staff member' : 'Add staff member'?></h2>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="<?=$edit ? 'update' : 'create'?>">
<?php if ($edit): ?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif; ?>
<label>Full name<input name="name" value="<?=e($edit['name'] ?? '')?>" required></label>
<label>Email address<input type="email" name="email" value="<?=e($edit['email'] ?? '')?>" required></label>
<label>Phone number<input name="phone" value="<?=e($edit['phone'] ?? '')?>"></label>
<label><?=$edit ? 'New password (leave blank to keep current)' : 'Temporary password (minimum 8 characters)'?><input type="password" name="password" minlength="8" <?=$edit ? '' : 'required'?>></label>
<button class="btn btn-primary full"><?=$edit ? 'Save changes' : 'Create staff account'?></button>
<?php if ($edit): ?><a class="btn btn-light full" href="admin_staff.php">Cancel editing</a><?php endif; ?>
</form>
<div class="table-wrap"><table><thead><tr><th>Staff member</th><th>Contact</th><th>Assigned</th><th>Account</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><strong><?=e($r['name'])?></strong></td><td><?=e($r['email'])?><br><small><?=e($r['phone'] ?? '')?></small></td><td><?=$r['assigned_count']?></td><td><?=((int)$r['is_active']===1)?'<span class="badge badge-completed">Active</span>':'<span class="badge badge-cancelled">Inactive</span>'?></td><td><div class="inline-form"><a class="btn btn-small btn-outline" href="?edit=<?=$r['id']?>">Edit</a><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-small <?=$r['is_active']?'btn-danger':'btn-primary'?>"><?=$r['is_active']?'Deactivate':'Activate'?></button></form></div></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty-cell">No staff accounts yet. Add your first team member.</td></tr><?php endif; ?>
</tbody></table></div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
