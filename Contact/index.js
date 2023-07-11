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

// Store the messagetype from php in js
// var messageType = "<?php echo $messageType; ?>";
// var submitButton = document.getElementById('organizationSubmitBtn');

// submitButton.addEventListener('click', function (event) {

//     if (messageType === "error") {
//         // Modal open
//         event.preventDefault(); // Blockiert das Standardverhalten des Buttons
//         document.querySelector(".modal").classList.add("active");
//         alert(messageType);
//     }
// });


