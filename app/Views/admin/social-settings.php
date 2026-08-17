<?php
$networks = [
    'instagram' => ['name' => 'Instagram', 'hint' => 'Perfil y contenido fotográfico', 'mark' => 'IG'],
    'facebook' => ['name' => 'Facebook', 'hint' => 'Página oficial de la marca', 'mark' => 'f'],
    'tiktok' => ['name' => 'TikTok', 'hint' => 'Videos y cobertura de eventos', 'mark' => '♪'],
    'youtube' => ['name' => 'YouTube', 'hint' => 'Canal y producciones audiovisuales', 'mark' => '▶'],
    'x' => ['name' => 'X / Twitter', 'hint' => 'Noticias y actualizaciones', 'mark' => 'X'],
];
?>
<div class="content social-admin-content">
 <section class="title social-admin-title">
  <div><span class="eyebrow">CANALES DIGITALES</span><h1>REDES SOCIALES <em>Y WHATSAPP.</em></h1><p>Administra los enlaces públicos y el canal de contacto flotante de toda la tienda.</p></div>
  <span class="social-module-state"><i></i> MÓDULO ACTIVO</span>
 </section>

 <?php if (!empty($_SESSION['success'])): ?><div class="flash success"><?=htmlspecialchars($_SESSION['success']);unset($_SESSION['success']);?></div><?php endif; ?>
 <?php if (!empty($_SESSION['error'])): ?><div class="flash error"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div><?php endif; ?>

 <form class="social-settings-form" method="post">
  <input type="hidden" name="_token" value="<?=csrf()?>">
  <section class="panel social-master-card">
   <div><span class="social-card-number">01</span><span><b>ENLACES EN EL PIE DE PÁGINA</b><small>Muestra únicamente las redes activadas y con una dirección válida.</small></span></div>
   <label class="social-toggle"><input type="checkbox" name="footer_active" <?=!empty($settings['footer_active'])?'checked':''?>><i></i><span>PUBLICAR REDES</span></label>
  </section>

  <div class="social-admin-grid">
   <section class="panel social-network-card">
    <header><div><span class="social-card-number">02</span><span><b>PERFILES OFICIALES</b><small>Configura las direcciones completas de cada plataforma.</small></span></div><em>5 REDES</em></header>
    <div class="social-network-list">
     <?php foreach ($networks as $key => $network): ?>
     <article class="social-network-row social-network-<?=$key?>">
      <span class="social-network-mark" aria-hidden="true"><?=$network['mark']?></span>
      <label><b><?=$network['name']?></b><small><?=$network['hint']?></small><input name="<?=$key?>_url" type="text" inputmode="url" maxlength="500" value="<?=htmlspecialchars((string)($settings[$key.'_url']??''))?>" placeholder="https://..."></label>
      <label class="social-toggle compact"><input type="checkbox" name="<?=$key?>_active" <?=!empty($settings[$key.'_active'])?'checked':''?>><i></i><span>ACTIVA</span></label>
     </article>
     <?php endforeach; ?>
    </div>
   </section>

   <section class="panel whatsapp-admin-card">
    <header><div><span class="social-card-number whatsapp">03</span><span><b>BOTÓN FLOTANTE DE WHATSAPP</b><small>Contacto directo visible en escritorio, tablet y celular.</small></span></div><label class="social-toggle compact"><input type="checkbox" name="whatsapp_active" <?=!empty($settings['whatsapp_active'])?'checked':''?>><i></i><span>ACTIVO</span></label></header>
    <div class="whatsapp-config-grid">
     <label>NÚMERO CON CÓDIGO DE PAÍS<input name="whatsapp_number" type="tel" inputmode="numeric" value="<?=htmlspecialchars((string)($settings['whatsapp_number']??''))?>" placeholder="56912345678"><small>Solo números, sin +, espacios ni guiones.</small></label>
     <label>TEXTO VISIBLE<input name="whatsapp_label" maxlength="80" value="<?=htmlspecialchars((string)($settings['whatsapp_label']??''))?>" placeholder="¿Necesitas ayuda?"></label>
     <label class="field-wide">MENSAJE INICIAL<textarea name="whatsapp_message" maxlength="300" rows="4"><?=htmlspecialchars((string)($settings['whatsapp_message']??''))?></textarea><small>Se completará automáticamente al abrir la conversación.</small></label>
     <fieldset class="field-wide"><legend>POSICIÓN DEL BOTÓN</legend><label><input type="radio" name="whatsapp_position" value="right" <?=($settings['whatsapp_position']??'right')==='right'?'checked':''?>> Inferior derecha</label><label><input type="radio" name="whatsapp_position" value="left" <?=($settings['whatsapp_position']??'right')==='left'?'checked':''?>> Inferior izquierda</label></fieldset>
    </div>
    <div class="whatsapp-admin-preview">
     <span>VISTA PREVIA</span>
     <div><strong><?=htmlspecialchars((string)($settings['whatsapp_label']??'¿Necesitas ayuda?'))?></strong><i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.075-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.002-5.45 4.438-9.886 9.892-9.886 2.64.001 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 7.002c-.002 5.45-4.438 9.887-9.889 9.889m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg></i></div>
    </div>
   </section>
  </div>

  <div class="social-save-bar"><span><b>CONFIGURACIÓN GLOBAL</b><small>Los cambios se reflejan inmediatamente en toda la web.</small></span><button type="submit">GUARDAR CONFIGURACIÓN →</button></div>
 </form>
</div>
