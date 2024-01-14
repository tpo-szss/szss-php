function dodajKoncniDatum() {
  // Get the checkbox
  var checkBox = document.getElementById("flexCheckDefault");
  // Get the output text
  var koledar = document.getElementById("koncniDatumDaily");

  // If the checkbox is checked, display the output text
  if (checkBox.checked == true) {
    koledar.style.display = "block";
    console.log("check");
  } else {
    koledar.style.display = "none";
    console.log("noncheck");
  }
}

function dodajKoncniDatumWeek() {
  // Get the checkbox
  var checkBox = document.getElementById("flexCheckDefaultWeek");
  // Get the output text
  var koledar = document.getElementById("koncniDatumWeekly");

  // If the checkbox is checked, display the output text
  if (checkBox.checked == true) {
    koledar.style.display = "block";
    console.log("check");
  } else {
    koledar.style.display = "none";
    console.log("noncheck");
  }
}

function dodajKoncniDatumMonth() {
  // Get the checkbox
  var checkBox = document.getElementById("flexCheckDefaultMonth");
  // Get the output text
  var koledar = document.getElementById("koncniDatumMonthly");

  // If the checkbox is checked, display the output text
  if (checkBox.checked == true) {
    koledar.style.display = "block";
    console.log("check");
  } else {
    koledar.style.display = "none";
    console.log("noncheck");
  }
}

function dodajKoncniDatumYear() {
  // Get the checkbox
  var checkBox = document.getElementById("flexCheckDefaultYear");
  // Get the output text
  var koledar = document.getElementById("koncniDatumYearly");

  // If the checkbox is checked, display the output text
  if (checkBox.checked == true) {
    koledar.style.display = "block";
    console.log("check");
  } else {
    koledar.style.display = "none";
    console.log("noncheck");
  }
}

function naredi() {
  // če gledamo dnevno
  var zacDat = "9999-99-99";
  var koncDatum = "9999-99-99";
  var kaj = "one_time";

  var one = document.getElementById("pills-one-tab");
  var day = document.getElementById("pills-daily-tab");
  var week = document.getElementById("pills-weekly-tab");
  var month = document.getElementById("pills-monthly-tab");
  var year = document.getElementById("pills-yearly-tab");

  console.log(day);
  if (one.classList.contains("active")) {
    console.log("ena");
    kaj = "one_time";
    zacDat = document.getElementById("zacDatumDaily").value;
    koncDatum = document.getElementById("koncniDatumDaily").value;
  } else if (day.classList.contains("active")) {
    console.log("dan");
    kaj = "daily";
    zacDat = document.getElementById("zacDatumDaily").value;
    koncDatum = document.getElementById("koncniDatumDaily").value;
  } /*else if (week.classList.contains("active")) {
        console.log("teden");
        kaj = "weekly";
        zacDat = document.getElementById("zacDatumWeekly").value;
        koncDatum = document.getElementById("koncniDatumWeekly").value;
    }*/ else if (month.classList.contains("active")) {
    console.log("messec");
    kaj = "monthly";
    zacDat = document.getElementById("zacDatumMonthly").value;
    koncDatum = document.getElementById("koncniDatumMonthly").value;
  } else if (year.classList.contains("active")) {
    console.log("leto");
    kaj = "yearly";
    zacDat = document.getElementById("zacDatumYearly").value;
    koncDatum = document.getElementById("koncniDatumYearly").value;
  }

  if (zacDat == "") {
    zacDat = "2024-01-01";
  }
  console.log(kaj, zacDat, koncDatum);

  return [kaj, zacDat, koncDatum];
}

function narediVec() {
  var ponavljanje = document.getElementById("ponavljanjeID");
  var startDatum = document.getElementById("startDatumID");
  var endDatum = document.getElementById("endDatumID");

  [kaj, zac, kon] = naredi();
  ponavljanje.value = kaj;
  startDatum.value = zac;
  endDatum.value = kon;

  notyf.success("Uspešno shranjeno!");
}
