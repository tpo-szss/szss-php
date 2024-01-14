<?php deny_direct_access(); ?>


<link rel="" href="css/style.css" data-toggle="theme" data-theme-mode="navadno"/>
<link rel="" href="css/style-dark.css" data-toggle="theme" data-theme-mode="temno"/> 
<link rel="" href="css/style-blue.css" data-toggle="theme" data-theme-mode="modro"/> 
<link rel="" href="css/style-yellow.css" data-toggle="theme" data-theme-mode="rumeno"/> 


<script>
  var izbranaTema = JSON.parse(localStorage.getItem("izbranaTema")) || "navadno";
  console.log(izbranaTema);
  var themeMode = localStorage.getItem(izbranaTema);
  var themeFile = document.querySelector('[data-toggle="theme"][data-theme-mode="' + izbranaTema + '"]');

  themeFile.rel = 'stylesheet';
</script>



<header>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary" id="heda">
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
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Peskovnik
              </a>
              <ul class="dropdown-menu"></ul>
            </li>
			
            <li class="nav-item">
              <?php $button = isset($_GET['home']) ? "<a class=\"nav-link\" data-bs-toggle=\"modal\" data-bs-target=\"#popupModal\" onclick=\"clearInput();\"
                href=\"#\" >Kalkulator</a>" : ""; echo $button; 
              ?>
            </li>
            <li class="nav-item">
              <script src="js/calc-plus.js"></script>
              <?php $button = isset($_GET['home']) ? "<a class=\"nav-link\" onclick=\"clearInputZ(); makeVisible();\"
                href=\"#\">Zunanji Kalkulator</a>" : ""; echo $button; 
              ?>
                
            </li>
          <?php } ?>
        </ul>
        <ul class="navbar-nav ms-auto">
          <?php if (is_logged_in()) { ?>
            <li class="nav-item"><a class="nav-link <?php $page = isset($_GET['user']) ? "active" : "";
            echo $page; ?>" href="page.php?user">Pozdravljeni
                <?php echo get_logged_in_name(); ?>
              </a></li>

              <!-- novo -->
              <li class="nav-item"><a class="nav-link <?php $page = isset($_GET['theme']) ? "active" : "";
              echo $page; ?>" href="page.php?theme">Nastavitve
                </a></li>
              <!-- novo end-->


            <li class="nav-item"><a class="nav-link"
                href="action.php?logout&sesskey=<?php echo get_sesskey(); ?>">Odjava</a></li>
          <?php } else { ?>
            <li class="nav-item"><a class="nav-link" href="page.php?user">Pozdravljen Gost</a></li>

              <!-- novo -->
              <li class="nav-item"><a class="nav-link " href="page.php?theme">Nastavitve
                </a></li>
              <!-- novo end-->


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