<?php
$currentPath = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$basePath = trim((string)parse_url(url(), PHP_URL_PATH), '/');
$relativePath = $basePath !== '' && str_starts_with($currentPath, $basePath) ? trim(substr($currentPath, strlen($basePath)), '/') : $currentPath;
$isHome = $relativePath === '';
$isEvents = $relativePath === 'eventos' || $relativePath === 'evento';
$isArchive = $isHome || $relativePath === 'foto';
$isHelp = $relativePath === 'preguntas-frecuentes';
$isAccount = str_starts_with($relativePath, 'mi-cuenta');
?>
<div class="site-topline"><span><?=htmlspecialchars($toplineLeft??'ARCHIVO DE FOTOGRAFÍA DEPORTIVA')?></span><span><?=htmlspecialchars($toplineRight??'ALTA RESOLUCIÓN · DESCARGA DIGITAL')?></span></div>
<header class="site-header">
 <a class="site-brand" href="<?=url()?>"><img src="<?=url('assets/astrosport-logo.png')?>" alt="AstroSport"></a>
 <nav class="site-nav" id="nav" aria-label="Navegación principal"><a class="<?=$isHome?'active':''?>" href="<?=url()?>">Inicio</a><a class="<?=$isEvents?'active':''?>" href="<?=url('eventos')?>">Eventos</a><a class="<?=$isArchive?'active':''?>" href="<?=url('#fotos')?>">Fotografías</a><a class="<?=$isHelp?'active':''?>" href="<?=url('preguntas-frecuentes')?>">Ayuda</a><a class="<?=$isAccount?'active':''?>" href="<?=url('mi-cuenta')?>"><?=customer_user()?'Mi cuenta':'Ingresar'?></a></nav>
 <div class="site-actions"><a class="site-search" href="<?=url('#fotos')?>" aria-label="Buscar fotografías"><span aria-hidden="true">⌕</span><b>Buscar</b></a><a class="site-cart" href="<?=url('carrito')?>"><span>Carrito</span><b class="cart-count"><?=count($_SESSION['cart']??[])?></b></a><button class="site-menu-toggle" id="menuBtn" type="button" aria-label="Abrir navegación" aria-controls="nav" aria-expanded="false"><i></i><i></i><i></i></button></div>
</header>
