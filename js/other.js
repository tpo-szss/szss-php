const notyf = new Notyf({
  duration: 2500,
});

let sandbox = [];
loadSandbox();

function loadSandbox() {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      sandbox = JSON.parse(xhr.responseText);
      populateDropdownMenu();
    }
  };
  xhr.send("peskovnik&izpis&sesskey=" + sesskey + "");
}

function populateDropdownMenu() {
  const dropdownMenu = document.querySelector(".dropdown-menu");
  dropdownMenu.innerHTML = "";

  const mainSandboxLink = document.createElement("li");
  if (sandboxId == "")
    mainSandboxLink.innerHTML =
      '<a class="dropdown-item active" onClick="mainSandbox();" href="#">Glavni račun</a>';
  else
    mainSandboxLink.innerHTML =
      '<a class="dropdown-item" onClick="mainSandbox();" href="#">Glavni račun</a>';
  dropdownMenu.appendChild(mainSandboxLink);
  const dividerMain = document.createElement("li");
  dividerMain.innerHTML = '<hr class="dropdown-divider">';
  dropdownMenu.appendChild(dividerMain);

  if (sandbox.length === 0) {
    const noSandboxItem = document.createElement("li");
    noSandboxItem.innerHTML =
      '<a class="dropdown-item disabled" href="#">Ni peskovnika</a>';
    dropdownMenu.appendChild(noSandboxItem);
  } else {
    sandbox.forEach((sandbox) => {
      const li = document.createElement("li");
      const a = document.createElement("a");
      a.classList.add("dropdown-item");
      a.href = "#";

      if (sandboxId == sandbox.ID_SANDBOX) a.classList.add("active");

      a.textContent = `Peskovnik ${sandbox.IME}`;

      a.addEventListener("click", function (event) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "action.php?ajax", true);
        xhr.setRequestHeader(
          "Content-type",
          "application/x-www-form-urlencoded"
        );
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4 && xhr.status === 200) {
            notyf.success("Peskovnik je naložen.");
            const url = "page.php?home";
            window.location.href = url;
          }
        };
        xhr.send(
          "peskovnik&izberi&id=" +
            sandbox.ID_SANDBOX +
            "&sesskey=" +
            sesskey +
            ""
        );
      });

      li.appendChild(a);
      dropdownMenu.appendChild(li);
    });
  }
  const divider = document.createElement("li");
  divider.innerHTML = '<hr class="dropdown-divider">';
  dropdownMenu.appendChild(divider);

  const addSandboxLink = document.createElement("li");
  addSandboxLink.innerHTML =
    '<a class="dropdown-item" onClick="addSandbox();" href="#">Dodaj nov peskovnik</a>';
  dropdownMenu.appendChild(addSandboxLink);

  const deleteSandboxLink = document.createElement("li");
  if (sandboxId == "")
    deleteSandboxLink.innerHTML =
      '<a class="dropdown-item disabled" href="#">Izbriši trenutni peskovnik</a>';
  else
    deleteSandboxLink.innerHTML =
      '<a class="dropdown-item "onClick="deleteSandbox();" href="#">Izbriši trenutni peskovnik</a>';

  dropdownMenu.appendChild(deleteSandboxLink);
}

function mainSandbox() {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      notyf.success("Glavni račun je naložen.");
      const url = "page.php?home";
      window.location.href = url;
    }
  };
  xhr.send("peskovnik&izberi&id=main&sesskey=" + sesskey + "");
}

function deleteSandbox() {
  Swal.fire({
    title: "Ali ste prepričani?",
    text: "Te spremembe ni mogoče razveljaviti!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Da, izbriši!",
    cancelButtonText: "Prekliči",
  }).then((result) => {
    if (result.isConfirmed) {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "action.php?ajax", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          notyf.success("Peskovnik uspešno izbrisan.");
          mainSandbox();
        }
      };
      xhr.send(
        "peskovnik&izbrisi&id=" + sandboxId + "&sesskey=" + sesskey + ""
      );
    }
  });
}

function addSandbox() {
  Swal.fire({
    title: "Dodaj peskovnik",
    input: "text",
    icon: "question",
    cancelButtonText: "Prekliči",
    confirmButtonText: "Dodaj",
    inputLabel: "Vnesite ime peskovnika:",
    showCancelButton: true,
    inputValidator: (value) => {
      if (!value) {
        return "Polje ne sme biti prazno!";
      }
    },
  }).then((result) => {
    if (result.isConfirmed) {
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "action.php?ajax", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          var xhr2 = new XMLHttpRequest();
          xhr2.open("POST", "action.php?ajax", true);
          xhr2.setRequestHeader(
            "Content-type",
            "application/x-www-form-urlencoded"
          );
          xhr2.onreadystatechange = function () {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
              notyf.success("Peskovnik je uspešno dodan in naložen.");
              const url = "page.php?home";
              window.location.href = url;
            }
          };
          xhr2.send(
            "peskovnik&izberi&id=" +
              response.sandbox_id +
              "&sesskey=" +
              sesskey +
              ""
          );
        }
      };
      xhr.send(
        "peskovnik&dodaj&ime=" + result.value + "&sesskey=" + sesskey + ""
      );
    }
  });
}
