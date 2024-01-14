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

	<title>Sistem za upravljanje stroškov - Dodaj transakcijo</title>
</head>

<body>
	<?php include 'header.php'; ?>
	<main>
		<div class="container mt-5">
			<div class="row">
				<div class="col-md-8">




					<h2 class="mb-4">Dodaj novo transakcijo:</h2>

					<form id="transactionFormBig" action="action.php?dodaj" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="sesskey" value="<?= get_sesskey() ?>">
						<div class="mb-3">
							<label for="description" class="form-label" style="font-weight: bold">Ime
								transakcije:</label>
							<input type="text" class="form-control" id="description" placeholder="Vnesi ime"
								name="description" required>
						</div>

						<div class="mb-3">
							<label for="amount" class="form-label" style="font-weight: bold">Znesek (v €):</label>
							<input type="number" class="form-control" id="amount" placeholder="Vnesi znesek"
								name="amount" required>
						</div>

						<div class="mb-3">
							<label class="form-label" style="font-weight: bold">Vrsta:</label><br>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="vrsta" id="priliv" value="priliv"
									checked>
								<label class="form-check-label" for="priliv"><span
										class="badge bg-success">Priliv</span></label>
							</div>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="vrsta" id="odliv" value="odliv">
								<label class="form-check-label" for="odliv"><span
										class="badge bg-danger">Odliv</span></label>
							</div>
						</div>

						<div class="mb-3">
							<label for="datum" class="form-label" style="font-weight: bold">Datum:</label>
							<input type="datetime-local" class="form-control" name="datum-c" id="datum-c"
								placeholder="Izberite datum" value="<?php echo date('Y-m-d\TH:i:s'); ?>">

						</div>

						<div class="mb-3">
							<label for="datum" class="form-label" style="font-weight: bold">Ponavljanje:</label> <br>

							<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active" id="pills-one-tab" data-bs-toggle="pill"
										data-bs-target="#pills-one" type="button" role="tab" aria-controls="pills-one"
										aria-selected="true" onclick="naredi()">Enkratno</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="pills-daily-tab" data-bs-toggle="pill"
										data-bs-target="#pills-daily" type="button" role="tab"
										aria-controls="pills-daily" aria-selected="false"
										onclick="naredi()">Dnevno</button>
								</li>

								<!--
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="pills-weekly-tab" data-bs-toggle="pill" data-bs-target="#pills-weekly" type="button" role="tab" aria-controls="pills-weekly" aria-selected="false" onclick="naredi()">Tedensko</button>
								</li>
								 -->

								<li class="nav-item" role="presentation">
									<button class="nav-link" id="pills-monthly-tab" data-bs-toggle="pill"
										data-bs-target="#pills-monthly" type="button" role="tab"
										aria-controls="pills-monthly" aria-selected="false"
										onclick="naredi()">Mesečno</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="pills-yearly-tab" data-bs-toggle="pill"
										data-bs-target="#pills-yearly" type="button" role="tab"
										aria-controls="pills-yearly" aria-selected="false"
										onclick="naredi()">Letno</button>
								</li>

							</ul>
							<div class="tab-content" id="pills-tabContent">
								<div class="tab-pane fade show active" id="pills-one" role="tabpanel"
									aria-labelledby="pills-one-tab" tabindex="0">Samo enkrat se bo zgodilo.</div>

								<div class="tab-pane fade" id="pills-daily" role="tabpanel"
									aria-labelledby="pills-daily-tab" tabindex="0">
									Začetni datum:
									<input type="date" class="form-control" name="datum" placeholder="Izberite datum"
										id="zacDatumDaily" onclick="naredi()">
									<br>

									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"
											onclick="dodajKoncniDatum()">
										<label class="form-check-label" for="flexCheckDefault" id="koncniDatumCheck"
											onclick="dodajKoncniDatum()">
											Nastavi končni datum
										</label>
										<input type="date" class="form-control" name="datum"
											placeholder="Izberite datum" id="koncniDatumDaily" style="display: none"
											onclick="naredi()">
									</div>


								</div>

								<!--
								<div class="tab-pane fade" id="pills-weekly" role="tabpanel" aria-labelledby="pills-weekly-tab" tabindex="0">
								Začetni datum: 
									<input type="date" class="form-control" name="datum" placeholder="Izberite datum" id="zacDatumWeekly">
									<br>

									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="" id="flexCheckDefaultWeek" onclick="dodajKoncniDatumWeek()">
										<label class="form-check-label" for="flexCheckDefaultWeek" id="koncniDatumCheck" onclick="dodajKoncniDatumWeek()">
											Nastavi končni datum
										</label>
										<input type="date" class="form-control" name="datum" placeholder="Izberite datum" id="koncniDatumWeekly" style="display: none">
									</div>
									<br>
									<label for="cas" class="form-label">Nastavite uro, ob kateri se bo uresničila transakcija:</label>
									<input type="time" class="form-control" name="datum" placeholder="Izberite čas">
								</div>
								-->

								<div class="tab-pane fade" id="pills-monthly" role="tabpanel"
									aria-labelledby="pills-monthly-tab" tabindex="0">Mesečno
									Začetni datum:
									<input type="date" class="form-control" name="datum" placeholder="Izberite datum"
										id="zacDatumMonthly">
									<br>

									<div class="form-check">
										<input class="form-check-input" type="checkbox" value=""
											id="flexCheckDefaultMonth" onclick="dodajKoncniDatumMonth()">
										<label class="form-check-label" for="flexCheckDefaultMonth"
											id="koncniDatumCheck" onclick="dodajKoncniDatumMonth()">
											Nastavi končni datum
										</label>
										<input type="date" class="form-control" name="datum"
											placeholder="Izberite datum" id="koncniDatumMonthly" style="display: none">
									</div>

								</div>
								<div class="tab-pane fade" id="pills-yearly" role="tabpanel"
									aria-labelledby="pills-yearly-tab" tabindex="0">Letno
									Začetni datum:
									<input type="date" class="form-control" name="datum" placeholder="Izberite datum"
										id="zacDatumYearly">
									<br>

									<div class="form-check">
										<input class="form-check-input" type="checkbox" value=""
											id="flexCheckDefaultYear" onclick="dodajKoncniDatumYear()">
										<label class="form-check-label" for="flexCheckDefaultYear" id="koncniDatumCheck"
											onclick="dodajKoncniDatumYear()">
											Nastavi končni datum
										</label>
										<input type="date" class="form-control" name="datum"
											placeholder="Izberite datum" id="koncniDatumYearly" style="display: none">
									</div>

								</div>

							</div>

							<input type="text" name="ponavljanje" id="ponavljanjeID" hidden> </input>
							<input type="text" name="startDatum" id="startDatumID" hidden></input>
							<input type="text" name="endDatum" id="endDatumID" hidden></input>

							<input type="text" name="zadnjiCasTeka" hidden></input>

							<a class="btn btn-secondary mt-1" onclick="narediVec()"><i class="bi bi-save"></i>
								Shrani</a>

							<script src="js/trans.js"></script>

						</div>


						<div class="mb-3">
							<label style="font-weight: bold" for="opis" class="form-label">Opis:</label>
							<textarea class="form-control" rows="3" placeholder="Vnesite opis" id="long_desc"
								name="long_desc"></textarea>
						</div>

						<div class="mb-3">
							<label for="formFileMultiple" class="form-label" style="font-weight: bold">Dodajte
								datoteke:</label>
							<input class="form-control" type="file" id="slika" name="slika" accept=".png, .jpg, .jpeg">
						</div>

						<a href="page.php?home" class="btn btn-primary mt-1"><i class="bi bi-arrow-return-left"></i>
							Nazaj</a>
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-plus-square"></i> Dodaj transakcijo
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