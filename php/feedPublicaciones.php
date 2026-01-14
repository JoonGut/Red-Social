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
  $usuario   = '@' . $row['usuario'];
  $texto     = $row['texto'] ?? '';
  $ubicacion = $row['ubicacion'] ?? '';
  $pie       = $row['pie_foto'] ?? '';
  $fecha     = $row['fecha_publicacion'] ?? '';
  $imagen    = $row['imagen'] ?? null;

  $imgUrl = $imagen ? "../uploads/" . rawurlencode($imagen) : null;
?>
  <article class="publicacion">
    <h3><?= htmlspecialchars($usuario) ?></h3>

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
