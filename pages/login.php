<?php deny_direct_access(); ?>

<!DOCTYPE html>
<html lang="sl">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.0.18/sweetalert2.min.js"
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>


  <title>Sistem za upravljanje stroškov - Prijava</title>
</head>

<body>
  <?php include 'header.php'; ?>

  <main>
    <div class="container mt-5">
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <h2 class="mb-4">Prijava</h2>

          <form action="action.php" method="POST">
            <input type="hidden" name="sesskey" value="<?php echo get_sesskey(); ?>">
            <div class="mb-3">
              <label for="naziv" class="form-label">Uporabniško ime</label>
              <input type="text" class="form-control" name="username" placeholder="Vnesite uporabniško ime" required>
            </div>

            <div class="mb-3">
              <label for="naziv" class="form-label">Geslo</label>
              <input type="password" class="form-control" name="password" placeholder="Vnesite geslo" required>
            </div>


            <button type="submit" class="btn btn-primary" name="login"><i class="bi bi-person-check"></i> Prijavi
              se</button>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/bootstrap.min.js"
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>