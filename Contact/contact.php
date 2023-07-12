<?php
$message = "";
$messageType = "";
$showMessage = "";

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

        //assigning null to the not required input fields if its empty, so the DB gets the value Null. 
        if ($Vertragsdatum_organization == "") {
            $Vertragsdatum_organization = null;
        }
        if ($Ansprechpartner_organization == "") {
            $Ansprechpartner_organization = null;
        }
        if ($gender_organization != "M" && $gender_organization != "F") {
            $gender_organization = null;
        }

        //Checking if Firmenname and/or Rechnungskürzel already exisit in DB because it is not allowed to have multiple same type of them
        if (!checkIfValueExists('FirmenName', $firmenName_organization) && !checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_organization)) {
            insertOrganizationDataIntoKundenTable($firmenName_organization, $firmenAdresse_organization, $rechnungsKuerzel_organization, $PLZ_organization, $Ort_organization, $Vertragsdatum_organization, $Ansprechpartner_organization, $gender_organization);
            $messageType = "success";
            $message = "Erfolgreich Werte in die Datenbank hinzugefügt.";
        } elseif (checkIfValueExists('FirmenName', $firmenName_organization) && checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_organization)) {
            $messageType = "error";
            $message = "Fehler: Firmenname und Rechnungskürzel existieren bereits in der Datenbank.";
        } elseif (checkIfValueExists('FirmenName', $firmenName_organization)) {
            $messageType = "error";
            $message = "Fehler: Firmenname existiert bereits in der Datenbank.";
        } elseif (checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_organization)) {
            $messageType = "error";
            //Extension: Which company the existing Rechnungskürzel was assigned to for $message
            $message = "Fehler: Rechnungskürzel exisitiert bereits in der Datenbank.";
        }
        //storing the php variable to js
        echo "<script>";
        echo "var messageType = '$messageType';";
        echo "var firmenName_organization = '$firmenName_organization';";
        echo "var firmenAdresse_organization = '$firmenAdresse_organization';";
        echo "var rechnungsKuerzel_organization = '$rechnungsKuerzel_organization';";
        echo "var PLZ_organization = '$PLZ_organization';";
        echo "var Ort_organization = '$Ort_organization';";
        echo "var Vertragsdatum_organization = '$Vertragsdatum_organization';";
        echo "var Ansprechpartner_organization = '$Ansprechpartner_organization';";
        echo "var gender_organization = '$gender_organization';";
        echo "</script>";
        $showMessage = "flex";
    }
    // Code for Person"-Form
    elseif (isset($_POST["personSubmit"])) {
        $Ansprechpartner_Person = $_POST['Ansprechpartner_Person'];
        $Adresse_Person = $_POST['Adresse_Person'];
        $rechnungsKuerzel_Person = $_POST['rechnungsKuerzel_Person'];
        $PLZ_Person = $_POST['PLZ_Person'];
        $Ort_Person = $_POST['Ort_Person'];
        $Vertragsdatum_Person = $_POST['Vertragsdatum_Person'];
        $gender_Person = $_POST['gender_person'];

        if ($Vertragsdatum_Person == "") {
            $Vertragsdatum_Person = null;
        }

        if (!checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_Person)) {
            insertPersonDataIntoKundenTable($Adresse_Person, $rechnungsKuerzel_Person, $PLZ_Person, $Ort_Person, $Vertragsdatum_Person, $Ansprechpartner_Person, $gender_person);
            $messageType = "success";
            $message = "Erfolgreich Werte in die Datenbank hinzugefügt.";
        } else if (checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_Person)) {
            $messageType = "error";
            $message = "Fehler: Rechnungskürzel exisitiert bereits in der Datenbank.";
        }

        //storing the php variable to js
        echo "<script>";
        echo "var bShowPersonModal = true;";
        echo "var messageType = '$messageType';";
        echo "var Ansprechpartner_Person = '$Ansprechpartner_Person';";
        echo "var Adresse_Person = '$Adresse_Person';";
        echo "var rechnungsKuerzel_Person = '$rechnungsKuerzel_Person';";
        echo "var PLZ_Person = '$PLZ_Person';";
        echo "var Ort_Person = '$Ort_Person';";
        echo "var Vertragsdatum_Person = '$Vertragsdatum_Person';";
        echo "var gender_Person = '$gender_Person';";
        echo "</script>";
        $showMessage = "flex";
    }
}

function checkIfValueExists($columnName, $value)
{
    include('../dbPhp/dbOpenConnection.php'); // Verbindung öffnen

    try {
        // Prepare the SELECT-query
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Kunden WHERE $columnName = :value");

        // Bind the value to the parameter
        $stmt->bindParam(':value', $value);

        // Execute the query
        $stmt->execute();

        // Fetch the result
        $result = $stmt->fetchColumn();

        // Return true if the value exists, otherwise return false
        $exists = ($result > 0);

        include('../dbPhp/dbCLoseConnection.php'); // Verbindung schließen

        return $exists;
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
        return false;
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

            <div class="message <?php echo $messageType; ?>" id="message" style="display: <?php echo $showMessage ?>">
                <h2><?php echo $message; ?></h2>
                <span class="material-icons-sharp">close</span>
            </div>

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
                            <input type="text" id="firmenName_organization" name="firmenName_organization" placeholder="Firmenname*" value="" required>

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

                            <button type="submit" name="organizationSubmit" class="sendNewContactData-Btn" id="organizationSubmitBtn">Senden</button>
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