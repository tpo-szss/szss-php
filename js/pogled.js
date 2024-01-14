var izbranPogled = JSON.parse(localStorage.getItem("izbranPogled")) || [];
var izbranaTema = JSON.parse(localStorage.getItem("izbranaTema")) || [
  "navadno",
];

function shraniPogled() {
  localStorage.setItem("izbranPogled", JSON.stringify(izbranPogled));
  document.getElementById(izbranPogled).checked = true;
  location.reload();
}

function nastaviPogled() {
  //document.getElementById("izbira-pogled");
  var izbrani_pogled;

  if (document.getElementById("sirse").checked) {
    izbrani_pogled = document.getElementById("sirse").value;
  }

  if (document.getElementById("zgosceno").checked) {
    izbrani_pogled = document.getElementById("zgosceno").value;
  }

  console.log(izbrani_pogled);
  izbranPogled = izbrani_pogled;
  shraniPogled();
}

function shraniTemo() {
  console.log(izbranaTema);
  localStorage.setItem("izbranaTema", JSON.stringify(izbranaTema));
  document.getElementById(izbranaTema).checked = true;
  location.reload();
}

function nastaviTemo() {
  //document.getElementById("izbira-pogled");
  document.getElementById(izbranaTema).checked = false;
  var izbrana_tema;

  if (document.getElementById("navadno").checked) {
    izbrana_tema = document.getElementById("navadno").value;
  }

  if (document.getElementById("temno").checked) {
    izbrana_tema = document.getElementById("temno").value;
  }

  if (document.getElementById("modro").checked) {
    izbrana_tema = document.getElementById("modro").value;
  }

  if (document.getElementById("rumeno").checked) {
    izbrana_tema = document.getElementById("rumeno").value;
  }

  console.log(izbrana_tema);

  izbranaTema = izbrana_tema;
  shraniTemo();
}

function poisciChecked() {
  document.getElementById(izbranaTema).checked = true;
  document.getElementById(izbranPogled).checked = true;
}

function shraniSpremembe() {
  location.reload();
}
