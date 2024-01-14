<?php

define('IncludeAccess', TRUE);

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';

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

		if ($user['AKTIVEN'] && $user && password_verify($password, $user['GESLO'])) {
			$_SESSION['user_data'] = $user;
			set_sesskey(true);
			header("Location: page.php?home");
			exit();
		} else if (!$user['AKTIVEN']) {
			$_SESSION['error'] = "Vaš račun ni aktiven<br /><a href=\"action.php?reactivate\" class=\"alert-link\">Ponovno pošlji potrditveni email</a>";
			header("Location: page.php?login");
			exit();
		} else {
			$_SESSION['error'] = "Napačno uporabniško ime ali geslo";
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

					if ($require_email_verify)
						$aktiven = false;
					else
						$aktiven = true;

					$etoken = bin2hex(random_bytes(32));

					$stmt = $pdo->prepare("INSERT INTO uporabniki (UPORABNISKO_IME, GESLO, EMAIL, AKTIVEN, EMAIL_TOKEN) VALUES (:username, :password, :email, :aktiven, :etoken)");

					$stmt->bindParam(':username', $username);
					$stmt->bindParam(':password', $hashed_password);
					$stmt->bindParam(':email', $email);
					$stmt->bindParam(':aktiven', $aktiven);
					$stmt->bindParam(':etoken', $etoken);
					$stmt->execute();

					$user_id = $pdo->lastInsertId();
					$name = "primary_sandbox";
					$type = "main";

					$insertStmt = $pdo->prepare("INSERT INTO sandbox (ID_UPORABNIKA, IME, TIP) VALUES (?,?,?)");
					$insertStmt->execute([$user_id, $name, $type]);


					if ($require_email_verify) {

						$mail = new PHPMailer(true);

						try {

							$mail->isSMTP();
							$mail->Host = 'smtp.zoho.eu';
							$mail->SMTPAuth = true;
							$mail->Username = 'szss.tpo@posta.streznik.me';
							$mail->Password = $email_key;
							$mail->SMTPSecure = 'ssl';
							$mail->Port = 465;

							$mail->setFrom('szss.tpo@posta.streznik.me', 'Sistem za spremljanje stroškov');
							$mail->addAddress($email, $username);

							$mail->isHTML(true);
							$mail->CharSet = 'UTF-8';
							$mail->Subject = '[SZSS] Potrditveno sporočilo';
							$povezavaAktivacije = 'https://tpo.streznik.me/action.php?activate=' . $etoken;
							$mail->Body = "Pozdravljen $username,<br><br>
									   Hvala vam za registracijo. Prosimo, kliknite spodnjo povezavo za aktivacijo svojega računa:<br>
									   <a href='$povezavaAktivacije'>$povezavaAktivacije</a><br><br>
									   Lep pozdrav,<br>
									   Sistem za spremljanje stroškov";

							$mail->send();
							$_SESSION['success'] = "Registracija je bila uspešna!<br>Potrditvena povezava je bila poslana na email.";
						} catch (Exception $e) {
							$_SESSION['error'] = "Prišlo je do napake";
							header("Location: page.php?register");
							exit();
						}
					} else {
						$_SESSION['success'] = "Registracija je bila uspešna!";
					}

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


} else if (isset($_GET['reactivate'])) {
	$email = htmlspecialchars($_GET['reactivate']);

	$query = "SELECT * FROM uporabniki WHERE EMAIL = :email";
	$stmt = $pdo->prepare($query);
	$stmt->execute(['email' => $email]);
	$user = $stmt->fetch();

	if ($user) {
		$expirationTime = strtotime($user['EMAIL_TOKEN_CAS']) + 24 * 3600;
		$currentTime = time();

		if ($currentTime > $expirationTime) {

			$newActivationToken = bin2hex(random_bytes(32));
			$newTimestamp = date('Y-m-d H:i:s');


			$updateQuery = "UPDATE uporabniki SET EMAIL_TOKEN = :token, EMAIL_TOKEN_CAS = :timestamp WHERE EMAIL = :email";
			$updateStmt = $pdo->prepare($updateQuery);
			$updateStmt->execute([
				'token' => $newActivationToken,
				'timestamp' => $newTimestamp,
				'email' => $email
			]);

			$mail = new PHPMailer(true);

			try {
				$mail->isSMTP();
				$mail->Host = 'smtp.zoho.eu';
				$mail->SMTPAuth = true;
				$mail->Username = 'szss.tpo@posta.streznik.me';
				$mail->Password = $email_key;
				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;

				$mail->setFrom('szss.tpo@posta.streznik.me', 'Sistem za spremljanje stroškov');
				$mail->addAddress($email, $username);

				$mail->isHTML(true);
				$mail->CharSet = 'UTF-8';
				$mail->Subject = '[SZSS] Potrditveno sporočilo';
				$activationLink = 'https://tpo.streznik.me/action.php?activate=' . $newActivationToken;
				$mail->Body = "Pozdravljen $username,<br><br>
							   Prosimo, kliknite spodnjo povezavo za aktivacijo svojega računa:<br>
							   <a href='$povezavaAktivacije'>$povezavaAktivacije</a><br><br>
							   Lep pozdrav,<br>
							   Sistem za spremljanje stroškov";

				$mail->send();
				$_SESSION['success'] = "Potrditvena povezava je bila poslana na email.";
				header("Location: page.php?login");
				exit();

			} catch (Exception $e) {
				$_SESSION['error'] = "Prišlo je do napake";
				header("Location: page.php?register");
				exit();
			}
		} else {
			$_SESSION['error'] = "Prišlo je do napake";
			header("Location: page.php?register");
			exit();
		}
	} else {
		$_SESSION['error'] = "Prišlo je do napake";
		header("Location: page.php?register");
		exit();
	}
} else if (isset($_GET['webcron']) && $_GET['webcron'] === $cronKey) {
	$today = date('Y-m-d');

	$query = "SELECT * FROM opravila 
          WHERE AKTIVNO = 1 AND (
                (PONOVITEV = 'daily' AND ZADNJI_CAS_TEKA < DATE_SUB(NOW(), INTERVAL 1 DAY)) OR
                (PONOVITEV = 'monthly' AND ZADNJI_CAS_TEKA < DATE_SUB(NOW(), INTERVAL 1 MONTH)) OR
                (PONOVITEV = 'yearly' AND ZADNJI_CAS_TEKA < DATE_SUB(NOW(), INTERVAL 1 YEAR))
              )
          AND (
                (START_DATUM <= NOW() OR START_DATUM = '0000-00-00') AND 
                (END_DATUM >= NOW() OR END_DATUM = '0000-00-00')
              )";


	$result = $pdo->query($query);

	if ($result) {
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

			$oldValuesQuery = "SELECT * FROM transakcije WHERE ID_TRANSAKCIJE = :ID_TRANSAKCIJE";
			$oldValuesStmt = $pdo->prepare($oldValuesQuery);
			$oldValuesStmt->bindParam(':ID_TRANSAKCIJE', $row['ID_TRANSAKCIJE']);
			$oldValuesStmt->execute();
			$oldValues = $oldValuesStmt->fetch(PDO::FETCH_ASSOC);

			$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_SANDBOX, TIP, OPIS, DOLG_OPIS, ZNESEK, DATOTEKA) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
			$ime = $oldValues['OPIS'] . " <i class=\"bi bi-clock-history\"></i> ";
			$insertStmt->bindParam(1, $oldValues['ID_SANDBOX']);
			$insertStmt->bindParam(2, $oldValues['TIP']);
			$insertStmt->bindParam(3, $ime);
			$insertStmt->bindParam(4, $oldValues['DOLG_OPIS']);
			$insertStmt->bindParam(5, $oldValues['ZNESEK']);
			$insertStmt->bindParam(6, $oldValues['DATOTEKA']);

			$insertStmt->execute();

			$ID_TRANSAKCIJE = $row['ID_TRANSAKCIJE'];
			$PONOVITEV = $row['PONOVITEV'];
			$updateQuery = "UPDATE opravila SET ZADNJI_CAS_TEKA = CURRENT_TIMESTAMP WHERE ID_TRANSAKCIJE = :ID_TRANSAKCIJE";
			$stmt = $pdo->prepare($updateQuery);
			$stmt->bindParam(':ID_TRANSAKCIJE', $ID_TRANSAKCIJE);
			$stmt->execute();

		}
	}
	echo 'CRON run';
	exit();
} else if (isset($_GET['dodaj'])) {

	if (!is_logged_in()) {
		$_SESSION['error'] = "Prijava je zahtevana";
		header("Location: page.php?login");
		exit();
	}

	if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
		$_SESSION['error'] = "Sejni ključ je napačen";
		header("Location: page.php?home");
		exit();
	}

	$user_id = get_logged_in_id();
	$type = htmlspecialchars($_POST['vrsta']) === "priliv" ? "priliv" : "odliv";
	$amount = htmlspecialchars($_POST['amount']);
	$desc = htmlspecialchars($_POST['description']);
	$long_desc = htmlspecialchars($_POST['long_desc']);
	$datum_transakcije = htmlspecialchars($_POST['datum-c']);

	if (!isset($user_id) || !isset($type) || !isset($amount)) {
		$_SESSION['error'] = "Manjkajo parametri";
		header("Location: page.php?transaction");
		exit();
	}

	$targetDir = $uploads_folder;

	if (!is_dir($targetDir)) {
		mkdir($targetDir, 0777, true);
	}

	if (isset($_FILES['slika']) && $_FILES['slika']['error'] === UPLOAD_ERR_OK) {
		echo 'g';
		$file_name = uniqid('IMG_', true) . '.' . pathinfo($_FILES['slika']['name'], PATHINFO_EXTENSION);
		$file_tmp = $_FILES['slika']['tmp_name'];
		$file_size = $_FILES['slika']['size'];
		$file_type = $_FILES['slika']['type'];
		$allowed_types = array('image/jpeg', 'image/png', 'image/gif');

		if (in_array($file_type, $allowed_types) && $file_size <= 15000000) {
			$file_path = $targetDir . $file_name;
			move_uploaded_file($file_tmp, $file_path);
		} else {
			$_SESSION['error'] = "Neveljavna vrsta ali velikost datoteke (največ 15 MB).";
			header("Location: page.php?transaction");
			exit();
		}
	} else {
		$file_path = '';
	}
	$cas_datum_transakcije = $datum_transakcije . " 00:00:00";

	$ponavljanje = htmlspecialchars($_POST['ponavljanje']);
	$startDatum = htmlspecialchars($_POST['startDatum']);
	$endDatum = htmlspecialchars($_POST['endDatum']);

	if (!empty($ponavljanje) && $ponavljanje != "one_time") {

		$desc .= " <i class=\"bi bi-clock-fill\"></i>";
	}

	if (!empty($file_path)) {
		$desc .= " <i class=\"bi bi-file-earmark-image\"></i>";
	}



	if (get_sandbox() !== "") {
		$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_SANDBOX, TIP, OPIS, DOLG_OPIS, ZNESEK, DATOTEKA, CAS_DATUM_TRANSAKCIJE) VALUES (?, ?, ?, ?, ?, ?,?)");
		$sandbox_id = get_sandbox();
		$insertStmt->execute([$sandbox_id, $type, $desc, $long_desc, $amount, $file_path, $cas_datum_transakcije]);
	} else {
		$main_sandbox_id_query = $pdo->prepare("SELECT ID_SANDBOX FROM SANDBOX WHERE ID_UPORABNIKA = :user_id AND TIP = 'main'");
		$main_sandbox_id_query->bindParam(':user_id', $user_id);
		$main_sandbox_id_query->execute();
		$main_sandbox_id = $main_sandbox_id_query->fetchColumn();

		$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_SANDBOX, TIP, OPIS, DOLG_OPIS, ZNESEK, DATOTEKA, CAS_DATUM_TRANSAKCIJE) VALUES (?, ?, ?, ?, ?, ?,?)");
		$insertStmt->execute([$main_sandbox_id, $type, $desc, $long_desc, $amount, $file_path, $cas_datum_transakcije]);
	}



	if (!empty($ponavljanje) && $ponavljanje != "one_time") {
		$insertQuery = "INSERT INTO opravila (PONOVITEV, START_DATUM, END_DATUM, ZADNJI_CAS_TEKA, AKTIVNO, USTVARJENI_CAS, ID_TRANSAKCIJE)
                VALUES (:ponavljanje, :startDatum, :endDatum, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, :t_id)";
		$insertStmt = $pdo->prepare($insertQuery);
		$t_id = $pdo->lastInsertId();
		$insertStmt->bindParam(':ponavljanje', $ponavljanje);
		$insertStmt->bindParam(':startDatum', $startDatum);
		$insertStmt->bindParam(':endDatum', $endDatum);
		$insertStmt->bindParam(':t_id', $t_id);


		$insertStmt->execute();
	}
	header("location:page.php?home");
	exit();
} else if (isset($_GET['activate'])) {
	$receivedToken = $_GET['activate'];
	$query = "SELECT * FROM uporabniki WHERE EMAIL_TOKEN = :token";
	$stmt = $pdo->prepare($query);
	$stmt->execute(['token' => $receivedToken]);
	$user = $stmt->fetch();

	if ($user) {
		$expirationTime = strtotime($user['EMAIL_TOKEN_CAS']) + 24 * 3600;
		$currentTime = time();

		if ($currentTime <= $expirationTime) {

			$updateQuery = "UPDATE uporabniki SET AKTIVEN = 1 WHERE EMAIL_TOKEN = :token";
			$updateStmt = $pdo->prepare($updateQuery);
			$updateStmt->execute(['token' => $receivedToken]);

			$updateQuery = "UPDATE uporabniki SET EMAIL_TOKEN = NULL WHERE EMAIL_TOKEN = :token";
			$updateStmt = $pdo->prepare($updateQuery);
			$updateStmt->execute(['token' => $receivedToken]);

			$rowCount = $updateStmt->rowCount();
			if ($rowCount > 0) {
				$_SESSION['success'] = "Uporabniški račun je bil uspešno aktiviran";
				header("Location: page.php?login");
				exit();
			} else {
				$_SESSION['error'] = "Aktivacijski ključ ni pravilen ali pa je potekel";
				header("Location: page.php?login");
				exit();
			}
		} else {
			$_SESSION['error'] = "Aktivacijski ključ ni pravilen ali pa je potekel";
			header("Location: page.php?login");
			exit();
		}
	} else {
		$_SESSION['error'] = "Aktivacijski ključ ni pravilen ali pa je potekel";
		header("Location: page.php?login");
		exit();
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
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$_SESSION['destroy_localstorage'] = true;
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

	$stmt = $pdo->prepare('DELETE t FROM OPRAVILA t JOIN TRANSAKCIJE s ON t.ID_TRANSAKCIJE = s.ID_TRANSAKCIJE WHERE s.ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();

	$stmt = $pdo->prepare('DELETE t FROM TRANSAKCIJE t JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE s.ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();

	$stmt = $pdo->prepare('DELETE FROM SANDBOX WHERE ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();

	$stmt = $pdo->prepare('DELETE FROM uporabniki WHERE ID_UPORABNIKA = :user_id');
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();

	$_SESSION = array();
	session_destroy();

	$back = "page.php?home";
	header("Location: " . $back);

} else if (isset($_GET['reset-account'])) {

	if (!is_logged_in()) {
		header("Location: page.php?home");
		exit();
	}

	if (!isset($_GET["sesskey"]) || !verify_sesskey(htmlspecialchars($_GET["sesskey"]))) {
		header("Location: page.php?home");
		exit();
	}

	if (isset($_GET['all']) && $_GET['all'] === 'true') {
		$user_id = get_logged_in_id();

		$stmt = $pdo->prepare('DELETE t FROM OPRAVILA t JOIN TRANSAKCIJE s ON t.ID_TRANSAKCIJE = s.ID_TRANSAKCIJE WHERE s.ID_UPORABNIKA = :user_id');
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

		$stmt = $pdo->prepare('DELETE t FROM TRANSAKCIJE t JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE s.ID_UPORABNIKA = :user_id');
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

		$stmt = $pdo->prepare('DELETE FROM SANDBOX WHERE ID_UPORABNIKA = :user_id');
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

		$name = "primary_sandbox";
		$type = "main";

		$insertStmt = $pdo->prepare("INSERT INTO sandbox (ID_UPORABNIKA, IME, TIP) VALUES (?,?,?)");
		$insertStmt->execute([$user_id, $name, $type]);

		$_SESSION['success'] = "Vaš celoten račun je bil ponastavljen.";
		$back = "page.php?home";
		header("Location: " . $back);
	} else {
		$user_id = get_logged_in_id();

		$stmt = $pdo->prepare("DELETE t FROM TRANSAKCIJE t JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX WHERE s.ID_UPORABNIKA = :user_id AND s.TIP = 'main'");
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();

		$_SESSION['success'] = "Vaš glavni račun je bil ponastavljen.";
		$back = "page.php?home";
		header("Location: " . $back);
	}

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

	} else if (isset($_POST['peskovnik'])) {
		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}

		if (isset($_POST['dodaj'])) {
			$user_id = get_logged_in_id();
			$name = htmlspecialchars($_POST['ime']);
			$type = "side";

			if (!isset($user_id) || !isset($name)) {
				echo 'missing params';
				exit();
			}

			$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sandbox WHERE ID_UPORABNIKA = ? AND TIP = 'side'");
			$countStmt->execute([$user_id]);
			$sandboxCount = $countStmt->fetchColumn();

			if ($sandboxCount >= $maxSandboxes) {
				echo json_encode(['type' => 'error', 'msg' => 'Oprostite, a že imate največje dovoljeno število peskovnikov (5 peskovnikov).']);
				exit();
			}

			$insertStmt = $pdo->prepare("INSERT INTO sandbox (ID_UPORABNIKA, IME, TIP) VALUES (?,?,?)");
			$insertStmt->execute([$user_id, $name, $type]);
			$sandbox_id = $pdo->lastInsertId();
			echo json_encode(['type' => 'ok', 'sandbox_id' => $sandbox_id, 'name' => $name]);
		}

		if (isset($_POST['izpis'])) {
			$user_id = get_logged_in_id();
			$stmt = $pdo->prepare("SELECT ID_SANDBOX, ID_UPORABNIKA, IME, TIP FROM sandbox WHERE ID_UPORABNIKA = :user_id AND TIP = 'side'");
			$stmt->bindParam(':user_id', $user_id);
			$stmt->execute();
			$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			echo $response;
		}

		if (isset($_POST['izberi'])) {
			if (isset($_POST['id'])) {
				if ($_POST['id'] === "main") {
					$_SESSION['sandbox_id'] = "";
					$_SESSION['sandbox_name'] = "";
				} else {
					$tmp = htmlspecialchars($_POST['id']);
					$_SESSION['sandbox_id'] = $tmp;

					$user_id = get_logged_in_id();
					$ownershipCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM SANDBOX s
										   INNER JOIN TRANSAKCIJE t ON s.ID_SANDBOX = t.ID_SANDBOX
										   WHERE s.ID_UPORABNIKA = :user_id");
					$ownershipCheckStmt->bindParam(':user_id', $user_id);
					$ownershipCheckStmt->execute();
					$ownershipCount = $ownershipCheckStmt->fetchColumn();

					if ($ownershipCount > 0) {
						$query = $pdo->prepare("SELECT IME FROM SANDBOX WHERE ID_SANDBOX = :sandbox_id");
						$query->bindParam(':sandbox_id', $tmp);
						$query->execute();
						$sandbox = $query->fetch(PDO::FETCH_ASSOC);
						if ($sandbox) {
							$_SESSION['sandbox_name'] = htmlspecialchars($sandbox['IME']);

						}
					}

				}
			}
		}

		if (isset($_POST['izbrisi'])) {
			if (isset($_POST['id'])) {
				$sanbox_id = htmlspecialchars($_POST['id']);
				$user_id = get_logged_in_id();
				$ownershipCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM SANDBOX s
										   INNER JOIN TRANSAKCIJE t ON s.ID_SANDBOX = t.ID_SANDBOX
										   WHERE s.ID_UPORABNIKA = :user_id");
				$ownershipCheckStmt->bindParam(':user_id', $user_id);
				$ownershipCheckStmt->execute();
				$ownershipCount = $ownershipCheckStmt->fetchColumn();

				if ($ownershipCount > 0) {

					$stmt = $pdo->prepare('DELETE t FROM OPRAVILA t JOIN TRANSAKCIJE s ON t.ID_TRANSAKCIJE = s.ID_TRANSAKCIJE WHERE s.ID_SANDBOX = :sandbox_id');

					$stmt->bindParam(':sandbox_id', $sanbox_id);
					$stmt->execute();

					$query = $pdo->prepare("DELETE FROM TRANSAKCIJE WHERE ID_SANDBOX = :sandbox_id");
					$query->bindParam(':sandbox_id', $sanbox_id);
					$query->execute();

					$query = $pdo->prepare("DELETE FROM SANDBOX WHERE ID_SANDBOX = :sandbox_id");
					$query->bindParam(':sandbox_id', $sanbox_id);
					$query->execute();

				}
			}
		}

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

		if (get_sandbox() !== "") {
			$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_SANDBOX, TIP, OPIS, ZNESEK) VALUES (?, ?, ?, ?)");
			$sandbox_id = get_sandbox();
			$insertStmt->execute([$sandbox_id, $type, $desc, $amount]);
		} else {
			$main_sandbox_id_query = $pdo->prepare("SELECT ID_SANDBOX FROM SANDBOX WHERE ID_UPORABNIKA = :user_id AND TIP = 'main'");
			$main_sandbox_id_query->bindParam(':user_id', $user_id);
			$main_sandbox_id_query->execute();
			$main_sandbox_id = $main_sandbox_id_query->fetchColumn();

			$insertStmt = $pdo->prepare("INSERT INTO transakcije (ID_SANDBOX, TIP, OPIS, ZNESEK) VALUES (?, ?, ?, ?)");
			$insertStmt->execute([$main_sandbox_id, $type, $desc, $amount]);
		}

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
		echo $totalAmount;

	} else if (isset($_POST['transakcije'])) {

		if (!is_logged_in()) {
			echo "login required.";
			exit();
		}

		if (!isset($_POST["sesskey"]) || !verify_sesskey(htmlspecialchars($_POST["sesskey"]))) {
			echo 'invalid sesskey';
			exit();
		}
		$searchTerm = isset($_POST['search']) ? '%' . htmlspecialchars($_POST['search']) . '%' : '';
		$user_id = get_logged_in_id();
		if (get_sandbox() !== "") {
			if (isset($_POST['priliv'])) {
				$_SESSION["list_type"] = "priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['odliv'])) {
				$_SESSION["list_type"] = "odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);


				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['abeceda'])) {
				$_SESSION["list_type"] = "abeceda";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.OPIS ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najvisja-vrednost'])) {
				$_SESSION["list_type"] = "najvisja-vrednost";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najnizja-vrednost'])) {
				$_SESSION["list_type"] = "najnizja-vrednost";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najvisji-priliv'])) {
				$_SESSION["list_type"] = "najvisji-priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najnizji-priliv'])) {
				$_SESSION["list_type"] = "najnizji-priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najvisji-odliv'])) {
				$_SESSION["list_type"] = "najvisji-odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);


				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najnizji-odliv'])) {
				$_SESSION["list_type"] = "najnizji-odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND t.TIP = :transaction_type
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);


				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else {
				$_SESSION["list_type"] = "vse";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.ID_SANDBOX = :sandbox_id
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$sandbox_id = get_sandbox();
				$stmt->bindParam(':sandbox_id', $sandbox_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			}
		} else {
			if (isset($_POST['priliv'])) {
				$_SESSION["list_type"] = "priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['odliv'])) {
				$_SESSION["list_type"] = "odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['abeceda'])) {
				$_SESSION["list_type"] = "abeceda";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm)";
				}

				$sql .= " ORDER BY t.OPIS ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najvisja-vrednost'])) {
				$_SESSION["list_type"] = "najvisja-vrednost";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm)";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najnizja-vrednost'])) {
				$_SESSION["list_type"] = "najnizja-vrednost";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm)";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			} else if (isset($_POST['najvisji-priliv'])) {
				$_SESSION["list_type"] = "najvisji-priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najnizji-priliv'])) {
				$_SESSION["list_type"] = "najnizji-priliv";
				$transaction_type = 'priliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najvisji-odliv'])) {
				$_SESSION["list_type"] = "najvisji-odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else if (isset($_POST['najnizji-odliv'])) {
				$_SESSION["list_type"] = "najnizji-odliv";
				$transaction_type = 'odliv';

				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
					  AND t.TIP = :transaction_type
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm))";
				}

				$sql .= " ORDER BY t.ZNESEK ASC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				$stmt->bindParam(':transaction_type', $transaction_type);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

			} else {
				$_SESSION["list_type"] = "vse";
				$sql = "
					SELECT t.ID_TRANSAKCIJE, t.OPIS, t.TIP, t.ZNESEK, t.CAS_DATUM_TRANSAKCIJE
					FROM transakcije t
					INNER JOIN SANDBOX s ON t.ID_SANDBOX = s.ID_SANDBOX
					INNER JOIN UPORABNIKI u ON s.ID_UPORABNIKA = u.ID_UPORABNIKA
					WHERE u.ID_UPORABNIKA = :user_id
					  AND s.TIP = 'main'
				";

				if (!empty($searchTerm)) {
					$sql .= " AND (t.OPIS LIKE :searchTerm OR CAST(t.ZNESEK AS CHAR) LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%e. %c. %Y') LIKE :searchTerm OR DATE_FORMAT(t.CAS_DATUM_TRANSAKCIJE, '%H:%i:%s') LIKE :searchTerm)";
				}

				$sql .= " ORDER BY t.CAS_DATUM_TRANSAKCIJE DESC";

				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':user_id', $user_id);
				if (!empty($searchTerm)) {
					$stmt->bindParam(':searchTerm', $searchTerm);
				}
				$stmt->execute();
				$response = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
			}
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

		$ownershipCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM SANDBOX s
										   INNER JOIN TRANSAKCIJE t ON s.ID_SANDBOX = t.ID_SANDBOX
										   WHERE s.ID_UPORABNIKA = :user_id AND t.ID_TRANSAKCIJE = :transaction_id");
		$ownershipCheckStmt->bindParam(':user_id', $user_id);
		$ownershipCheckStmt->bindParam(':transaction_id', $transaction_id);
		$ownershipCheckStmt->execute();
		$ownershipCount = $ownershipCheckStmt->fetchColumn();

		if ($ownershipCount > 0) {

			$stmt = $pdo->prepare('DELETE t FROM OPRAVILA t JOIN TRANSAKCIJE s ON t.ID_TRANSAKCIJE = s.ID_TRANSAKCIJE WHERE s.ID_TRANSAKCIJE = :transaction_id');

			$stmt->bindParam(':transaction_id', $transaction_id);
			$stmt->execute();

			$deleteStmt = $pdo->prepare("DELETE FROM transakcije WHERE ID_TRANSAKCIJE = :transaction_id");
			$deleteStmt->bindParam(':transaction_id', $transaction_id);
			$deleteStmt->execute();

			$response = "Transaction successfully deleted.";
		}

		echo $response;

	} else if (isset($_POST['edit'])) {

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


		if (!isset($_POST["ime"]) || !isset($_POST["znesek"])) {
			echo 'invalid input';
			exit();
		}

		$tip = htmlspecialchars($_POST['tip']);
		$opis = htmlspecialchars($_POST['ime']);
		$znesek = htmlspecialchars($_POST['znesek']);
		$datum_transakcije = htmlspecialchars($_POST['datum']);
		$cas_transakcije = htmlspecialchars($_POST['ura']);
		$dolg_opis = htmlspecialchars($_POST['opis']);
		$datoteka = htmlspecialchars($_POST['datoteka']);

		$cas_datum_transakcije = $datum_transakcije . " " . $cas_transakcije;
		echo $cas_datum_transakcije;


		//$stmt->bindParam(':username', $user_check);	// ni prav


		$ownershipCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM SANDBOX s
										   INNER JOIN TRANSAKCIJE t ON s.ID_SANDBOX = t.ID_SANDBOX
										   WHERE s.ID_UPORABNIKA = :user_id AND t.ID_TRANSAKCIJE = :transaction_id");
		$ownershipCheckStmt->bindParam(':user_id', $user_id);
		$ownershipCheckStmt->bindParam(':transaction_id', $transaction_id);
		$ownershipCheckStmt->execute();
		$ownershipCount = $ownershipCheckStmt->fetchColumn();

		if ($ownershipCount > 0) {

			$editStmt = $pdo->prepare("UPDATE transakcije SET TIP = :tip, OPIS = :opis, ZNESEK = :znesek, CAS_DATUM_TRANSAKCIJE = :cas_datum_transakcije, DOLG_OPIS = :dolg_opis WHERE ID_TRANSAKCIJE = :transaction_id");

			$editStmt->bindParam(':transaction_id', $transaction_id);
			$editStmt->bindParam(':tip', $tip);
			$editStmt->bindParam(':opis', $opis);
			$editStmt->bindParam(':znesek', $znesek);
			$editStmt->bindParam(':cas_datum_transakcije', $cas_datum_transakcije);
			$editStmt->bindParam(':dolg_opis', $dolg_opis);

			$editStmt->execute();

			$response = "Transaction updated successfully.";
		}

		echo $response;
	}

} else {
	http_response_code(400);
	echo 'Napačna zahteva!';
}