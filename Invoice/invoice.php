<?php
session_start();

function reset_vars()
{

    global $KundenID;
    $KundenID = null;

    global $firmenName_organization, $firmenAdresse_organization, $rechnungsKuerzel_organization, $PLZ_organization, $Ort_organization, $Vertragsdatum_organization, $Ansprechpartner_organization, $gender_organization;
    $firmenName_organization = null;
    $firmenAdresse_organization = null;
    $rechnungsKuerzel_organization = null;
    $PLZ_organization = null;
    $Ort_organization = null;
    $Vertragsdatum_organization = null;
    $Ansprechpartner_organization = null;
    $gender_organization = null;

    global $sql_query;
    // $sql_query = "SELECT * FROM `kunden`";

    global $param;
    // $param = [];

    global $message, $messageType, $showMessage;
    $message = "";
    $messageType = "";
    $showMessage = "";

    global $saveUpdate;
    $saveUpdate = "save";


    global $modalHeadline;
    $modalHeadline = "Erstelle Rechnung";

    global $showMessage;
    $showMessage = "none";
}


global $restart;
$restart = false;


if ($_SESSION['sql_query'] == "") {
    $_SESSION['sql_query'] = "SELECT * FROM `kunden`";
    $restart = true;
}

if ($_SESSION['param'] == "") {
    $_SESSION['param'] = [];
    $restart = true;
}

if ($restart) {
    header("Refresh:0");
}

$sql_query = $_SESSION['sql_query'];
$param = $_SESSION['param'];

//reset of every variables.
reset_vars();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['button'])) {
        $action = $_POST['button'];
        switch ($action) {
            case 'save':
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                //KundenID from hidden inputfield
                $KundenID = $_POST['selectedKundenID'];

                // Rechnungsdatum
                $Rechnungsdatum = $_POST['RechnungsDatum'];
                $RechnungsMonatJahr = $_POST['RechnungsMonatJahr'];


                // Storing the content from the LeistungEditor
                $Leistung = $_POST['leistungEditor'];
                $LeistungDB = implode(",", $Leistung);

                //Abrechnungsart
                $AbrechnungsartList = $_POST['AbrechnungsartList'];
                $AbrechnungsartStunden = $_POST['Stunden'];
                // Replace Stunden with the inputfield number
                for ($i = 0; $i < count($AbrechnungsartList); $i++) {
                    if ($AbrechnungsartList[$i] != "Pauschal") {
                        $AbrechnungsartList[$i] = $AbrechnungsartStunden[$i];
                    }
                }
                $AbrechnungsartListDB = implode(",", $AbrechnungsartList);

                //Nettopreis
                $nettoPreis = $_POST['nettoPreis'];

                //Calculation MwSt and Gesamtbetrag
                $MwSt_Percentage = 19;

                $MwStArray = [];
                $GesamtBetragArray = [];
                foreach ($nettoPreis as $nettoBetrag) {
                    $MwSt = round($nettoBetrag * ($MwSt_Percentage / 100), 2);
                    $GesamtBetrag = $nettoBetrag + $MwSt;
                    $MwStArray[] = $MwSt;
                    $GesamtBetragArray[] = $GesamtBetrag;
                }

                // Converting the nettoPreis, MwSt and Gesamtbetrag Array into a String with comma 
                $nettoPreisDB = implode(",", $nettoPreis);
                $MwStDB = implode(",", $MwStArray);
                $GesamtBetragDB = implode(",", $GesamtBetragArray);


                // Storing the value of the checkbox 
                $monatlicheRechnung = "0";
                if (isset($_POST['monatlicheRechnung'])) {
                    $monatlicheRechnung = "1";
                }

                // LeistungDB in Leistung speichern
                // AbrechnungsartListDB in Abrechnungsart
                // nettoPreisDB in NettoPreis
                // MwStDB in MwSt
                // GesamtBetragDB in GesamtBetrag
                // KundenID in KundenID
                // monatlicheRechnung in MonatlicheRechnungBool
                // Rechnungsdatum in RechnungsDatum
                // RechnungsMonatJahr in Monat_Jahr

                // RechnungsNummer suchen von tabelle Rechnungsnr. 
                // wenn kein eintrag => 1 
                // sonst => Nr + 1


                // Zuerst Rechnung Erstellen und dann anschließend werte in DB speichern
                // Grund: Wenn erstlelen von Rechnung fehlerhaft ist => speichern von Werten in DB nicht machen

                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;


            case 'edit':
                $messageType = "edit";
                $message = "Editieren Sie Ihre Daten";
                $modalHeadline = "Update Kontakt";
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                $KundenID = $_POST['KundenID'];
                $query = "SELECT * FROM Kunden WHERE KundenID = :KundenID";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':KundenID', $KundenID, PDO::PARAM_INT);
                $stmt->execute();

                $result = [];
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['organization'] == '1') {
                    $saveUpdate = "update";

                    $firmenName_organization = $result['FirmenName'];
                    $firmenAdresse_organization = $result['Adresse'];
                    $rechnungsKuerzel_organization = $result['RechnungsKürzel'];
                    $PLZ_organization = $result['PLZ'];
                    $Ort_organization = $result['Ort'];
                    $Vertragsdatum_organization = $result['VertragsDatum'];
                    $Ansprechpartner_organization = $result['Name_Ansprechpartner'];
                    $gender_organization = $result['Gender'];

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
                }
                $showMessage = "flex";

                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;

            case 'update':

                //values of the DB
                $KundenID = $_POST['kID'];

                include('../dbPhp/dbOpenConnection.php'); // dbConnection open

                $query = "SELECT * FROM Kunden WHERE KundenID = :KundenID";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':KundenID', $KundenID, PDO::PARAM_INT);
                $stmt->execute();

                $result = [];
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $firmenName_organization = $result['FirmenName'];
                $firmenAdresse_organization = $result['Adresse'];
                $rechnungsKuerzel_organization = $result['RechnungsKürzel'];
                $PLZ_organization = $result['PLZ'];
                $Ort_organization = $result['Ort'];
                $Vertragsdatum_organization = $result['VertragsDatum'];
                $Ansprechpartner_organization = $result['Name_Ansprechpartner'];
                $gender_organization = $result['Gender'];

                //Values of the inputfield
                $updated_firmenName_organization = $_POST['firmenName_organization'];
                $updated_firmenAdresse_organization = $_POST['firmenAdresse_organization'];
                $updated_rechnungsKuerzel_organization = $_POST['rechnungsKuerzel_organization'];
                $updated_PLZ_organization = $_POST['PLZ_organization'];
                $updated_Ort_organization = $_POST['Ort_organization'];
                $updated_Vertragsdatum_organization = $_POST['Vertragsdatum_organization'];
                $updated_Ansprechpartner_organization = $_POST['Ansprechpartner_organization'];
                $updated_gender_organization = $_POST['gender_organization'];

                //assigning null to the not required input fields if its empty, so the DB gets the value Null. 
                if ($updated_Vertragsdatum_organization == "") {
                    $updated_Vertragsdatum_organization = null;
                }
                if ($updated_Ansprechpartner_organization == "") {
                    $updated_Ansprechpartner_organization = null;
                }
                if ($updated_gender_organization != "Male" && $updated_gender_organization != "Female") {
                    $updated_gender_organization = null;
                }

                if (checkOrganizationDataChangedValues($firmenName_organization, $updated_firmenName_organization, $firmenAdresse_organization, $updated_firmenAdresse_organization, $rechnungsKuerzel_organization, $updated_rechnungsKuerzel_organization, $PLZ_organization, $updated_PLZ_organization, $Ort_organization, $updated_Ort_organization, $Vertragsdatum_organization, $updated_Vertragsdatum_organization, $Ansprechpartner_organization, $updated_Ansprechpartner_organization, $gender_organization, $updated_gender_organization)) {
                    //Atleast one of the inputfields changed. Now we are checking if the firmenname and rechnungskürzel didnt changed so we can instantly update the inputfields.
                    if ($firmenName_organization == $updated_firmenName_organization && $rechnungsKuerzel_organization == $updated_rechnungsKuerzel_organization) {
                        $messageType = "success";
                        $message = "Daten wurden erfolgreich bearbeitet!";
                        updateOrganizationDataIntoKundenTable($KundenID, $updated_firmenName_organization, $updated_firmenAdresse_organization, $updated_rechnungsKuerzel_organization, $updated_PLZ_organization, $updated_Ort_organization, $updated_Vertragsdatum_organization, $updated_Ansprechpartner_organization, $updated_gender_organization);
                    } //Now we check if both variables have changed. If this is the case, we check whether the data already exists in the DB
                    elseif ($firmenName_organization != $updated_firmenName_organization && $rechnungsKuerzel_organization != $updated_rechnungsKuerzel_organization) {
                        if (!checkIfValueExists('FirmenName', $updated_firmenName_organization) && !checkIfValueExists('RechnungsKürzel', $updated_rechnungsKuerzel_organization)) {
                            updateOrganizationDataIntoKundenTable($KundenID, $updated_firmenName_organization, $updated_firmenAdresse_organization, $updated_rechnungsKuerzel_organization, $updated_PLZ_organization, $updated_Ort_organization, $updated_Vertragsdatum_organization, $updated_Ansprechpartner_organization, $updated_gender_organization);
                            $messageType = "success";
                            $message = "Daten wurden erfolgreich bearbeitet!";
                        } //FirmenName and Rechnungskürzel both exist in DB
                        elseif (checkIfValueExists('FirmenName', $updated_firmenName_organization) && checkIfValueExists('RechnungsKürzel', $updated_rechnungsKuerzel_organization)) {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Firmenname ($updated_firmenName_organization) und Rechnungskürzel ($updated_rechnungsKuerzel_organization) existieren bereits in der Datenbank.";
                        } //FirmenName exist in DB
                        elseif (checkIfValueExists('FirmenName', $updated_firmenName_organization)) {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Firmenname ($updated_firmenName_organization) existiert bereits in der Datenbank.";
                        } //RechnungsKürzel exist in DB
                        elseif (checkIfValueExists('RechnungsKürzel', $updated_rechnungsKuerzel_organization)) {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Rechnungskürzel ($updated_rechnungsKuerzel_organization) exisitiert bereits in der Datenbank.";
                        }
                    } //check if firmenname only got changed
                    elseif ($firmenName_organization != $updated_firmenName_organization) {
                        if (!checkIfValueExists('FirmenName', $updated_firmenName_organization)) {
                            $messageType = "success";
                            $message = "Daten wurden erfolgreich bearbeitet!";
                            updateOrganizationDataIntoKundenTable($KundenID, $updated_firmenName_organization, $updated_firmenAdresse_organization, $updated_rechnungsKuerzel_organization, $updated_PLZ_organization, $updated_Ort_organization, $updated_Vertragsdatum_organization, $updated_Ansprechpartner_organization, $updated_gender_organization);
                        } else {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Firmenname ($updated_firmenName_organization) exisitiert bereits in der Datenbank.";
                        }
                    } //check if rechnungskürzel only got changed
                    elseif ($rechnungsKuerzel_organization != $updated_rechnungsKuerzel_organization) {
                        if (!checkIfValueExists('RechnungsKürzel', $updated_rechnungsKuerzel_organization)) {
                            $messageType = "success";
                            $message = "Daten wurden erfolgreich bearbeitet!";
                            updateOrganizationDataIntoKundenTable($KundenID, $updated_firmenName_organization, $updated_firmenAdresse_organization, $updated_rechnungsKuerzel_organization, $updated_PLZ_organization, $updated_Ort_organization, $updated_Vertragsdatum_organization, $updated_Ansprechpartner_organization, $updated_gender_organization);
                        } else {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Rechnungskürzel ($updated_rechnungsKuerzel_organization) existiert bereits in der Datenbank.";
                        }
                    }
                } else {
                    $messageType = "edit";
                    $message = "Daten wurden nicht geändert!";
                }

                if ($messageType == "errorUpdate") {
                    echo "<script>";
                    echo "var messageType = '$messageType';";
                    echo "</script>";
                }
                $showMessage = "flex";
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                break;

            case 'search':
                reset_vars();
                $contentSearchbar = '%' . $_POST['Search-Input'] . '%';

                //If any of the Filter Buttons are active/clicked
                if ($_POST['Firmenname_StateSearchButton'] == "true" || $_POST['Adresse_StateSearchButton'] == "true" || $_POST['RechnungsKürzel_StateSearchButton'] == "true" || $_POST['PLZ_StateSearchButton'] == "true" || $_POST['Ort_StateSearchButton'] == "true" || $_POST['Vertragsdatum_StateSearchButton'] == "true" || $_POST['Ansprechpartner_StateSearchButton'] == "true" || $_POST['Gender_StateSearchButton'] == "true") {
                    $sql_query = "SELECT * FROM `kunden` WHERE";

                    if ($_POST['Firmenname_StateSearchButton'] == "true") {
                        $sql_query .= " Firmenname LIKE :search_string OR";
                    }

                    if ($_POST['Adresse_StateSearchButton'] == "true") {
                        $sql_query .= " Adresse LIKE :search_string OR";
                    }

                    if ($_POST['RechnungsKürzel_StateSearchButton'] == "true") {
                        $sql_query .= " RechnungsKürzel LIKE :search_string OR";
                    }

                    if ($_POST['PLZ_StateSearchButton'] == "true") {
                        $sql_query .= " PLZ LIKE :search_string OR";
                    }

                    if ($_POST['Ort_StateSearchButton'] == "true") {
                        $sql_query .= " Ort LIKE :search_string OR";
                    }

                    if ($_POST['Vertragsdatum_StateSearchButton'] == "true") {
                        $sql_query .= " Vertragsdatum LIKE :search_string OR";
                    }

                    if ($_POST['Ansprechpartner_StateSearchButton'] == "true") {
                        $sql_query .= " Name_Ansprechpartner LIKE :search_string OR";
                    }

                    if ($_POST['Gender_StateSearchButton'] == "true") {
                        $sql_query .= " Gender LIKE :search_string OR";
                    }

                    // Delete the last "AND" of the Query
                    $sql_query = rtrim($sql_query, "OR");
                } else {
                    $sql_query = "SELECT * FROM `kunden` WHERE Firmenname LIKE :search_string OR Adresse LIKE :search_string OR RechnungsKürzel LIKE :search_string OR PLZ LIKE :search_string OR Ort LIKE :search_string OR Vertragsdatum LIKE :search_string OR Name_Ansprechpartner LIKE :search_string OR Gender LIKE :search_string";
                }
                $param = ['search_string' => $contentSearchbar];
                break;

            case 'delete':
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                $KundenID = $_POST['KundenID'];
                $sql = "DELETE FROM kunden WHERE KundenID=:KundenID";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['KundenID' => $KundenID]);

                if ($stmt->rowCount() > 0) {
                    $message = $stmt->rowCount() . " Datensatz gelöscht!";
                    $messageType = "success";
                } else {
                    $message = "Datensatz wurde nicht gelöscht!";
                    $messageType = "errorDelete";
                }
                $showMessage = "flex";
                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;
        }
        $_SESSION['sql_query'] = $sql_query;
        $_SESSION['param'] = $param;
    }
}
include('../dbPhp/dbOpenConnection.php'); // dbConnection open
$stmt = $conn->prepare($sql_query);
$stmt->execute($param);
$result = $stmt->fetchAll();

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
        $stmt = $conn->prepare("INSERT INTO kunden (FirmenName, Adresse, RechnungsKürzel, PLZ, Ort, VertragsDatum, Name_Ansprechpartner, Gender, organization)
                                   VALUES (:firmenName, :Adresse, :rechnungsKuerzel, :plz, :ort, :vertragsDatum, :ansprechpartner, :gender, 1)");

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

        // header("Refresh:0");
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }

    include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
}

function updateOrganizationDataIntoKundenTable($id, $firmenName, $Adresse, $rechnungsKuerzel, $PLZ, $Ort, $Vertragsdatum, $Ansprechpartner, $gender)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection öffnen

    try {
        // Prepare the UPDATE-Abfrage
        $stmt = $conn->prepare("UPDATE kunden SET FirmenName = :firmenName, Adresse = :Adresse, RechnungsKürzel = :rechnungsKuerzel, PLZ = :plz, Ort = :ort, VertragsDatum = :vertragsDatum, Name_Ansprechpartner = :ansprechpartner, Gender = :gender WHERE KundenID = :id");

        // Bind the variables on parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':firmenName', $firmenName);
        $stmt->bindParam(':Adresse', $Adresse);
        $stmt->bindParam(':rechnungsKuerzel', $rechnungsKuerzel);
        $stmt->bindParam(':plz', $PLZ);
        $stmt->bindParam(':ort', $Ort);
        $stmt->bindParam(':vertragsDatum', $Vertragsdatum);
        $stmt->bindParam(':ansprechpartner', $Ansprechpartner);
        $stmt->bindParam(':gender', $gender);

        $stmt->execute();
    } catch (PDOException $e) {
        echo "Fehler: " . $e->getMessage();
    }

    include('../dbPhp/dbCloseConnection.php'); // dbConnection schließen
}

function checkOrganizationDataChangedValues($firmenName_organization, $updated_firmenName_organization, $firmenAdresse_organization, $updated_firmenAdresse_organization, $rechnungsKuerzel_organization, $updated_rechnungsKuerzel_organization, $PLZ_organization, $updated_PLZ_organization, $Ort_organization, $updated_Ort_organization, $Vertragsdatum_organization, $updated_Vertragsdatum_organization, $Ansprechpartner_organization, $updated_Ansprechpartner_organization, $gender_organization, $updated_gender_organization)
{
    if (notEqualString($firmenName_organization, $updated_firmenName_organization) || notEqualString($firmenAdresse_organization, $updated_firmenAdresse_organization) || notEqualString($rechnungsKuerzel_organization, $updated_rechnungsKuerzel_organization) || notEqualString($PLZ_organization, $updated_PLZ_organization) || notEqualString($Ort_organization, $updated_Ort_organization) || notEqualString($Vertragsdatum_organization, $updated_Vertragsdatum_organization) || notEqualString($Ansprechpartner_organization, $updated_Ansprechpartner_organization) || notEqualString($gender_organization, $updated_gender_organization)) {
        return true;
    } else {
        return false;
    }
}

function notEqualString($string0, $string1)
{
    if ($string0 == $string1) {
        return false;
    } else {
        return true;
    }
}

function setSessionVariableFalse($session)
{
    if ($_SESSION[$session] != "false" && $_SESSION[$session] != "true") {
        $_SESSION[$session] = "false";
        $restart = true;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechnung</title>

    <!--Link to Kontakt.css | Stylesheet-->
    <link rel="stylesheet" href="./invoice.css">
    <!--Link to Material Icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <!-- <link rel="stylesheet" type="text/css" href="../ckeditor/sample/styles.css"> -->
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="WebsiteHeadline">Rechnung</h1>
            <div class="header-search">

                <div class="search-container">
                    <form method="POST" class="search-form">
                        <div class="search">
                            <button type="submit" name="button" value="search" class="Search-Btn" id="searchButton"><span class="material-icons-sharp">search</span></button>
                            <input type="search" id="search" name="Search-Input" class="Search-Input" placeholder="Search..." autocomplete="off">
                        </div>
                        <div class="buttons-container">
                            <div class="search-buttons">
                                <button type="submit" name="button" value="Search_FirmenName" onclick="changeBackground(this)">FirmenName</button>
                                <button type="submit" name="button" value="Search_Adresse" onclick="changeBackground(this)">Adresse</button>
                                <button type="submit" name="button" value="Search_RechnungsKürzel" onclick="changeBackground(this)">RechnungsKürzel</button>
                                <button type="submit" name="button" value="Search_PLZ" onclick="changeBackground(this)">PLZ</button>
                                <button type="submit" name="button" value="Search_Ort" onclick="changeBackground(this)">Ort</button>
                                <button type="submit" name="button" value="Search_Vertragsdatum" onclick="changeBackground(this)">Vertragsdatum</button>
                                <button type="submit" name="button" value="Search_Ansprechpartner" onclick="changeBackground(this)">Ansprechpartner</button>
                                <button type="submit" name="button" value="Search_Gender" onclick="changeBackground(this)">Gender</button>
                            </div>
                        </div>
                        <input type="hidden" name="Firmenname_StateSearchButton" value="<?php echo $Firmenname_StateSearchButton; ?>">
                        <input type="hidden" name="Adresse_StateSearchButton" value="<?php echo $Adresse_StateSearchButton; ?>">
                        <input type="hidden" name="RechnungsKürzel_StateSearchButton" value="<?php echo $RechnungsKürzel_StateSearchButton; ?>">
                        <input type="hidden" name="PLZ_StateSearchButton" value="<?php echo $PLZ_StateSearchButton; ?>">
                        <input type="hidden" name="Ort_StateSearchButton" value="<?php echo $Ort_StateSearchButton; ?>">
                        <input type="hidden" name="Vertragsdatum_StateSearchButton" value="<?php echo $Vertragsdatum_StateSearchButton; ?>">
                        <input type="hidden" name="Ansprechpartner_StateSearchButton" value="<?php echo $Ansprechpartner_StateSearchButton; ?>">
                        <input type="hidden" name="Gender_StateSearchButton" value="<?php echo $Gender_StateSearchButton; ?>">

                    </form>
                </div>
            </div>
        </div>


        <!--Create New Invoice with Button to open Modal-->
        <div class="createInvoices">

            <div class="message <?php echo $messageType; ?>" id="message" style="display: <?php echo $showMessage; ?>">
                <h2 id="messageText"><?php echo $message; ?></h2>
                <span class="material-icons-sharp">close</span>
            </div>

            <!-- Trigger/Open The Modal -->
            <button type="button" id="CreateInvoiceModal" class="createInvoice-Btn">Rechnung erstellen</button>

            <div class="modal">
                <!-- Modal content -->
                <div class="modal-header">
                    <h2><?php echo $modalHeadline; ?></h2>
                    <span class="material-icons-sharp">close</span>
                </div>

                <div class="form-container">

                    <form method="POST" id="form-modal">

                        <!-- DropDown List for Customers -->
                        <div class="kundenListe">
                            <label for="customerList">Wähle einen Kunden:</label>
                            <select name="customerList" id="customerList" required>
                                <option value="" class="firstSelectedOption">Bitte auswählen</option>
                                <?php
                                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                                $sql_customer = "SELECT KundenID, FirmenName, Name_Ansprechpartner, RechnungsKürzel, Adresse, PLZ, Ort, person, organization FROM kunden ORDER BY organization DESC, CASE WHEN organization = 1 THEN FirmenName ELSE Name_Ansprechpartner END;";
                                $stmt = $conn->prepare($sql_customer);
                                $stmt->execute();
                                $customer = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($customer as $row) {

                                    if ($row['organization'] == 1) {
                                        $name = htmlspecialchars($row['FirmenName']);
                                        echo '<option value="' . htmlspecialchars($row['KundenID']) . '" data-rechnungskuerzel="' . htmlspecialchars($row['RechnungsKürzel']) . '" data-adresse="' . htmlspecialchars($row['Adresse']) . '" data-plz="' . htmlspecialchars($row['PLZ']) . '" data-ort="' . htmlspecialchars($row['Ort']) . '">' . $name . '</option>';
                                    } elseif ($row['person'] == 1) {
                                        $name = htmlspecialchars($row['Name_Ansprechpartner']);
                                        echo '<option value="' . htmlspecialchars($row['KundenID']) . '" data-rechnungskuerzel="' . htmlspecialchars($row['RechnungsKürzel']) . '" data-adresse="' . htmlspecialchars($row['Adresse']) . '" data-plz="' . htmlspecialchars($row['PLZ']) . '" data-ort="' . htmlspecialchars($row['Ort']) . '">' . $name . '</option>';
                                    }
                                }

                                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                                ?>

                            </select>

                            <div class="customer-details" id="customer-details">
                                <div class="rechnungskuerzel">
                                    <span id="rechnungskuerzel"></span>
                                    <label> (Rechnungskürzel)</label>
                                </div>
                                <span id="adresse"></span>

                                <div class="plz-ort">
                                    <span id="plz"></span>
                                    <span id="ort"></span>
                                </div>
                            </div>
                        </div>

                        <div class="datum">
                            <label for="RechnungsDatum">Wähle das Rechnungsdatum sowie den Monat und das Jahr für die Rechnung aus:</label>
                            <div class="RechnungsDatum">
                                <input type="date" name="RechnungsDatum" id="RechnungsDatum" required>
                                <input type="month" name="RechnungsMonatJahr" id="RechnungsMonatJahr" required>
                            </div>
                        </div>

                        <!-- ckEditor 5 CustomBuild -->
                        <div class="dienstleistungs-details">
                            <table>
                                <thead>
                                    <th>Leistung und ggf. Leistungsstraße:</th>
                                    <th>Wähle die Abrechnungsart aus:</th>
                                    <th>NettoPreis:</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="leistung">
                                                <textarea class="leistungEditor" id="leistungEditor" name="leistungEditor[]"></textarea>
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
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="add-row">
                                            <label onclick="addDienstleistungsRow()">+ Hinzufügen weitere Leistung</label>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="monatlicheRechnung">
                            <input type="checkbox" name="monatlicheRechnung" id="monatlicheRechnung">
                            <label for="monatlicheRechnung">Monatliche Rechnung</label>
                        </div>

                        <!-- Store KundenID in hidden Inputfield to get access in update Switch Case-->
                        <input type="hidden" name="selectedKundenID" id="selectedKundenID" value="">

                        <button type="submit" name="button" onclick="updateFormActionTarget(event)" value="<?php echo $saveUpdate; ?>" class="sendNewInvoiceData-Btn" id="<?php echo $saveUpdate ?>"><?php if ($saveUpdate == "save") {
                                                                                                                                                                                                            echo "Senden";
                                                                                                                                                                                                        } elseif ($saveUpdate == "update") {
                                                                                                                                                                                                            echo "Update";
                                                                                                                                                                                                        } ?></button>
                    </form>
                </div>
            </div>
        </div>


        <!-- End of Create Contacts with Modal -->

        <!-- Beginning of the Crud Table -->
        <div class="crud">
            <div class="crud-table">
                <table class="table">
                    <thead>
                        <th>Firmenname</th>
                        <th>Adresse</th>
                        <th>Rechnungskürzel</th>
                        <th>PLZ</th>
                        <th>Ort</th>
                        <th>Vertragsdatum</th>
                        <th>Ansprechpartner</th>
                        <th>Gender</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <!-- PHP to load all rows of the contacts -->
                        <?php

                        foreach ($result as $row) {
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['FirmenName']); ?></td>
                                <td><?php echo htmlspecialchars($row['Adresse']); ?></td>
                                <td><?php echo htmlspecialchars($row['RechnungsKürzel']); ?></td>
                                <td><?php echo htmlspecialchars($row['PLZ']); ?></td>
                                <td><?php echo htmlspecialchars($row['Ort']); ?></td>
                                <td><?php echo htmlspecialchars($row['VertragsDatum']); ?></td>
                                <td><?php echo htmlspecialchars($row['Name_Ansprechpartner']); ?></td>
                                <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="KundenID" value=<?php echo htmlspecialchars($row['KundenID']); ?>>
                                        <button type="submit" class="CrudEdit" name="button" value="edit">Edit</button>
                                        <button type="submit" class="CrudDelete" name="button" value="delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }

                        include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                        ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./index.js"></script>
    <!-- Rich Text Editor ckEditor 5 CustomBuild -->
    <script src="../ckeditor/build/ckeditor.js"></script>
    <script>
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

                //Check if ckEditor 5 has an empty input so we can simulate the required
                document.getElementById('form-modal').addEventListener('submit', function(event) {
                    const editorData = editor.getData();
                    const messageDiv = document.getElementById('message');
                    const messageText = document.getElementById('messageText');

                    if (editorData.trim() === '' || editorData == '') {
                        event.preventDefault();
                        messageDiv.style.display = 'flex';
                        messageText.innerText = 'Leere Eingabe für die Leistung';
                        // Error Message Style
                        messageDiv.classList.add('error');
                    }
                });

            })
            .catch(error => {
                console.error(error);
            });
    </script>

    <!-- Adding onclick for the tfoot label -->
    <script>
        let editorCount = 0; // Countvariable for the created editor
        const editorArray = []; // Array to store editor instances/objects

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
                })
                .catch(error => {
                    console.error(error);
                });
        }

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
        document.getElementById('form-modal').addEventListener('submit', function(event) {
            const rows = document.querySelectorAll('.dienstleistungs-details tbody tr');

            for (const row of rows) {
                const editorIsActive = row.getAttribute('data-editor-active');
                const editorIndex = row.getAttribute('data-editor-index');

                const editor = editorArray[editorIndex];

                // Check if the editor is active (not deleted)
                if (editorIsActive === 'true') {
                    const editorData = editor.getData();
                    const messageDiv = document.getElementById('message');
                    const messageText = document.getElementById('messageText');

                    if (editorData.trim() === '' || editorData == '') {
                        event.preventDefault();
                        messageDiv.style.display = 'flex';
                        messageText.innerText = 'Leere Eingabe für die Leistung';
                        // Error Message Style
                        messageDiv.classList.add('error');
                        return; // Stop checking other rows once one empty editor is found
                    }
                }
            }
        });
    </script>

    <script>
        function updateFormActionTarget(event) {

            // check if the btn value is save
            const submitButton = event.target;
            if (submitButton.value === 'save') {
                const form = document.getElementById('form-modal');

                // form action and target is added; the values from the form are given to the new windowtab invoiceMuster.php
                form.action = '/projekt/website_vereinfacht/Invoice/Muster/generate-pdf.php';
                form.target = '_blank';

                allCkEditorFilled = true;
                if (firstEditor[0].getData().trim() == '') {
                    allCkEditorFilled = false;
                }

                for ($i = 0; $i < editorArray.length; i++) {
                    if (editorArray[$i].getData().trim() == '') {
                        allCkEditorFilled = false;
                        break;
                    }
                }

                if (allCkEditorFilled) {
                    location.reload();
                }
            }
        }
    </script>
</body>

</html>