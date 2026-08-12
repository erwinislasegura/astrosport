<div class="content upload-content photos-workspace">
    <section class="title photos-title">
        <div>
            <span class="eyebrow">BIBLIOTECA DIGITAL</span>
            <h1>NUEVO SET FOTOGRÁFICO</h1>
            <p>Carga las imágenes, completa los datos comerciales y publica el set desde una sola pantalla.</p>
        </div>
        <span class="photos-step-note">4 pasos · publicación inmediata</span>
    </section>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form class="photo-upload-form photo-create-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= csrf() ?>">

        <article class="panel photo-upload-card">
            <div class="upload-head">
                <b>01</b>
                <span>
                    <h2>Fotografías del set</h2>
                    <p>Selecciona archivos JPG, PNG o WEBP en alta resolución.</p>
                </span>
                <em id="fileCount">0 archivos</em>
            </div>

            <div class="photo-upload-body">
                <label class="drop" id="drop">
                    <input id="files" name="photos[]" type="file" multiple accept="image/*" required>
                    <i>↑</i>
                    <h3>Arrastra las fotografías aquí</h3>
                    <p>También puedes seleccionarlas directamente desde tu equipo.</p>
                    <strong>SELECCIONAR ARCHIVOS</strong>
                    <small>Admite múltiples imágenes en una sola carga.</small>
                </label>

                <section class="queue photo-queue-card">
                    <div class="photo-queue-head">
                        <label><input type="checkbox" id="all" checked> Seleccionar todo</label>
                        <button class="btn btn-ghost" type="button" id="remove">QUITAR</button>
                    </div>
                    <section id="queue"><p>La selección aparecerá aquí</p></section>
                </section>
            </div>
        </article>

        <section class="photo-details-grid">
            <article class="panel config photo-data-card">
                <div class="upload-head">
                    <b>02</b>
                    <span><h2>Información del set</h2><p>Datos visibles para el cliente en la tienda.</p></span>
                </div>
                <div class="photo-fields-grid">
                    <label>EVENTO
                        <select name="event_id" required>
                            <?php foreach ($events as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>CATEGORÍA
                        <select name="category_id" required>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach (($categories ?? []) as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field-wide">NOMBRE DEL SET
                        <input name="set_name" placeholder="Ej. Competidor 184 · Trail Antuco">
                    </label>
                    <label>Nº COMPETIDOR
                        <input name="bib_number" placeholder="Ej. 184">
                    </label>
                    <label>PRECIO POR FOTO
                        <input name="price" type="number" min="0" value="4990">
                    </label>
                </div>
            </article>

            <article class="panel config photo-sale-card">
                <div class="upload-head">
                    <b>03</b>
                    <span><h2>Venta y publicación</h2><p>Controla cómo se ofrecerá el contenido.</p></span>
                </div>
                <div class="photo-sale-options">
                    <label class="switch"><span><strong>Venta individual</strong><small>Permite comprar cada fotografía.</small></span><input type="checkbox" name="individual_enabled" checked><i></i></label>
                    <label class="switch"><span><strong>Set completo</strong><small>Entrega todas las fotos en ZIP.</small></span><input type="checkbox" name="set_enabled" checked><i></i></label>
                    <label class="switch"><span><strong>Destacar en inicio</strong><small>Muestra el set en la portada.</small></span><input type="checkbox" name="featured_home"><i></i></label>
                    <label class="switch"><span><strong>Descarga protegida</strong><small>Disponible después del pago.</small></span><input type="checkbox" name="download_enabled" checked><i></i></label>
                    <label class="sale-price">PRECIO DEL SET COMPLETO
                        <input name="set_price" type="number" min="0" value="19990">
                    </label>
                </div>
            </article>
        </section>

        <article class="panel config watermark-card">
            <div class="upload-head">
                <b>04</b>
                <span><h2>Protección y marca de agua</h2><p>Configura la vista previa sin afectar los archivos originales.</p></span>
                <label class="switch mini"><input id="wmToggle" name="watermark" type="checkbox" checked><i></i></label>
            </div>
            <div class="watermark-layout">
                <div class="watermark-fields">
                    <div class="wm-options">
                        <label><input type="radio" name="watermark_type" value="text" checked> TEXTO</label>
                        <label><input type="radio" name="watermark_type" value="image"> IMAGEN</label>
                    </div>
                    <label>TEXTO DE MARCA
                        <input id="wmInput" name="watermark_text" value="ASTROSPORT" maxlength="80">
                    </label>
                    <label>IMAGEN DE MARCA
                        <input id="wmImageInput" name="watermark_image" type="file" accept="image/png,image/jpeg,image/webp">
                    </label>
                    <div class="security-note"><b>ORIGINAL PROTEGIDO</b><span>La tienda publica una copia reducida. El original se entrega únicamente después de validar el pago.</span></div>
                </div>
                <div class="wm-preview" id="wmPreview">
                    <img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=900&q=80" alt="Vista previa">
                    <span id="wmText">ASTROSPORT</span>
                </div>
            </div>
        </article>

        <div class="publish photo-publish-bar">
            <span><b id="ready">Lote sin archivos</b><small>Selecciona fotografías para habilitar la publicación.</small></span>
            <button class="btn btn-secondary" type="button">GUARDAR BORRADOR</button>
            <button class="btn btn-primary" id="publish" type="submit" disabled>PUBLICAR SET →</button>
        </div>
    </form>

    <section class="panel photo-library">
        <div class="panel-head">
            <div><span class="eyebrow">BIBLIOTECA</span><h2>Sets registrados</h2><p>Administra los lotes publicados y sus modalidades de venta.</p></div>
            <span class="result-count"><?= count($sets) ?> SETS</span>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>SET</th><th>EVENTO</th><th>DORSAL</th><th>FOTOGRAFÍAS</th><th>PRECIOS</th><th>ESTADO</th><th>VENTA</th><th>ACCIONES</th></tr></thead>
                <tbody>
                    <?php foreach ($sets as $s): ?>
                        <tr>
                            <td><div class="photo-cell"><img src="<?= preview_url(['id' => $s['cover_id']]) ?>" alt=""><span><b><?= htmlspecialchars($s['name']) ?></b><small>SET #<?= $s['id'] ?> · Portada #<?= $s['cover_id'] ?></small></span></div></td>
                            <td><?= htmlspecialchars($s['event_name']) ?></td>
                            <td><?= $s['bib_number'] !== null && $s['bib_number'] !== '' ? '#' . htmlspecialchars($s['bib_number']) : '—' ?></td>
                            <td><b><?= $s['photos_count'] ?> fotos</b><small><?= $s['published_count'] ?> publicadas</small></td>
                            <td><b><?= money((int)$s['individual_price']) ?> c/u</b><small>Set: <?= money((int)$s['set_price']) ?></small></td>
                            <td><span class="table-status <?= $s['status'] ?>"><?= $s['status'] === 'active' ? 'PUBLICADO' : 'DESHABILITADO' ?></span></td>
                            <td><small><?= $s['individual_enabled'] ? 'Individual' : '' ?><?= $s['set_enabled'] ? ' · Completo' : '' ?><?= ($s['featured_home'] ?? 0) ? ' · En inicio' : '' ?></small></td>
                            <td><div class="table-actions"><a class="btn btn-edit" href="<?= url('admin/fotos/editar?id=' . $s['cover_id']) ?>">EDITAR SET</a><form method="post" action="<?= url('admin/fotos/estado') ?>"><input type="hidden" name="_token" value="<?= csrf() ?>"><input type="hidden" name="set_id" value="<?= $s['id'] ?>"><input type="hidden" name="status" value="<?= $s['status'] === 'active' ? 'hidden' : 'active' ?>"><button class="btn btn-toggle"><?= $s['status'] === 'active' ? 'DESHABILITAR' : 'PUBLICAR' ?></button></form></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
