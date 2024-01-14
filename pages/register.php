<?php deny_direct_access(); ?>
<!DOCTYPE html>
<html lang="sl">

<head>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
		crossorigin="anonymous">
	<link rel="stylesheet"
		href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.css"
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
	<link rel="stylesheet" href="css/style.css" />

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.js"
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<script src="https://www.google.com/recaptcha/api.js" async defer></script>

	<title>Sistem za upravljanje stroškov - Registracija</title>

	<style>
		main {
			padding-bottom: 100px !important;
		}
	</style>

</head>

<body>
	<?php include 'header.php'; ?>
	<main>
		<div class="container mt-5">
			<div class="row">
				<div class="col-md-8">


					<h2 class="mb-4">Registracija</h2>
					<form action="action.php" method="POST">
						<input type="hidden" name="sesskey" value="<?php echo get_sesskey(); ?>">
						<div class="mb-3">
							<label for="naziv" class="form-label">Uporabniško ime</label>
							<input type="text" class="form-control" name="username" id="username"
								placeholder="Vnesite uporabniško ime" min="5" required>
							<div class="invalid-feedback">
								Uporabniško ime je že uporabljeno.
							</div>
							<small id="userHelpInline" class="text-muted">
								Uporabniško ime mora imeti vsaj 5 znakov
							</small>
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Email naslov</label>
							<input type="email" class="form-control" name="email" placeholder="Vnesite email naslov"
								required>
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Geslo</label>
							<input type="password" class="form-control" name="password" placeholder="Vnesite geslo"
								required>
							<small id="passwordHelpInline" class="text-muted">
								Geslo mora vsebovati najmanj 8 znakov, vključno z eno veliko črko, eno malo črko, eno
								številko in enim posebnim znakom (!@#$%^&-_*)
							</small>
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Ponovi geslo</label>
							<input type="password" class="form-control" name="password-repeat"
								placeholder="Ponovite geslo" required>
						</div>

						<div class="g-recaptcha" data-sitekey="6LcMDu0lAAAAAD9CwKZWsBW0qn9LOpaSyqCmVSLh"></div><br>

						<button type="submit" class="btn btn-primary" name="register"><i class="bi bi-person-plus"></i>
							Registriraj se</button>
					</form>

					<?php if (isset($_SESSION['success'])) { ?>
						<br>
						<div class="alert alert-success" role="alert">
							<?php
							echo $_SESSION['success'];
							unset($_SESSION['success']);
							?>
						</div>
					<?php } ?>


					<?php if (isset($_SESSION['error'])) { ?>
						<br>
						<div class="alert alert-danger" role="alert">
							<?php
							echo $_SESSION['error'];
							unset($_SESSION['error']);
							?>
						</div>
					<?php } ?>

				</div>
			</div>
		</div>
	</main>

	<footer class="bg-light py-3 fixed-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<p class="text-center">&copy; 2023 Sistem za spremljanje stroškov</p>
				</div>
			</div>
		</div>
	</footer>

	<script>
		document.getElementById("username").addEventListener("keyup", function () {
			var username = this.value;

			var xhr = new XMLHttpRequest();
			xhr.open("POST", "action.php?ajax", true);
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhr.onreadystatechange = function () {
				if (xhr.readyState === 4 && xhr.status === 200) {
					var response = xhr.responseText;
					if (response != "available")
						document.getElementById("username").classList.add("is-invalid");
					else
						document.getElementById("username").classList.remove("is-invalid");
				}
			};
			xhr.send("username=" + username + "&sesskey=<?= get_sesskey() ?>");
		});
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.min.js"
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>