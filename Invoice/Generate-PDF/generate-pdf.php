<?php
// Path in which the Invoice are getting stored
// Need to be changed
$UserPath = "C:/Users/yusuf/OneDrive/Desktop/Rechnung/";

// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../../loginSystem/checkLogin.php";

// ============ Values from invoice.php ============

$KundenID = $_POST['selectedKundenID'];
// Rechnungsdatum
$Rechnungsdatum = $_POST['RechnungsDatum'];
$RechnungsMonatJahr = $_POST['RechnungsMonatJahr'];
// Storing the content from the LeistungEditor
$Leistung = $_POST['leistungEditor'];
//Abrechnungsart
$AbrechnungsartList = $_POST['AbrechnungsartList'];
$AbrechnungsartStunden = $_POST['Stunden'];
//Nettopreis
$nettoPreis = $_POST['nettoPreis'];

$gesamtNettoPreis = 0;
$gesamtBetragMwSt = 0;
$gesamtBetragBrutto = 0;


// Replace Stunden with the inputfield number
for ($i = 0; $i < count($AbrechnungsartList); $i++) {
    if ($AbrechnungsartList[$i] != "Pauschal") {
        $AbrechnungsartList[$i] = $AbrechnungsartStunden[$i] . ' Stunden';
    }
}

// Format the Html Data and Month_Year to the european version
$Rechnungsdatum = formatDate($Rechnungsdatum);
$RechnungsMonatJahr = formatMonthYear($RechnungsMonatJahr);


//Calculation MwSt and Gesamtbetrag
$MwSt_Percentage = 19;

$MwStArray = [];
$GesamtBetragArray = [];
foreach ($nettoPreis as $nettoBetrag) {
    //always round up after the 2nd decimal places 
    $MwSt = ceil($nettoBetrag * ($MwSt_Percentage / 100) * 100) / 100;
    $GesamtBetrag = $nettoBetrag + $MwSt;
    $MwStArray[] = $MwSt;
    $GesamtBetragArray[] = $GesamtBetrag;
}


// Storing the value of the checkbox 
$monatlicheRechnung = "0";
if (isset($_POST['monatlicheRechnung'])) {
    $monatlicheRechnung = "1";
}
// Storing the value
$saveUpdate = $_POST['saveUpdate'];

// ============ Values from Kunden/Customer ============
include('../../dbPhp/dbOpenConnection.php');
$sql = "SELECT * FROM Kunden WHERE KundenID = :KundenID";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':KundenID', $KundenID, PDO::PARAM_INT);
$stmt->execute();

$result = [];
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$FirmenName = "";

if ($result['organization'] == '1') {
    $FirmenName = $result['FirmenName'];
}
$Adresse = $result['Adresse'];
$RechnungsKürzel = $result['RechnungsKürzel'];
$PLZ = $result['PLZ'];
$Ort = $result['Ort'];
$VertragsDatum = $result['VertragsDatum'];
$Ansprechpartner = $result['Name_Ansprechpartner'];
$gender = $result['Gender'];

$name_parts = explode(" ", $Ansprechpartner);
$Nachname = end($name_parts);

if ($gender == "Male") {
    $Ansprechpartner = "Herr " . $Ansprechpartner;
    $Anrede = "Sehr geehrter Herr " . $Nachname . ",";
} else if ($gender == "Female") {
    $Ansprechpartner = "Frau " . $Ansprechpartner;
    $Anrede = "Sehr geehrte Frau " . $Nachname . ",";
} else {
    $Ansprechpartner = "";
    $Anrede = "Sehr geehrte Damen und Herren,";
}
include('../../dbPhp/dbCloseConnection.php');


$insertInvoiceDB = false;

if ($saveUpdate == "save") {
    // ============ Last RechnungsNR of DB-Table Rechnung ============
    $RechnungsNr = lastRechnungsNr($KundenID);
    $insertInvoiceDB = true;
} else if ($saveUpdate == "update") {
    // RechnungsID from the hidden Inputfield of the Modal
    $RechnungsID = $_POST['RechnungsID'];

    include('../../dbPhp/dbOpenConnection.php');
    $query = "SELECT KundenID, RechnungsNummer, MonatlicheRechnungBool FROM rechnung WHERE RechnungsID = :RechnungsID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam('RechnungsID', $RechnungsID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $KundenID_update = $result['KundenID'];
    $RechnungsNr_update = $result['RechnungsNummer'];
    $MonatlicheRechnungBool_update = $result['MonatlicheRechnungBool'];

    include('../../dbPhp/dbCloseConnection.php');

    // Delete the invoice pdf file, so we can create the new pdf later without getting duplicated files in our folder "system"
    deleteFile($RechnungsID);

    if ($KundenID_update == $KundenID) {
        $RechnungsNr = $RechnungsNr_update;
    } else {
        // Delete the Invoice with the RechnungsID; The deleted Data is getting stored in the Table deletedRechnung, so we can have a backup and can follow the RechnungsNr.
        $RechnungsNr = lastRechnungsNr($KundenID);
        deleteRechnung($RechnungsID);
        $insertInvoiceDB = true;
    }

    // Kunde wurde geändert, und monatlicheRechnung nicht! Aufpassen weil in der Datenbank Tabelle MonatlicheRechnung der alte Kunde (KundenID) noch drinne ist. Muss geändert werden um FolgeFehler zu vermeiden
}

$RechnungsKürzelNummer = $RechnungsKürzel . convertToMMYY($RechnungsMonatJahr) . "/" . formatRechnungsNr($RechnungsNr);
include('../../dbPhp/dbCloseConnection.php');


function displayStringBR($String)
{
    if (!empty($String)) {
        return $String . "<br>";
    }
}

function displayString($String)
{
    if (!empty($String)) {
        return $String;
    }
}

function displayVertragsdatum($vertragsDatum)
{
    if (!empty($vertragsDatum)) {
        return " laut Vertrag vom " . $vertragsDatum;
    }
}

//From MMMM JJJJ to MMJJ for the RechnungsKürzelNummer
function convertToMMYY($dateStr)
{
    $months = array(
        'Januar' => '01',
        'Februar' => '02',
        'März' => '03',
        'April' => '04',
        'Mai' => '05',
        'Juni' => '06',
        'Juli' => '07',
        'August' => '08',
        'September' => '09',
        'Oktober' => '10',
        'November' => '11',
        'Dezember' => '12'
    );

    $dateArr = explode(" ", $dateStr);
    $month = $months[$dateArr[0]];
    $year = substr($dateArr[1], -2);

    return $month . $year;
}

//With the Inputfield type date (invoice.php) the values for 23.05.2023 are 2023-05.23 which are not suitable for the pdf.
function formatDate($date)
{
    return date("d.m.Y", strtotime($date));
}
// With the Inputfield type Month (invoice.php), the values for Mai 2023 are 2023-05 which are not suitable.
function formatMonthYear($monthYear)
{
    $months = array(
        '01' => 'Januar',
        '02' => 'Februar',
        '03' => 'März',
        '04' => 'April',
        '05' => 'Mai',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'August',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Dezember'
    );

    $dateArr = explode("-", $monthYear);
    $year = $dateArr[0];
    $month = $dateArr[1];

    return $months[$month] . " " . $year;
}

// Convert RechnungsNr to a format from 1 to 0001
function formatRechnungsNr($rechnungsNr)
{
    return str_pad($rechnungsNr, 4, '0', STR_PAD_LEFT);
}

function AbrechnungsArtPauschalStunden($abrechnungsart)
{
    $html = '';
    if ($abrechnungsart == 'Pauschal') {
        $html .= '<td></td>';
        $html .= '<td>' . $abrechnungsart . '</td>';
    } else {
        $abrechnungsart = str_replace(' Stunden', ' Std.', $abrechnungsart);
        $html .= '<td>' . $abrechnungsart . '</td>';
        $html .= '<td></td>';
    }
    return $html;
}

function lastRechnungsNr($kundenID)
{
    include('../../dbPhp/dbOpenConnection.php');
    try {
        $query = "SELECT MAX(RechnungsNummer) AS MaxRechnungsNr FROM 
        ( SELECT RechnungsNummer FROM Rechnung WHERE KundenID = :KundenID UNION SELECT RechnungsNummer FROM deletedRechnung WHERE KundenID = :KundenID ) 
        AS CombinedResults;";
        $stmt = $conn->prepare($query);
        $stmt->bindParam('KundenID', $kundenID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rechnungsNr = $result['MaxRechnungsNr'] + 1;
    } catch (PDOException) {
        $rechnungsNr = 1;
    }
    return $rechnungsNr;
    include('../../dbPhp/dbCloseConnection.php');
}

// Function to copy the data from the given invoice and deleting it from the database rechnung 
function deleteRechnung($rechnungsID)
{

    deleteFile($rechnungsID);

    include('../../dbPhp/dbOpenConnection.php'); // dbConnection open

    // The data of the deleted Invoice is getting stored in a Table deletedRechnung as Backup.
    $query = "INSERT INTO deletedrechnung (Leistung, Abrechnungsart, NettoPreis, KundenID, MonatlicheRechnungBool, RechnungsDatum, Monat_Jahr, RechnungsNummer, RechnungsKürzelNummer, RechnungsID, MwSt, GesamtBetrag, Pfad, Bezahlt, UeberweisungsDatum, Zeitpunkt_Erstellung)
    SELECT Leistung, Abrechnungsart, NettoPreis, KundenID, MonatlicheRechnungBool, RechnungsDatum, Monat_Jahr, RechnungsNummer, RechnungsKürzelNummer, RechnungsID, MwSt, GesamtBetrag, Pfad, Bezahlt, UeberweisungsDatum, Zeitpunkt_Erstellung
    FROM rechnung
    WHERE RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();

    // Deleting the Invoice from Table rechnung
    $query = "DELETE FROM rechnung WHERE RechnungsID=:RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();

    // If there is a record with the given RechnungsID in the Database Table monatliche_rechnungen, then it should be deleted aswell
    $query = "DELETE FROM monatliche_rechnung WHERE RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();
    include('../../dbPhp/dbCloseConnection.php');    // dbConnection close
}

function createFolderIfNotExists($path)
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function deleteFile($rechnungsID)
{
    include('../../dbPhp/dbOpenConnection.php'); // dbConnection open

    $query = "SELECT Pfad FROM rechnung where RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $rechnungsID);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pfad = $result['Pfad'];

    if (file_exists($pfad)) {
        unlink($pfad);
    }

    include('../../dbPhp/dbCloseConnection.php');    // dbConnection close
}
// ============ Variables which need to be inserted into the PDF/invoiceMuster.html ============

$KontaktInformationen = displayStringBR($FirmenName) . displayStringBR($Ansprechpartner) . displayStringBR($Adresse) . $PLZ . " " . $Ort;
// $Rechnungsdatum = $Rechnungsdatum;
// $RechnungsKürzelNummer = $RechnungsKürzelNummer;
// $Anrede = $Anrede;
// $TABLE_ROWS = $TABLE_ROWS
$RechnungsMonatJahr_ggfVertragsDatum =  $RechnungsMonatJahr . displayVertragsdatum($VertragsDatum);
// $gesamtBetragMwSt = $gesamtBetragMwSt;
// $gesamtBetragBrutto = $gesamtBetragBrutto;



// Creating the Table Rows for the Leistungen, Abrechnungsart and Nettopreis
$TABLE_ROWS = '';
for ($i = 0; $i < count($nettoPreis); $i++) {
    //format the number from for example, 1000 to 1.000,00

    $TABLE_ROWS .= '<tr>';
    $TABLE_ROWS .= '<td style="text-align: left;">' . $Leistung[$i] . '</td>';
    $TABLE_ROWS .= AbrechnungsArtPauschalStunden($AbrechnungsartList[$i]);
    $TABLE_ROWS .= '<td>' . number_format($nettoPreis[$i], 2, ',', '.') . ' Euro</td>';
    $TABLE_ROWS .= '</tr>';
    $gesamtNettoPreis += $nettoPreis[$i];
    $gesamtBetragMwSt += $MwStArray[$i];
    $gesamtBetragBrutto += $GesamtBetragArray[$i];
}

//format the number from for example, 1000 to 1.000,00
$gesamtNettoPreis = number_format($gesamtNettoPreis, 2, ',', '.');
$gesamtBetragMwSt = number_format($gesamtBetragMwSt, 2, ',', '.');
$gesamtBetragBrutto = number_format($gesamtBetragBrutto, 2, ',', '.');


// If there is more than one "Leistung", the total Netto amount will be shown in a seperate row
if (count($nettoPreis) > 1) {
    $TABLE_ROWS .= "<tr>";
    $TABLE_ROWS .= "<td colspan='3' style='text-align: left;'>";
    $TABLE_ROWS .= "Gesamtbetrag Netto";
    $TABLE_ROWS .= "</td>";
    $TABLE_ROWS .= "<td>";
    $TABLE_ROWS .= $gesamtNettoPreis . " Euro";
    $TABLE_ROWS .= "</td>";
    $TABLE_ROWS .= "</tr>";
}


// ============ Creating and downloading the PDF ============

require __DIR__ . "/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;


$splitedRechnungsMonatJahr = explode(" ", $RechnungsMonatJahr);
$Jahr = end($splitedRechnungsMonatJahr);

$downloadPath = $UserPath;
$downloadPath .= $Jahr . "/";
$downloadPath .= $RechnungsMonatJahr . "/";
createFolderIfNotExists($downloadPath);

if ($FirmenName != "") {
    $KundenName = $FirmenName;
} elseif ($Ansprechpartner != "") {
    $KundenName = $Ansprechpartner;
} else {
    $KundenName = "ERROR";
}
$filename = $KundenName . " RechnungNr. " . $RechnungsNr . " " . $RechnungsMonatJahr . ".pdf";

/**
 * Set the Dompdf options
 */
$options = new Options;
$options->setChroot(__DIR__);
$options->setIsRemoteEnabled(true);

$dompdf = new Dompdf($options);


// Load the HTML and replace placeholders with values from the form
$html = file_get_contents("invoiceMuster.html");
$html = str_replace('%KontaktInformationen%', $KontaktInformationen, $html);
$html = str_replace('%Rechnungsdatum%', $Rechnungsdatum, $html);
$html = str_replace('%RechnungsKürzelNummer%', $RechnungsKürzelNummer, $html);
$html = str_replace('%Anrede%', $Anrede, $html);
$html = str_replace('%TABLE_ROWS%', $TABLE_ROWS, $html);
$html = str_replace('%RechnungsMonatJahr_ggfVertragsDatum%', $RechnungsMonatJahr_ggfVertragsDatum, $html);
$html = str_replace('%gesamtBetragMwSt%', $gesamtBetragMwSt, $html);
$html = str_replace('%gesamtBetragBrutto%', $gesamtBetragBrutto, $html);


$dompdf->setPaper("A4", "portrait");
$dompdf->loadHtml($html);
//Create PDF
$dompdf->render();

$output = $dompdf->output();
file_put_contents($downloadPath . $filename, $output);
// $dompdf->addInfo("Title", "An Example PDF");

//Open new Tab with PDF
// $dompdf->stream("invoice.pdf", ["Attachment" => 0]);


// Download the files from the serverside
echo '<script type = "text/javascript">';
$fileUrl = $downloadPath . $filename;
echo 'window.open("' . $fileUrl . '");';
echo '</script>';
// ====================== End of Creating and Download PDF ======================


// ============ Inserting the Data into the Database Table Rechnung ============

// monatlicheRechnung in MonatlicheRechnungBool; 
// Wert muss in Tabelle monatliche_rechnung später gespeichert werden und wenn man edit klickt, und den check entfernt soll auch die eingetragene Rechnung in datenbank Tabelle MonatlicheRechnung gelöscht werden
include('../../dbPhp/dbOpenConnection.php');
try {

    $Leistung = serialize($Leistung);
    $AbrechnungsartList = serialize($AbrechnungsartList);
    $nettoPreis = serialize($nettoPreis);

    if ($saveUpdate == "update" && $insertInvoiceDB == false) {
        $sql = "UPDATE rechnung
        SET Leistung = :leistung,
            Abrechnungsart = :abrechnungsart,
            NettoPreis = :nettoPreis,
            KundenID = :kundenID,
            MonatlicheRechnungBool = :monatlicheRechnung,
            RechnungsDatum = :rechnungsDatum,
            Monat_Jahr = :rechnungsMonatJahr,
            RechnungsNummer = :rechnungsNr,
            RechnungsKürzelNummer = :rechnungsKuerzelNummer,
            MwSt = :mwSt,
            GesamtBetrag = :gesamtBetrag,
            Pfad = :pfad
        WHERE RechnungsID = :rechnungsID;";
    } else {
        $sql = "INSERT INTO rechnung (Leistung, Abrechnungsart, NettoPreis, KundenID, MonatlicheRechnungBool, RechnungsDatum, Monat_Jahr, RechnungsNummer, RechnungsKürzelNummer, MwSt, GesamtBetrag, Pfad)
        VALUES (:leistung, :abrechnungsart, :nettoPreis, :kundenID, :monatlicheRechnung, :rechnungsDatum, :rechnungsMonatJahr, :rechnungsNr, :rechnungsKuerzelNummer, :mwSt, :gesamtBetrag, :pfad)";
    }

    $stmt = $conn->prepare($sql);

    // Bind the values 
    $stmt->bindParam(':leistung', $Leistung);
    $stmt->bindParam(':abrechnungsart', $AbrechnungsartList);
    $stmt->bindParam(':nettoPreis', $nettoPreis);
    $stmt->bindParam(':kundenID', $KundenID);
    $stmt->bindParam(':monatlicheRechnung', $monatlicheRechnung);
    $stmt->bindParam(':rechnungsDatum', $Rechnungsdatum);
    $stmt->bindParam(':rechnungsMonatJahr', $RechnungsMonatJahr);
    $stmt->bindParam(':rechnungsNr', $RechnungsNr);
    $stmt->bindParam(':rechnungsKuerzelNummer', $RechnungsKürzelNummer);
    $stmt->bindParam(':mwSt', $gesamtBetragMwSt);
    $stmt->bindParam(':gesamtBetrag', $gesamtBetragBrutto);
    $stmt->bindParam(':pfad', $fileUrl);
    if ($saveUpdate == "update" && $insertInvoiceDB == false) {
        $stmt->bindParam(':rechnungsID', $RechnungsID);
    }
    $stmt->execute();

    if ($saveUpdate == "update") {
        $RechnungsID_New = $RechnungsID;
    }
    if ($insertInvoiceDB == true) {
        $RechnungsID_New = $conn->lastInsertId();
    }
    // Success message
} catch (PDOException  $error) {
    // Error message
}
include('../../dbPhp/dbCloseConnection.php');


// MonatlicheRechnung hinzufügen/löschen/updaten 
if ($saveUpdate == "update") {
    if ($MonatlicheRechnungBool_update == "1" && $monatlicheRechnung == "0") {
        // MonatlicheRechnung angeklickt (von 1 => 0) := Datenbank Tabelle monatlicheRechnung löschen
        include('../../dbPhp/dbOpenConnection.php');
        $query = "DELETE FROM monatliche_rechnung WHERE RechnungsID = :RechnungsID;";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':RechnungsID', $RechnungsID);
        $stmt->execute();
        include('../../dbPhp/dbCloseConnection.php');
    } else if (($MonatlicheRechnungBool_update == "0" && $monatlicheRechnung == "1") || ($insertInvoiceDB && $monatlicheRechnung == "1")) {
        // MonatlicheRechnung angeklickt (von 0 => 1) ODER wenn eine Rechnung hinzugefügt wurde && Monatlicherechnung ist checked := Datenbank Tabelle monatlicheRechnung hinzufügen
        include('../../dbPhp/dbOpenConnection.php');
        $query = "INSERT INTO monatliche_rechnung (RechnungsID) VALUES (:RechnungsID);";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':RechnungsID', $RechnungsID_New);
        $stmt->execute();
        include('../../dbPhp/dbCloseConnection.php');
    }
} else if ($saveUpdate == "save" && $monatlicheRechnung == "1") {
    // überprüfen ob $MonatlicheRechnung == "1", dann in Tabelle INSERTEN
    include('../../dbPhp/dbOpenConnection.php');
    $query = "INSERT INTO monatliche_rechnung (RechnungsID) VALUES (:RechnungsID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':RechnungsID', $RechnungsID_New);
    $stmt->execute();
    include('../../dbPhp/dbCloseConnection.php');
}



header("location: ../invoice.php");
