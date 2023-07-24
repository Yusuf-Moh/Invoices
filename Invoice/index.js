// Close Error/ Success Message 
var closeBtn = document.querySelector(".message span");
closeBtn.addEventListener("click", function () {
    document.getElementById('message').style.display = 'none';
});


//MessageType from PHP to JS with the help of "echo"
var messageType;

//Organization-Form all input values from PHP with "echo"
var firmenName_organization, firmenAdresse_organization, rechnungsKuerzel_organization, PLZ_organization, Ort_organization, Vertragsdatum_organization, Ansprechpartner_organization;
var gender_organization;

var updatePerson = false, updateOrganization = false;

if (messageType == "error" || messageType == "edit") {
    document.querySelector(".modal").classList.add("active");
    //insert the last data to inputfields at a error 
    document.getElementById("firmenName_organization").value = firmenName_organization;
    document.getElementById("firmenAdresse_organization").value = firmenAdresse_organization;
    document.getElementById("rechnungsKuerzel_organization").value = rechnungsKuerzel_organization;
    document.getElementById("PLZ_organization").value = PLZ_organization;
    document.getElementById("Ort_organization").value = Ort_organization;
    document.getElementById("Vertragsdatum_organization").value = Vertragsdatum_organization;
    document.getElementById("Ansprechpartner_organization").value = Ansprechpartner_organization;
    if (gender_organization == "Male") {
        maleRadio_organization.checked = true;
    } else if (gender_organization == "Female") {
        femaleRadio_organization.checked = true;
    }

}


// Detect page reloads
if (performance.navigation.type === 1) {
    // Page reload detected, do the redirect to the same page
    window.location.replace('invoice.php');
}


// ==================== START OF SEARCHBAR ====================

// Select Search-Input-Element
var searchInput = document.getElementById('search');

// Event-Listener for Keydown
searchInput.addEventListener('keydown', function (event) {
    // Check, if the holding key is the enter-key
    if (event.key === 'Enter') {
        // Select and Click Button-Element for switchcase in php
        var searchButton = document.getElementById('searchButton');
        searchButton.click();
    }
});


// Search-Btns
function changeBackground(button) {
    var buttonState = localStorage.getItem(button.value);
    if (buttonState === 'clicked') {
        button.classList.remove('clicked');
        localStorage.setItem(button.value, 'unclicked');
    } else {
        button.classList.add('clicked');
        localStorage.setItem(button.value, 'clicked');
    }
}


// Prüfe den Zustand der Buttons beim Laden der Seite
document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('.search-buttons button');
    buttons.forEach(function (button) {
        var buttonState = localStorage.getItem(button.value);
        if (buttonState === 'clicked') {
            button.classList.add('clicked');
        }
    });
});

// ==================== END OF SEARCHBAR ====================


// ==================== START OF MODAL ====================

// Modal open
document.querySelector("#CreateInvoiceModal").addEventListener("click", function () {
    document.querySelector(".modal").classList.add("active");
});
// Modal close 
document.querySelector(".modal .modal-header span").addEventListener("click", function () {
    document.querySelector(".modal").classList.remove("active");
    window.location.replace('invoice.php');

});


// KundenID stored in the hidden Inputfield "selectedKundenID"
function setCustomerId() {
    // Das ausgewählte Element im <select> abrufen
    var selectElement = document.getElementById('customerList');
    var selectedValue = selectElement.value;

    // Den Wert des Hidden Input-Felds setzen
    document.getElementById('selectedKundenID').value = selectedValue;
}


// Function to handle the change event of the dropdown list
function handleDropdownChange() {
    // Get the selected option and show the
    const select = document.getElementById('customerList');
    const selectedOption = select.options[select.selectedIndex];

    if (select.value == "") {
        document.getElementById("customer-details").style.display = "none";
    } else {
        document.getElementById('customer-details').style.display = "inline-grid";
        document.getElementById('rechnungskuerzel').textContent = selectedOption.dataset.rechnungskuerzel || '';
        document.getElementById('adresse').textContent = selectedOption.dataset.adresse || '';
        document.getElementById('plz').textContent = selectedOption.dataset.plz || '';
        document.getElementById('ort').textContent = selectedOption.dataset.ort || '';
    }
    setCustomerId();

}

// Add an event listener to the dropdown list
const dropdown = document.getElementById('customerList');
dropdown.addEventListener('change', handleDropdownChange);


// Event listener to hide customer details when click occurs outside the elements
document.addEventListener('click', function (event) {
    const formContainer = document.querySelector('.modal');

    if (!formContainer.contains(event.target)) {
        document.getElementById("customer-details").style.display = "none";
    }
});




//Dynamic Inputfields for Leistung

$(document).ready(function () {

    //Storing the currentYear and write the value in the inputfield RechnungsJahr
    // Set the current year and month as default values for the "RechnungsJahr" and "RechnungsMonat" input fields
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth() + 1; // JavaScript returns the month as a 0-based value, so +1 for the actual month

    // Add leading zero if the month is single-digit
    if (currentMonth < 10) {
        currentMonth = '0' + currentMonth;
    }

    // Assign the values to the input fields
    $('#RechnungsMonatJahr').val(currentYear + '-' + currentMonth);



    //When save btn is clicked, the data should be stored
    $('#save').on('click', function (e) {
        e.preventDefault();

        var form = $('#form-modal')[0];
        if (form.checkValidity()) {
            saveData();
            form.submit();
        }
    });

});

function add_leistung_inputfield() {
    var html = '<div class="leistung-leistungsstraße">';
    html += '<div class="leistung-container">';
    html += '<input type="text" name="leistung[]" class="leistung-input" placeholder="Leistung*" value="" required>';
    html += '<span class="material-icons-sharp remove-leistung">remove</span>';
    html += '<span class="material-icons-sharp add-leistungsstraße">add</span>';
    html += '</div>';
    html += '</div>';
    return html;
}

function add_leistungsstraße_inputfield() {
    var html = '';
    html += '<div class="leistungsstraße-container">'
    html += '<input type="text" name="leistungsstraße[]" class="leistungsstraße-input" placeholder="Leistungsstraße*" value="" required>';
    html += '<span class="material-icons-sharp remove-leistungsstraße">remove</span>';
    html += '</div>';
    return html;
}


//Toggle the Inputfield at the Dropdownliste ABrechnungsart
function toggleInputField() {
    var selectElement = document.getElementById("AbrechnungsartList");
    var inputElement = document.getElementById("Stunden");

    if (selectElement.value === "Stunden") {
        inputElement.style.display = "block"; // Display the input field if "Stunden" is selected
    } else {
        inputElement.style.display = "none"; // Hide the input field if "Pauschal" or other option is selected
        inputElement.value = ""; // Set the input field value to empty when hiding it
    }
}



// ==================== END OF MODAL ====================