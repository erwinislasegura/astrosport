<?php
$cta = array_merge([
    'eyebrow' => 'Para clubes y organizaciones',
    'title' => '¿Necesitas cobertura para tu evento?',
    'description' => '',
    'button_text' => 'Solicitar propuesta',
    'button_url' => 'mailto:contacto@astrosport.cl',
    'active' => 1,
], is_array($cta ?? null) ? $cta : []);
?>
<div class="content cta-admin-content">
    <section class="title">
        <div><span class="eyebrow">PORTADA</span><h1>CTA de contacto</h1><p>Administra la franja para solicitar cobertura fotográfica de eventos.</p></div>
        <a class="btn btn-secondary" href="<?= url() ?>" target="_blank" rel="noopener">VER PORTADA ↗</a>
    </section>
    <?php if (!empty($_SESSION['success'])): ?><div class="flash success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?><div class="flash error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div><?php endif; ?>
    <div class="cta-admin-grid cta-contact-admin">
        <form class="panel cta-form" method="post" id="ctaForm">
            <input type="hidden" name="_token" value="<?= csrf() ?>">
            <label class="switch-row"><span><b>MOSTRAR CTA EN PORTADA</b><small>Activa o desactiva completamente esta franja.</small></span><input type="checkbox" name="active" <?= !empty($cta['active']) ? 'checked' : '' ?>></label>
            <label>ETIQUETA SUPERIOR<input name="eyebrow" maxlength="100" required value="<?= htmlspecialchars($cta['eyebrow']) ?>"></label>
            <label>TÍTULO PRINCIPAL<input name="title" maxlength="180" required value="<?= htmlspecialchars($cta['title']) ?>"></label>
            <div class="two-fields">
                <label>TEXTO DEL BOTÓN<input name="button_text" maxlength="80" required value="<?= htmlspecialchars($cta['button_text']) ?>"></label>
                <label>ENLACE DEL BOTÓN<input name="button_url" maxlength="255" required value="<?= htmlspecialchars($cta['button_url']) ?>"><small>Admite una página, correo con mailto: o enlace externo.</small></label>
            </div>
            <input type="hidden" name="description" value="">
            <button type="submit">GUARDAR CTA →</button>
        </form>
        <aside class="cta-contact-preview" id="ctaPreview">
            <small>VISTA PREVIA EN PORTADA</small>
            <div><span data-cta-preview="eyebrow"><?= htmlspecialchars($cta['eyebrow']) ?></span><h2 data-cta-preview="title"><?= htmlspecialchars($cta['title']) ?></h2></div>
            <b><span data-cta-preview="button_text"><?= htmlspecialchars($cta['button_text']) ?></span> →</b>
        </aside>
    </div>
</div>
