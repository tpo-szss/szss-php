<?php deny_direct_access(); ?>
<!DOCTYPE html>
<html lang="sl">

<head>
	<link rel="shortcut icon" href="images/favicon.png" type="image/png">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sistem za spremljanje stroškov</title>

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


</head>

<body>
	<?php include 'header.php'; ?>

	<main>

		<div class="container">
			<div class="row">
				<div class="col-md-4">
					<h1 class="mt-4 mb-4">Vnos</h1>

					<form id="transactionForm">
						<div class="form-group">
							<label for="description">Opis:</label>
							<input type="text" class="form-control" id="description" placeholder="Vnesi opis" required>
						</div>
						<br />
						<div class="form-group">
							<label for="amount">Znesek (v €):</label>
							<input type="number" class="form-control" id="amount" placeholder="Vnesi znesek" required>
						</div>
						<br />
						<div class="form-group">
							<label>Vrsta:</label><br>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="priliv" value="priliv"
									checked>
								<label class="form-check-label" for="priliv">Priliv</label>
							</div>
							<br />
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="odliv" value="odliv">
								<label class="form-check-label" for="odliv">Odliv</label>
							</div>
						</div>
						<br />
						<button type="button" class="btn btn-primary" onclick="addTransaction()"><i
								class="bi bi-plus-square"></i> Dodaj
							transakcijo</button>
					</form>
				</div>

				<div class="col-md-8">
					<h2 class="mt-4 mb-4">Seznam transakcij</h2>
					<ul class="nav nav-underline">
						<li class="nav-item">
							<a class="nav-link" aria-current="page" style="cursor: pointer;"
								onclick="loadTransactions()">Vse
								transakcije</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" style="cursor: pointer;"
								onclick="showTransactions('priliv')">Prilivi</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" style="cursor: pointer;" onclick="showTransactions('odliv')">Odlivi</a>
						</li>
					</ul>
					<br />

					<div id="transactionDiv">
						<ul id="transactionList" class="list-group">
						</ul>
					</div>

					<h3 class="mt-4">Stanje: <span id="balance"></span></h3>
				</div>
			</div>
		</div>
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
			crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

		<script>
			const sesskey = "<?= get_sesskey() ?>";
		</script>
		<script src="js/home.js"></script>

	</main>



	<?php if (isset($_SESSION['success'])) { ?>

		<script>
			Swal.fire({
				icon: 'success',
				title: 'Uspeh!',
				text: '<?php echo $_SESSION['success']; ?>'
			})
		</script>

		<?php unset($_SESSION['success']); ?>
		</div>
	<?php } ?>

	<?php if (isset($_SESSION['error'])) { ?>

		<script>
			Swal.fire({
				icon: 'error',
				title: 'Napaka!',
				text: '<?php echo $_SESSION['error']; ?>'
			})
		</script>

		<?php unset($_SESSION['error']); ?>
		</div>
	<?php } ?>


	<footer class="bg-light py-3">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<p class="text-center">&copy; 2023 Sistem za spremljanje stroškov</p>
				</div>
			</div>
		</div>
	</footer>


</body>

</html>