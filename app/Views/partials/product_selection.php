<?php
$packOptions = !empty($photo['set_id']) ? \App\Models\PhotoPack::forSet((int) $photo['set_id']) : [];
$hasCombos = !empty($packOptions);
$individualEnabled = !empty($photo['individual_enabled']);
$similarUrl = !empty($photo['category_slug']) ? url('?category='.rawurlencode($photo['category_slug']).'#fotos') : url('#fotos');
$editorialDate = !empty($photo['event_date']) ? date('d/m/Y', strtotime($photo['event_date'])) : '';
?>
<?php if ($hasCombos): ?>
<form class="pack-purchase-panel smart-photo-selection" method="post" action="<?=url('carrito/agregar-seleccion')?>" data-individual-enabled="<?=$individualEnabled?'1':'0'?>">
 <input type="hidden" name="_token" value="<?=csrf()?>">
 <input type="hidden" name="set_id" value="<?=(int)$photo['set_id']?>">
 <input type="hidden" name="return_photo_id" value="<?=(int)$photo['id']?>">
 <div class="pack-selector-summary"><span><small>ARMA TU COMPRA</small><strong>ELIGE TUS FOTOGRAFÍAS</strong></span><strong class="smart-total"><?=money((int)$photo['price'])?></strong></div>
 <div class="available-packs">
  <?php foreach ($packOptions as $slot => $option): ?>
  <div data-pack-quantity="<?=(int)$option['quantity']?>" data-pack-price="<?=(int)$option['price']?>"><small>COMBO <?=$slot+1?></small><b><?=(int)$option['quantity']?> FOTOS</b><strong><?=money((int)$option['price'])?></strong></div>
  <?php endforeach; ?>
 </div>
 <div class="pack-selection-status"><span><b class="smart-count">1</b> fotografía(s) seleccionada(s)</span><em class="smart-mode"><?=$individualEnabled?'VALOR INDIVIDUAL':'ELIGE UN COMBO'?></em></div>
<?php endif; ?>

<div class="photo-selector editorial-photo-selector<?=$hasCombos?' smart-selector':''?>" data-photo-gallery data-cart-add-url="<?=url('carrito/agregar')?>" data-csrf="<?=csrf()?>">
 <?php foreach ($related as $n => $item): ?>
 <?php $protectedPreview = htmlspecialchars(preview_url($item), ENT_QUOTES, 'UTF-8'); $selected = (int)$item['id'] === (int)$photo['id']; ?>
 <article class="photo-choice<?=$selected?' is-viewing':''?><?=$hasCombos&&$selected?' is-selected':''?>" data-preview-image="<?=$protectedPreview?>" data-preview-title="<?=htmlspecialchars($item['title'])?>" data-photo-index="<?=$n+1?>" tabindex="0">
  <?php if ($hasCombos): ?><input class="photo-pack-check" type="checkbox" name="ids[]" value="<?=(int)$item['id']?>" data-price="<?=(int)$item['price']?>" <?=$selected?'checked':''?>><?php endif; ?>
  <span class="editorial-photo-frame">
   <img src="<?=$protectedPreview?>" alt="<?=htmlspecialchars($item['title'])?>" draggable="false">
   <?php if ($hasCombos): ?><button class="photo-pack-toggle<?=$selected?' selected':''?>" type="button" data-select-photo aria-pressed="<?=$selected?'true':'false'?>"><?=$selected?'✓ SELECCIONADA':'＋ SELECCIONAR'?></button><?php endif; ?>
   <span class="editorial-photo-meta"><b><?=htmlspecialchars($photo['set_name']?:$item['title'])?></b><small><?=htmlspecialchars($photo['category_name']?:$photo['event_name'])?><?=$editorialDate?' · '.$editorialDate:''?></small></span>
   <span class="editorial-photo-actions"><a href="<?=$similarUrl?>">Imágenes similares</a><button class="editorial-photo-expand" type="button" data-lightbox-open data-lightbox-src="<?=$protectedPreview?>" data-lightbox-title="<?=htmlspecialchars($item['title'])?>" data-lightbox-meta="<?=htmlspecialchars(($photo['set_name']?:$item['title']).' · '.($photo['category_name']?:$photo['event_name']))?>" aria-haspopup="dialog" aria-label="Ampliar <?=htmlspecialchars($item['title'])?>" title="Ampliar fotografía"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5"/></svg><span>Ampliar</span></button><button type="button" data-save-photo="<?=(int)$item['id']?>">＋ Guardar</button><?php if ($individualEnabled): ?><button type="button" data-cart-photo data-photo-id="<?=(int)$item['id']?>" aria-label="Agregar fotografía individual al carrito"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 10.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.3 8H7M10 20h.01M17 20h.01"/></svg><span>Individual</span></button><?php endif; ?></span>
  </span>
  <small><?=htmlspecialchars($item['title'])?></small><b><?=money((int)$item['price'])?></b>
 </article>
 <?php endforeach; ?>
</div>

<?php if ($hasCombos): ?>
 <div class="pack-purchase-footer"><p><?=$individualEnabled?'Al alcanzar una cantidad configurada se aplicará automáticamente el precio del combo.':'Selecciona exactamente una de las cantidades disponibles para comprar.'?></p><button class="desktop-selection-submit" type="submit">AGREGAR SELECCIÓN AL CARRITO →</button></div>
</form>
<?php endif; ?>
