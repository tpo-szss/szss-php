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

					<h2 class="mb-4">Uredi transakcijo:</h2>

                    <?php
                        $id = $view_data['ID_TRANSAKCIJE'];
                        $datum = $view_data['CAS_DATUM_TRANSAKCIJE'];
                        $znesek = $view_data['ZNESEK'];
                        $tip = $view_data[2];
                        $lokacija = $view_data[9] == "main" ? "<span class=\"badge bg-primary\">Glavni račun</span>" : "<span class=\"badge bg-secondary\">Peskovnik " . $view_data['IME'] . "</span>";
                        $stanje = ($view_data[3] >= 0 ? "<span class=\"badge bg-success\">" . $view_data[3] . " €</span>" : "<span class=\"badge bg-danger\">" . $view_data[3] . " €</span>");
                        $ime = $view_data['OPIS'];
                        $opis = isset($view_data['DOLG_OPIS']) ? $view_data['DOLG_OPIS'] : '';
                        $slika = isset($view_data['DATOTEKA']) ? $view_data['DATOTEKA'] : '';

                        $date = new DateTime($datum);
                        $formattedDatum = $date->format('d. m. Y H:i');
                        $formattedDatumExport = $date->format('Y-m-d');

                        $ura = new DateTime($datum);
                        $formattedUra= $ura->format('d. m. Y H:i');
                        $formattedUraExport = $ura->format('H:i');

                    ?>




                    <form id="urediTrans" action="action.php?ajax" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="sesskey" value="<?= get_sesskey() ?>">
						<div class="mb-3">
							<label for="ime" class="form-label" style="font-weight: bold">Ime transakcije:</label>
							<input type="text" class="form-control" id="ime" value="<?= $ime ?>" name="ime" required>
						</div>

						<div class="mb-3">
							<label for="znesek" class="form-label" style="font-weight: bold">Znesek (v €):</label>
							<input type="number" class="form-control" id="znesek" value="<?= $znesek ?>" name="znesek" required>
						</div>

						<div class="mb-3">
							<label class="form-label" style="font-weight: bold">Vrsta:</label><br>
							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="tip" id="tip" value="priliv"
									<?php if($tip == 'priliv'){echo 'checked';} ?>>
								<label class="form-check-label" for="priliv"><span
										class="badge bg-success">Priliv</span></label>
							</div>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="tip" id="tip" value="odliv"
                                    <?php if($tip == 'odliv'){echo 'checked';} ?>>
								<label class="form-check-label" for="odliv"><span
										class="badge bg-danger">Odliv</span></label>
							</div>
						</div>

                        <div class="mb-3">
							<label for="datum" class="form-label" style="font-weight: bold">Datum:</label>
							<input type="date" class="form-control" name="datum" id="datum" value="<?= $formattedDatumExport ?>">
								
                            <input type="time" class="form-control" name="ura" id="ura" value="<?= $formattedUraExport ?>">
						</div>


						<div class="mb-3">
							<label style="font-weight: bold" for="opis" class="form-label" >Opis:</label>
                            <textarea class="form-control" rows="3"  name="opis" id="opis" value="" ><?= $opis ?></textarea>
                            
						</div>
                       
                 

                        <a href="page.php?view" class="btn btn-primary mt-1"><i class="bi bi-arrow-return-left"></i> Nazaj</a>
                        
                        <a  type="submit" class="btn btn-primary mt-1" onclick="editTransaction(<?= $id ?>)"><i class="bi bi-save"></i> Shrani</a>

                        
                    </form>
                    
                    <script src="js/home.js"></script>


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