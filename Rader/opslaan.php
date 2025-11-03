<?php
// <!-- V0.01 -->
include(__DIR__ . '/../DataBase/db_connect.php');

if ($conn->connect_error) {
  die("Verbinding mislukt: " . $conn->connect_error);
}

$labels = $_POST['labels'] ?? [];
$values = $_POST['values'] ?? [];
$radarNaam = $_POST['radarNaam'] ?? 'Nieuwe Radar';


$radar_id = time();

if (!empty($labels) && !empty($values)) {
  for ($i = 0; $i < count($labels); $i++) {
    $label = $conn->real_escape_string($labels[$i]);
    $value = (int)$values[$i];

    $conn->query("
      INSERT INTO radar_data (radar_id, radar_naam, label, value)
      VALUES ($radar_id, '$radarNaam', '$label', $value)
    ");
  }

  echo "✅ Radar '$radarNaam' succesvol opgeslagen!";
} else {
  echo "⚠️ Geen data ontvangen.";
}

$conn->close();
?>
