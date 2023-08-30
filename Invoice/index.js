// Close Error/ Success Message 
var closeBtn = document.querySelector(".message span");
closeBtn.addEventListener("click", function () {
    document.getElementById('message').style.display = 'none';
});

// Detect page reloads
if (performance.navigation.type === 1) {
    // Page reload detected, do the redirect to the same page
    window.location.replace('invoice.php');
}


// ==================== START OF SEARCHBAR ====================

// Select Search-Input-Element
var searchInput = document.getElementById('search');

// Event-Listener for Keydown => If in the Searchbar, the Enter-Key on the Keyboard is clicked, the searchButton should also be clicked
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

// Modal MonatlicheRechnungen Open
document.querySelector('#CreateMonatlicheRechnungenModal').addEventListener('click', function () {
    document.querySelector('.modal-MonatlicheRechnungen').classList.add('active');
});
// Modal MonatlicheRechnungen Close
document.querySelector('.modal-MonatlicheRechnungen .modal-header span').addEventListener('click', function () {
    document.querySelector('.modal-MonatlicheRechnungen').classList.remove('active');
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

// Event listener to hide RechnungsInformationen when click occurs outside the modal-MonatlicheRechnung
document.addEventListener('click', function (event) {
    const modalContainer = document.querySelector('.modal-MonatlicheRechnungen');
    const rechnungsInformationenElements = document.getElementsByClassName('RechnungsInformationen');

    if (!modalContainer.contains(event.target)) {
        for (const element of rechnungsInformationenElements) {
            element.style.display = 'none';
        }
    }
});

function toggleRechnungsInformationen(checkbox) {
    const rechnungsInformationen = checkbox.closest(".monatlicheRechnung-Kunde").querySelector(".RechnungsInformationen");

    if (checkbox.checked) {
        rechnungsInformationen.style.display = "block";
    } else {
        rechnungsInformationen.style.display = "none";
    }
}


function setRechnungsMonatJahrCurrentMonthYear() {
    // Set the current year and month as default values for the "RechnungsJahr" and "RechnungsMonat" input fields
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth() + 1; // JavaScript returns the month as a 0-based value, so +1 for the actual month

    // Add leading zero if the month is single-digit
    if (currentMonth < 10) {
        currentMonth = '0' + currentMonth;
    }

    // Assign the values to the input fields
    document.getElementById('RechnungsMonatJahr').value = currentYear + '-' + currentMonth;
    document.getElementById('RechnungsMonatJahr-MonatlicheRechnungen').value = currentYear + '-' + currentMonth;
}
setRechnungsMonatJahrCurrentMonthYear();

//Toggle the Inputfield at the Dropdownliste Abrechnungsart
function toggleInputField(containerElement) {
    var selectElement = containerElement.querySelector("select[name='AbrechnungsartList[]']");
    var inputElement = containerElement.querySelector("input[name='Stunden[]']");

    if (selectElement.value === "Stunden") {
        inputElement.style.display = "block"; // Display the input field if "Stunden" is selected
        inputElement.required = true; // Set the "required" attribute to true
    } else {
        inputElement.style.display = "none"; // Hide the input field if "Pauschal" or other option is selected
        inputElement.value = ""; // Set the input field value to empty when hiding it
        inputElement.required = false; // Set the "required" attribute to false
    }
}

// PHP Variables in JS
var jsonEditData;
var messageType;

//Adding first ckEditor which can not be deleted

const firstEditor = [];
//Add ckEditor 5 Custom Build
ClassicEditor
    .create(document.querySelector('#leistungEditor'))
    .then(editor => {

        firstEditor.push(editor);

        //default font is Arial
        const fontFamily = editor.commands.get('fontFamily');
        fontFamily.execute({
            value: 'Arial, Helvetica, sans-serif'
        });

        // Insert value into the ckEditor if we edit the given Invoice
        if (messageType == "edit") {
            editor.setData(jsonEditData.Leistung_edit[0]);
        }
    })
    .catch(error => {
        console.error(error);
    });



let editorCount = 0; // Countvariable for the created editor
const editorArray = []; // Array to store editor instances/objects

//  Adding onclick for the tfoot label 
function addDienstleistungsRow() {
    const tBody = document.querySelector('.dienstleistungs-details tbody');
    const newRow = document.createElement('tr');


    newRow.setAttribute('data-editor-index', editorCount); // Add attribute to mark the editor index
    newRow.setAttribute('data-editor-active', 'true'); // Add attribute to mark the editor as active

    editorCount++;

    newRow.innerHTML = `
        <td>
            <div class="leistung">
                <textarea class="leistungEditor" name="leistungEditor[]"></textarea>
            </div>
        </td>
        <td>
            <div class="abrechnungsart">
                <div class="Abrechnungsart-container" onchange="toggleInputField(this)">
                    <input type="number" name="Stunden[]" id="Stunden" value="" placeholder="Anzahl der Stunden" style="display: none;" step="any">
                    <select name="AbrechnungsartList[]" id="AbrechnungsartList" required>
                        <option value="Pauschal">Pauschal</option>
                        <option value="Stunden">Stunden</option>
                    </select>
                </div>
            </div>
        </td>
        <td>
            <div class="preis">
                <input type="number" name="nettoPreis[]" id="nettoPreis" value="" placeholder="NettoPreis*" step="0.01" required>
            </div>
        </td>
        <td class="delete-icon-cell">
            <span class="material-icons-sharp" onclick="deleteRow(this)">delete</span>
        </td>
    `;

    tBody.appendChild(newRow);

    // Creating new ckEditor
    ClassicEditor
        .create(newRow.querySelector('.leistungEditor'))
        .then(editor => {

            //default font is Arial
            const fontFamily = editor.commands.get('fontFamily');
            fontFamily.execute({
                value: 'Arial, Helvetica, sans-serif'
            });

            editorArray.push(editor); // Add the editor instance to the array

            if (messageType == "edit" && editorCount < jsonEditData.Leistung_edit.length) {
                editor.setData(jsonEditData.Leistung_edit[editorCount]);
            }

        })
        .catch(error => {
            console.error(error);
        });
}
// Delete the added Row 
function deleteRow(deleteIcon) {
    const rowToDelete = deleteIcon.closest('tr');
    const editorIndex = rowToDelete.getAttribute('data-editor-index');

    const editor = editorArray[editorIndex]; // Get the corresponding editor instance from the array

    // Destroy the editor
    editor.destroy();

    // Remove the row
    rowToDelete.remove();
}

// Event listener for form submission; checking if inputfield of the ckEditor is empty => dont submit form
document.getElementById('form-modal').addEventListener('submit', function (event) {

    var allCkEditorFilled = true;

    if (firstEditor[0].getData().trim() == '') {
        allCkEditorFilled = false;
    }

    if (allCkEditorFilled) {
        const rows = document.querySelectorAll('.dienstleistungs-details tbody tr');

        for (const row of rows) {
            const editorIsActive = row.getAttribute('data-editor-active');
            const editorIndex = row.getAttribute('data-editor-index');

            const editor = editorArray[editorIndex];
            // Check if the editor is active (not deleted)
            if (editorIsActive === 'true') {
                const editorData = editor.getData();

                if (editorData.trim() === '' || editorData == '') {
                    allCkEditorFilled = false;
                    break;
                }
            }
        }
    }

    // all ckEditors are filled => reload website
    if (allCkEditorFilled) {
        const submitButton = event.target.querySelector('button[type="submit"]');
        if (submitButton.value === 'save' || submitButton.value == "update") {
            const form = document.getElementById('form-modal');

            // form action and target is added; the values from the form are given to the new windowtab invoiceMuster.php
            form.action = '/projekt/website_vereinfacht/Invoice/Muster/generate-pdf.php';
            form.target = '_blank';
            if (submitButton.value == "update") {
                messageType = "";
            }
        }
        window.location.replace('invoice.php');
    } else {
        const messageDiv = document.getElementById('message');
        const messageText = document.getElementById('messageText');
        event.preventDefault();
        messageDiv.style.display = 'flex';
        messageText.innerText = 'Leere Eingabe für die Leistung!';
        // Error Message Style
        messageDiv.classList.add('error');
    }
});

// Data from PHP (Switchcase edit) insert into the Inputfields
if (messageType == "edit") {
    var parsedEditData = jsonEditData;
    document.querySelector(".modal").classList.add("active");

    // Assuming parsedEditData.RechnungsDatum_edit is in the format "04.08.2023"
    // Convert it to "YYYY-MM-DD" format
    const rechnungsDatumParts = parsedEditData.RechnungsDatum_edit.split('.');
    const rechnungsDatumFormatted = `${rechnungsDatumParts[2]}-${rechnungsDatumParts[1]}-${rechnungsDatumParts[0]}`;

    // Assuming parsedEditData.Monat_Jahr_edit is in the format "August 2023"
    // Convert it to "YYYY-MM" format
    const monatJahrParts = parsedEditData.Monat_Jahr_edit.split(' ');
    const monthName = monatJahrParts[0];
    const year = monatJahrParts[1];

    const monthNameToNumber = {
        'Januar': '01',
        'Februar': '02',
        'März': '03',
        'April': '04',
        'Mai': '05',
        'Juni': '06',
        'Juli': '07',
        'August': '08',
        'September': '09',
        'Oktober': '10',
        'November': '11',
        'Dezember': '12'
    }
    const monthNumber = monthNameToNumber[monthName];
    const monatJahrFormatted = `${year}-${monthNumber}`;

    document.getElementById('RechnungsDatum').value = rechnungsDatumFormatted;
    document.getElementById('RechnungsMonatJahr').value = monatJahrFormatted;

    // Store the KundenID into the hiddenInputfield
    const kundenID_edit = parsedEditData.KundenID_edit;
    const selectedKundenIDInput = document.getElementById('selectedKundenID');
    selectedKundenIDInput.value = kundenID_edit;

    // Select the corresponding customer in the dropdown list
    const customerList = document.getElementById('customerList');
    for (let i = 0; i < customerList.options.length; i++) {
        if (customerList.options[i].value == kundenID_edit) {
            customerList.options[i].selected = true;
            break;
        }
    }


    // Calculate the rows to create; -1 because there is one row in default
    var addDienstleistungsRows = parsedEditData.NettoPreis_edit.length - 1;

    var nettoPreisInputFields = document.querySelectorAll('input[name="nettoPreis[]"]');
    var AbrechnungsartStundenInputFields = document.querySelectorAll("input[name='Stunden[]']");
    var AbrechnungsartSelectFields = document.querySelectorAll("select[name='AbrechnungsartList[]']");

    // const LeistungArray = parsedEditData.Leistung_edit;
    const AbrechnungsartArray = parsedEditData.Abrechnungsart_edit;
    const nettoPreisArray = parsedEditData.NettoPreis_edit;



    if (AbrechnungsartArray[0] == "Pauschal") {
        AbrechnungsartSelectFields[0].value = "Pauschal";
        AbrechnungsartStundenInputFields[0].style.display = "none"; // Hide the input field if "Pauschal" or other option is selected
        AbrechnungsartStundenInputFields[0].value = ""; // Set the input field value to empty when hiding it
        AbrechnungsartStundenInputFields[0].required = false; // Set the "required" attribute to false

    } else {
        AbrechnungsartSelectFields[0].value = "Stunden";
        AbrechnungsartStundenInputFields[0].style.display = "block"; // Display the input field if "Stunden" is selected
        AbrechnungsartArray[0] = AbrechnungsartArray[0].replace(' Stunden', '');
        AbrechnungsartStundenInputFields[0].value = AbrechnungsartArray[0];
        AbrechnungsartStundenInputFields[0].required = true; // Set the "required" attribute to true
    }

    nettoPreisInputFields[0].value = nettoPreisArray[0];

    if (0 < addDienstleistungsRows) {
        for (let i = 1; i <= addDienstleistungsRows; i++) {
            addDienstleistungsRow();

            AbrechnungsartStundenInputFields = document.querySelectorAll("input[name='Stunden[]']");
            AbrechnungsartSelectFields = document.querySelectorAll("select[name='AbrechnungsartList[]']");

            if (AbrechnungsartArray[i] == "Pauschal") {
                AbrechnungsartSelectFields[i].value = "Pauschal";
                AbrechnungsartStundenInputFields[i].style.display = "none"; // Hide the input field if "Pauschal" or other option is selected
                AbrechnungsartStundenInputFields[i].value = ""; // Set the input field value to empty when hiding it
                AbrechnungsartStundenInputFields[i].required = false; // Set the "required" attribute to false

            } else {
                AbrechnungsartSelectFields[i].value = "Stunden";
                AbrechnungsartStundenInputFields[i].style.display = "block"; // Display the input field if "Stunden" is selected
                AbrechnungsartArray[i] = parseFloat(AbrechnungsartArray[i].replace(' Stunden', ''));
                AbrechnungsartStundenInputFields[i].value = AbrechnungsartArray[i];
                AbrechnungsartStundenInputFields[i].required = true; // Set the "required" attribute to true
            }

            // Update nettoPreisInputFields array after adding a new row, so we can access the value of the certain inputfield
            nettoPreisInputFields = document.querySelectorAll('input[name="nettoPreis[]"]');
            nettoPreisInputFields[i].value = nettoPreisArray[i];
        }
    }



    // checkbox for MonatlicheRechnungBool_edit
    if (parsedEditData.MonatlicheRechnungBool_edit == "1") {
        // If MonatlicheRechnungBool_edit is "1", check the checkbox
        document.getElementById('monatlicheRechnung').checked = true;
    } else {
        // If MonatlicheRechnungBool_edit is "0", uncheck the checkbox
        document.getElementById('monatlicheRechnung').checked = false;
    }
}

// Modal Monatliche-Rechnung check submit button
document.getElementById('form-modal-MonatlicheRechnung').addEventListener('submit', function (event) {
    // mindestens eins der chechboxen muss "gechecked" sein, sonst => nicht abschickend

    const checkboxes = document.querySelectorAll('[name="erstelleMonatlicheRechnung[]"]');
    let anyCheckboxChecked = false;

    for (const checkbox of checkboxes) {
        if (checkbox.checked) {
            anyCheckboxChecked = true;
            break;
        }
    }

    if (anyCheckboxChecked) {
        // form action and target is added; the values from the form are given to the new windowtab invoiceMuster.php
        const form_MonatlicheRechnung = document.getElementById('form-modal-MonatlicheRechnung');

        // Neue Datei muss verwendet werden für erstlleung der MonatlicheRechnung
        form_MonatlicheRechnung.action = '/projekt/website_vereinfacht/Invoice/Muster/generate-monatlicheRechnung-pdf.php';
        form_MonatlicheRechnung.target = '_blank';

        // Am besten neuladen nach form action damit wir vernünftig fehler vermeiden könenn
        window.location.replace('invoice.php');
    } else {
        const messageDiv = document.getElementById('message');
        const messageText = document.getElementById('messageText');
        event.preventDefault();
        // Error Message => Klick mindestens einen Checkbox an 
        messageDiv.style.display = 'flex';
        messageText.innerText = 'Mindestens eine Checkbox muss angeklickt sein';
        // Error Message Style
        messageDiv.classList.add('error');
    }

});

// Uncheck every checked checkbox of monatlicheRechnung
document.getElementById('uncheck_AllCheckboxes').addEventListener('click', function () {
    let checkboxes = document.querySelectorAll('input[name="erstelleMonatlicheRechnung[]"]');

    checkboxes.forEach(function (checkbox) {
        checkbox.checked = false;
    });
});

document.getElementById('checkAllCheckboxes').addEventListener('click', function () {
    let checkboxes = document.querySelectorAll('input[name="erstelleMonatlicheRechnung[]"]');

    checkboxes.forEach(function (checkbox) {
        checkbox.checked = true;
    });
});
// ==================== END OF MODAL ====================


// ==================== Start OF Crud ====================

function showDeleteConfirmation(button) {
    var confirmation = confirm("Bist du dir ganz sicher, das du diesen Datensatz löschen möchtest?");
    if (confirmation) {
        button.type = 'submit';
    } else {
        event.preventDefault();
    }
}


// ==================== END OF Crud ====================

