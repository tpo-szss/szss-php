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

	$query = "SELECT * FROM transakcije WHERE ID_TRANSAKCIJE = :id";
	$stmt = $pdo->prepare($query);
	$stmt->execute([':id' => $_SESSION['view_id']]);
	$view_data = $stmt->fetch();

	if (empty($view_data)) {
		header("Location: page.php?home");
		exit();
	}
	include 'pages/view.php';

} else {
	header("Location: page.php?home");
	exit();
}
?>