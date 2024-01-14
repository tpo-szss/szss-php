var currentInputCalc = "";
var lastResult = "";
var calcHistory = JSON.parse(localStorage.getItem("calcHistory")) || [];
var calculated = false;

window.onload = function () {
  updateHistory();
};

function appendNumber(number) {
  calculated = false;
  currentInputCalc += number;
  updateResult();
}

function appendOperator(operator) {
  if (calculated) {
    currentInputCalc = lastResult;
    calculated = false;
  }
  currentInputCalc += " " + operator + " ";
  updateResult();
}

function appendDecimal() {
  calculated = false;
  if (!currentInputCalc.includes(".")) {
    currentInputCalc += ".";
    updateResult();
  }
}

function clearInput() {
  calculated = false;
  currentInputCalc = "";
  updateResult();
}

function deleteDigit() {
  currentInputCalc = currentInputCalc.slice(0, -1);
  updateResult();
}

function calculateResult() {
  if (calculated) return;

  try {
    var result = eval(currentInputCalc);
    var formattedResult = result.toFixed(2);

    if (!isNaN(result) && currentInputCalc.trim() !== formattedResult) {
      if (formattedResult !== lastResult) {
        lastResult = formattedResult;

        var fullExpression = `${currentInputCalc} = ${formattedResult}`;

        if (!calcHistory.includes(fullExpression)) {
          calcHistory.unshift(fullExpression);
          calcHistory = calcHistory.slice(0, 10);
        }
      }
    }

    currentInputCalc = formattedResult;
    updateResult();
    updateHistory();
    saveHistory();
    currentInputCalc = "";
    calculated = true;
  } catch (error) {
    currentInputCalc = "Napaka";
    updateResult();
  }
}

function toggleSign() {
  currentInputCalc = currentInputCalc.startsWith("-")
    ? currentInputCalc.slice(1)
    : "-" + currentInputCalc;
  updateResult();
}

function updateResult() {
  document.getElementById("resultCalc").value = currentInputCalc;
}

function updateHistory() {
  var historyHtml = "";
  for (var i = 0; i < calcHistory.length; i++) {
    var entry = calcHistory[i];
    var parts = entry.split("=");
    if (parts.length === 2) {
      entry = `${parts[0]} = <strong>${parts[1]}</strong>`;
    }
    historyHtml += `<div class="history-entry">${entry}</div>`;
  }
  document.getElementById("calcHistory").innerHTML = historyHtml;
}
function saveHistory() {
  localStorage.setItem("calcHistory", JSON.stringify(calcHistory));
}

document.addEventListener("keydown", function (event) {
  const key = event.key;

  if (/\d/.test(key) && !event.key.startsWith("F")) {
    appendNumber(key);
  } else if (["+", "-", "*", "/"].includes(key)) {
    appendOperator(key);
  } else if (key === "Enter") {
    calculateResult();
  } else if (key === "Backspace") {
    deleteDigit();
  } else if (key === ".") {
    appendDecimal();
  }
});

function xhrCalcResult() {
  const rawAmountCalc = parseFloat(lastResult).toFixed(2);
  const amountCalc = isNaN(lastResult)
    ? 0
    : Math.abs(parseFloat(lastResult).toFixed(2));
  const desc = "Dodano prek kalkulatorja";
  const type = rawAmountCalc >= 0 ? "priliv" : "odliv";
  const date = new Date().getTime();

  if (desc && !isNaN(amountCalc) && amountCalc > 0) {
    const transaction = {
      OPIS: desc,
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
        amountCalc +
        "&tip=" +
        type +
        "&sesskey=" +
        sesskey +
        ""
    );

    document.getElementById("transactionForm").reset();
  } else {
    notyf.error("Prišlo je do težave.");
  }
}
