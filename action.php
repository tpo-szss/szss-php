<?php

define('IncludeAccess', TRUE);

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require('core/config.php');
require('core/functions.php');

if (isset($_POST['login'])) {

	if (is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ ni pravilen.";
		header("Location: page.php?login");
		exit();
	}

	$username = $_POST['username'];
	$password = $_POST['password'];

	if (empty($username) || empty($password)) {
		$_SESSION['error'] = "Prosimo vnesite vaše uporabniško ime in geslo";
		header("Location: page.php?login");
		exit();
	} else {

		$query = "SELECT * FROM uporabniki WHERE UPORABNISKO_IME = :username";
		$stmt = $pdo->prepare($query);
		$stmt->execute(['username' => $username]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user['GESLO'])) {
			$_SESSION['user_data'] = $user;
			set_sesskey(true);
			header("Location: page.php?home");
			exit();
		} else {
			$_SESSION['error'] = "Napačno uporabniško ime ali geslo.";
			header("Location: page.php?login");
			exit();
		}
	}
} else if (isset($_POST['register'])) {

	if (is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ ni pravilen.";
		header("Location: page.php?login");
		exit();
	}

	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	$password_repeat = filter_input(INPUT_POST, 'password-repeat', FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);


	$hashed_password = password_hash($password, PASSWORD_DEFAULT);
	$hashed_password_repeat = password_hash($password_repeat, PASSWORD_DEFAULT);




	if (strlen($username) < 5) {
		$_SESSION['error'] = "Uporabniško ime mora imeti vsaj 5 znakov.";
		header("Location: page.php?register");
		exit();
	}

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['error'] = "Email naslov je neveljaven.";
		header("Location: page.php?register");
		exit();
	}

	if (
		strlen($password) < 8 ||
		!preg_match('/[A-Z]/', $password) ||
		!preg_match('/[a-z]/', $password) ||
		!preg_match('/\d/', $password) ||
		!preg_match('/[!@#$%^&-_*]/', $password)
	) {
		$_SESSION['error'] = "Geslo mora vsebovati najmanj 8 znakov, vključno z eno veliko črko, eno malo črko, eno številko in enim posebnim znakom (!@#$%^&*).";
		header("Location: page.php?register");
		exit();
	}

	if (!password_verify($password, $hashed_password_repeat)) {
		$_SESSION['error'] = "Vnešena gesla nista enaka!";
		header("Location: page.php?register");
		exit();
	}

	$recaptcha_secret = "6LcMDu0lAAAAAMXgsfqWaoZIHj8x96bNVw4t8gpL";
	$recaptcha_response = $_POST['g-recaptcha-response'];

	$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
	$recaptcha_data = array(
		'secret' => $recaptcha_secret,
		'response' => $recaptcha_response
	);

	$recaptcha_options = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
			'content' => http_build_query($recaptcha_data)
		)
	);

	$recaptcha_context = stream_context_create($recaptcha_options);
	$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);

	if ($recaptcha_result === false) {
		$_SESSION['error'] = "reCAPTCHA fail";
		header("Location: page.php?register");
		exit();
	} else {
		$recaptcha_result_json = json_decode($recaptcha_result);
		if ($recaptcha_result_json->success === true) {

			try {

				$stmt = $pdo->prepare("SELECT COUNT(*) FROM uporabniki WHERE UPORABNISKO_IME = :username");
				$stmt->bindParam(':username', $username);
				$stmt->execute();
				$count = $stmt->fetchColumn();

				if ($count > 0) {
					$_SESSION['error'] = "Registration failed: username already taken";
					header("Location: page.php?register");
					exit();

				} else {

					$stmt = $pdo->prepare("INSERT INTO uporabniki (UPORABNISKO_IME, GESLO, EMAIL) VALUES (:username, :password, :email)");

					$stmt->bindParam(':username', $username);
					$stmt->bindParam(':password', $hashed_password);
					$stmt->bindParam(':email', $email);
					$stmt->execute();

					$_SESSION['success'] = "Registracija je bila uspešna!";
					header("Location: page.php?login");
					exit();
				}
			} catch (PDOException $e) {
				$_SESSION['error'] = "Prišlo je do napake";
				header("Location: page.php?register");
				exit();
			}
		} else {
			$_SESSION['error'] = "reCAPTCHA fail";
			header("Location: page.php?register");
			exit();
		}
	}


} else if (isset($_POST['update'])) {

	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ ni pravilen.";
		header("Location: page.php?login");
		exit();
	}

	$user_id = get_logged_in_id();
	$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

	if (isset($_POST['password-old']) && !empty($_POST['password-old']) || isset($_POST['password-repeat']) && !empty($_POST['password-repeat']) || isset($_POST['password']) && !empty($_POST['password'])) {

		$id = get_logged_in_id();
		$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
		$password_old = filter_input(INPUT_POST, 'password-old', FILTER_SANITIZE_STRING);
		$password_repeat = filter_input(INPUT_POST, 'password-repeat', FILTER_SANITIZE_STRING);

		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$hashed_password_repeat = password_hash($password_repeat, PASSWORD_DEFAULT);

		$query = "SELECT * FROM uporabniki WHERE ID_UPORABNIKA = :user_id";
		$stmt = $pdo->prepare($query);
		$stmt->execute(['user_id' => $id]);
		$user = $stmt->fetch();

		if (!($user && password_verify($password_old, $user['geslo']))) {
			$_SESSION['error'] = "Staro geslo je napačno.";
			header("Location: page.php?user");
			exit();
		}

		if (!password_verify($password, $hashed_password_repeat)) {
			$_SESSION['error'] = "Vnešena gesla nista enaka!";
			header("Location: page.php?user");
			exit();
		}


		if (
			strlen($password) < 8 ||
			!preg_match('/[A-Z]/', $password) ||
			!preg_match('/[a-z]/', $password) ||
			!preg_match('/\d/', $password) ||
			!preg_match('/[!@#$%^&-_*]/', $password)
		) {
			$_SESSION['error'] = "Geslo mora vsebovati najmanj 8 znakov, vključno z eno veliko črko, eno malo črko, eno številko in enim posebnim znakom (!@#$%^&*).";
			header("Location: page.php?user");
			exit();
		}

		$stmt = $pdo->prepare('UPDATE uporabniki SET  GESLO = :geslo, EMAIL = :email WHERE ID_UPORABNIKA = :user_id');
		$stmt->bindParam(':geslo', $hashed_password);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

	} else {


		$stmt = $pdo->prepare('UPDATE uporabniki SET  EMAIL = :email WHERE ID_UPORABNIKA = :user_id');
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

	}

	$query = "SELECT * FROM uporabniki WHERE ID_UPORABNIKA = :user_id";
	$stmt = $pdo->prepare($query);
	$stmt->execute([':user_id' => $user_id]);
	$user = $stmt->fetch();

	$_SESSION['user_data'] = $user;
	set_sesskey(true);

	$_SESSION['success'] = "Uporabniški podatki so bili shranjeni!";
	header("Location: page.php?user");

} else if (isset($_GET['logout'])) {
	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_GET["sesskey"]) || !verify_sesskey(htmlspecialchars($_GET["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ ni pravilen.";
		header("Location: page.php?home");
		exit();
	}

	$_SESSION = array();
	session_destroy();

	$back = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : "page.php?home";
	header("Location: " . $back);

} else if (isset($_GET['delete-account'])) {
	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_GET["sesskey"]) || !verify_sesskey(htmlspecialchars($_GET["sesskey"]))) {
		header("Location: page.php?home");
		exit();
	}

	$user_id = get_logged_in_id();

	$stmt = $pdo->prepare('DELETE FROM transakcije WHERE ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();


	$stmt = $pdo->prepare('DELETE FROM uporabniki WHERE ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();

	$_SESSION = array();
	session_destroy();

	$back = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : "page.php?home";
	header("Location: " . $back);

} else if (isset($_GET['view'])) {
	
	if (!isset($_GET["sesskey"]) || !verify_sesskey(htmlspecialchars($_GET["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ ni pravilen.";
		header("Location: page.php?home");
		exit();
	}
	
	if (isset($_GET['id'])) {
		$_SESSION['view_id'] = intval($_GET['id']);
		header("Location: page.php?view");
		exit();
	} else {
		header("Location: page.php?home");
		exit();
	}
	
} else if (isset($_GET['ajax'])) {


	if (isset($_POST['username'])) {

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		$user_check = htmlspecialchars($_POST['username']);
		$stmt = $pdo->prepare("SELECT COUNT(*) FROM uporabniki WHERE UPORABNISKO_IME = :username");
		$stmt->bindParam(':username', $user_check);
		$stmt->execute();
		$count = $stmt->fetchColumn();

		if ($count > 0)
			echo 'not available';
		else
			echo 'available';

	} else if (isset($_POST['dodaj'])) {

		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		$user_id = get_logged_in_id();
		$type = htmlspecialchars($_POST['tip']) === "priliv" ? "priliv" : "odliv";
		$amount = htmlspecialchars($_POST['znesek']);
		$desc = htmlspecialchars($_POST['opis']);


		if (!isset($user_id) || !isset($type) || !isset($amount)) {
			echo 'missing params';
			exit();
		}


		$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_UPORABNIKA, TIP, OPIS, ZNESEK) VALUES (?, ?, ?, ?)");
		$insertStmt->execute([$user_id, $type, $desc, $amount]);

		echo "ok";
		exit();

	} else if (isset($_POST['stanje'])) {

		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		$user_id = get_logged_in_id();
		$stmt = $pdo->prepare("SELECT STANJE FROM uporabniki WHERE ID_UPORABNIKA = :user_id");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		$response = $stmt->fetchColumn();

		echo $response;

	} else if (isset($_POST['transakcije'])) {

		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		if (isset($_POST['priliv'])){
			$user_id = get_logged_in_id();
			$stmt = $pdo->prepare("SELECT ID_TRANSAKCIJE, OPIS, TIP, ZNESEK, CAS_DATUM_TRANSAKCIJE FROM transakcije WHERE ID_UPORABNIKA = :user_id AND TIP = :tip ORDER BY CAS_DATUM_TRANSAKCIJE DESC");
			$tmp = 'priliv';
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':tip', $tmp);
			$stmt->execute();
			$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
		}else if (isset($_POST['odliv'])){
			$user_id = get_logged_in_id();
			$stmt = $pdo->prepare("SELECT ID_TRANSAKCIJE, OPIS, TIP, ZNESEK, CAS_DATUM_TRANSAKCIJE FROM transakcije WHERE ID_UPORABNIKA = :user_id AND TIP = :tip ORDER BY CAS_DATUM_TRANSAKCIJE DESC");
			$tmp = 'odliv';
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':tip', $tmp);
			$stmt->execute();
			$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
		}else{
			$user_id = get_logged_in_id();
			$stmt = $pdo->prepare("SELECT ID_TRANSAKCIJE, OPIS,TIP,ZNESEK,CAS_DATUM_TRANSAKCIJE FROM transakcije WHERE ID_UPORABNIKA = :user_id ORDER BY CAS_DATUM_TRANSAKCIJE DESC");
			$stmt->bindParam(':user_id', $user_id);
			$stmt->execute();
			$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
		}

		echo $response;

	} else if (isset($_POST['izbrisi'])) {

		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		$user_id = get_logged_in_id();
		$transaction_id = htmlspecialchars($_POST['id']);
		
		$stmt = $pdo->prepare("DELETE FROM transakcije WHERE ID_TRANSAKCIJE = :transaction_id AND ID_UPORABNIKA = :user_id");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->bindParam(':transaction_id', $transaction_id);
		$stmt->execute();
		$response = $stmt->fetchColumn();

		echo $response;

	}

} else {
	http_response_code(400);
	echo 'Napačna zahteva!';
}