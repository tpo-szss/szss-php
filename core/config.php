<?php

$host = "localhost";
$dbname = "sistem_za_spremljanje_stroskov";
$username = "root";
$password = "";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Napaka pri povezavi na podatkovno bazo: " . $e->getMessage();
  exit();
}

?>