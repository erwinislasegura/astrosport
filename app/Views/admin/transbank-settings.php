<?php
$appUrl = rtrim(url(), '/');
$isPublicHttps = str_starts_with($appUrl, 'https://') && !preg_match('~https://(localhost|127\.0\.0\.1)(?:[:/]|$)~i', $appUrl);
?>
<div class="content">
 <section class="title"><div><span class="eyebrow">PAGOS ONLINE</span><h1>WEBPAY PLUS <em>TRANSBANK.</em></h1><p>Configura la pasarela oficial para pagos con tarjetas de crédito, débito y prepago.</p></div><a class="btn btn-secondary" href="https://www.transbankdevelopers.cl/documentacion/webpay-plus" target="_blank" rel="noopener">DOCUMENTACIÓN OFICIAL ↗</a></section>
 <?php if(!empty($_SESSION['success'])):?><div class="flash success"><?=htmlspecialchars($_SESSION['success']);unset($_SESSION['success']);?></div><?php endif;?>
 <?php if(!empty($_SESSION['error'])):?><div class="flash error"><?=htmlspecialchars($_SESSION['error']);unset($_SESSION['error']);?></div><?php endif;?>
 <div class="flow-admin-grid transbank-admin-grid">
  <form class="panel flow-settings-form" method="post"><input type="hidden" name="_token" value="<?=csrf()?>">
   <div class="gateway-head transbank-head"><span>tbk</span><div><h2>WEBPAY PLUS</h2><p>Integración REST API 1.2 de Transbank.</p></div><em class="gateway-state <?=!empty($settings['active'])?'online':'offline'?>"><?=!empty($settings['active'])?'ACTIVA':'INACTIVA'?></em></div>
   <label class="switch-row"><span><b>HABILITAR TRANSBANK EN EL CHECKOUT</b><small>Los clientes podrán elegir Webpay Plus antes de pagar.</small></span><input type="checkbox" name="active" <?=!empty($settings['active'])?'checked':''?>></label>
   <label>AMBIENTE<select name="environment"><option value="sandbox" <?=($settings['environment']??'sandbox')==='sandbox'?'selected':''?>>Integración · Pruebas</option><option value="production" <?=($settings['environment']??'sandbox')==='production'?'selected':''?>>Producción · Pagos reales</option></select></label>
   <label>TBK-API-KEY-ID · CÓDIGO DE COMERCIO<input name="commerce_code" inputmode="numeric" maxlength="20" value="<?=htmlspecialchars((string)($settings['api_key']??''))?>" autocomplete="off" required placeholder="Código de comercio entregado por Transbank"></label>
   <label>TBK-API-KEY-SECRET<input name="api_key_secret" type="password" maxlength="255" autocomplete="new-password" placeholder="<?=empty($settings['secret_key'])?'Ingresa la llave secreta':'•••••••••••••••• · dejar vacío para conservar'?>"><small>La llave se cifra con AES-256-GCM y nunca vuelve a mostrarse.</small></label>
   <button class="btn btn-primary" type="submit">GUARDAR CONFIGURACIÓN →</button>
  </form>
  <aside class="panel gateway-guide transbank-guide"><span class="eyebrow">ESTADO</span><h2><?=!empty($settings['active'])?'LISTA PARA EL CHECKOUT':'CONFIGURACIÓN PENDIENTE'?></h2><div><b>AMBIENTE</b><strong><?=($settings['environment']??'sandbox')==='production'?'PRODUCCIÓN':'INTEGRACIÓN'?></strong></div><div><b>CÓDIGO DE COMERCIO</b><strong><?=!empty($settings['api_key'])?'CONFIGURADO':'PENDIENTE'?></strong></div><div><b>LLAVE SECRETA</b><strong><?=!empty($settings['secret_key'])?'PROTEGIDA':'PENDIENTE'?></strong></div><div><b>URL DE RETORNO</b><strong class="<?=$isPublicHttps?'ready':'warning'?>"><?=$isPublicHttps?'HTTPS LISTA':'REVISAR APP_URL'?></strong></div><article><b>RETORNO CONFIGURADO</b><p><?=htmlspecialchars(($appUrl!==''?$appUrl:'http://localhost').'/pago/transbank/retorno')?></p></article><?php if(!$isPublicHttps):?><p class="gateway-warning">Para operar en producción, configura <b>APP_URL</b> con el dominio público HTTPS. Transbank no puede retornar pagos hacia localhost.</p><?php endif;?></aside>
 </div>
</div>
