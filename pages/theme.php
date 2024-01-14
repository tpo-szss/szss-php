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
	<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

	<style>
		main {
			padding-bottom: 100px !important;
		}
	</style>

	<title>Sistem za upravljanje stroškov - Nastavitve</title>
</head>

<body onload="poisciChecked()">
	<?php include 'header.php'; ?>
	<main>
		<div class="container mt-5">
			<div class="row">
				<div class="col-md-8 offset-md-2">


					<h2 class="mb-4">Nastavitve</h2>
					<form id="update-form" action="action.php" method="POST">
						<input type="hidden" name="sesskey" value="<?= get_sesskey() ?>">

						<div class="mb-5">
							<label for="naziv" class="form-label" style="font-weight: bold;">Izbira teme:</label>
							<br>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="navadno" value="navadno"
									onclick="nastaviTemo()">
								<label class="form-check-label" for="navadno">Navadno</label>
							</div>
							<br />
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="temno" value="temno"
									onclick="nastaviTemo()">
								<label class="form-check-label" for="temno">Temno</label>
							</div>
							<br />
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="modro" value="modro"
									onclick="nastaviTemo()">
								<label class="form-check-label" for="modro">Modro</label>
							</div>
							<br />
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="rumeno" value="rumeno"
									onclick="nastaviTemo()">
								<label class="form-check-label" for="rumeno">Rumeno</label>
							</div>
						</div>





					</form>


					<form id="izbira-pogled" action="action.php" method="POST" style="display:none;">
						<input type="hidden" name="sesskey" value="<?= get_sesskey() ?>">

						<div class="mb-5" id="izbira-pogled1">
							<label for="pogled" class="form-label" style="font-weight: bold;">Izbira pogleda:</label>
							<br>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="sirse" value="sirse"
									checked onclick="nastaviPogled()">
								<label class="form-check-label" for="sirse">Širše</label>
							</div>
							<br />
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="zgosceno" value="zgosceno"
									onclick="nastaviPogled()">
								<label class="form-check-label" for="zgosceno">Zgoščeno</label>
							</div>
							<br />
						</div>

					</form>

					<script>
						const sesskey = "<?= get_sesskey() ?>";
						var sandboxId = "<?= get_sandbox() ?>";
						var sandboxName = "<?= get_sandboxName() ?>";
						var listType = "<?= get_listType() ?>";

					</script>
					<script src="js/other.js"></script>

					<script src="js/pogled.js"></script>


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

</body>