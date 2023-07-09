<?php

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt</title>

    <!--Link to Kontakt.css | Stylesheet-->
    <link rel="stylesheet" href="./contact.css">
    <!--Link to Material Icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Kontakte</h1>
            <form action="/search" method="get" class="search-form">
                <div class="search">
                    <span class="material-icons-sharp">search</span>
                    <input type="search" name="q" id="query" placeholder="Search..." autocomplete="off">
                </div>
            </form>
        </div>



        <!--Create New Contact with Button to open Modal-->
        <div class="createContacts">
            <!-- Trigger/Open The Modal -->
            <button type="button" id="CreateContactModal" class="createContact-Btn">Kontakt erstellen</button>

            <div class="modal" id="ContactModal">
                <!-- Modal content -->
                <div class="modal-header">
                    <h2>Create Contact</h2>
                    <span class="material-icons-sharp">close</span>
                </div>


                <div class="form">
                    <button type="button" id="organization" class="organization">Organization</button>
                    <button type="button" id="person" class="person">Person</button>

                    <div id="organizationForm" class="form-container">
                        <form>
                            <input type="text" id="firmenName_organization" name="firmenName_organization"
                                placeholder="Firmenname*" required>

                            <input type="text" id="firmenAdresse_organization" name="firmenAdresse_organization"
                                placeholder="Firmenadresse*" required>

                            <input type="text" id="rechnungsKuerzel_organization" name="rechnungsKuerzel_organization"
                                placeholder="RechnungsKürzel*" required>

                            <input type="text" id="PLZ_organization" name="PLZ_organization" placeholder="PLZ*"
                                required>

                            <input type="text" id="Ort_organization" name="Ort_organization" placeholder="Ort*"
                                required>

                            <input type="text" id="Vertragsdatum_organization" name="Vertragsdatum_organization"
                                placeholder="Vertragsdatum">

                            <input type="text" id="Ansprechpartner_organization" name="Ansprechpartner_organization"
                                placeholder="Ansprechpartner (Vorname Nachname)">

                            <div class="gender-container">
                                <input type="radio" name="gender_organization" id="male_organization" value="male">
                                <label for="male_organization">Male</label>

                                <input type="radio" name="gender_organization" id="female_organization" value="female">
                                <label for="female_organization">Female</label>
                            </div>

                            <button type="submit" class="sendNewContactData-Btn">Senden</button>
                        </form>
                    </div>

                    <div id="personForm" class="form-container">
                        <form>
                            <input type="text" id="Ansprechpartner_Person" name="Ansprechpartner_Person"
                                placeholder="Ansprechpartner* (Vorname Nachname)" required>

                            <input type="text" id="Adresse_Person" name="Adresse_Person" placeholder="Adresse*"
                                required>

                            <input type="text" id="rechnungsKuerzel_Person" name="rechnungsKuerzel_Person"
                                placeholder="RechnungsKürzel*" required>

                            <input type="text" id="PLZ_Person" name="PLZ_Person" placeholder="PLZ*" required>

                            <input type="text" id="Ort_Person" name="Ort_Person" placeholder="Ort*" required>

                            <input type="text" id="Vertragsdatum_Person" name="Vertragsdatum_Person"
                                placeholder="Vertragsdatum">

                            <div class="gender-container">
                                <input type="radio" name="gender_person" id="male_Person" value="male" required>
                                <label for="male_Person">Male*</label>

                                <input type="radio" name="gender_person" id="female_Person" value="female" required>
                                <label for="female_Person">Female*</label>
                            </div>

                            <button type="submit" class="sendNewContactData-Btn">Senden</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Create Contacts with Modal -->

        <div class="crud">

        </div>

    </div>
    <script src="./index.js"></script>
</body>

</html>