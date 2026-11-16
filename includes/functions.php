<?php
require_once __DIR__ . '/../config.php';

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . BASE_URL . '/' . ltrim($path, '/')); exit; }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): array {
    $u = current_user();
    if (!$u) { flash('error', 'Please log in to continue.'); redirect('login.php'); }
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('Access denied.'); }
    return $u;
}
function require_staff(): array {
    $u = require_login();
    if (!in_array($u['role'], ['staff', 'admin'], true)) { http_response_code(403); exit('Access denied.'); }
    return $u;
}
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) { $_SESSION['flash'][$key] = $message; return null; }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!hash_equals(csrf_token(), $token)) { http_response_code(419); exit('Invalid form token. Please refresh and try again.'); }
    }
}
function money($amount): string { return '₱' . number_format((float)$amount, 2); }
function record_payment(int $appointmentId, float $amount, string $method = 'cash'): void {
    $pdo = db();
    $paidAt = date('Y-m-d H:i:s');
    $existing = $pdo->prepare('SELECT id FROM payments WHERE appointment_id=?');
    $existing->execute([$appointmentId]);
    if ($existing->fetchColumn()) {
        $pdo->prepare("UPDATE payments SET amount=?, payment_method=?, payment_status='paid', paid_at=? WHERE appointment_id=?")
            ->execute([$amount, $method, $paidAt, $appointmentId]);
    } else {
        $pdo->prepare("INSERT INTO payments (appointment_id, amount, payment_method, payment_status, paid_at) VALUES (?, ?, ?, 'paid', ?)")
            ->execute([$appointmentId, $amount, $method, $paidAt]);
    }
}
function status_badge(string $status): string {
    $s = strtolower($status);
    return '<span class="badge badge-' . e($s) . '">' . e(ucfirst($s)) . '</span>';
}
function setting(string $key, string $fallback = ''): string { return $fallback; }
