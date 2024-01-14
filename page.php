<?php

define('IncludeAccess', TRUE);


require('core/config.php');
require('core/functions.php');

start_session();

$expires = 60 * 60 * 24 * 7;
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

if (isset($_GET['home'])) {
	if (!is_logged_in()) {
		$_SESSION['error'] = "Za uporabo storitev se prijavite.";
		header("Location: page.php?login");
		exit();
	}
	include 'pages/home.php';
} else if (isset($_GET['login'])) {
	if (is_logged_in()) {
		$_SESSION['error'] = "Ste že prijavljeni.";
		header("Location: page.php?home");
		exit();
	}

	include 'pages/login.php';
} else if (isset($_GET['register'])) {
	if (is_logged_in()) {
		$_SESSION['error'] = "Ste že prijavljeni.";
		header("Location: page.php?home");
		exit();
	}

	include 'pages/register.php';
} else if (isset($_GET['user'])) {
	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	include 'pages/user.php';

} else if (isset($_GET['view'])) {

	if (!isset($_SESSION['view_id'])) {
		header("Location: page.php?home");
		exit();
	}

	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	$query = "SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE, t.DATOTEKA, t.DOLG_OPIS, s.ID_SANDBOX, s.ID_UPORABNIKA, s.IME, s.TIP FROM transakcije t LEFT JOIN sandbox s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE t.ID_TRANSAKCIJE = :id";

	$stmt = $pdo->prepare($query);
	$stmt->execute([':id' => $_SESSION['view_id']]);
	$view_data = $stmt->fetch();

	$user_id = get_logged_in_id();

	if (get_sandbox() !== "") {
		$stmt = $pdo->prepare("
				SELECT SUM(CASE WHEN t.TIP = 'odliv' THEN -t.ZNESEK ELSE t.ZNESEK END) AS total_amount
				FROM TRANSAKCIJE t
				INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
				INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
				WHERE u.ID_UPORABNIKA = :user_id
				  AND s.ID_SANDBOX = :sandbox_id
			");

		$stmt->bindParam(':user_id', $user_id);
		$sandbox_id = get_sandbox();
		$stmt->bindParam(':sandbox_id', $sandbox_id);
	} else {
		$stmt = $pdo->prepare("
				SELECT SUM(CASE WHEN t.TIP = 'odliv' THEN -t.ZNESEK ELSE t.ZNESEK END) AS total_amount
				FROM TRANSAKCIJE t
				INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
				INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
				WHERE u.ID_UPORABNIKA = :user_id
				  AND s.TIP = 'main'
			");

		$stmt->bindParam(':user_id', $user_id);
	}

	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$totalAmount = $result['total_amount'] ?? 0;

	$view_data['STANJE'] = $totalAmount;


	$stmt = $pdo->prepare("
				SELECT PONOVITEV from opravila inner join transakcije on transakcije.ID_TRANSAKCIJE = opravila.ID_TRANSAKCIJE WHERE transakcije.ID_TRANSAKCIJE=:id
			");
	$stmt->execute([':id' => $_SESSION['view_id']]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$ponov = $result['PONOVITEV'] ?? 'enkratno';
	$view_data['PONOVITEV'] = $ponov;


	if (empty($view_data)) {
		header("Location: page.php?home");
		exit();
	}
	include 'pages/view.php';

} else if (isset($_GET['edit'])) {				// NOVO

	if (!isset($_SESSION['view_id'])) {
		header("Location: page.php?home");
		exit();
	}

	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	//$query = "SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE, t.DOLG_OPIS, t.DATOTEKA, s.ID_SANDBOX, s.ID_UPORABNIKA, s.IME, s.STANJE, s.TIP FROM transakcije t LEFT JOIN sandbox s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE t.ID_TRANSAKCIJE = :id";
	$query = "SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE, t.DATOTEKA, t.DOLG_OPIS, s.ID_SANDBOX, s.ID_UPORABNIKA, s.IME, s.TIP FROM transakcije t LEFT JOIN sandbox s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE t.ID_TRANSAKCIJE = :id";
	$stmt = $pdo->prepare($query);
	$stmt->execute([':id' => $_SESSION['view_id']]);
	$view_data = $stmt->fetch();

	if (empty($view_data)) {
		header("Location: page.php?home");
		exit();
	}
	include 'pages/edit.php';

} else if (isset($_GET['transaction'])) {				// novo
	if (!is_logged_in()) {
		$_SESSION['error'] = "Za uporabo storitev se prijavite.";
		header("Location: page.php?login");
		exit();
	}
	include 'pages/transaction.php';
}												// do tu novo
else if (isset($_GET['theme'])) {				// novo
	/*
	   if (!is_logged_in()) {
		   $_SESSION['error'] = "Za uporabo storitev se prijavite.";
		   header("Location: page.php?login");
		   exit();
	   }
	   */
	include 'pages/theme.php';
}												// do tu novo
else {
	header("Location: page.php?home");
	exit();
}
?>