var organizationBtn = document.getElementById('organization');
var personBtn = document.getElementById('person');
var registrationForm = document.getElementById('organizationForm');
var loginForm = document.getElementById('personForm');

// Content Switch between Organization and Person
//automaticlly clicked after opening the website
organizationBtn.classList.add('clicked');
registrationForm.style.display = 'block';
loginForm.style.display = 'none';

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


// Modal open
document.querySelector("#CreateContactModal").addEventListener("click", function () {
    document.querySelector(".modal").classList.add("active");
});

// Modal close 
document.querySelector(".modal .modal-header span").addEventListener("click", function () {
    document.querySelector(".modal").classList.remove("active");
});


// Close Error/ Success Message 
var closeBtn = document.querySelector(".message span");

closeBtn.addEventListener("click", function () {
    document.getElementById('message').style.display = 'none';
});



var submitButton = document.getElementById('organizationSubmitBtn');
var messageDiv = document.getElementById('message');

var messageType;

//Organization-Form all input values:
var firmenName_organization, firmenAdresse_organization, rechnungsKuerzel_organization, PLZ_organization, Ort_organization, Vertragsdatum_organization, Ansprechpartner_organization;
var gender_organization;
var maleRadio_organization = document.getElementById("male_organization");
var femaleRadio_organization = document.getElementById("female_organization");

//radio buttons are abit special to insert value into the input field

if (organizationBtn.classList.contains('clicked')) {
    if (messageType == "error") {
        document.querySelector(".modal").classList.add("active");
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
    }
}