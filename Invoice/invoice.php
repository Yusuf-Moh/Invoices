<?php
// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../loginSystem/checkLogin.php";

global $search_Color;
$search_Color = "black";
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

    global $sql_query_invoice;

    global $param_invoice;


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

    $search_Color = "black";
}

global $KundenInformationen_StateSearchButton, $Leistung_StateSearchButton, $Abrechnungsart_StateSearchButton, $NettoPreis_StateSearchButton, $GesamtBetrag_StateSearchButton, $RechnungsDatum_StateSearchButton, $Monat_Jahr_StateSearchButton, $RechnungsKürzelNummer_StateSearchButton, $MonatlicheRechnung_StateSearchButton, $Bezahlt_StateSearchButton, $UeberweisungsDatum_StateSearchButton;

setSessionVariableFalse('KundenInformationen_StateSearchButton');
setSessionVariableFalse('Leistung_StateSearchButton');
setSessionVariableFalse('Abrechnungsart_StateSearchButton');
setSessionVariableFalse('NettoPreis_StateSearchButton');
setSessionVariableFalse('GesamtBetrag_StateSearchButton');
setSessionVariableFalse('RechnungsDatum_StateSearchButton');
setSessionVariableFalse('Monat_Jahr_StateSearchButton');
setSessionVariableFalse('RechnungsKürzelNummer_StateSearchButton');
setSessionVariableFalse('MonatlicheRechnung_StateSearchButton');
setSessionVariableFalse('Bezahlt_StateSearchButton');
setSessionVariableFalse('UeberweisungsDatum_StateSearchButton');

global $restart;
$restart = false;


if (!isset($_SESSION['sql_query_invoice'])) {
    $_SESSION['sql_query_invoice'] = "SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner FROM Rechnung r JOIN Kunden k ON r.KundenID = k.KundenID ORDER BY CASE WHEN Bezahlt = 0 THEN 0 ELSE 1 END, STR_TO_DATE(Rechnungsdatum, '%d.%m.%Y') DESC;";
    $restart = true;
}

if (!isset($_SESSION['param_invoice'])) {
    $_SESSION['param_invoice'] = [];
    $restart = true;
}

if (isset($_SESSION['search_Color_Invoice'])) {
    $search_Color_Invoice = $_SESSION['search_Color_Invoice'];
} else {
    $_SESSION['search_Color_Invoice'] = 'black';
    $restart = true;
}


if ($restart) {
    header("Refresh:0");
}

$KundenInformationen_StateSearchButton = $_SESSION['KundenInformationen_StateSearchButton'];
$Leistung_StateSearchButton = $_SESSION['Leistung_StateSearchButton'];
$Abrechnungsart_StateSearchButton = $_SESSION['Abrechnungsart_StateSearchButton'];
$NettoPreis_StateSearchButton = $_SESSION['NettoPreis_StateSearchButton'];
$GesamtBetrag_StateSearchButton = $_SESSION['GesamtBetrag_StateSearchButton'];
$RechnungsDatum_StateSearchButton = $_SESSION['RechnungsDatum_StateSearchButton'];
$Monat_Jahr_StateSearchButton = $_SESSION['Monat_Jahr_StateSearchButton'];
$RechnungsKürzelNummer_StateSearchButton = $_SESSION['RechnungsKürzelNummer_StateSearchButton'];
$MonatlicheRechnung_StateSearchButton = $_SESSION['MonatlicheRechnung_StateSearchButton'];
$Bezahlt_StateSearchButton = $_SESSION['Bezahlt_StateSearchButton'];
$UeberweisungsDatum_StateSearchButton = $_SESSION['UeberweisungsDatum_StateSearchButton'];

$sql_query_invoice = $_SESSION['sql_query_invoice'];
$param_invoice = $_SESSION['param_invoice'];

//reset of every variables.
reset_vars();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['button'])) {
        $action = $_POST['button'];
        switch ($action) {
            case 'save':
                // Because of the script, when the send button is pressed, the form sends on to generate-pdf.php which creates a pdf file based on the input from the form
                // the switch case is not necessary because it doesnt get executed (Reason: form action = "gernerate-pdf.php")
                break;

            case 'edit':
                $messageType = "edit";
                $message = "Editieren Sie Ihre Daten";
                $modalHeadline = "Update Rechnung";

                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                // RechnungsID from the Crud row (Form in the edit/delete btns)
                $RechnungsID = $_POST['RechnungsID'];
                $query = "SELECT * FROM Rechnung WHERE RechnungsID = :RechnungsID";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':RechnungsID', $RechnungsID, PDO::PARAM_INT);
                $stmt->execute();

                $result = [];
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $Leistung_edit = unserialize($result['Leistung']);
                $Abrechnungsart_edit = unserialize($result['Abrechnungsart']);
                $NettoPreis_edit = unserialize($result['NettoPreis']);
                $KundenID_edit = $result['KundenID'];
                $MonatlicheRechnungBool_edit = $result['MonatlicheRechnungBool'];
                $RechnungsDatum_edit = $result['RechnungsDatum'];
                $Monat_Jahr_edit = $result['Monat_Jahr'];
                $RechnungsNummer_edit = $result['RechnungsNummer'];
                $RechnungsKürzelNummer_edit = $result['RechnungsKürzelNummer'];
                $MwSt_edit = $result['MwSt'];
                $GesamtBetrag_edit = $result['GesamtBetrag'];
                $Bezahlt_edit = $result['Bezahlt'];

                // Storing the data from the selected Rechnung into the inputfields of the modal by transfering the values from php to js

                // Create a array with all values and transfer it to javascript
                $data = array(
                    'Leistung_edit' => $Leistung_edit,
                    'Abrechnungsart_edit' => $Abrechnungsart_edit,
                    'NettoPreis_edit' => $NettoPreis_edit,
                    'KundenID_edit' => $KundenID_edit,
                    'MonatlicheRechnungBool_edit' => $MonatlicheRechnungBool_edit,
                    'RechnungsDatum_edit' => $RechnungsDatum_edit,
                    'Monat_Jahr_edit' => $Monat_Jahr_edit,
                    'RechnungsNummer_edit' => $RechnungsNummer_edit,
                    'RechnungsKürzelNummer_edit' => $RechnungsKürzelNummer_edit,
                    'MwSt_edit' => $MwSt_edit,
                    'GesamtBetrag_edit' => $GesamtBetrag_edit,
                    'Bezahlt_edit' => $Bezahlt_edit,
                );

                // Convert the array to a JSON string safely
                $jsonEditData = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

                echo "<script>";
                // Echo the JavaScript code with the JSON data
                echo "var jsonEditData = $jsonEditData;";
                echo "var messageType = '$messageType';";
                echo "</script>";

                $saveUpdate = "update";
                $showMessage = "flex";


                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;

            case 'update':
                header("Refresh:0");
                break;
            case 'Search_KundenInformationen':
                $_SESSION['KundenInformationen_StateSearchButton'] = stateSearchButton($KundenInformationen_StateSearchButton);
                $KundenInformationen_StateSearchButton = $_SESSION['KundenInformationen_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_Leistung':
                $_SESSION['Leistung_StateSearchButton'] = stateSearchButton($Leistung_StateSearchButton);
                $Leistung_StateSearchButton = $_SESSION['Leistung_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_Abrechnungsart':
                $_SESSION['Abrechnungsart_StateSearchButton'] = stateSearchButton($Abrechnungsart_StateSearchButton);
                $Abrechnungsart_StateSearchButton = $_SESSION['Abrechnungsart_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_NettoPreis':
                $_SESSION['NettoPreis_StateSearchButton'] = stateSearchButton($NettoPreis_StateSearchButton);
                $NettoPreis_StateSearchButton = $_SESSION['NettoPreis_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_GesamtBetrag':
                $_SESSION['GesamtBetrag_StateSearchButton'] = stateSearchButton($GesamtBetrag_StateSearchButton);
                $GesamtBetrag_StateSearchButton = $_SESSION['GesamtBetrag_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_RechnungsDatum':
                $_SESSION['RechnungsDatum_StateSearchButton'] = stateSearchButton($RechnungsDatum_StateSearchButton);
                $RechnungsDatum_StateSearchButton = $_SESSION['RechnungsDatum_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_Monat_Jahr':
                $_SESSION['Monat_Jahr_StateSearchButton'] = stateSearchButton($Monat_Jahr_StateSearchButton);
                $Monat_Jahr_StateSearchButton = $_SESSION['Monat_Jahr_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_RechnungsKürzelNummer':
                $_SESSION['RechnungsKürzelNummer_StateSearchButton'] = stateSearchButton($RechnungsKürzelNummer_StateSearchButton);
                $RechnungsKürzelNummer_StateSearchButton = $_SESSION['RechnungsKürzelNummer_StateSearchButton'];
                resetSearch();
                break;

            case 'Search_MonatlicheRechnung':
                $_SESSION['MonatlicheRechnung_StateSearchButton'] = stateSearchButton($MonatlicheRechnung_StateSearchButton);
                $MonatlicheRechnung_StateSearchButton = $_SESSION['MonatlicheRechnung_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Bezahlt':
                $_SESSION['Bezahlt_StateSearchButton'] = stateSearchButton($Bezahlt_StateSearchButton);
                $Bezahlt_StateSearchButton = $_SESSION['Bezahlt_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_UeberweisungsDatum':
                $_SESSION['UeberweisungsDatum_StateSearchButton'] = stateSearchButton($UeberweisungsDatum_StateSearchButton);
                $UeberweisungsDatum_StateSearchButton = $_SESSION['UeberweisungsDatum_StateSearchButton'];
                resetSearch();
                break;
            case 'search':
                reset_vars();
                $contentSearchbar = '%' . $_POST['Search-Input'] . '%';
                $SelectEveryData = false;

                if ($_SESSION['KundenInformationen_StateSearchButton'] || $_SESSION['Leistung_StateSearchButton'] || $_SESSION['Abrechnungsart_StateSearchButton'] || $_SESSION['NettoPreis_StateSearchButton'] || $_SESSION['GesamtBetrag_StateSearchButton'] || $_SESSION['RechnungsDatum_StateSearchButton'] || $_SESSION['Monat_Jahr_StateSearchButton'] || $_SESSION['RechnungsKürzelNummer_StateSearchButton'] || $_SESSION['MonatlicheRechnung_StateSearchButton'] || $_SESSION['Bezahlt_StateSearchButton'] || $_SESSION['UeberweisungsDatum_StateSearchButton']) {

                    // Check for a empty Input so we can select every record from the database
                    if ($_POST['Search-Input'] == "") {
                        $sql_query_invoice = "SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner 
                        FROM Rechnung r 
                        JOIN Kunden k ON r.KundenID = k.KundenID";
                        $sql_query_invoice .= " ORDER BY CASE WHEN Bezahlt = 0 THEN 0 ELSE 1 END, STR_TO_DATE(Rechnungsdatum, '%d.%m.%Y') DESC;";

                        $SelectEveryData = true;
                    } else {
                        $sql_query_invoice = 'SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner 
                                            FROM Rechnung r 
                                            JOIN Kunden k ON r.KundenID = k.KundenID WHERE';

                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['KundenInformationen_StateSearchButton'], " FirmenName LIKE :search_string OR Adresse LIKE :search_string OR PLZ LIKE :search_string OR ORT LIKE :search_string OR Name_Ansprechpartner LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['Leistung_StateSearchButton'], " Leistung LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['Abrechnungsart_StateSearchButton'], " Abrechnungsart LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['NettoPreis_StateSearchButton'], " NettoPreis LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['GesamtBetrag_StateSearchButton'], " GesamtBetrag LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['RechnungsDatum_StateSearchButton'], " RechnungsDatum LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['Monat_Jahr_StateSearchButton'], " Monat_Jahr LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['RechnungsKürzelNummer_StateSearchButton'], " RechnungsKürzelNummer LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['MonatlicheRechnung_StateSearchButton'], " MonatlicheRechnungBool LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['Bezahlt_StateSearchButton'], " Bezahlt LIKE :search_string OR");
                        $sql_query_invoice .= updateSearchQueryStateSearchButtons($_SESSION['UeberweisungsDatum_StateSearchButton'], " UeberweisungsDatum LIKE :search_string OR");

                        // Delete the last "OR" of the Query
                        $sql_query_invoice = rtrim($sql_query_invoice, "OR");
                        $sql_query_invoice .= " ORDER BY CASE WHEN Bezahlt = 0 THEN 0 ELSE 1 END, STR_TO_DATE(Rechnungsdatum, '%d.%m.%Y') DESC;";
                    }
                } else {
                    $sql_query_invoice = "SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner 
                                        FROM Rechnung r 
                                        JOIN Kunden k ON r.KundenID = k.KundenID
                                        WHERE r.Leistung LIKE :search_string 
                                            OR r.Abrechnungsart LIKE :search_string 
                                            OR r.NettoPreis LIKE :search_string 
                                            OR r.GesamtBetrag LIKE :search_string 
                                            OR r.RechnungsDatum LIKE :search_string 
                                            OR r.Monat_Jahr LIKE :search_string 
                                            OR r.RechnungsKürzelNummer LIKE :search_string 
                                            OR r.MonatlicheRechnungBool LIKE :search_string
                                            OR k.FirmenName LIKE :search_string 
                                            OR Adresse LIKE :search_string 
                                            OR PLZ LIKE :search_string 
                                            OR ORT LIKE :search_string 
                                            OR Name_Ansprechpartner LIKE :search_string
                                            OR Bezahlt LIKE :search_string
                                            OR UeberweisungsDatum LIKE :search_string
                                            ORDER BY CASE WHEN Bezahlt = 0 THEN 0 ELSE 1 END, STR_TO_DATE(Rechnungsdatum, '%d.%m.%Y') DESC;";
                }

                if ($_POST['Search-Input'] != "") {
                    $search_Color_Invoice = "#F62217";
                } else {
                    $search_Color_Invoice = 'black';
                }

                $param_invoice = ['search_string' => $contentSearchbar];

                // If searchInput is empty and the bool SelectEveryData is true, the param_invoice should be a empty array because the query is selecting every record of the Table Invoice
                if ($_POST['Search-Input'] == "" && $SelectEveryData) {
                    $param_invoice = [];
                }
                break;

            case 'delete':
                $RechnungsID = $_POST['RechnungsID'];
                $numberDeletedRows = deleteRechnung($RechnungsID);
                if ($numberDeletedRows > 0) {
                    $message = $numberDeletedRows . " Datensatz gelöscht!";
                    $messageType = "success";
                } else {
                    $message = "Datensatz wurde nicht gelöscht!";
                    $messageType = "errorDelete";
                }
                $showMessage = "flex";
                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;
            case 'bezahlt':
                $RechnungsID_Bezahlt = $_POST['RechnungsID_Bezahlt'];
                $Ueberweisungsdatum = $_POST['Ueberweisungsdatum'];
                $bezahlt_unbezahlt_checkbox = $_POST['bezahlt_unbezahlt_checkbox'];
                $bezahlt = true;
                // Wenn Submited wird, müssen wir nur den wert in die datenbank updaten!
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                $sql = "UPDATE rechnung SET Bezahlt = :bezahlt, UeberweisungsDatum = :ueberweisungsDatum WHERE RechnungsID = :RechnungsID;";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':bezahlt', $bezahlt);
                $stmt->bindParam(':ueberweisungsDatum', $Ueberweisungsdatum);
                $stmt->bindParam(':RechnungsID', $RechnungsID_Bezahlt);

                $stmt->execute();

                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;
        }
        $_SESSION['sql_query_invoice'] = $sql_query_invoice;
        $_SESSION['param_invoice'] = $param_invoice;
        $_SESSION['search_Color_Invoice'] = $search_Color_Invoice;
    }
}
include('../dbPhp/dbOpenConnection.php'); // dbConnection open
$stmt = $conn->prepare($sql_query_invoice);
$stmt->execute($param_invoice);
$result = $stmt->fetchAll();


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
    if (!isset($_SESSION[$session])) {
        $_SESSION[$session] = 0;
        global $restart;
        $restart = true;
    }
}

function KundenInformationen($FirmenName, $Ansprechpartner, $Adresse, $PLZ, $Ort)
{
    $KontaktInformationen = displayStringBR($FirmenName) . displayStringBR($Ansprechpartner) . displayStringBR($Adresse) . $PLZ . " " . $Ort;

    echo $KontaktInformationen;
}

function displayStringBR($String)
{
    if (!empty($String)) {
        return $String . "<br>";
    }
}

//Arrays stored in database getting unserialize for the crud-table
function parseSerializedData($serializedData)
{
    $dataArray = unserialize($serializedData);
    $lengthArray = count($dataArray);

    foreach ($dataArray as $index => $data) {
        echo '<p>' . nl2br($data) . '</p>';

        // Add the <br> tag for all Array elements except the last one
        if ($index != $lengthArray - 1) {
            echo "<br>";
        }
    }
}

function parseSerializedDataLeistung($serializedData)
{
    $dataArray = unserialize($serializedData);
    $lengthArray = count($dataArray);

    foreach ($dataArray as $index => $data) {
        echo nl2br($data);

        // Add the <br> tag for all Array elements except the last one
        if ($index != $lengthArray - 1) {
            echo "<br>";
        }
    }
}

// Function to copy the data from the given invoice and deleting it from the database rechnung 
function deleteRechnung($rechnungsID)
{
    deleteFile($rechnungsID);

    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

    $query = "INSERT INTO deletedrechnung (Leistung, Abrechnungsart, NettoPreis, KundenID, MonatlicheRechnungBool, RechnungsDatum, Monat_Jahr, RechnungsNummer, RechnungsKürzelNummer, RechnungsID, MwSt, GesamtBetrag, Pfad, Bezahlt, UeberweisungsDatum, Zeitpunkt_Erstellung)
    SELECT Leistung, Abrechnungsart, NettoPreis, KundenID, MonatlicheRechnungBool, RechnungsDatum, Monat_Jahr, RechnungsNummer, RechnungsKürzelNummer, RechnungsID, MwSt, GesamtBetrag, Pfad, Bezahlt, UeberweisungsDatum, Zeitpunkt_Erstellung
    FROM rechnung
    WHERE RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();

    // If there is a record with the given RechnungsID in the Database Table monatliche_rechnungen, then it should be deleted aswell
    $query = "DELETE FROM monatliche_rechnung WHERE RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();

    $query = "DELETE FROM rechnung WHERE RechnungsID=:RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();

    $numberDeletedRows = $stmt->rowCount();
    include('../dbPhp/dbCLoseConnection.php'); // dbConnection close

    return $numberDeletedRows;
}

function stateSearchButton($currentState)
{
    if ($currentState) {
        return 0;
    } else {
        return 1;
    }
}

function deleteFile($rechnungsID)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

    $query = "SELECT Pfad FROM rechnung where RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pfad = $result['Pfad'];

    if (file_exists($pfad)) {
        unlink($pfad);
    }

    include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
}

// if the Invoice is paid, the checkbox should be checked
function setCheckboxBezahltChecked($bezahlt_bool)
{
    if ($bezahlt_bool == "1") {
        echo "checked disabled";
    }
}

// If there is a Datum, it should be inserted into the Inputfield
function setUeberweisungsdatum($ueberweisungsdatum)
{
    if ($ueberweisungsdatum != null) {
        echo $ueberweisungsdatum;
    }
}

// If the Invoice is paid, the button should be disabled.
function disableButton($bezahlt_bool)
{
    if ($bezahlt_bool == 1) {
        echo 'disabled';
    }
}
// If the invoice is not paid, the background should be red to indicate it instantly
function unpaidInvoiceBackground($bezahlt_bool)
{
    if ($bezahlt_bool == "0") {
        echo "style = 'background: indianred;'";
    }
}

function updateSearchQueryStateSearchButtons($bool, $query)
{
    if ($bool) {
        return $query;
    } else {
        return "";
    }
}

function resetSearch()
{
    global $sql_query_invoice, $param_invoice, $search_Color_Invoice;
    $sql_query_invoice = "SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner FROM Rechnung r JOIN Kunden k ON r.KundenID = k.KundenID";
    $sql_query_invoice .= " ORDER BY CASE WHEN Bezahlt = 0 THEN 0 ELSE 1 END, STR_TO_DATE(Rechnungsdatum, '%d.%m.%Y') DESC;";
    $param_invoice = [];
    $search_Color_Invoice = 'black';
    header("Refresh:0");
}

function changeBackgroundSearchButton($bool)
{
    if ($bool) {
        echo "clicked";
    } else {
        echo "";
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
</head>

<body>
    <header> <?php include '../NavBar/navbar.html'; ?></header>
    <div class="container">

        <div class="header">
            <!-- <h1 class="WebsiteHeadline">Rechnung</h1> -->
            <div class="header-search">

                <div class="search-container">
                    <form method="POST" class="search-form">
                        <div class="search">
                            <button type="submit" name="button" value="search" class="Search-Btn" id="searchButton"><span class="material-icons-sharp" style="color: <?php echo $search_Color_Invoice; ?>">search</span></button>
                            <input type="search" id="search" name="Search-Input" class="Search-Input" placeholder="Search..." autocomplete="off">
                        </div>
                        <div class="buttons-container">
                            <div class="search-buttons">
                                <button type="submit" name="button" value="Search_KundenInformationen" class="<?php changeBackgroundSearchButton($KundenInformationen_StateSearchButton); ?>">KundenInformationen</button>
                                <button type="submit" name="button" value="Search_Leistung" class="<?php changeBackgroundSearchButton($Leistung_StateSearchButton); ?>">Leistung</button>
                                <button type="submit" name="button" value="Search_Abrechnungsart" class="<?php changeBackgroundSearchButton($Abrechnungsart_StateSearchButton); ?>">Abrechnungsart</button>
                                <button type="submit" name="button" value="Search_NettoPreis" class="<?php changeBackgroundSearchButton($NettoPreis_StateSearchButton); ?>">NettoPreis</button>
                                <button type="submit" name="button" value="Search_GesamtBetrag" class="<?php changeBackgroundSearchButton($GesamtBetrag_StateSearchButton); ?>">GesamtBetrag</button>
                                <button type="submit" name="button" value="Search_RechnungsDatum" class="<?php changeBackgroundSearchButton($RechnungsDatum_StateSearchButton); ?>">RechnungsDatum</button>
                                <button type="submit" name="button" value="Search_Monat_Jahr" class="<?php changeBackgroundSearchButton($Monat_Jahr_StateSearchButton); ?>">Monat Jahr</button>
                                <button type="submit" name="button" value="Search_RechnungsKürzelNummer" class="<?php changeBackgroundSearchButton($RechnungsKürzelNummer_StateSearchButton); ?>">RechnungsKürzelNummer</button>
                                <button type="submit" name="button" value="Search_MonatlicheRechnung" class="<?php changeBackgroundSearchButton($MonatlicheRechnung_StateSearchButton); ?>">MonatlicheRechnung (1/0)</button>
                                <button type="submit" name="button" value="Search_Bezahlt" class="<?php changeBackgroundSearchButton($Bezahlt_StateSearchButton); ?>">Bezahlt (1/0)</button>
                                <button type="submit" name="button" value="Search_UeberweisungsDatum" class="<?php changeBackgroundSearchButton($UeberweisungsDatum_StateSearchButton); ?>">Überweisungsdatum</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="message <?php echo $messageType; ?>" id="message" style="display: <?php echo $showMessage; ?>">
            <h2 id="messageText"><?php echo $message; ?></h2>
            <span class="material-icons-sharp">close</span>
        </div>
        <div class="Modals">

            <!--Create New Invoice with Button to open Modal-->
            <div class="createInvoices">
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
                                                            <option value="Gutschrift">Gutschrift</option>
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
                                </table>
                            </div>

                            <div class="add-row">
                                <label onclick="addDienstleistungsRow()" id="add-row">+ Hinzufügen weitere Leistung</label>
                            </div>

                            <div class="monatlicheRechnung">
                                <input type="checkbox" name="monatlicheRechnung" id="monatlicheRechnung">
                                <label for="monatlicheRechnung">Monatliche Rechnung</label>
                            </div>

                            <!-- Store KundenID from div KundenListe of the selected Customer for the future -->
                            <input type="hidden" name="selectedKundenID" id="selectedKundenID" value="">

                            <!-- Store RechnungsID in hidden Inputfield to get access in update Switch Case-->
                            <input type="hidden" name="RechnungsID" id="RechnungsID" value="<?php echo htmlspecialchars($RechnungsID); ?>">

                            <input type="hidden" name="saveUpdate" value="<?php echo $saveUpdate; ?>">

                            <button type="submit" name="button" value="<?php echo $saveUpdate; ?>" class="sendNewInvoiceData-Btn" id="<?php echo $saveUpdate ?>"><?php if ($saveUpdate == "save") {
                                                                                                                                                                        echo "Senden";
                                                                                                                                                                    } elseif ($saveUpdate == "update") {
                                                                                                                                                                        echo "Update";
                                                                                                                                                                    } ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="MonatlicheRechnungen">
                <!-- Open Modal for the MonatlicheRechnungen -->
                <button type="button" id="CreateMonatlicheRechnungenModal" class="createMonatlicheRechnungen-Btn">Monatliche-Rechnung</button>

                <!-- Content of the Modal -->
                <div class="modal-MonatlicheRechnungen">

                    <div class="modal-header">
                        <h2>Erstellen Monatliche-Rechnung</h2>
                        <span class="material-icons-sharp">close</span>
                    </div>

                    <div class="form-container">
                        <form method="POST" id="form-modal-MonatlicheRechnung">
                            <div class="datum-MonatlicheRechnungen">
                                <label for="RechnungsDatum-MonatlicheRechnungen">Wähle das Rechnungsdatum sowie den Monat und das Jahr für die Rechnung aus:</label>
                                <div class="RechnungsDatum-MonatlicheRechnungen">
                                    <input type="date" name="RechnungsDatum-MonatlicheRechnungen" id="RechnungsDatum-MonatlicheRechnungen" required>
                                    <input type="month" name="RechnungsMonatJahr-MonatlicheRechnungen" id="RechnungsMonatJahr-MonatlicheRechnungen" required>
                                </div>

                                <div class="ContentMonatlicheRechnungen">
                                    <?php
                                    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

                                    // One Table is combined by adding Monatliche_Rechnung with Kunde & Rechnung
                                    $sql_monatlicheRechnung = "SELECT MR.*,
                                    CASE
                                        WHEN K.Organization = 1 THEN K.Firmenname
                                        WHEN K.Person = 1 THEN K.Name_Ansprechpartner
                                    END AS KundenName,
                                        R.Leistung,
                                        R.Abrechnungsart,
                                        R.NettoPreis,
                                        R.RechnungsKürzelNummer
                                    FROM
                                        Monatliche_rechnung MR
                                    INNER JOIN
                                        Rechnung R ON MR.RechnungsID = R.RechnungsID
                                    INNER JOIN
                                        Kunden K ON R.KundenID = K.KundenID;";


                                    $stmt = $conn->prepare($sql_monatlicheRechnung);
                                    $stmt->execute();
                                    $customer = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $countRows = $stmt->rowCount();

                                    foreach ($customer as $row) {
                                        $monatlicheRechnugsID = $row['MonatlicheRechnungsID'];
                                        $Leistung_MonatlicheRechnung = unserialize($row['Leistung']);
                                        $Abrechnungsart_MonatlicheRechnung = unserialize($row['Abrechnungsart']);
                                        $NettoPreis_MonatlicheRechnung = unserialize($row['NettoPreis']);

                                        $html = '';
                                        $html .= '<div class="monatlicheRechnung-Kunde">';
                                        $html .= '<div class="KundenName">';
                                        $html .= '<input type="checkbox" value = "' . $monatlicheRechnugsID . '" name="erstelleMonatlicheRechnung[]" id = "erstelleMonatlicheRechnung-' . $monatlicheRechnugsID . '" onclick="toggleRechnungsInformationen(this, \'monatlicheRechnung-Kunde\')" checked>';
                                        $html .= '<label for="erstelleMonatlicheRechnung-' . $monatlicheRechnugsID . '">' . $row['KundenName'] . " - " . $row['RechnungsKürzelNummer'] . '</label>';
                                        $html .= '</div>';
                                        $html .= '<div class="RechnungsInformationen" id="RechnungsInformationen">';

                                        $html .= '<table class="RechnungsInformationen-Table">';
                                        $html .= '<tbody>';

                                        for ($i = 0; $i < count($Leistung_MonatlicheRechnung); $i++) {
                                            $html .= '<tr>';
                                            $html .= '<td>' . $Leistung_MonatlicheRechnung[$i] . '</td>';
                                            $html .= '<td>' . $Abrechnungsart_MonatlicheRechnung[$i] . '</td>';
                                            $html .= '<td>' . $NettoPreis_MonatlicheRechnung[$i] . '</td>';
                                            $html .= '</tr>';
                                        }

                                        $html .= '</tbody>';
                                        $html .= '</table>';

                                        $html .= '</div>';
                                        $html .= '</div>';
                                        echo $html;
                                    }

                                    if ($countRows < 1) {
                                        $html = '';
                                        $html .= '<h2>';
                                        $html .= 'Keine monatlichen Rechnungen vorhanden!';
                                        $html .= '</h2>';
                                        echo $html;
                                    }
                                    ?>
                                </div>
                                <div class="uncheck_check-AllCheckboxes">
                                    <button type="button" class="uncheck_AllCheckboxes" onclick="toggleCheckboxes('erstelleMonatlicheRechnung[]', false)">Uncheck All</button>
                                    <button type="button" class="checkAllCheckboxes" onclick="toggleCheckboxes('erstelleMonatlicheRechnung[]', true)">Check All</button>
                                </div>

                                <button type="submit" class="sendMonatlicheRechnungData-Btn">Senden</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="deletedInvoices">
                <button type="button" id="restoreDeletedInvoicesModal" class="restoreDeletedInvoices-Btn">Gelöschte Rechnung wiederherstellen</button>
                <div class="modal-restoreDeletedInvoices">

                    <div class="modal-header">
                        <h2>Wiederherstellung Rechnungen</h2>
                        <span class="material-icons-sharp">close</span>
                    </div>

                    <div class="form-container">
                        <form method="POST" id="form-modal-restoreDeletedInvoices">
                            <div class="ContentRestoreDeletedInvoices">
                                <?php
                                include('../dbPhp/dbOpenConnection.php'); // dbConnection open

                                $sql_restoreDeletedInvoices = "SELECT DR.*,
                                CASE
                                    WHEN K.Organization = 1 THEN K.Firmenname
                                    WHEN K.Person = 1 THEN K.Name_Ansprechpartner
                                END AS KundenName
                                FROM
                                    deletedrechnung DR
                                INNER JOIN
                                    Kunden K ON DR.KundenID = K.KundenID;";
                                $stmt = $conn->prepare($sql_restoreDeletedInvoices);
                                $stmt->execute();
                                $deletedInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $countRows = $stmt->rowCount();

                                foreach ($deletedInvoices as $row) {

                                    $Leistung_deletedRechnung = unserialize($row['Leistung']);
                                    $Abrechnungsart_deletedRechnung = unserialize($row['Abrechnungsart']);
                                    $NettoPreis_deletedRechnung = unserialize($row['NettoPreis']);
                                    $RechnungsNummer_deletedRechnung = $row['RechnungsNummer'];
                                    $RechnungsDatum_deletedRechnung = $row['RechnungsDatum'];
                                    $RechnungsID_deletedRechnung = $row['RechnungsID'];

                                    $html = '';
                                    $html .= '<div class="deletedRechnung-Kunde">';
                                    $html .= '<div class="KundenName">';
                                    $html .= '<input type="checkbox" value = "' . $RechnungsID_deletedRechnung . '" name="restoreDeletedInvoices[]" id = "restoreDeletedInvoices-' . $RechnungsID_deletedRechnung . '" onclick="toggleRechnungsInformationen(this, \'deletedRechnung-Kunde\')">';
                                    $html .= '<label for="restoreDeletedInvoices-' . $RechnungsID_deletedRechnung . '">' . $row['KundenName'] . " - " . $row['RechnungsKürzelNummer'] . '</label>';
                                    $html .= '</div>';
                                    $html .= '<div class="RechnungsInformationen" id="RechnungsInformationen">';

                                    $html .= '<table class="RechnungsInformationen-Table">';
                                    $html .= '<tbody>';

                                    $html .= '<tr>';
                                    $html .= '<td colspan="3">' . $RechnungsDatum_deletedRechnung . '</td>';
                                    $html .= '</tr>';
                                    for ($i = 0; $i < count($Leistung_deletedRechnung); $i++) {
                                        $html .= '<tr>';
                                        $html .= '<td>' . $Leistung_deletedRechnung[$i] . '</td>';
                                        $html .= '<td>' . $Abrechnungsart_deletedRechnung[$i] . '</td>';
                                        $html .= '<td>' . $NettoPreis_deletedRechnung[$i] . '</td>';
                                        $html .= '</tr>';
                                    }

                                    $html .= '</tbody>';
                                    $html .= '</table>';

                                    $html .= '</div>';
                                    $html .= '</div>';
                                    echo $html;
                                }
                                if ($countRows < 1) {
                                    $html = '';
                                    $html .= '<h2>';
                                    $html .= 'Keine gelöschten Rechnungen vorhanden!';
                                    $html .= '</h2>';
                                    echo $html;
                                }
                                ?>
                            </div>

                            <div class="uncheck_check-AllCheckboxes">
                                <button type="button" class="uncheck_AllCheckboxes" onclick="toggleCheckboxes('restoreDeletedInvoices[]', false)">Uncheck All</button>
                                <button type="button" class="checkAllCheckboxes" onclick="toggleCheckboxes('restoreDeletedInvoices[]', true)">Check All</button>
                            </div>

                            <button type="submit" class="sendRestoredDeletedInvoiceData-Btn">Senden</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Create Contacts with Modal -->

        <!-- Beginning of the Crud Table -->
        <div class="crud">
            <div class="crud-table">
                <table class="table">
                    <thead>
                        <th>KundenInformationen</th>
                        <th>Leistung</th>
                        <th>Abrechnungsart</th>
                        <th>NettoPreis</th>
                        <th>MwSt</th>
                        <th>GesamtBetrag</th>
                        <th>RechnungsDatum</th>
                        <th>Monat Jahr</th>
                        <!-- <th>RechnungsNummer</th> -->
                        <th>RechnungsKürzelNummer</th>
                        <th>Monatl.<br> Rech.</th>
                        <th>Bezahlt</th>
                        <!-- <th>RechnungsID</th> -->
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <!-- PHP to load all rows of the contacts -->
                        <?php

                        foreach ($result as $row) {
                        ?>
                            <tr>
                                <td><?php KundenInformationen($row['FirmenName'], $row['Name_Ansprechpartner'], $row['Adresse'], $row['PLZ'], $row['Ort']); ?></td>
                                <td><?php parseSerializedDataLeistung($row['Leistung']); ?></td>
                                <td><?php parseSerializedData($row['Abrechnungsart']); ?></td>
                                <td><?php parseSerializedData($row['NettoPreis']); ?></td>
                                <td><?php echo htmlspecialchars($row['MwSt']); ?></td>
                                <td><?php echo htmlspecialchars($row['GesamtBetrag']); ?></td>
                                <td><?php echo htmlspecialchars($row['RechnungsDatum']); ?></td>
                                <td><?php echo htmlspecialchars($row['Monat_Jahr']); ?></td>
                                <td><?php echo htmlspecialchars($row['RechnungsKürzelNummer']); ?></td>
                                <td><?php echo htmlspecialchars($row['MonatlicheRechnungBool']); ?></td>
                                <td <?php unpaidInvoiceBackground($row['Bezahlt']); ?>>
                                    <form method="post">
                                        <div class="bezahlt_checkbox_button">
                                            <input type="checkbox" name="bezahlt_unbezahlt_checkbox" id="bezahlt_unbezahl_checkbox-<?php echo $row['RechnungsID'] ?>" class="bezahlt_unbezahl_checkbox" <?php setCheckboxBezahltChecked($row['Bezahlt']); ?> required>
                                            <button type="button" name="button" value="bezahlt" class="bezahlt-btn" onclick="bezahlt(this)" <?php disableButton($row['Bezahlt']); ?>>Bezahlt</button>
                                        </div>
                                        <input type="text" class="Ueberweisungsdatum" name="Ueberweisungsdatum" placeholder="ÜberweisungsDatum" value="<?php setUeberweisungsdatum($row['UeberweisungsDatum']); ?>" <?php disableButton($row['Bezahlt']); ?> required>
                                        <input type="hidden" name="RechnungsID_Bezahlt" value=<?php echo htmlspecialchars($row['RechnungsID']); ?>>
                                    </form>
                                </td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="RechnungsID" value=<?php echo htmlspecialchars($row['RechnungsID']); ?>>
                                        <button type="submit" class="CrudEdit" name="button" value="edit">Edit</button>
                                        <button type="submit" class="CrudDelete" name="button" value="delete" onclick="showDeleteConfirmation()" <?php disableButton($row['Bezahlt']); ?>>Delete</button>
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
    <!-- Rich Text Editor ckEditor 5 CustomBuild -->
    <script src="../ckeditor/build/ckeditor.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./index.js"></script>
</body>

</html>