<?php
require_once __DIR__.'/includes/functions.php';require_admin();$pdo=db();
$daily=$pdo->query("SELECT CAST(p.paid_at AS DATE) AS sale_day,SUM(p.amount) total,COUNT(*) transactions FROM payments p WHERE p.payment_status='paid' GROUP BY CAST(p.paid_at AS DATE) ORDER BY sale_day DESC LIMIT 14")->fetchAll();
$totals=$pdo->query("SELECT COALESCE(SUM(amount),0) total,COUNT(*) transactions FROM payments WHERE payment_status='paid'")->fetch();
$byStatus=$pdo->query("SELECT status,COUNT(*) qty FROM appointments GROUP BY status")->fetchAll();
$pageTitle='Reports';require __DIR__.'/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">BUSINESS OVERVIEW</span><h1>Sales & reports</h1><p>Summary of payments recorded when appointments are marked completed.</p></div><div class="stats-grid"><div class="stat-card highlight"><span>Total recorded sales</span><strong><?=money($totals['total'])?></strong></div><div class="stat-card"><span>Paid transactions</span><strong><?=$totals['transactions']?></strong></div></div>
<div class="section-heading"><div><span class="eyebrow">PAYMENT HISTORY</span><h2>Recent daily sales</h2></div></div><div class="table-wrap"><table><thead><tr><th>Date</th><th>Transactions</th><th>Total sales</th></tr></thead><tbody><?php foreach($daily as $r):?><tr><td><?=date('M d, Y',strtotime($r['sale_day']))?></td><td><?=$r['transactions']?></td><td><strong><?=money($r['total'])?></strong></td></tr><?php endforeach;?><?php if(!$daily):?><tr><td colspan="3" class="empty-cell">No payments recorded yet.</td></tr><?php endif;?></tbody></table></div>
<div class="section-heading"><div><span class="eyebrow">BOOKING BREAKDOWN</span><h2>Appointments by status</h2></div></div><div class="stats-grid"><?php foreach($byStatus as $r):?><div class="stat-card"><span><?=e(ucfirst($r['status']))?></span><strong><?=$r['qty']?></strong></div><?php endforeach;?></div>
<?php require __DIR__.'/includes/footer.php';?>
