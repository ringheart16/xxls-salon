<?php
require_once __DIR__.'/includes/functions.php';
$pageTitle='Our Services'; $services=db()->query("SELECT * FROM services WHERE is_active=1 ORDER BY name")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="page-intro"><span class="eyebrow">A LITTLE SELF-CARE</span><h1>Services & pricing</h1><p>Find the perfect service for your next salon visit.</p></div>
<div class="service-grid">
<?php foreach($services as $s):?><article class="service-card"><div class="service-icon">✿</div><h3><?=e($s['name'])?></h3><p><?=e($s['description'])?></p><div class="service-bottom"><strong><?=money($s['price'])?></strong><span><?= (int)$s['duration_minutes']?> min</span></div><a class="btn btn-outline full" href="book.php?service=<?=(int)$s['id']?>">Book this service</a></article><?php endforeach;?>
<?php if(!$services):?><div class="empty-state">No services are currently listed.</div><?php endif;?></div>
<?php require __DIR__.'/includes/footer.php';?>
