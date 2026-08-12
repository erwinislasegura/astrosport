<?php
$similarUrl = !empty($photo['category_slug']) ? url('?category='.rawurlencode($photo['category_slug']).'#fotos') : url('#fotos');
$editorialDate = !empty($photo['event_date']) ? date('d/m/Y',strtotime($photo['event_date'])) : '';
?>
<div class="photo-selector editorial-photo-selector" data-photo-gallery data-cart-add-url="<?=url('carrito/agregar')?>" data-csrf="<?=csrf()?>">
 <?php foreach($related as $n=>$item):?>
 <article class="photo-choice" data-preview-image="<?=preview_url($item)?>" data-preview-title="<?=htmlspecialchars($item['title'])?>" data-photo-index="<?=$n+1?>" tabindex="0">
  <span class="editorial-photo-frame">
   <img src="<?=preview_url($item)?>" alt="<?=htmlspecialchars($item['title'])?>">
   <span class="editorial-photo-meta"><b><?=htmlspecialchars($photo['set_name']?:$item['title'])?></b><small><?=htmlspecialchars($photo['category_name']?:$photo['event_name'])?><?=$editorialDate?' · '.$editorialDate:''?></small></span>
   <span class="editorial-photo-actions"><a href="<?=$similarUrl?>">Imágenes similares</a><button type="button" data-save-photo="<?=$item['id']?>">＋ Guardar</button><button type="button" data-cart-photo data-photo-id="<?=$item['id']?>" aria-label="Agregar al carrito">🛒</button></span>
  </span>
  <small><?=htmlspecialchars($item['title'])?></small><b><?=money((int)$item['price'])?></b>
 </article>
 <?php endforeach;?>
</div>
