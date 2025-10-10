<?php
// Verbinden met de database
$conn = new mysqli("localhost", "root", "", "radar_roos_db");

if ($conn->connect_error) {
  die("Verbinding mislukt: " . $conn->connect_error);
}

// Data ontvangen via POST
$labels = $_POST['labels'] ?? [];
$values = $_POST['values'] ?? [];

// Controleren of er data is
if (!empty($labels) && !empty($values)) {
  for ($i = 0; $i < count($labels); $i++) {
    $label = $conn->real_escape_string($labels[$i]);
    $value = (int)$values[$i];
    $conn->query("INSERT INTO radar_data (label, value) VALUES ('$label', $value)");
  }
  echo "✅ Data succesvol opgeslagen in de database!";
} else {
  echo "⚠️ Geen data ontvangen.";
}

$conn->close();
?>
