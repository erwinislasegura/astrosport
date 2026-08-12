<?php $heroEvent=$events[0]??null;$heroCover=$heroEvent?(!empty($heroEvent['cover_path'])?media($heroEvent['cover_path']):preview_url(['id'=>$heroEvent['cover_id'],'preview_path'=>$heroEvent['preview_path']])):'';?>
<section class="events-home-hero"<?=$heroCover?' style="--events-cover:url(\''.htmlspecialchars($heroCover).'\')"':''?>>
 <div class="events-home-shade"></div><div class="events-home-copy"><span>ARCHIVO DEPORTIVO ASTROSPORT</span><h1>Encuentra las fotos<br>de tu evento.</h1><p>Explora las últimas coberturas deportivas, busca por disciplina o ciudad y accede a todas las fotografías del set.</p><label class="events-live-search"><i>⌕</i><input type="search" data-event-search placeholder="Buscar evento, deporte o ciudad…"><button type="button" data-event-search-clear>Limpiar</button></label></div>
</section>

<section class="events-home-catalog">
 <div class="events-home-heading"><div><span>COLECCIONES PUBLICADAS</span><h2>Últimos eventos</h2><p>Selecciona una cobertura para revisar todos sus sets y fotografías.</p></div><b data-event-count><?=count($events)?> eventos</b></div>
 <div class="events-home-grid" data-events-grid>
  <?php foreach($events as $event):?>
  <?php $cover=!empty($event['cover_path'])?media($event['cover_path']):preview_url(['id'=>$event['cover_id'],'preview_path'=>$event['preview_path']]);?>
  <a class="events-home-card" data-event-card data-search="<?=htmlspecialchars(strtolower($event['name'].' '.$event['sport'].' '.($event['location']??'').' '.$event['event_date']))?>" href="<?=url('evento?slug='.$event['slug'])?>">
   <div class="events-home-cover"><img src="<?=htmlspecialchars($cover)?>" alt="<?=htmlspecialchars($event['name'])?>"><span><?=htmlspecialchars(strtoupper($event['sport']))?></span><i>Ver galería →</i></div>
   <div class="events-home-info"><small><?=date('d M Y',strtotime($event['event_date']))?> · <?=htmlspecialchars($event['location']??'')?></small><h2><?=htmlspecialchars($event['name'])?></h2><p><?=htmlspecialchars($event['description']?:'Revisa todos los sets fotográficos disponibles de este evento.')?></p><div><span><b><?=$event['sets_count']?></b> sets</span><span><b><?=$event['photos_count']?></b> fotografías</span><strong>Explorar</strong></div></div>
  </a>
  <?php endforeach;?>
 </div>
 <div class="events-no-results" data-events-empty hidden><h2>No encontramos eventos</h2><p>Prueba con otro deporte, nombre o ciudad.</p></div>
 <?php if(!$events):?><div class="events-no-results"><h2>Próximamente</h2><p>Todavía no hay eventos publicados con sets disponibles.</p></div><?php endif;?>
</section>
<script>const eventSearch=document.querySelector('[data-event-search]'),eventCards=[...document.querySelectorAll('[data-event-card]')],eventCount=document.querySelector('[data-event-count]'),eventEmpty=document.querySelector('[data-events-empty]');function filterEvents(){const q=(eventSearch?.value||'').trim().toLocaleLowerCase('es');let visible=0;eventCards.forEach(card=>{const show=!q||card.dataset.search.toLocaleLowerCase('es').includes(q);card.hidden=!show;if(show)visible++});if(eventCount)eventCount.textContent=visible+' '+(visible===1?'evento':'eventos');if(eventEmpty)eventEmpty.hidden=visible!==0}eventSearch?.addEventListener('input',filterEvents);document.querySelector('[data-event-search-clear]')?.addEventListener('click',()=>{if(eventSearch){eventSearch.value='';filterEvents();eventSearch.focus()}});</script>
