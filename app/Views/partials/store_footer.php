<?php
$socialSettings = \App\Models\SocialSettings::get();
$socialLinks = \App\Models\SocialSettings::networkLinks($socialSettings);
$whatsappUrl = \App\Models\SocialSettings::whatsappUrl($socialSettings);
?>
<footer class="site-footer">
 <div class="footer-main<?=$socialLinks?' has-social':''?>">
  <div class="footer-brand"><img src="<?=url('assets/astrosport-logo.png')?>" alt="AstroSport"><p>Archivo profesional de fotografía deportiva. Encuentra, guarda y descarga los momentos que cuentan tu historia.</p></div>
  <div><b>EXPLORAR</b><a href="<?=url('eventos')?>">Eventos</a><a href="<?=url('#fotos')?>">Archivo fotográfico</a><a href="<?=url('preguntas-frecuentes')?>">Preguntas frecuentes</a></div>
  <div><b>CUENTA</b><a href="<?=url('mi-cuenta')?>">Mis pedidos</a><a href="<?=url('carrito')?>">Carrito</a><a href="mailto:contacto@astrosport.cl">Contacto</a></div>
  <?php if ($socialLinks): ?>
  <div class="footer-social"><b>SÍGUENOS</b><nav aria-label="Redes sociales AstroSport">
   <?php foreach ($socialLinks as $key => $network): ?>
   <a href="<?=htmlspecialchars($network['url'])?>" target="_blank" rel="noopener noreferrer" aria-label="Visitar AstroSport en <?=htmlspecialchars($network['label'])?>" title="<?=htmlspecialchars($network['label'])?>">
    <svg viewBox="0 0 24 24" aria-hidden="true">
     <?php if ($key === 'instagram'): ?><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.4" cy="6.6" r="1" class="fill"></circle>
     <?php elseif ($key === 'facebook'): ?><path class="fill" d="M13.7 22v-8h2.8l.4-3.2h-3.2V8.7c0-.9.3-1.6 1.7-1.6H17V4.2c-.7-.1-1.5-.2-2.4-.2-2.4 0-4.1 1.5-4.1 4.3v2.4H8V14h2.5v8h3.2Z"></path>
     <?php elseif ($key === 'tiktok'): ?><path class="fill" d="M15.6 3c.3 2.1 1.5 3.4 3.4 3.6v3a7.7 7.7 0 0 1-3.4-.9v6.2A6.1 6.1 0 1 1 10.3 9v3.1a3.1 3.1 0 1 0 2.2 2.9V3h3.1Z"></path>
     <?php elseif ($key === 'youtube'): ?><path class="fill" d="M21.5 7.2a2.6 2.6 0 0 0-1.8-1.9C18.1 4.9 12 4.9 12 4.9s-6.1 0-7.7.4a2.6 2.6 0 0 0-1.8 1.9A27 27 0 0 0 2.1 12a27 27 0 0 0 .4 4.8 2.6 2.6 0 0 0 1.8 1.9c1.6.4 7.7.4 7.7.4s6.1 0 7.7-.4a2.6 2.6 0 0 0 1.8-1.9 27 27 0 0 0 .4-4.8 27 27 0 0 0-.4-4.8ZM10 15.1V8.9l5.2 3.1-5.2 3.1Z"></path>
     <?php else: ?><path d="M4 4l16 16M20 4 4 20"></path><?php endif; ?>
    </svg><span><?=htmlspecialchars($network['label'])?></span>
   </a>
   <?php endforeach; ?>
  </nav></div>
  <?php endif; ?>
 </div>
 <div class="footer-bottom"><small>© <?=date('Y')?> ASTROSPORT · FOTOGRAFÍA DEPORTIVA</small><a class="footer-admin" href="<?=url('admin')?>">Administración</a></div>
</footer>

<?php if ($whatsappUrl): ?>
<a class="whatsapp-float is-<?=htmlspecialchars((string)($socialSettings['whatsapp_position']??'right'))?>" href="<?=htmlspecialchars($whatsappUrl)?>" target="_blank" rel="noopener noreferrer" aria-label="Contactar a AstroSport por WhatsApp">
 <span><?=htmlspecialchars((string)($socialSettings['whatsapp_label']??'¿Necesitas ayuda?'))?></span>
 <i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.075-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.002-5.45 4.438-9.886 9.892-9.886 2.64.001 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 7.002c-.002 5.45-4.438 9.887-9.889 9.889m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"></path></svg></i>
</a>
<?php endif; ?>

<script>document.getElementById('menuBtn')?.addEventListener('click',event=>{const nav=document.getElementById('nav'),open=nav?.classList.toggle('open')||false;event.currentTarget.setAttribute('aria-expanded',open?'true':'false')});document.querySelectorAll('.photo img,.detail-img img').forEach(img=>{img.draggable=false;img.addEventListener('contextmenu',event=>event.preventDefault())});</script>
