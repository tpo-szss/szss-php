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


	<!--<link rel="stylesheet" href="css/style.css" />-->

	<!--
	<link rel="stylesheet" href="css/style-dark.css" />
	-->


	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
		crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.js"
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>





</head>

<body>
	<?php include 'header.php'; ?>
	<main>

		<div class="container">
			<div class="row">

				<!-- <h3 class="mt-4">Stanje: <span id="balance"></span></h3> -->
				<!-- <div class="col-md-12">
					<h3 class="mt-4 text-center" id="stanje">Trenutno stanje: <br> <div id="kvadrat"> <span id="balance"></span></h3>	
				</div> -->


				<!-- POSKUSI ZA PREMIKAJOČ SE KALKULATOR -->

				<!-- Draggable DIV -->
				<div id="mydiv">
					<!-- Include a header DIV with the same name as the draggable DIV, followed by "header" -->
					<div id="kalkHead">
						<div id="mydivheader">Drži tukaj za premikanje </div>
						<button type="button" class="btn-close" onclick="makeVisible()" aria-label="Zapri"
							id="gumbX"></button>
					</div>

					<br>
					<div class="calculator-zunanji">
						<input type="text" id="resultCalc-zunanji" class="form-control" readonly> <br>
						<div class="calculator-buttons">
							<button class="btn btn-secondary" onclick="appendNumberZ('1')">1</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('2')">2</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('3')">3</button>
							<button class="btn btn-primary" onclick="appendOperatorZ('+')">+</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('4')">4</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('5')">5</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('6')">6</button>
							<button class="btn btn-primary" onclick="appendOperatorZ('-')">-</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('7')">7</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('8')">8</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('9')">9</button>
							<button class="btn btn-primary" onclick="appendOperatorZ('*')">*</button>
							<button class="btn btn-secondary" onclick="appendNumberZ('0')">0</button>
							<button class="btn btn-secondary" onclick="appendDecimalZ()">.</button>
							<button class="btn btn-success" onclick="toggleSignZ()">+/-</button>
							<button class="btn btn-primary" onclick="appendOperatorZ('/')">/</button>
							<button class="btn btn-danger" onclick="clearInputZ()">C</button>
							<button class="btn btn-danger" onclick="deleteDigitZ()">⌫</button>
							<button class="btn btn-success" onclick="calculateResultZ()">=</button>
							<button class="btn btn-success" onclick="xhrCalcResultZ()" data-toggle="tooltip"
								data-placement="top" title="Rezultat shrani kot transakcijo"><i
									class="bi bi-save"></i></button>
						</div>
						<br>
						<!-- <div id="calcHistory" class="text-start"></div> -->
					</div>
				</div>
				<script src="js/calc-plus.js"></script>

				<!--
				<div class="container">
					<header>Draggable element</header>
					<div class="draggable-container">
						<p> This is the example. <br>
							Click on me and try to drag within this Page.
						</p>
					</div>
				</div>
				<script src="script.js"></script>
				-->
				<!-- POSKUSI ZA PREMIKAJOČ SE KALKULATOR  END-->



				<div class="col-sm-4">

					<div class="col-md-12">
						<h3 class="mt-4 text-center" id="stanje">Trenutno stanje: <br>
							<div id="kvadrat" class="object-fit-cover"> <span id="balance"></span>
						</h3>
					</div>
					<hr>



					<h3 class="mt-2 mb-2">Vnos</h3>

					<form id="transactionForm">
						<div class="mb-3">
							<label for="description" class="form-label">Opis:</label>
							<input type="text" class="form-control" id="description" placeholder="Vnesi opis" required>
						</div>


						<div class="mb-3">
							<label for="amount" class="form-label">Znesek (v €):</label>
							<input type="number" class="form-control" id="amount" placeholder="Vnesi znesek" required>
						</div>

						<div class="mb-3">
							<label class="form-label">Vrsta:</label><br>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="priliv" value="priliv"
									checked>
								<label class="form-check-label" for="priliv"><span
										class="badge bg-success">Priliv</span></label>
							</div>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="type" id="odliv" value="odliv">
								<label class="form-check-label" for="odliv"><span
										class="badge bg-danger">Odliv</span></label>
							</div>
						</div>

						<button type="button" class="btn btn-primary" onclick="addTransaction()">
							<i class="bi bi-plus-square"></i> Dodaj transakcijo
						</button>
						<br>
						<br>
						<button type="button" class="btn btn-secondary" id="vecTrans">
							Več možnosti transakcije
						</button>
						<script>
							var btn = document.getElementById('vecTrans');
							btn.addEventListener('click', function () {
								document.location.href = '<?php echo 'page.php?transaction'; ?>';

							});
						</script>

					</form>
				</div>

				<div class="col-sm-7">
					<h3 class="mt-4 mb-2" id="title-transactions">Seznam transakcij</h3>

					<ul class="nav nav-underline" style="align-items: center;">
						<li class="nav-item">
							<a class="nav-link" aria-current="page" style="cursor: pointer;"
								onclick="showTransactions('vse')" id="type-vse">Vse
								transakcije</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" style="cursor: pointer;" onclick="showTransactions('priliv')"
								id="type-priliv">Prilivi</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" style="cursor: pointer;" onclick="showTransactions('odliv')"
								id="type-odliv">Odlivi</a>
						</li>
						<form class="form-inline mt-3 mb-3" id="sortForm" style="cursor: pointer;">
							<button class="btn btn-outline-secondary dropdown-toggle btn-sm barvaGumba" type="button"
								data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 15px;"
								onclick="napolniFilter()" id="vecFiltrovGumb">Več filtrov</button>
							<ul class="dropdown-menu" id="dropdown-filter">
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "vse") {
									echo "active";
								} else
									echo ""; ?>"
									onclick="ponastaviPogled()">Ponastavi</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "abeceda") {
									echo "active";
								} else
									echo ""; ?>"
									onclick="sortABC()">Po abecedi</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najvisja-vrednost") {
									echo "active";
								} else
									echo ""; ?>"
									onclick="najvisjaVrednost()">Najvišja vrednost</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najnizja-vrednost") {
									echo "active";
								} else
									echo ""; ?>"
									onclick="najnizjaVrednost()">Najnižja vrednost</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najvisji-priliv") {
									echo "active";
								} else
									echo ""; ?>"" onclick="
									najvisjiPriliv()">Najvišji priliv</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najnizji-priliv") {
									echo "active";
								} else
									echo ""; ?>"" onclick="
									najnizjiPriliv()">Najnižji priliv</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najvisji-odliv") {
									echo "active";
								} else
									echo ""; ?>"" onclick="
									najvisjiOdliv()">Najvišji odliv</li>
								<li class="dropdown-item <?php if ($_SESSION["list_type"] == "najnizji-odliv") {
									echo "active";
								} else
									echo ""; ?>"" onclick="
									najnizjiOdliv()">Najnižji odliv</li>
							</ul>

						</form>
					</ul>

					<form class="form-inline mt-3 mb-3" id="searchForm">
						<input class="form-control mr-sm-2" type="search" placeholder="Iskanje transakcij"
							aria-label="Iskanje transakcij" style="font-size: 13px;">


					</form>




					<div id="transactionDiv">
						<ul id="transactionList" class="list-group">
						</ul>

					</div>
					<br />



					<nav aria-label="Page navigation" class="d-flex justify-content-end">
						<ul id="pagination" class="pagination"></ul>
					</nav>



					<!-- <h3 class="mt-4">Stanje: <span id="balance"></span></h3> -->
					<br />

				</div>





			</div>

			<div class="row" id="statistika">
				<h1 class="mt-4 mb-4" id="title-transactions"
					style="text-align: center; margin-bottom: 3.5rem!important;">Grafični prikaz</h1>

				<div class="col-sm-6" id="grafiStatistika" style="text-align: center;">

					<canvas id="transactionChart" width="400" height="250"></canvas>


				</div>
				<div class="col-sm-6" id="grafiStatistika1" style="text-align: center;">
					<canvas id="transactionChartBar" width="400" height="250"></canvas>
				</div>
			</div>



		</div>
		<br>



		<script>
			const sesskey = "<?= get_sesskey() ?>";
			var sandboxId = "<?= get_sandbox() ?>";
			var sandboxName = "<?= get_sandboxName() ?>";
			var listType = "<?= get_listType() ?>";

			if (listType == "vse") {
				var liElement = document.getElementById('type-vse');
				liElement.classList.add('active');
			} else if (listType == "priliv") {
				var liElement = document.getElementById('type-priliv');
				liElement.classList.add('active');
			} else if (listType == "odliv") {
				var liElement = document.getElementById('type-odliv');
				liElement.classList.add('active');
			}

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


	<div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="popupModalLabel">Kalkulator</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zapri"></button>
				</div>
				<div class="modal-body">
					<div class="calculator">
						<input type="text" id="resultCalc" class="form-control" readonly>
						<div class="calculator-buttons">
							<button class="btn btn-secondary" onclick="appendNumber('1')">1</button>
							<button class="btn btn-secondary" onclick="appendNumber('2')">2</button>
							<button class="btn btn-secondary" onclick="appendNumber('3')">3</button>
							<button class="btn btn-primary" onclick="appendOperator('+')">+</button>
							<button class="btn btn-secondary" onclick="appendNumber('4')">4</button>
							<button class="btn btn-secondary" onclick="appendNumber('5')">5</button>
							<button class="btn btn-secondary" onclick="appendNumber('6')">6</button>
							<button class="btn btn-primary" onclick="appendOperator('-')">-</button>
							<button class="btn btn-secondary" onclick="appendNumber('7')">7</button>
							<button class="btn btn-secondary" onclick="appendNumber('8')">8</button>
							<button class="btn btn-secondary" onclick="appendNumber('9')">9</button>
							<button class="btn btn-primary" onclick="appendOperator('*')">*</button>
							<button class="btn btn-secondary" onclick="appendNumber('0')">0</button>
							<button class="btn btn-secondary" onclick="appendDecimal()">.</button>
							<button class="btn btn-success" onclick="toggleSign()">+/-</button>
							<button class="btn btn-primary" onclick="appendOperator('/')">/</button>
							<button class="btn btn-danger" onclick="clearInput()">C</button>
							<button class="btn btn-danger" onclick="deleteDigit()">⌫</button>
							<button class="btn btn-success" onclick="calculateResult()">=</button>
							<button class="btn btn-success" onclick="xhrCalcResult()" data-toggle="tooltip"
								data-placement="top" title="Rezultat shrani kot transakcijo"><i
									class="bi bi-save"></i></button>
						</div>
						<div id="calcHistory" class="text-start"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="js/calc.js"></script>
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