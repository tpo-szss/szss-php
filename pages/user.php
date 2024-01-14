<?php deny_direct_access(); ?>


<!DOCTYPE html>
<html lang="sl">

<head>
	<link rel="shortcut icon" href="images/favicon.png" type="image/png">
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
	<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
	<style>
		main {
			padding-bottom: 100px !important;
		}
	</style>

	<title>Sistem za upravljanje stroškov - Uporabniške nastavitve</title>
</head>

<body>
	<?php include 'header.php'; ?>
	<main>
		<div class="container mt-5">
			<div class="row">
				<div class="col-md-8 offset-md-2">


					<h2 class="mb-4">Spremenba uporabniških podatkov</h2>
					<form id="update-form" action="action.php" method="POST">
						<input type="hidden" name="sesskey" value="<?= get_sesskey() ?>">
						<div class="mb-3">
							<label for="naziv" class="form-label">Uporabniško ime</label>
							<input type="text" class="form-control" name="username" value="<?= get_logged_in_name() ?>"
								required disabled>
						</div>
						<div class="mb-5">
							<label for="naziv" class="form-label">Email naslov</label>
							<input type="email" class="form-control" name="email" value="<?= get_logged_in_email() ?>">
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Staro geslo</label>
							<input type="password" class="form-control" name="password-old"
								placeholder="Vpišite staro geslo">
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Geslo</label>
							<input type="password" class="form-control" name="password"
								placeholder="Vpišite novo geslo">
							<small id="passwordHelpInline" class="text-muted">
								Geslo mora vsebovati najmanj 8 znakov, vključno z eno veliko črko, eno malo črko, eno
								številko in enim posebnim znakom (!@#$%^&-_*)
							</small>
						</div>
						<div class="mb-3">
							<label for="naziv" class="form-label">Ponovitev gesla</label>
							<input type="password" class="form-control" name="password-repeat"
								placeholder="Ponovite novo geslo">
						</div>

						<button formnovalidate formaction="action.php?delete-account" type="submit"
							class="btn btn-danger delete-btn" name="delete-account"><i class="bi bi-trash"></i> Izbriši
							račun</button>
						<button type="submit" class="btn btn-primary" name="update"><i class="bi bi-save"></i> Posodobi
							podatke</button>

						<button formnovalidate formaction="action.php?reset-account" type="submit"
							class="btn btn-warning reset-btn" name="reset-account"><i
								class="bi bi-arrow-counterclockwise"></i> Ponastavi račun
						</button>


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
	<script>
		const sesskey = "<?= get_sesskey() ?>";
		var sandboxId = "<?= get_sandbox() ?>";
		var sandboxName = "<?= get_sandboxName() ?>";
		var listType = "<?= get_listType() ?>";

	</script>
	<script src="js/other.js"></script>
	<script>
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
		})

		$(document).ready(function () {
			$('.delete-btn').click(function (e) {
				e.preventDefault();
				var url = $(this).attr('formaction');

				Swal.fire({
					title: 'Ali ste prepričani?',
					text: 'Te spremembe ni mogoče razveljaviti!',
					icon: 'warning',
					showDenyButton: false,
					showCancelButton: true,
					confirmButtonText: 'Izbriši račun',
					cancelButtonText: 'Prekliči',
				}).then((result) => {
					window.location.href = url + "&sesskey=<?= get_sesskey() ?>";
				})

			});
		});


		$(document).ready(function () {
			$('.reset-btn').click(function (e) {
				e.preventDefault();
				var url = $(this).attr('formaction');

				Swal.fire({
					title: 'Kaj želite ponastaviti?',
					text: 'Te spremembe ni mogoče razveljaviti!',
					icon: 'warning',
					showDenyButton: true,
					showCancelButton: true,
					confirmButtonText: 'Samo glavni račun',
					denyButtonText: 'Celoten račun',
					cancelButtonText: 'Prekliči',
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = url + "&all=false&sesskey=<?= get_sesskey() ?>";
					} else if (result.isDenied) {
						window.location.href = url + "&all=true&sesskey=<?= get_sesskey() ?>";
					}
				})

			});
		});

	</script>
	<footer class="bg-light py-3 fixed-bottom">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<p class="text-center">&copy; 2023 Sistem za spremljanje stroškov</p>
				</div>
			</div>
		</div>
	</footer>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.min.js"
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>