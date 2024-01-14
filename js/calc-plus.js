clearInputZ();
makeVisible();

dragElement(document.getElementById("mydiv"));

function dragElement(elmnt) {
  var pos1 = 0,
    pos2 = 0,
    pos3 = 0,
    pos4 = 0;
  if (document.getElementById(elmnt.id + "header")) {
    document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
  } else {
    elmnt.onmousedown = dragMouseDown;
  }

  function dragMouseDown(e) {
    e = e || window.event;
    e.preventDefault();
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    document.onmousemove = elementDrag;
  }

  function elementDrag(e) {
    e = e || window.event;
    e.preventDefault();
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;
    elmnt.style.top = elmnt.offsetTop - pos2 + "px";
    elmnt.style.left = elmnt.offsetLeft - pos1 + "px";
  }

  function closeDragElement() {
    document.onmouseup = null;
    document.onmousemove = null;
  }
}

function makeVisible() {
  elmnt = document.getElementById("mydiv");
  if (elmnt.style.visibility == "hidden") elmnt.style.visibility = "visible";
  else elmnt.style.visibility = "hidden";
}

var currentInputCalc = "";
var lastResult = "";
var calcHistory = JSON.parse(localStorage.getItem("calcHistory")) || [];
var calculated = false;

window.onload = function () {
  updateHistory();
};

function appendNumberZ(number) {
  console.log("appendNumberZ");
  calculated = false;
  currentInputCalc += number;
  updateResultZ();
}

function appendOperatorZ(operator) {
  if (calculated) {
    currentInputCalc = lastResult;
    calculated = false;
  }
  currentInputCalc += " " + operator + " ";
  updateResultZ();
}

function appendDecimalZ() {
  calculated = false;
  if (!currentInputCalc.includes(".")) {
    currentInputCalc += ".";
    updateResultZ();
  }
}

function clearInputZ() {
  calculated = false;
  currentInputCalc = "";
  updateResultZ();
}

function deleteDigitZ() {
  currentInputCalc = currentInputCalc.slice(0, -1);
  updateResultZ();
}

function calculateResultZ() {
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
    updateResultZ();
    updateHistoryZ();
    saveHistory();
    currentInputCalc = "";
    calculated = true;
  } catch (error) {
    currentInputCalc = "Napaka";
    updateResultZ();
  }
}

function toggleSignZ() {
  currentInputCalc = currentInputCalc.startsWith("-")
    ? currentInputCalc.slice(1)
    : "-" + currentInputCalc;
  updateResultZ();
}

function updateResultZ() {
  document.getElementById("resultCalc-zunanji").value = currentInputCalc;
}

function updateHistoryZ() {
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

function xhrCalcResultZ() {
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
