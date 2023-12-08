const notyf = new Notyf({
  duration: 2500,
});

let balance = 0;
let transactions = [];
loadTransactions();

function loadTransactions(x) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "action.php?ajax", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      transactions = JSON.parse(xhr.responseText);
      updateBalance();
      updateTransactionList();
    }
  };
  xhr.send("transakcije&" + x + "&sesskey=" + sesskey + "");
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

function updateTransactionList() {
  const transactionList = document.getElementById("transactionList");
  transactionList.innerHTML = "";

  if (transactions.length === 0) {
    const noTransactionItem = document.createElement("li");
    noTransactionItem.className = "list-group-item align-middle";
    noTransactionItem.innerHTML =
      "<b>Ni transakcij, ustvarite vašo prvo transakcijo.</b>";
    transactionList.appendChild(noTransactionItem);
  } else {
    transactions.forEach((transaction) => {
      const listItem = document.createElement("li");
      listItem.className = "list-group-item align-middle";

      const badge = document.createElement("span");
      badge.className =
        transaction.TIP === "priliv" ? "badge bg-success" : "badge bg-danger";
      badge.textContent = transaction.TIP === "priliv" ? "priliv" : "odliv";
      listItem.appendChild(badge);

      listItem.innerHTML += ` ${new Date(
        transaction.CAS_DATUM_TRANSAKCIJE
      ).toLocaleString()} <b>${transaction.OPIS}</b>: ${transaction.ZNESEK} €`;

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
  loadTransactions(type);
}
