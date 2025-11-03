<?php
// <!-- V0.05 -->
//Database connectie
include('../DataBase/db_connect.php');

// Selecteert radar_id, labels, values en laatste aanmaakdatum per radar_id
$sql = "SELECT radar_id, 
               GROUP_CONCAT(label SEPARATOR ',') AS labels,
               GROUP_CONCAT(`value` SEPARATOR ',') AS values_list,
               MAX(created_at) AS created_at
        FROM radar_data
        GROUP BY radar_id";

$result = $conn->query($sql);

//toont de radars op het dashboard
$radars = [];
while ($row = $result->fetch_assoc()) {
  $row['labels'] = array_filter(array_map('trim', explode(',', $row['labels'])));
  $row['values'] = $row['values_list'] ? array_map('intval', explode(',', $row['values_list'])) : [];
  $radars[] = $row;
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>The SkillRadar Dashboard</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Horizon&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Archivo+Black&display=swap" rel="stylesheet">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="../CSS/dashboard.css" />

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

  <!-- Navigatie + logo -->
  <div class="nav">
    <a href="../Homepagina/index.html" class="title-link">
      <img src="../Foto/SkillRader_Logo.png" alt="Logo" class="logo">
      <h1>The SkillRadar</h1>
    </a>
    <button class="btn logout">Uitloggen</button>
  </div>

  <!-- Header -->
  <header class="header-round">
    <div class="welcome">
      <h2>Welkom, <span id="username">Gebruiker</span></h2>
      <p>Hier is je overzicht van de skillradar</p>
    </div>
  </header>

  <!-- Dashboard cards -->
  <main class="dashboard">
    <div class="card">
      <h3>Totaal aantal mensen in de klas</h3>
      <p id="total-students">32</p>
      <div class="line">
        <div class="fill" style="width: 80%;"></div>
      </div>
    </div>

    <div class="card">
      <h3>Mensen met een radar roos</h3>
      <p id="radar-count">20</p>
      <div class="line">
        <div class="fill" style="width: 50%;"></div>
      </div>
    </div>

    <div class="card">
      <h3>Scrum Masters</h3>
      <p id="scrum-count">5</p>
      <div class="line">
        <div class="fill" style="width: 15%;"></div>
      </div>
    </div>
  </main>

  <!-- Radar sectie -->
  <section class="radar-container">
    <h3>Hier kan je je radar rozen zien</h3>
    <?php foreach ($radars as $radar): ?>
      <?php if (count($radar['labels']) > 0): ?>
        <div class="radar-chart-box">
          <h4>Radar ID: <?= $radar['radar_id'] ?></h4>
          <canvas id="radar-<?= $radar['radar_id'] ?>" width="400" height="400"></canvas>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </section>

  <!-- Sidebar -->
  <aside class="sidebar">
    <a href="../Rader/Roosmaken.html" class="btn">Roos aanmaken</a>
  </aside>

  <!-- Scripts -->
  <script>
    // Dynamische naam
    document.getElementById("username").textContent = "Colin";

    const radars = <?php echo json_encode($radars, JSON_UNESCAPED_UNICODE); ?>;

    radars.forEach(radar => {
      if (radar.labels.length > 0) {
        const ctx = document.getElementById(`radar-${radar.radar_id}`).getContext('2d');
        new Chart(ctx, {
          type: 'radar',
          data: {
            labels: radar.labels,
            datasets: [{
              label: `Radar ID ${radar.radar_id}`,
              data: radar.values,
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 2,
              pointBackgroundColor: 'rgba(54, 162, 235, 1)'
            }]
          },
          options: {
            scales: {
              r: {
                suggestedMin: 0,
                suggestedMax: 10
              }
            }
          }
        });
      }
    });
  </script>

</body>

</html>