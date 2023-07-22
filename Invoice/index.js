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


// Modal open
document.querySelector("#CreateInvoiceModal").addEventListener("click", function () {
    document.querySelector(".modal").classList.add("active");
});
// Modal close 
document.querySelector(".modal .modal-header span").addEventListener("click", function () {
    document.querySelector(".modal").classList.remove("active");
    window.location.replace('invoice.php');

});


// Detect page reloads
if (performance.navigation.type === 1) {
    // Page reload detected, do the redirect to the same page
    window.location.replace('invoice.php');
}


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


function setCustomerId() {
    // Das ausgewählte Element im <select> abrufen
    var selectElement = document.getElementById('customerList');
    var selectedValue = selectElement.value;

    // Den Wert des Hidden Input-Felds setzen
    document.getElementById('selectedKundenID').value = selectedValue;
}


// Function to handle the change event of the dropdown list
function handleDropdownChange() {
    // Get the selected option
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
    //existing inputfield with add leistung
    var count = 1;
    var addedAddLeistungsstraße = false;

    // Function to update the +/- span visibility
    function updateLeistungButtons() {
        var leistungContainers = $('.leistungen .leistung-leistungsstraße .leistung-container');


        leistungContainers.each(function (index) {
            var addSpan = $(this).find('.add-leistung');
            var removeSpan = $(this).find('.remove-leistung');

            removeSpan.show();

            let LeistungsstraßeSpan = $(this).find('.add-leistungsstraße');

            if (!LeistungsstraßeSpan.length) {
                $(this).append('<span class="material-icons-sharp add-leistungsstraße">add</span>');
            }

            LeistungsstraßeSpan.show();

            var lastLeistungContainer = leistungContainers.last();
            var lastLeistungsstraßeSpan = lastLeistungContainer.find('.add-leistungsstraße');
            lastLeistungsstraßeSpan.hide();


            if (index === 0) {
                addSpan.show();
                removeSpan.hide();

                if (leistungContainers.length > 0 && !addedAddLeistungsstraße) {
                    LeistungsstraßeSpan.show();
                    addedAddLeistungsstraße = true;
                }

                //Remove "span add Leistungsstraße" when one Inputfield is given
                else if (leistungContainers.length == 1) {
                    LeistungsstraßeSpan.hide();
                    addedAddLeistungsstraße = false;
                }
            }
        });
    }

    $(document).on('click', '.add-leistung', function () {
        count++;
        var leistungenContainer = $(this).closest('.leistungen');
        leistungenContainer.append(add_leistung_inputfield(count));
        updateLeistungButtons();
    });

    $(document).on('click', '.remove-leistung', function () {
        count--;
        var leistungenContainer = $(this).closest('.leistung-leistungsstraße');
        leistungenContainer.remove();
        updateLeistungButtons();
    });


    $(document).on('click', '.add-leistungsstraße', function () {
        //Inputfield with Leisutng; the add leistungsstraße should be hidden
        //Inputfield leistungsstraße is getting added with a remove span to remove the inputfield
        //clicked remove inputfield leistungsstraße => remove inputfield leistungsstraße; span add leistungsstraße at inputfield leistung should be shown again

        var leistungContainer = $(this).closest('.leistung-leistungsstraße');
        leistungContainer.append(add_leistungsstraße_inputfield);

    });

});

function add_leistung_inputfield(count) {
    var html = '<div class="leistung-leistungsstraße">';
    html += '<div class="leistung-container">';
    html += '<input type="text" name="leistung[]" class="leistung-input" placeholder="Leistung*" value="" required>';
    html += '<span class="material-icons-sharp remove-leistung">remove</span>';
    html += '</div>';
    html += '</div>';
    //   (count > 1)
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