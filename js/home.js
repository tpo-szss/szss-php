const notyf = new Notyf({
  duration: 2500,
});

let balance = 0;
let transactions = [];
let sandbox = [];

if (typeof listType === "undefined") {
  listType = "vse";
}

loadTransactions(listType);

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

if (sandboxId == "") {
  document.getElementById("title-transactions").innerHTML =
    'Seznam transakcij <h5><span class="badge bg-primary">Glavni račun</span></h5>';
} else {
  document.getElementById("title-transactions").innerHTML =
    'Seznam transakcij <h5><span class="badge bg-secondary">Peskovnik ' +
    sandboxName +
    "</span></h5>";
}

var searchForm = document.getElementById("searchForm");

searchForm.addEventListener("input", function (event) {
  event.preventDefault();

  if (typeof listType === "undefined") {
    listType = "vse";
  }
  var searchTerm = event.target.value;

  loadTransactions(listType, searchTerm);
});

function loadTransactions(order, search) {
  var animate = false;
  if (typeof order === "undefined") {
    order = "vse";
    animate = true;
  }

  if (typeof search === "undefined") {
    search = "";
  } else {
    search = "search=" + search + "&";
  }

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var graph = false;
      if (
        !arraysAreEqual(transactions, JSON.parse(xhr.responseText)) ||
        arraysAreEqual(transactions, [])
      ) {
        transactions = JSON.parse(xhr.responseText);
        graph = true;
      }

      if (!search) {
        loadSandbox();
        updateBalance();
      }

      updateTransactionList(getCurrentPage(), search);

      if (order === "vse" && graph) {
        drawGraph(animate);
      } else if (graph) {
        // NOVO
        var placeholder = order;
        order = "vse";
        drawGraph(animate);
        order = placeholder;
      }
    }
  };

  xhr.send("transakcije&" + order + "&" + search + "sesskey=" + sesskey + "");
}

function arraysAreEqual(arr1, arr2) {
  return JSON.stringify(arr1) === JSON.stringify(arr2);
}

function drawGraph(animate) {
  const sortedTransactions = [...transactions].sort(
    (a, b) =>
      new Date(a.CAS_DATUM_TRANSAKCIJE) - new Date(b.CAS_DATUM_TRANSAKCIJE)
  );

  const labels = sortedTransactions.map((transaction) => {
    const maxLength = 5;
    const label = transaction.OPIS;

    if (label.length > maxLength) {
      return label.substring(0, maxLength) + "...";
    } else {
      return label;
    }
  });

  const amounts = sortedTransactions.map((transaction) => {
    const amount = parseFloat(transaction.ZNESEK);
    return transaction.TIP === "odliv" ? -amount : amount;
  });

  const cumulativeSum = amounts.reduce((acc, value) => {
    acc.push((acc.length > 0 ? acc[acc.length - 1] : 0) + value);
    return acc;
  }, []);

  //console.log(labels);
  //console.log(amounts);
  //console.log(cumulativeSum);
  //console.log(sortedTransactions);

  amounts.unshift(0);
  cumulativeSum.unshift(0);
  labels.unshift("Začetek");

  const ctx = document.getElementById("transactionChart").getContext("2d");

  const existingChart = Chart.getChart(ctx);
  if (existingChart) {
    existingChart.destroy();
  }

  const catChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Transakcija",
          data: cumulativeSum,
          fill: false,
          borderColor: "rgba(75, 192, 192, 1)",
          borderWidth: 3,
        },
      ],
    },
    options: {
      scales: {
        x: {
          type: "category",
          position: "bottom",
          beginAtZero: true,
        },
        y: {
          beginAtZero: true,
          suggestedMin: Math.min(...cumulativeSum) - 50,
          suggestedMax: Math.max(...cumulativeSum) + 50,
        },
      },
      animation: {
        duration: animate ? 1000 : 0,
      },
    },
  });

  var positiveSum = 0;
  var negativeSum = 0;

  amounts.forEach((amount) => {
    if (amount >= 0) {
      positiveSum += amount;
    } else {
      negativeSum += amount;
    }
  });

  negativeSum = Math.abs(negativeSum);
  var totalSum = positiveSum - negativeSum;

  const ctxBar = document
    .getElementById("transactionChartBar")
    .getContext("2d");

  const existingChartBar = Chart.getChart(ctxBar);
  if (existingChartBar) {
    existingChartBar.destroy();
  }

  const barChart = new Chart(ctxBar, {
    type: "bar",
    data: {
      labels: ["Pozitivno stanje", "Negativno stanje"],
      datasets: [
        {
          label: "Transakcije",
          data: [positiveSum, negativeSum],
          backgroundColor: ["rgba(75, 192, 192, 0.2)", "rgba(255, 0, 0, 0.2)"],
          borderColor: ["rgba(75, 192, 192, 1)", "rgba(255, 0, 0, 1)"],
          borderWidth: 2,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: Math.max(positiveSum, negativeSum) + 50,
          ticks: {
            callback: function (value, index, values) {
              return value.toFixed(2);
            },
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
      },
      animation: {
        duration: animate ? 1000 : 0,
      },
    },
  });
}

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
            document.getElementById("title-transactions").innerHTML =
              'Seznam transakcij <h5><span class="badge bg-secondary">Peskovnik ' +
              sandbox.IME +
              "</span></h5>";
            sandboxName = sandbox.IME;
            sandboxId = sandbox.ID_SANDBOX;
            loadTransactions();
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
      document.getElementById("title-transactions").innerHTML =
        'Seznam transakcij <h5><span class="badge bg-primary">Glavni račun</span></h5>';
      sandboxName = "";
      sandboxId = "";
      loadTransactions();
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

function addTransaction() {
  const description = document.getElementById("description").value;
  const rawAmount = parseFloat(document.getElementById("amount").value);
  const amount = isNaN(rawAmount) ? 0 : parseFloat(rawAmount.toFixed(2));
  const desc = document.getElementById("description").value;
  const type = document.querySelector('input[name="type"]:checked').value;
  const date = new Date().getTime();

  if (description && !isNaN(amount) && amount > 0) {
    const transaction = {
      OPIS: description,
      ZNESEK: amount,
      TIP: type,
      CAS_DATUM_TRANSAKCIJE: date,
    };

    transactions.unshift(transaction);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "action.php?ajax", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        notyf.success("Transakcija uspešno dodana.");
        loadTransactions();
      }
    };
    xhr.send(
      "dodaj&opis=" +
        desc +
        "&znesek=" +
        amount +
        "&tip=" +
        type +
        "&sesskey=" +
        sesskey +
        ""
    );

    document.getElementById("transactionForm").reset();
  } else {
    notyf.error("Prosimo, vnesite veljaven opis in znesek.");
  }
}

const transactionsPerPage = 6;
function getCurrentPage() {
  const storedPage = localStorage.getItem("currentPage");
  return storedPage ? parseInt(storedPage, 10) : 1;
}

function setCurrentPage(page) {
  localStorage.setItem("currentPage", page);
}
function updateTransactionList(page, search) {
  const currentPage = page || getCurrentPage();
  const transactionList = document.getElementById("transactionList");
  transactionList.innerHTML = "";

  if (transactions.length === 0) {
    const noTransactionItem = document.createElement("li");
    noTransactionItem.className = "list-group-item align-middle";
    if (search !== "")
      noTransactionItem.innerHTML = "<b>Iskanje ni vrnilo rezultatov.</b>";
    else
      noTransactionItem.innerHTML = "<b>V sistemu ni nobene transakcije.</b>";
    transactionList.appendChild(noTransactionItem);
  } else {
    const startIndex = (currentPage - 1) * transactionsPerPage;
    const endIndex = startIndex + transactionsPerPage;
    const visibleTransactions = transactions.slice(startIndex, endIndex);

    visibleTransactions.forEach((transaction) => {
      const listItem = document.createElement("li");
      listItem.className = "list-group-item align-middle";

      var badge = document.createElement("span");

      badge = document.createElement("span");
      badge.className =
        transaction.TIP === "priliv" ? "badge bg-success" : "badge bg-danger";
      badge.textContent = transaction.TIP === "priliv" ? "Priliv" : "Odliv";
      listItem.appendChild(badge);

      listItem.innerHTML += ` ${new Date(
        transaction.CAS_DATUM_TRANSAKCIJE
      ).toLocaleString()}<br><b>${transaction.OPIS}</b>: ${
        transaction.ZNESEK
      } €`;

      const containerBtn = document.createElement("div");
      containerBtn.style.display = "inline";
      containerBtn.style.float = "right";

      const viewIcon = document.createElement("i");
      viewIcon.className = "bi bi-eye m-1 text-primary";
      viewIcon.style.cursor = "pointer";
      viewIcon.onclick = () => viewTransaction(transaction.ID_TRANSAKCIJE);
      containerBtn.appendChild(viewIcon);

      const deleteIcon = document.createElement("i");
      deleteIcon.className = "bi bi-trash m-1 text-danger float-right";
      deleteIcon.style.cursor = "pointer";
      deleteIcon.onclick = () => deleteTransaction(transaction.ID_TRANSAKCIJE);
      containerBtn.appendChild(deleteIcon);

      listItem.appendChild(containerBtn);

      transactionList.appendChild(listItem);
    });
  }
  const totalPages = Math.ceil(transactions.length / transactionsPerPage);
  addPaginationControls(currentPage, totalPages);
  setCurrentPage(currentPage);
}
function addPaginationControls(currentPage, totalPages) {
  const paginationContainer = document.getElementById("pagination");
  paginationContainer.innerHTML = "";

  if (totalPages <= 8) {
    for (let i = 1; i <= totalPages; i++) {
      const paginationItem = document.createElement("li");
      paginationItem.className = "page-item";

      const paginationLink = document.createElement("a");
      paginationLink.className = `page-link ${
        currentPage === i ? "active" : ""
      }`;
      paginationLink.textContent = i;
      paginationLink.href = "#";
      paginationLink.onclick = () => updateTransactionList(i);

      paginationItem.appendChild(paginationLink);
      paginationContainer.appendChild(paginationItem);
    }
    return;
  }

  const maxButtons = 8;
  const sideButtons = Math.floor((maxButtons - 3) / 2);
  const startPage = Math.max(1, currentPage - sideButtons);
  const endPage = Math.min(totalPages, startPage + maxButtons - 1);

  if (startPage > 1) {
    addPaginationButton(1, currentPage);
    if (startPage > 2) {
      addEllipsis();
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    addPaginationButton(i, currentPage);
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      addEllipsis();
    }
    addPaginationButton(totalPages, currentPage);
  }

  function addPaginationButton(pageNumber, currentPage) {
    const paginationItem = document.createElement("li");
    paginationItem.className = "page-item";

    const paginationLink = document.createElement("a");
    paginationLink.className = `page-link ${
      currentPage === pageNumber ? "active" : ""
    }`;
    paginationLink.textContent = pageNumber;
    paginationLink.href = "#";
    paginationLink.onclick = () => updateTransactionList(pageNumber);

    paginationItem.appendChild(paginationLink);
    paginationContainer.appendChild(paginationItem);
  }

  function addEllipsis() {
    const ellipsisItem = document.createElement("li");
    ellipsisItem.className = "page-item disabled";
    const ellipsisSpan = document.createElement("span");
    ellipsisSpan.className = "page-link";
    ellipsisSpan.textContent = "...";
    ellipsisItem.appendChild(ellipsisSpan);
    paginationContainer.appendChild(ellipsisItem);
  }
}

function deleteTransaction(x) {
  var url = $(this).attr("href");
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
          notyf.success("Transakcija uspešno izbrisana.");
          loadTransactions();
        }
      };
      xhr.send("izbrisi&id=" + x + "&sesskey=" + sesskey + "");
    }
  });
}

//  NOVO
function editTransaction(x) {
  console.log(x);

  var forma = document.getElementById("urediTrans");
  var ime = document.getElementById("ime").value;
  var znesek = document.getElementById("znesek").value;
  //var tip = document.getElementById("tip").value;
  var tip = document.querySelector('input[name="tip"]:checked').value;
  var datum = document.getElementById("datum").value;
  var ura = document.getElementById("ura").value;
  var opis = document.getElementById("opis").value;
  //var opis = document.querySelector('input[name="opis"]').value;

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      notyf.success("Transakcija uspešno posodobljena.");
      window.location.href = "page.php?view";
    }
  };
  console.log("sjhd");
  //xhr.send("edit&id=" + x + "&sesskey=" + sesskey + "");
  xhr.send(
    "edit&id=" +
      x +
      "&ime=" +
      ime +
      "&znesek=" +
      znesek +
      "&tip=" +
      tip +
      "&datum=" +
      datum +
      "&ura=" +
      ura +
      "&opis=" +
      opis +
      "&datoteka=" +
      datoteka +
      "&sesskey=" +
      sesskey +
      ""
  );
  console.log("end");
}

function odstraniDatoteko() {
  document.getElementById("datoteka_prejsnja").value = "";
  console.log("Datoteka odstranjena.");
}

// NOVO END

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

          if (response.type === "ok") {
            var xhr2 = new XMLHttpRequest();
            xhr2.open("POST", "action.php?ajax", true);
            xhr2.setRequestHeader(
              "Content-type",
              "application/x-www-form-urlencoded"
            );
            xhr2.onreadystatechange = function () {
              if (xhr2.readyState === 4 && xhr2.status === 200) {
                notyf.success("Peskovnik je uspešno dodan in naložen.");
                document.getElementById("title-transactions").innerHTML =
                  'Seznam transakcij <h5><span class="badge bg-secondary">Peskovnik ' +
                  response.name +
                  "</span></h5>";
                sandboxName = sandbox.IME;
                sandboxId = sandbox.ID_SANDBOX;
                loadTransactions();
              }
            };
            xhr2.send(
              "peskovnik&izberi&id=" +
                response.sandbox_id +
                "&sesskey=" +
                sesskey +
                ""
            );
          } else {
            notyf.error(response.msg);
          }
        }
      };
      xhr.send(
        "peskovnik&dodaj&ime=" + result.value + "&sesskey=" + sesskey + ""
      );
    }
  });
}

function viewTransaction(x) {
  const url = "action.php?view&id=" + x + "&sesskey=" + sesskey + "";
  window.location.href = url;
}

function updateBalance() {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      balance = parseFloat(xhr.responseText).toFixed(2);
      if (balance >= 0)
        document.getElementById("balance").className = "text-success";
      else document.getElementById("balance").className = "text-danger";
      document.getElementById("balance").textContent = balance + " €";
    }
  };
  xhr.send("stanje&sesskey=" + sesskey + "");
}

function showTransactions(type) {
  if (type == "vse") {
    listType = "vse";
  } else if (type == "priliv") listType = "priliv";
  else if (type == "odliv") listType = "odliv";

  var liElementVse = document.getElementById("type-vse");
  var liElementPriliv = document.getElementById("type-priliv");
  var liElementOdliv = document.getElementById("type-odliv");
  if (listType == "vse") {
    liElementVse.classList.add("active");
    liElementPriliv.classList.remove("active");
    liElementOdliv.classList.remove("active");
  } else if (listType == "priliv") {
    liElementVse.classList.remove("active");
    liElementPriliv.classList.add("active");
    liElementOdliv.classList.remove("active");
  } else if (listType == "odliv") {
    liElementVse.classList.remove("active");
    liElementPriliv.classList.remove("active");
    liElementOdliv.classList.add("active");
  }

  loadTransactions(type);
}

// novo
function addItem(list, inputField) {
  var list = document.getElementById(list);
  //var list = document.getElementById("hitriVnosi");

  var listItem = document.createElement("li");

  inputField = document.getElementById("novOpis");
  listItem.innerText = inputField.value; // passed the field.
  list.appendChild(listItem);
  return false; // stop submission
}

hitriVnosi = [];

function populateHitriVnosiMenu() {
  const hitriVnosMenu = document.querySelector("#hitriVnosi");
  hitriVnosMenu.innerHTML = "";

  if (hitriVnosi.length === 0) {
    const noVnosi = document.createElement("li");
    noVnosi.innerHTML =
      '<a class="dropdown-item disabled" href="#">Ni vnosov</a>';
    dropdownMenu.appendChild(noVnosi);
  } else {
    hitriVnosi.forEach((hitriVnosi) => {
      const li = document.createElement("li");
      const a = document.createElement("a");
      a.classList.add("dropdown-item");
      a.href = "#";

      a.textContent = `${hitriVnosi.IME}`;

      // ----------------------
      a.addEventListener("click", function (event) {
        // todo
      });

      li.appendChild(a);
      hitriVnosMenu.appendChild(li);
    });
  }
  // --------------------------------

  const divider = document.createElement("li");
  divider.innerHTML = '<hr class="dropdown-divider">';
  hitriVnosMenu.appendChild(divider);

  const addVnos = document.createElement("li");
  addVnos.innerHTML =
    '<a class="dropdown-item" onClick="addItem();" href="#">Nov vnos</a>';
  hitriVnosMenu.appendChild(addVnos);
}

function ponastaviPogled() {
  loadTransactions("vse");
  document.getElementById("sortForm").reset();

  listType = "vse";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Več filtrov";
}

function sortABC() {
  loadTransactions("abeceda");
  document.getElementById("sortForm").reset();

  listType = "abeceda";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Po abecedi";
}

function najvisjaVrednost() {
  loadTransactions("najvisja-vrednost");
  document.getElementById("sortForm").reset();

  listType = "najvisja-vrednost";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najvišja vrednost";
}

function najnizjaVrednost() {
  loadTransactions("najnizja-vrednost");

  listType = "najnizja-vrednost";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najnižja vrednost";
}

function najvisjiPriliv() {
  loadTransactions("najvisji-priliv");

  listType = "najvisji-priliv";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najvišji priliv";
}

function najnizjiPriliv() {
  loadTransactions("najnizji-priliv");

  listType = "najnizji-priliv";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najnižji priliv";
}

function najvisjiOdliv() {
  loadTransactions("najvisji-odliv");

  listType = "najvisji-odliv";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najvišji odliv";
}

function najnizjiOdliv() {
  loadTransactions("najnizji-odliv");

  listType = "najnizji-odliv";
  var gumb = document.getElementById("vecFiltrovGumb");
  gumb.innerHTML = "Najnižji odliv";
}

function napolniFilter() {
  var ul = document.getElementById("dropdown-filter");
  ul.innerHTML = "";

  if (listType == "vse") {
    var li = document.createElement("li");
    li.innerHTML =
      '<li class="dropdown-item active" onclick="ponastaviPogled()" >Ponastavi</li>';
    ul.appendChild(li);
  } else {
    var li = document.createElement("li");
    li.innerHTML =
      '<li class="dropdown-item" onclick="ponastaviPogled()" >Ponastavi</li>';
    ul.appendChild(li);
  }

  if (listType == "abeceda") {
    var li1 = document.createElement("li");
    li1.innerHTML =
      '<li class="dropdown-item active" onclick="sortABC()">Po abecedi</li>';
    ul.appendChild(li1);
  } else {
    var li1 = document.createElement("li");
    li1.innerHTML =
      '<li class="dropdown-item" onclick="sortABC()">Po abecedi</li>';
    ul.appendChild(li1);
  }

  if (listType == "najvisja-vrednost") {
    var li2 = document.createElement("li");
    li2.innerHTML =
      '<li class="dropdown-item active" onclick="najvisjaVrednost()">Najvišja vrednost</li>';
    ul.appendChild(li2);
  } else {
    var li2 = document.createElement("li");
    li2.innerHTML =
      '<li class="dropdown-item" onclick="najvisjaVrednost()">Najvišja vrednost</li>';
    ul.appendChild(li2);
  }

  if (listType == "najnizja-vrednost") {
    var li3 = document.createElement("li");
    li3.innerHTML =
      '<li class="dropdown-item active" onclick="najnizjaVrednost()">Najnižja vrednost</li>';
    ul.appendChild(li3);
  } else {
    var li3 = document.createElement("li");
    li3.innerHTML =
      '<li class="dropdown-item" onclick="najnizjaVrednost()">Najnižja vrednost</li>';
    ul.appendChild(li3);
  }

  if (listType == "najvisji-priliv") {
    var li4 = document.createElement("li");
    li4.innerHTML =
      '<li class="dropdown-item active" onclick="najvisjiPriliv()">Najvišji priliv</li>';
    ul.appendChild(li4);
  } else {
    var li4 = document.createElement("li");
    li4.innerHTML =
      '<li class="dropdown-item" onclick="najvisjiPriliv()">Najvišji priliv</li>';
    ul.appendChild(li4);
  }

  if (listType == "najnizji-priliv") {
    var li5 = document.createElement("li");
    li5.innerHTML =
      '<li class="dropdown-item active" onclick="najnizjiPriliv()">Najnižji priliv</li>';
    ul.appendChild(li5);
  } else {
    var li5 = document.createElement("li");
    li5.innerHTML =
      '<li class="dropdown-item" onclick="najnizjiPriliv()">Najnižji priliv</li>';
    ul.appendChild(li5);
  }

  if (listType == "najvisji-odliv") {
    var li6 = document.createElement("li");
    li6.innerHTML =
      '<li class="dropdown-item active" onclick="najvisjiOdliv()">Najvišji odliv</li>';
    ul.appendChild(li6);
  } else {
    var li6 = document.createElement("li");
    li6.innerHTML =
      '<li class="dropdown-item" onclick="najvisjiOdliv()">Najvišji odliv</li>';
    ul.appendChild(li6);
  }

  if (listType == "najnizji-odliv") {
    var li7 = document.createElement("li");
    li7.innerHTML =
      '<li class="dropdown-item active" onclick="najnizjiOdliv()">Najnižji odliv</li>';
    ul.appendChild(li7);
  } else {
    var li7 = document.createElement("li");
    li7.innerHTML =
      '<li class="dropdown-item" onclick="najnizjiOdliv()">Najnižji odliv</li>';
    ul.appendChild(li7);
  }
}
