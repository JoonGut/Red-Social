<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sql = "
SELECT
  p.id_publicacion,
  p.texto,
  p.ubicacion,
  p.pie_foto,
  p.imagen,
  p.fecha_publicacion,
  u.usuario
FROM publicacion p
JOIN usuario u ON u.id_usuario = p.id_usuario
ORDER BY p.fecha_publicacion DESC
LIMIT 200
";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
  $username  = (string)($row['usuario'] ?? '');   // SIN @
  $texto     = $row['texto'] ?? '';
  $ubicacion = $row['ubicacion'] ?? '';
  $pie       = $row['pie_foto'] ?? '';
  $fecha     = $row['fecha_publicacion'] ?? '';
  $imagen    = $row['imagen'] ?? null;

  $imgUrl = $imagen ? "../multimedia/" . rawurlencode($imagen) : null;
?>
<article class="publicaciones"
  data-id="<?= (int)$row['id_publicacion'] ?>"
  data-usuario="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
  data-fecha="<?= htmlspecialchars($fecha ?? '', ENT_QUOTES) ?>"
  data-ubicacion="<?= htmlspecialchars($ubicacion ?? '', ENT_QUOTES) ?>"
  data-texto="<?= htmlspecialchars($texto ?? '', ENT_QUOTES) ?>"
  data-img="<?= htmlspecialchars($imgUrl ?? '', ENT_QUOTES) ?>"
  data-pie="<?= htmlspecialchars($pie ?? '', ENT_QUOTES) ?>"
  tabindex="0"
>
  <h3>
    <a class="user-link"
       href="../php/perfil_usuario.php?u=<?= urlencode($username) ?>">
      @<?= htmlspecialchars($username) ?>
    </a>
  </h3>

  <?php if ($fecha): ?>
    <small><?= htmlspecialchars($fecha) ?></small>
  <?php endif; ?>

  <?php if ($ubicacion !== ''): ?>
    <p><strong>📍</strong> <?= htmlspecialchars($ubicacion) ?></p>
  <?php endif; ?>

  <p><?= nl2br(htmlspecialchars($texto)) ?></p>

  <?php if ($imgUrl): ?>
    <div class="publicacion-imagen">
      <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Imagen de la publicación">
    </div>
  <?php endif; ?>

  <?php if ($pie !== ''): ?>
    <p><em><?= htmlspecialchars($pie) ?></em></p>
  <?php endif; ?>
</article>
<?php endwhile; ?>

