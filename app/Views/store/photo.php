<?php
$setTitle = (string)($photo['set_name'] ?: $photo['title']);
$eventName = (string)$photo['event_name'];
$photoCount = count($related);
$photoLabel = $photoCount === 1 ? 'fotografía' : 'fotografías';
$bibNumber = trim((string)($photo['bib_number'] ?? ''));
?>
<?php if (!empty($_SESSION['error'])): ?>
<div class="demo-note product-alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); endif; ?>

<section class="editorial-detail">
 <style>.editorial-detail{width:calc(100% - 12px)!important;max-width:none!important}.editorial-photo-meta,.editorial-photo-meta b,.editorial-photo-meta small,.editorial-photo-actions a,.editorial-photo-actions button{color:#fff!important}.editorial-photo-actions button svg,.editorial-photo-actions button i{color:#fff!important;fill:#fff!important;stroke:#fff!important}.editorial-photo-actions button[data-cart-photo].selected{background:rgba(8,11,16,.78)!important}</style>
 <header class="editorial-set-head">
  <div>
   <span><?=htmlspecialchars(strtoupper($photo['category_name']?:$eventName))?></span>
   <h1><?= htmlspecialchars($setTitle) ?></h1>
   <p><?=$photoCount?> <?=$photoLabel?> disponibles en alta resolución<?=$bibNumber!==''?' · Competidor #'.htmlspecialchars($bibNumber):''?></p>
  </div>
 </header>

 <div class="editorial-toolbar">
  <p><?= $photoCount ?> <?= $photoLabel ?> en alta resolución<?php if ($bibNumber !== ''): ?> · Competidor #<?= htmlspecialchars($bibNumber) ?><?php endif; ?></p>
  <div class="editorial-view-buttons" aria-label="Cambiar presentación"><button class="active" type="button" data-gallery-view="mosaic" aria-label="Vista mosaico" title="Vista mosaico">▦</button><button type="button" data-gallery-view="list" aria-label="Vista lista" title="Vista lista">☷</button></div>
 </div>

 <div class="editorial-gallery" data-editorial-gallery>
  <?php require ROOT.'/app/Views/partials/product_selection.php';?>
 </div>

 <?php if($photo['set_id']&&!empty($photo['set_enabled'])):?>
 <div class="editorial-set-buy" id="compra-set">
  <div><span>COLECCIÓN COMPLETA</span><h2>DESCARGA TODO EL SET</h2><p><?=count($related)?> fotografías originales, sin marca de agua y reunidas en un archivo ZIP.</p></div>
  <ul><li>Alta resolución</li><li>Descarga digital</li><li>Pago seguro con Flow</li></ul>
  <div><strong><small>VALOR DEL SET</small><?=money((int)$photo['set_price'])?></strong><form method="post" action="<?=url('carrito/agregar')?>"><input type="hidden" name="_token" value="<?=csrf()?>"><input type="hidden" name="type" value="set"><input type="hidden" name="id" value="<?=$photo['set_id']?>"><button>COMPRAR SET →</button></form></div>
 </div>
 <?php endif;?>
</section>

<script>
document.querySelectorAll('[data-gallery-view]').forEach(button=>button.addEventListener('click',()=>{
 const gallery=document.querySelector('[data-editorial-gallery]');
 document.querySelectorAll('[data-gallery-view]').forEach(item=>item.classList.toggle('active',item===button));
 gallery?.classList.toggle('is-list',button.dataset.galleryView==='list');
}));
</script>
