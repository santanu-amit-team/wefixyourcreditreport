   
var slider = document.getElementById('slider');

noUiSlider.create(slider, {
    start: [25],
    padding: [17, 0],
    connect: [true, false],
    range: {
        'min': [0],
        'max': [100]
    }
});


var creditTypeName = document.getElementById('credit-type');
var creditSavingsWrapper = document.getElementById('savings-amount-wrapper');
var creditSavingsAmount = document.getElementById('credit-savings-amount');
var mortgageRate = document.getElementById('mortgage-rate');
var mortgagePayment = document.getElementById('mortgage-payment');
var mortgageSavings = document.getElementById('mortgage-savings');
var ccRate = document.getElementById('cc-rate');
var ccPayment = document.getElementById('cc-payment');
var ccSavings = document.getElementById('cc-savings');
var autoRate = document.getElementById('auto-rate');
var autoPayment = document.getElementById('auto-payment');
var autoSavings = document.getElementById('auto-savings');
var ploanRate = document.getElementById('ploan-rate');
var ploanPayment = document.getElementById('ploan-payment');
var ploanSavings = document.getElementById('ploan-savings');
var rentalRate = document.getElementById('rental-rate');
var rentalPayment = document.getElementById('rental-payment');
var rentalSavings = document.getElementById('rental-savings');
var jobRate = document.getElementById('job-rate');
var jobPayment = document.getElementById('job-payment');
var jobSavings = document.getElementById('job-savings');

slider.noUiSlider.on('update', function () {
    values = this.get();
    children = slider.getElementsByClassName('noUi-touch-area');
  
    if ( values >= 0 && values <= 36 ) {
          children[0].innerHTML = "Bad";
          creditTypeName.innerHTML = "Bad";
          creditSavingsWrapper.className = "red";
          creditSavingsAmount.innerHTML = "0";
          mortgageRate.innerHTML = "8.36%";
          mortgagePayment.innerHTML = "$1,900";
          mortgageSavings.className = "red";
          mortgageSavings.innerHTML = "$0";
          ccRate.innerHTML = "22.99%";
          ccPayment.innerHTML = "$58";
          ccSavings.className = "red";
          ccSavings.innerHTML = "$0";
          autoRate.innerHTML = "15.11%";
          autoPayment.innerHTML = "$697";
          autoSavings.className = "red";
          autoSavings.innerHTML = "$0";
          ploanRate.innerHTML = "210%";
          ploanPayment.innerHTML = "$1,810";
          ploanSavings.className = "red";
          ploanSavings.innerHTML = "$0";
          rentalRate.innerHTML = "N/A";
          rentalPayment.className = "red";
          rentalPayment.innerHTML = "Declined";
          rentalSavings.className = "red";
          rentalSavings.innerHTML = "Declined";
          jobRate.innerHTML = "N/A";
          jobPayment.className = "red";
          jobPayment.innerHTML = "Declined";
          jobSavings.className = "red";
          jobSavings.innerHTML = "Declined";
    } else if ( values >= 36 && values <= 58 ) {
          children[0].innerHTML = "Average";   
          creditTypeName.innerHTML = "Average";
          creditSavingsWrapper.className = "green";
          creditSavingsAmount.innerHTML = "371,880";
          mortgageRate.innerHTML = "6%";
          mortgagePayment.innerHTML = "$1,500";
          mortgageSavings.className = "green";
          mortgageSavings.innerHTML = "$144,000";
          ccRate.innerHTML = "19.8%";
          ccPayment.innerHTML = "$50";
          ccSavings.className = "green";
          ccSavings.innerHTML = "$3,060";
          autoRate.innerHTML = "9.85%";
          autoPayment.innerHTML = "$632";
          autoSavings.className = "green";
          autoSavings.innerHTML = "$12,480";
          ploanRate.innerHTML = "18.35%";
          ploanPayment.innerHTML = "$255";
          ploanSavings.className = "green";
          ploanSavings.innerHTML = "$186,600";
          rentalRate.innerHTML = "N/A";
          rentalPayment.className = "normal";
          rentalPayment.innerHTML = "2X Deposit";
          rentalSavings.className = "red";
          rentalSavings.innerHTML = "2X Deposit";
          jobRate.innerHTML = "N/A";
          jobPayment.className = "normal";
          jobPayment.innerHTML = "Hired";
          jobSavings.className = "green";
          jobSavings.innerHTML = "Hired";
    }  else if ( values >= 58 && values <= 78 ) {
          children[0].innerHTML = "Good";  
          creditTypeName.innerHTML = "Good";
          creditSavingsWrapper.className = "green";
          creditSavingsAmount.innerHTML = "485,172";
          mortgageRate.innerHTML = "4.5%";
          mortgagePayment.innerHTML = "$1,267";
          mortgageSavings.className = "green";
          mortgageSavings.innerHTML = "$227,880";
          ccRate.innerHTML = "10.9%";
          ccPayment.innerHTML = "$27";
          ccSavings.className = "green";
          ccSavings.innerHTML = "$11,130";
          autoRate.innerHTML = "7.17%";
          autoPayment.innerHTML = "$601";
          autoSavings.className = "green";
          autoSavings.innerHTML = "$18,432";
          ploanRate.innerHTML = "9.17%";
          ploanPayment.innerHTML = "$208";
          ploanSavings.className = "green";
          ploanSavings.innerHTML = "$192,240";
          rentalRate.innerHTML = "N/A";
          rentalPayment.className = "normal";
          rentalPayment.innerHTML = "Accepted";
          rentalSavings.className = "green";
          rentalSavings.innerHTML = "Accepted";
          jobRate.innerHTML = "N/A";
          jobPayment.className = "normal";
          jobPayment.innerHTML = "Hired";
          jobSavings.className = "green";
          jobSavings.innerHTML = "Hired";
    } else {
          children[0].innerHTML = "Great"; 
          creditTypeName.innerHTML = "Great";
          creditSavingsWrapper.className = "green";
          creditSavingsAmount.innerHTML = "561,502";
          mortgageRate.innerHTML = "3.3%";
          mortgagePayment.innerHTML = "$1,095";
          mortgageSavings.className = "green";
          mortgageSavings.innerHTML = "$289,900";
          ccRate.innerHTML = "7.6%";
          ccPayment.innerHTML = "$19";
          ccSavings.className = "green";
          ccSavings.innerHTML = "$13,960";
          autoRate.innerHTML = "5.9%";
          autoPayment.innerHTML = "$586";
          autoSavings.className = "green";
          autoSavings.innerHTML = "$21,312";
          ploanRate.innerHTML = "7.19%";
          ploanPayment.innerHTML = "$198";
          ploanSavings.className = "green";
          ploanSavings.innerHTML = "$193,440";
          rentalRate.innerHTML = "N/A";
          rentalPayment.className = "normal";
          rentalPayment.innerHTML = "Accepted";
          rentalSavings.className = "green";
          rentalSavings.innerHTML = "Accepted";
          jobRate.innerHTML = "N/A";
          jobPayment.className = "normal";
          jobPayment.innerHTML = "Hired";
          jobSavings.className = "green";
          jobSavings.innerHTML = "Hired";
    }
});
