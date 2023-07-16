var organizationBtn = document.getElementById('organization');
var personBtn = document.getElementById('person');
var registrationForm = document.getElementById('organizationForm');
var loginForm = document.getElementById('personForm');

//Organization-modal automaticlly clicked after opening the website
//var bShowPersonModal in php if person modal has a error
var bShowPersonModal;
if (bShowPersonModal) {
    //if in PHP we get a error from inserting data into Database, the Person-form is getting displayed
    personBtn.classList.add('clicked');
    organizationBtn.classList.remove('clicked');
    registrationForm.style.display = 'none';
    loginForm.style.display = 'block';
} else {
    //else the organization-modal should always been displayed
    organizationBtn.classList.add('clicked');
    personBtn.classList.remove('clicked');
    registrationForm.style.display = 'block';
    loginForm.style.display = 'none';
}

//Show the Organization Form for Modal
organizationBtn.addEventListener('click', function () {
    organizationBtn.classList.add('clicked');
    personBtn.classList.remove('clicked');
    registrationForm.style.display = 'block';
    loginForm.style.display = 'none';
});
//Show the Person Form for Modal
personBtn.addEventListener('click', function () {
    personBtn.classList.add('clicked');
    organizationBtn.classList.remove('clicked');
    registrationForm.style.display = 'none';
    loginForm.style.display = 'block';
});


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
var maleRadio_organization = document.getElementById("male_organization");
var femaleRadio_organization = document.getElementById("female_organization");
//radio buttons are abit special to insert value into the input field

//Person-Form all input values from PHP with "echo"
var Ansprechpartner_Person, Adresse_Person, rechnungsKuerzel_Person, PLZ_Person, Ort_Person, Vertragsdatum_Person;
var gender_Person;
var maleRadio_Person = document.getElementById("male_Person");
var femaleRadio_Person = document.getElementById("female_Person");
//radio buttons are abit special to insert value into the input field

var updatePerson = false, updateOrganization = false;

//If the organizationBtn is clicked, and we have an error, the Modal stays open and the values in the Input fields doesnt reset
if (organizationBtn.classList.contains('clicked')) {
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
        if (gender_organization == "M") {
            maleRadio_organization.checked = true;
        } else if (gender_organization == "F") {
            femaleRadio_organization.checked = true;
        }

        if(messageType=="edit"){
            personBtn.style.display = "none";
            // updateOrganization = true;
            // updatePerson = false;
        }
    }
}
//If the personbtn is clicked, and we have an error, the Modal stays open and the values in the Input fields doesnt reset
if (personBtn.classList.contains('clicked')) {
    if (messageType == "error" || messageType == "edit") {
        document.querySelector(".modal").classList.add("active");
        //insert the last data to inputfields at a error 
        document.getElementById("Ansprechpartner_Person").value = Ansprechpartner_Person;
        document.getElementById("Adresse_Person").value = Adresse_Person;
        document.getElementById("rechnungsKuerzel_Person").value = rechnungsKuerzel_Person;
        document.getElementById("PLZ_Person").value = PLZ_Person;
        document.getElementById("Ort_Person").value = Ort_Person;
        document.getElementById("Vertragsdatum_Person").value = Vertragsdatum_Person;
        if (gender_Person == "M") {
            maleRadio_Person.checked = true;
        } else if (gender_Person == "F") {
            femaleRadio_Person.checked = true;
        }

        if(messageType=="edit"){
            organizationBtn.style.display = "none";
            // updatePerson = true;
            // updateOrganization = false;
        }
    }
}

// Modal open
document.querySelector("#CreateContactModal").addEventListener("click", function () {
    document.querySelector(".modal").classList.add("active");
});
// Modal close 
document.querySelector(".modal .modal-header span").addEventListener("click", function () {
    document.querySelector(".modal").classList.remove("active");
    window.location.replace('contact.php');

});


// Detect page reloads
if (performance.navigation.type === 1) {
    // Page reload detected, do the redirect to the same page
    window.location.replace('contact.php');
}




// Select Search-Input-Element
var searchInput = document.getElementById('search');

// Event-Listener for Keydown
searchInput.addEventListener('keydown', function(event) {
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
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.search-buttons button');
    buttons.forEach(function(button) {
        var buttonState = localStorage.getItem(button.value);
        if (buttonState === 'clicked') {
            button.classList.add('clicked');
        }
    });
});