<?php
$saved = !empty($photo['set_id']) ? \App\Models\PhotoPack::forSet((int)$photo['set_id'], false) : [];
$bySlot = [];
foreach ($saved as $option) $bySlot[(int)$option['slot']] = $option;
$defaults = [
    1 => ['quantity'=>5,'price'=>14990,'active'=>0],
    2 => ['quantity'=>10,'price'=>24990,'active'=>0],
    3 => ['quantity'=>15,'price'=>34990,'active'=>0],
];
?>
<div class="pack-config-three">
 <header><span><strong>COMBOS POR CANTIDAD</strong><small>Activa y configura hasta tres cantidades con precio fijo.</small></span><em>3 COMBOS</em></header>
 <?php foreach ([1,2,3] as $slot): $option = array_merge($defaults[$slot], $bySlot[$slot] ?? []); ?>
 <div class="pack-option-row">
  <label class="pack-check"><input type="checkbox" name="pack_active[<?=$slot?>]" <?=!empty($option['active'])?'checked':''?>><i></i><span>COMBO <?=$slot?></span></label>
  <label>CANTIDAD DE FOTOS<input name="pack_quantity[<?=$slot?>]" type="number" min="2" value="<?=(int)$option['quantity']?>"></label>
  <label>PRECIO DEL COMBO<input name="pack_price[<?=$slot?>]" type="number" min="0" value="<?=(int)$option['price']?>"></label>
 </div>
 <?php endforeach; ?>
</div>
