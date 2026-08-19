<?php
$heroDefaults=['eyebrow'=>'AstroSport · Archivo deportivo','title'=>'Fotografía que captura la intensidad del deporte.','description'=>'Encuentra tu evento, selecciona tus imágenes y recíbelas en alta resolución.','search_placeholder'=>'Busca por deporte, evento o ciudad','button_text'=>'Buscar','media_type'=>'youtube','video_url'=>'https://www.youtube.com/watch?v=1MieluM0c6c','background_url'=>'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1900&q=90'];
$hero=array_merge($heroDefaults,is_array($hero??null)?$hero:[]);
$heroTitle=trim($hero['title'].' '.($hero['highlight']??''));
$heroBackground=preg_match('~^https?://~i',(string)$hero['background_url'])?$hero['background_url']:url('hero-imagen?v='.rawurlencode((string)($hero['updated_at']??'')));
$heroMedia=in_array($hero['media_type']??'', ['image','local_video','youtube'], true)?$hero['media_type']:'image';
$heroVideo=trim((string)($hero['video_url']??''));
$youtubeId='';
if($heroMedia==='youtube'&&preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~',$heroVideo,$match))$youtubeId=$match[1];
$localVideo=$heroVideo!==''?(preg_match('~^https?://~i',$heroVideo)?$heroVideo:url(ltrim($heroVideo,'/'))):url('assets/hero-fluid.mp4');
?>
<section class="reference-hero hero-media-<?=htmlspecialchars($heroMedia)?>" id="inicio" style="--hero-image:url('<?=htmlspecialchars($heroBackground)?>');background-image:var(--hero-image);background-position:<?=htmlspecialchars($hero['background_position']??'center center')?>;background-size:cover">
 <?php if($heroMedia==='local_video'):?><video class="reference-hero-video" autoplay muted loop playsinline preload="metadata" poster="<?=htmlspecialchars($heroBackground)?>" aria-hidden="true"><source src="<?=htmlspecialchars($localVideo)?>" type="video/mp4"></video><?php endif;?>
 <?php if($heroMedia==='youtube'&&$youtubeId!==''):?><iframe class="reference-hero-youtube" src="https://www.youtube-nocookie.com/embed/<?=rawurlencode($youtubeId)?>?autoplay=1&amp;mute=1&amp;controls=0&amp;loop=1&amp;playlist=<?=rawurlencode($youtubeId)?>&amp;start=30&amp;playsinline=1&amp;rel=0&amp;modestbranding=1&amp;disablekb=1&amp;fs=0&amp;iv_load_policy=3&amp;cc_load_policy=0" title="Video de fondo AstroSport" allow="autoplay; encrypted-media" tabindex="-1" aria-hidden="true"></iframe><?php endif;?>
 <div class="reference-shade"></div><div class="reference-hero-copy"><span class="reference-eyebrow"><?=htmlspecialchars($hero['eyebrow'])?></span><h1><?=htmlspecialchars($heroTitle)?></h1><p><?=htmlspecialchars($hero['description'])?></p>
 <form class="reference-search" action="<?=url()?>" method="get"><span aria-hidden="true">⌕</span><input name="q" value="<?=htmlspecialchars($_GET['q']??'')?>" placeholder="<?=htmlspecialchars($hero['search_placeholder'])?>"><button><?=htmlspecialchars($hero['button_text'])?></button></form></div>
</section>
<?php if($events):?>
<section class="discovery-collections" aria-labelledby="featuredCollectionsTitle">
 <div class="discovery-heading"><div><span>COLECCIONES EDITORIALES</span><h2 id="featuredCollectionsTitle">Coberturas destacadas</h2><p>Explora eventos recientes y entra directamente a sus galerías deportivas.</p></div><a href="<?=url('eventos')?>">Ver todos los eventos →</a></div>
 <div class="discovery-track">
  <?php foreach(array_slice($events,0,4) as $event):?><?php $eventCover=!empty($event['cover_path'])?media($event['cover_path']):preview_url(['id'=>$event['cover_id'],'preview_path'=>$event['preview_path']]);?>
  <a class="discovery-event" href="<?=url('evento?slug='.rawurlencode($event['slug']))?>">
   <img src="<?=htmlspecialchars($eventCover)?>" alt="<?=htmlspecialchars($event['name'])?>">
   <span><?=htmlspecialchars($event['sport'])?></span><div><small><?=date('d M Y',strtotime($event['event_date']))?> · <?=htmlspecialchars($event['location']??'')?></small><h3><?=htmlspecialchars($event['name'])?></h3><p><?=$event['sets_count']?> sets · <?=$event['photos_count']?> fotografías</p></div>
  </a>
  <?php endforeach;?>
 </div>
</section>
<?php endif;?>
<section class="reference-archive" id="fotos">
 <div class="reference-archive-title"><div><span class="reference-kicker">Compra tus fotografías</span><h2>Últimas coberturas</h2></div><small><?=count($catalogSets)?> colecciones disponibles</small></div>
 <div class="reference-categories"><a class="<?=empty($categorySlug)?'active':''?>" href="<?=url('#fotos')?>">Todas</a><?php foreach($categories as $category):?><a class="<?=$categorySlug===$category['slug']?'active':''?>" href="<?=url('?category='.rawurlencode($category['slug']).'#fotos')?>"><?=htmlspecialchars($category['name'])?></a><?php endforeach;?></div>
 <form class="reference-filters" action="<?=url()?>" method="get"><label><span>Buscar archivo</span><input name="q" value="<?=htmlspecialchars($_GET['q']??'')?>" placeholder="Número, set o evento"></label><label><span>Ordenar</span><select name="orden"><option>Recientes</option><option>Nombre</option></select></label><button>Aplicar filtros</button><?php if(!empty($_GET['q'])):?><a href="<?=url('#fotos')?>">Limpiar</a><?php endif;?></form>
 <?php if($catalogSets):?><div class="reference-wall"><?php foreach($catalogSets as $index=>$set):?><?php $slides=$setPreviews[$set['id']]??[];$cover=$slides[0]??['id'=>$set['cover_id'],'preview_path'=>$set['preview_path']];?>
 <article class="reference-card"><a class="reference-photo <?=['landscape','portrait','square'][$index%3]?>" href="<?=url('foto?id='.$set['cover_id'])?>"><img src="<?=preview_url($cover)?>" alt="<?=htmlspecialchars($set['name'])?>"><b><?=htmlspecialchars($set['event_name'])?></b><span>Ver galería →</span></a><div class="reference-caption"><p><?=(int)$set['photos_count']?> fotografías<?=!empty($set['bib_number'])?' · Nº '.htmlspecialchars($set['bib_number']):''?></p><h3><?=htmlspecialchars($set['name'])?></h3><div><strong>Desde <?=money((int)$set['individual_price'])?></strong><a href="<?=url('foto?id='.$set['cover_id'])?>">Seleccionar</a></div></div></article>
 <?php endforeach;?></div><?php else:?><div class="reference-empty"><h3>No encontramos fotografías.</h3><a href="<?=url('#fotos')?>">Ver todo el archivo</a></div><?php endif;?>
</section>
<section class="reference-benefits"><div><b>Entrega digital HD</b><span>Archivos en alta resolución</span></div><div><b>Compra protegida</b><span>Confirmación antes del pago</span></div><div><b>Uso personal</b><span>Licencia incluida por fotografía</span></div></section>
<?php if($cta):?><section class="reference-contact"><div><span><?=htmlspecialchars($cta['eyebrow'])?></span><h2><?=htmlspecialchars($cta['title'])?></h2></div><a href="<?=htmlspecialchars($cta['button_url'])?>"><?=htmlspecialchars($cta['button_text'])?> →</a></section><?php endif;?>
