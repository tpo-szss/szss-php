<?php

$host = "localhost";
$dbname = "szss";
$username = "root";
$password = "";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Napaka pri povezavi na podatkovno bazo: " . $e->getMessage();
  exit();
}

$maxSandboxes = 5;
$uploads_folder = "uploads/";
$require_email_verify = false;

$hash_key = "<hash_key>";
$email_key = "<email_key>";
$cronKey = "<cron_key>"
  ?>