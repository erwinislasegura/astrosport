<?php
$similarUrl = !empty($photo['category_slug']) ? url('?category='.rawurlencode($photo['category_slug']).'#fotos') : url('#fotos');
$editorialDate = !empty($photo['event_date']) ? date('d/m/Y',strtotime($photo['event_date'])) : '';
?>
<div class="photo-selector editorial-photo-selector" data-photo-gallery data-cart-add-url="<?=url('carrito/agregar')?>" data-csrf="<?=csrf()?>">
 <?php foreach($related as $n=>$item):?>
 <?php $protectedPreview = htmlspecialchars(preview_url($item), ENT_QUOTES, 'UTF-8'); ?>
 <article class="photo-choice" data-preview-image="<?=$protectedPreview?>" data-preview-title="<?=htmlspecialchars($item['title'])?>" data-photo-index="<?=$n+1?>" tabindex="0">
  <span class="editorial-photo-frame">
   <img src="<?=$protectedPreview?>" alt="<?=htmlspecialchars($item['title'])?>" draggable="false">
   <span class="editorial-photo-meta"><b><?=htmlspecialchars($photo['set_name']?:$item['title'])?></b><small><?=htmlspecialchars($photo['category_name']?:$photo['event_name'])?><?=$editorialDate?' · '.$editorialDate:''?></small></span>
   <span class="editorial-photo-actions"><a href="<?=$similarUrl?>">Imágenes similares</a><button class="editorial-photo-expand" type="button" data-lightbox-open data-lightbox-src="<?=$protectedPreview?>" data-lightbox-title="<?=htmlspecialchars($item['title'])?>" data-lightbox-meta="<?=htmlspecialchars(($photo['set_name']?:$item['title']).' · '.($photo['category_name']?:$photo['event_name']))?>" aria-haspopup="dialog" aria-label="Ampliar <?=htmlspecialchars($item['title'])?>" title="Ampliar fotografía"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg><span>Ampliar</span></button><button type="button" data-save-photo="<?=$item['id']?>">＋ Guardar</button><button type="button" data-cart-photo data-photo-id="<?=$item['id']?>" aria-label="Agregar al carrito"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.3 8H7M10 20h.01M17 20h.01"/></svg><span>Carrito</span></button></span>
  </span>
  <small><?=htmlspecialchars($item['title'])?></small><b><?=money((int)$item['price'])?></b>
 </article>
 <?php endforeach;?>
</div>
