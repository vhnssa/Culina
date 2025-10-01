<?php
session_start();
include "koneksi.php";

// Ambil semua data dari tabel mealplan
$hasilmealplan = $conn->query("SELECT * FROM mealplan");

// Masukin data dari mealplan ke array $mealplans (hari => array of idresep)
$mealplans = [];
while ($row = $hasilmealplan->fetch_assoc()) {
    $mealplans[$row['hari']][] = $row['idresep'];
}

// Cek apakah mealplan-nya ada isinya atau kosong
$hasmealplan = !empty($mealplans);

$recipes = [];
if ($hasmealplan) {
    // Ambil semua idresep unik dari mealplans
    $all_ids = array_merge(...array_values($mealplans));
    $unique_ids = array_unique($all_ids);
    $placeholders = implode(',', array_fill(0, count($unique_ids), '?'));
    $types = str_repeat('i', count($unique_ids));

    $stmt = $conn->prepare("SELECT idresep, namaresep, foto FROM resep WHERE idresep IN ($placeholders)");
    $stmt->bind_param($types, ...$unique_ids);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($recipe = $result->fetch_assoc()) {
        $recipes[$recipe['idresep']] = $recipe;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Meal Plan</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background-color: #FCF5C7;
    }

    .hero {
      background-color: #FCF5C7;
      display: flex;
      height: 400px;
      padding-left: 60px;
      padding-right: 40px;
      justify-content: space-between;
      align-items: flex-start;
      position: relative;
      z-index: 2;
    }

    .hero h1 {
      font-size: 80px;
      font-weight: 700;
      margin-bottom: 0px;
      margin-top: 0px;
    }

    .edit-btn {
      background-color: #FF8C00;
      color: white;
      border: none;
      padding: 10px 20px;
      font-weight: 500;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.2s ease-in-out;
      display: inline-block;
      margin-top: 10px;
    }

    .edit-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.3);
      cursor: pointer;
    }

    .hero-right {
      position: relative;
      z-index: 3;
      margin-top: 0;
    }

    .side-img {
      width: 500px;
      height: 500px;
      top: -50px;
      object-fit: cover;
      border-radius: 50%;
      position: relative;
      right: -40px;
      z-index: 0;
    }

    .oops-section {
      background-color: #FF8C00;
      color: white;
      text-align: center;
      padding: 30px 20px;
      z-index: 3;
    }

    .oops-section h2 {
      font-size: 32px;
      font-weight: 700;
    }

    .oops-section a {
      background-color: #FCF5C7;
      padding: 10px 20px;
      border-radius: 6px;
      color: #FF8C00;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      margin-top: 20px;
    }

    .plan-section {
      background-color: #FF8C00;
      padding: 40px;
      overflow-x: auto;
      white-space: nowrap;
      margin-top: -80px; 
      position: relative;
      z-index: 2;
    }

    .day-card {
      background-color: #FCF5C7;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 30px;
      display: inline-block;
      min-width: 700px;
      max-width: 100%;
      vertical-align: top;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .plan-day {
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .recipe-list {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .card-meal {
      background-color: white;
      border-radius: 10px;
      padding: 12px;
      text-align: center;
      width: 200px;
      flex-shrink: 0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .card-meal img {
      width: 100%;
      height: 140px;
      border-radius: 10px;
      object-fit: cover;
    }

    .card-meal h6 {
      font-size: 15px;
      font-weight: 600;
      margin-top: 10px;
      margin-bottom: 5px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>

<?php include("headersearch4.php"); ?>

<div class="hero">
  <div>
    <h1>This Week's<br>Meal Plan</h1>
    <a href="editmealplan.php" class="edit-btn">Edit Meal Plan</a>
  </div>
  <div class="hero-right">
    <img src="images/meal.png" alt="Meal Plan" class="side-img" />
  </div>
</div>

<?php if (!$hasmealplan): ?>
  <div class="oops-section">
    <h2>Oops!<br>You have no recipes<br>on your Meal Plan...</h2>
    <h2>Search for Recipes?</h2>
    <a href="explore.php">Explore Recipes</a>
  </div>
<?php else: ?>
  <div class="plan-section">
    <?php
      $ordered_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
      foreach ($ordered_days as $day):
        if (!isset($mealplans[$day])) continue;
    ?>
      <div class="day-card">
        <div class="plan-day"><?= htmlspecialchars($day) ?></div>
        <div class="recipe-list">
          <?php foreach ($mealplans[$day] as $idresep):
            $recipe = $recipes[$idresep] ?? null;
            if (!$recipe) continue;
          ?>
            <div class="card-meal">
              <img src="images/<?= htmlspecialchars($recipe['foto']) ?>" alt="<?= htmlspecialchars($recipe['namaresep']) ?>">
              <h6><?= htmlspecialchars($recipe['namaresep']) ?></h6>
              <a class="edit-btn" href="<?= htmlspecialchars($recipe['namaresep']) ?>.php">See Recipe</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

</body>
</html>
