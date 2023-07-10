<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Storing the information in the variables
    //Code for Organization-Form
    if (isset($_POST['organizationSubmit'])) {
        $firmenName_organization = $_POST['firmenName_organization'];
        $firmenAdresse_organization = $_POST['firmenAdresse_organization'];
        $rechnungsKuerzel_organization = $_POST['rechnungsKuerzel_organization'];
        $PLZ_organization = $_POST['PLZ_organization'];
        $Ort_organization = $_POST['Ort_organization'];
        $Vertragsdatum_organization = $_POST['Vertragsdatum_organization'];
        $Ansprechpartner_organization = $_POST['Ansprechpartner_organization'];
        $gender_organization = $_POST['gender_organization'];

        if ($Vertragsdatum_organization == "") {
            $Vertragsdatum_organization = null;
        }
        if ($Ansprechpartner_organization == "") {
            $Ansprechpartner_organization = null;
        }
        if ($gender_organization != "M" && $gender_organization != "F") {
            $gender_organization = null;
        }

        insertOrganizationDataIntoKundenTable($firmenName_organization, $firmenAdresse_organization, $rechnungsKuerzel_organization, $PLZ_organization, $Ort_organization, $Vertragsdatum_organization, $Ansprechpartner_organization, $gender_organization);
    }
    // Code for Person"-Form
    elseif (isset($_POST["personSubmit"])) {
        $Ansprechpartner_Person = $_POST['Ansprechpartner_Person'];
        $Adresse_Person = $_POST['Adresse_Person'];
        $rechnungsKuerzel_Person = $_POST['rechnungsKuerzel_Person'];
        $PLZ_Person = $_POST['PLZ_Person'];
        $Ort_Person = $_POST['Ort_Person'];
        $Vertragsdatum_Person = $_POST['Vertragsdatum_Person'];
        $gender_person = $_POST['gender_person'];

        if ($Vertragsdatum_Person == "") {
            $Vertragsdatum_Person = null;
        }

        insertPersonDataIntoKundenTable($Adresse_Person, $rechnungsKuerzel_Person, $PLZ_Person, $Ort_Person, $Vertragsdatum_Person, $Ansprechpartner_Person, $gender_person);
    }
}



function insertOrganizationDataIntoKundenTable($firmenName, $Adresse, $rechnungsKuerzel, $PLZ, $Ort, $Vertragsdatum, $Ansprechpartner, $gender)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

    try {
        // Prepeare the INSERT-query
        $stmt = $conn->prepare("INSERT INTO kunden (FirmenName, Adresse, RechnungsKürzel, PLZ, Ort, VertragsDatum, Name_Ansprechpartner, Gender)
                                   VALUES (:firmenName, :Adresse, :rechnungsKuerzel, :plz, :ort, :vertragsDatum, :ansprechpartner, :gender)");

        // Bind Values to the parameters
        $stmt->bindParam(':firmenName', $firmenName);
        $stmt->bindParam(':Adresse', $Adresse);
        $stmt->bindParam(':rechnungsKuerzel', $rechnungsKuerzel);
        $stmt->bindParam(':plz', $PLZ);
        $stmt->bindParam(':ort', $Ort);
        $stmt->bindParam(':vertragsDatum', $Vertragsdatum);
        $stmt->bindParam(':ansprechpartner', $Ansprechpartner);
        $stmt->bindParam(':gender', $gender);

        // Execute the query
        $stmt->execute();

        echo "Daten erfolgreich eingefügt.";
        header("Refresh:0");
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }

    include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
}

function insertPersonDataIntoKundenTable($Adresse, $rechnungsKuerzel, $PLZ, $Ort, $Vertragsdatum, $Ansprechpartner, $gender)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

    try {
        // Prepeare the INSERT-query
        $stmt = $conn->prepare("INSERT INTO kunden (Adresse, RechnungsKürzel, PLZ, Ort, VertragsDatum, Name_Ansprechpartner, Gender)
                               VALUES (:Adresse, :rechnungsKuerzel, :plz, :ort, :vertragsDatum, :ansprechpartner, :gender)");

        // Bind Values to the parameters
        $stmt->bindParam(':Adresse', $Adresse);
        $stmt->bindParam(':rechnungsKuerzel', $rechnungsKuerzel);
        $stmt->bindParam(':plz', $PLZ);
        $stmt->bindParam(':ort', $Ort);
        $stmt->bindParam(':vertragsDatum', $Vertragsdatum);
        $stmt->bindParam(':ansprechpartner', $Ansprechpartner);
        $stmt->bindParam(':gender', $gender);

        // Execute the query
        $stmt->execute();

        echo "Daten erfolgreich eingefügt.";
        header("Refresh:0");
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }

    include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
}

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
                        <form method="POST">
                            <input type="text" id="firmenName_organization" name="firmenName_organization" placeholder="Firmenname*" required>

                            <input type="text" id="firmenAdresse_organization" name="firmenAdresse_organization" placeholder="Firmenadresse*" required>

                            <input type="text" id="rechnungsKuerzel_organization" name="rechnungsKuerzel_organization" placeholder="RechnungsKürzel*" required>

                            <input type="text" id="PLZ_organization" name="PLZ_organization" placeholder="PLZ*" required>

                            <input type="text" id="Ort_organization" name="Ort_organization" placeholder="Ort*" required>

                            <input type="text" id="Vertragsdatum_organization" name="Vertragsdatum_organization" placeholder="Vertragsdatum">

                            <input type="text" id="Ansprechpartner_organization" name="Ansprechpartner_organization" placeholder="Ansprechpartner (Vorname Nachname)">

                            <div class="gender-container">
                                <input type="radio" name="gender_organization" id="male_organization" value="M">
                                <label for="male_organization">Male</label>

                                <input type="radio" name="gender_organization" id="female_organization" value="F">
                                <label for="female_organization">Female</label>
                            </div>

                            <button type="submit" name="organizationSubmit" class="sendNewContactData-Btn">Senden</button>
                        </form>
                    </div>

                    <div id="personForm" class="form-container">
                        <form method="POST">
                            <input type="text" id="Ansprechpartner_Person" name="Ansprechpartner_Person" placeholder="Ansprechpartner* (Vorname Nachname)" required>

                            <input type="text" id="Adresse_Person" name="Adresse_Person" placeholder="Adresse*" required>

                            <input type="text" id="rechnungsKuerzel_Person" name="rechnungsKuerzel_Person" placeholder="RechnungsKürzel*" required>

                            <input type="text" id="PLZ_Person" name="PLZ_Person" placeholder="PLZ*" required>

                            <input type="text" id="Ort_Person" name="Ort_Person" placeholder="Ort*" required>

                            <input type="text" id="Vertragsdatum_Person" name="Vertragsdatum_Person" placeholder="Vertragsdatum">

                            <div class="gender-container">
                                <input type="radio" name="gender_person" id="male_Person" value="M" required>
                                <label for="male_Person">Male*</label>

                                <input type="radio" name="gender_person" id="female_Person" value="F" required>
                                <label for="female_Person">Female*</label>
                            </div>

                            <button type="submit" name="personSubmit" class="sendNewContactData-Btn">Senden</button>
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