<?php deny_direct_access(); ?>

<!DOCTYPE html>
<html lang="sl">

<head>
  <link rel="shortcut icon" href="images/favicon.png" type="image/png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem za upravljanje stroškov - Dogodki</title>

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


  <style>
    @media print {
      body * {
        visibility: hidden;
      }

      .container,
      .container * {
        visibility: visible;
      }

      a {
        visibility: hidden !important;
      }
    }

    main {
      padding: 0 20px;
    }

    a.btn {
      margin-right: 10px;
    }
  </style>

</head>

<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="container my-5">

      <h1 class="text-center mb-4 title">Pregled transakcije</h1>

      <?php
      $id = $view_data['ID_TRANSAKCIJE'];
      $datum = $view_data['CAS_DATUM_TRANSAKCIJE'];
      $znesek = "<span class=\"badge bg-primary\">" . $view_data['ZNESEK'] . " €</span>";
      $tip = $view_data['TIP'] == "priliv" ? "<span class=\"badge bg-success\">priliv</span>" : "<span class=\"badge bg-danger\">odliv</span>";

      $date = new DateTime($datum);
      $formattedDatum = $date->format('d. m. Y H:i');
      $formattedDatumExport = $date->format('Y-m-d\TH:i:s');



      echo '<div class="card mb-5">';
      echo '<div class="row">';
      echo '<div class="col">';
      echo '<div class="card-body">';
      ?>
      <h5 class="card-title">
        <?php echo "Transakcija ID: " . $id; ?>
      </h5>
      <br />
      <p class="card-text">
        <?php echo "<i class=\"bi bi-cash-stack\"></i> <b>Znesek: </b>" . $znesek; ?>
        <?php echo "<br />"; ?>
        <?php echo "<i class=\"bi bi-arrow-down-up\"></i> <b>Tip transakcije: </b>" . $tip; ?>
      </p>
      <ul class="list-unstyled">
        <li><i class="bi bi-calendar"></i> <strong>Datum transakcije:</strong>
          <?php echo $formattedDatum; ?>
        </li>
      </ul>
      <?php

      echo '</div>';
      echo '</div>';
      echo '</div>';

      echo '<form>
			<div class="card-footer">';
      echo '<a href="page.php?home" class="btn btn-primary mt-1"><i class="bi bi-arrow-return-left"></i> Nazaj</a>';
      echo '<a class="btn btn-primary mt-1" onclick="window.print()"><i class="bi bi-printer"></i> Natisni</a>';


      echo '</div>';
      ?>

    </div>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.min.js"
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</body>

</html>