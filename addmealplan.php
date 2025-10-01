<?php
session_start();
include "koneksi.php";

$idresep = $_GET['idresep'] ?? 1;

// Fetch recipe data
$stmt = $conn->prepare("SELECT * FROM resep WHERE idresep = ?");
$stmt->bind_param("i", $idresep);
$stmt->execute();
$result = $stmt->get_result();
$resep = $result->fetch_assoc();

// Fetch recipe count per day from mealplan
$dayRecipeCount = [];
$res = $conn->query("SELECT hari, COUNT(*) as count FROM mealplan GROUP BY hari");
while ($row = $res->fetch_assoc()) {
    $dayRecipeCount[$row['hari']] = (int)$row['count'];
}

// Define days of the week
$days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedday = $_POST['day'];

    // Default to 0 if day is not in array yet
    $currentCount = $dayRecipeCount[$selectedday] ?? 0;

    if ($currentCount < 3) {
        $insert = $conn->prepare("INSERT INTO mealplan (idresep, namaresep, namakategori, foto, hari) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param(
            "issss",
            $resep['idresep'],
            $resep['namaresep'],
            $resep['namakategori'],
            $resep['foto'],
            $selectedday
        );
        if ($insert->execute()) {
            $message = "Recipe added to $selectedday!";
            $dayRecipeCount[$selectedday] = $currentCount + 1;
        } else {
            $message = "Failed to add recipe.";
        }
    } else {
        $message = "You already have 3 recipes for $selectedday!";
    }
}
?>
