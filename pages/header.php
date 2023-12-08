<?php deny_direct_access(); ?>

<header>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="page.php?home">Sistem za spremljanje stroškov</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">


          <?php if (is_logged_in()) { ?>
            <li class="nav-item">
              <a class="nav-link <?php $page = isset($_GET['home']) ? "active" : "";
              echo $page; ?>" href="page.php?home">Domov</a>
            </li>

          <?php } ?>
        </ul>
        <ul class="navbar-nav ms-auto">
          <?php if (is_logged_in()) { ?>
            <li class="nav-item"><a class="nav-link <?php $page = isset($_GET['user']) ? "active" : "";
            echo $page; ?>" href="page.php?user">Pozdravljen
                <?php echo get_logged_in_name(); ?>
              </a></li>
            <li class="nav-item"><a class="nav-link"
                href="action.php?logout&sesskey=<?php echo get_sesskey(); ?>">Odjava</a></li>
          <?php } else { ?>
            <li class="nav-item"><a class="nav-link" href="page.php?user">Pozdravljen Gost</a></li>
            <li class="nav-item"><a class="nav-link <?php $page = isset($_GET['login']) ? "active" : "";
            echo $page; ?>" href="page.php?login">Prijava</a></li>
            <li class="nav-item"><a class="nav-link <?php $page = isset($_GET['register']) ? "active" : "";
            echo $page; ?>" href="page.php?register">Registracija</a></li>
          <?php } ?>


        </ul>

      </div>
    </div>
  </nav>
</header>