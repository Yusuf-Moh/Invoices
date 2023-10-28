<?php

// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../../loginSystem/checkLogin.php";

$UserPath = "C:/Users/yusuf/OneDrive/Desktop/Rechnung/";

$generatedFiles = [];

require __DIR__ . "/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $restoreDeletedInvoices_Checkboxes = $_POST["restoreDeletedInvoices"];
    foreach ($restoreDeletedInvoices_Checkboxes as $checkboxes) {
        // $checkboxes => RechnungsID

        // Rechnung wiederherstellen
        // 1. PDF erstellen und im jeweiligen Ordner speichern
        // 2. deletedrechnung =>  rechnung
        // 3. monatliche Rechnung = 1; => monatliche_rechnung

        restoreDeletedInvoice($checkboxes);
    }

    // ============== Download the files from the serverside ==============
    echo '<script type = "text/javascript">';
    global $generatedFiles;
    for ($i = 0; $i < count($generatedFiles); $i++) {
        $fileUrl = $generatedFiles[$i];
        echo 'window.open("' . $fileUrl . '");';
    }
    echo '</script>';

    // header("location: ../invoice.php");
} else {
    header("location: ../invoice.php");
}

function restoreDeletedInvoice($RechnungsID)
{
    include('../../dbPhp/dbOpenConnection.php');

    // ============ Values from deleted Invoice ============
    $sql = "SELECT * FROM deletedrechnung WHERE RechnungsID = :RechnungsID;";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':RechnungsID', $RechnungsID, PDO::PARAM_INT);
    $stmt->execute();
    $result = [];
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $KundenID = $result['KundenID'];
    $Leistung = unserialize($result['Leistung']);
    $Abrechnungsart = unserialize($result['Abrechnungsart']);
    $NettoPreis = unserialize($result['NettoPreis']);
    $MonatlicheRechnungBool = $result['MonatlicheRechnungBool'];
    $RechnungsDatum = $result['RechnungsDatum'];
    $Monat_Jahr = $result['Monat_Jahr'];
    $RechnungsNumer = $result['RechnungsNummer'];
    $RechnungsKürzelNummer = $result['RechnungsKürzelNummer'];
    $MwSt = $result['MwSt'];
    $GesamtBetrag = $result['GesamtBetrag'];
    $Pfad = $result['Pfad'];


    // ============ Values from Kunden/Customer ============
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

    // ============ Create Folder for the PDF ============
    // Generated PDF-Filenames stored in a array to access the files in javascript later
    $splitedRechnungsMonatJahr = explode(" ", $Monat_Jahr);
    $Jahr = end($splitedRechnungsMonatJahr);

    // Path to the Folder, in which the files should be stored
    global $UserPath;
    $downloadPath = $UserPath;
    $downloadPath .= $Jahr . "/";
    // Invoice getting stored in the respective MonatYear of it
    $downloadPath .= $Monat_Jahr . "/";

    // Create the folder, for the RechnungsMonatJahr, if it doesnt exist.
    createFolderIfNotExists($downloadPath);

    // ============ Converting Data for the PDF ==============
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

    $KontaktInformationen = displayStringBR($FirmenName) . displayStringBR($Ansprechpartner) . displayStringBR($Adresse) . $PLZ . " " . $Ort;
    $RechnungsMonatJahr_ggfVertragsDatum =  $Monat_Jahr . displayVertragsdatum($VertragsDatum);

    $TABLE_ROWS = '';
    $countLeistungsRow = count($Leistung);

    $gesamtNettoPreis = 0;

    for ($m = 0; $m < $countLeistungsRow; $m++) {
        //format the number from for example, 1000 to 1.000,00
        $TABLE_ROWS .= '<tr>';
        $TABLE_ROWS .= '<td style="text-align: left;">' . $Leistung[$m] . '</td>';
        $TABLE_ROWS .= AbrechnungsArtPauschalStundenGutschrift($Abrechnungsart[$m]);
        $TABLE_ROWS .= '<td>' . number_format($NettoPreis[$m], 2, ',', '.') . ' Euro</td>';
        $TABLE_ROWS .= '</tr>';
        if ($Abrechnungsart[$m] == 'Gutschrift') {
            $gesamtNettoPreis -= $NettoPreis[$m];
        } else {
            $gesamtNettoPreis += $NettoPreis[$m];
        }
    }

    $gesamtNettoPreis = number_format($gesamtNettoPreis, 2, ',', '.');

    // If there is more than one "Leistung", the total Netto amount will be shown in a seperate row
    if (count($NettoPreis) > 1) {
        $TABLE_ROWS .= "<tr>";
        $TABLE_ROWS .= "<td colspan='3' style='text-align: left;'>";
        $TABLE_ROWS .= "Gesamtbetrag Netto";
        $TABLE_ROWS .= "</td>";
        $TABLE_ROWS .= "<td>";
        $TABLE_ROWS .= $gesamtNettoPreis . " Euro";
        $TABLE_ROWS .= "</td>";
        $TABLE_ROWS .= "</tr>";
    }


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
    $html = str_replace('%Rechnungsdatum%', $RechnungsDatum, $html);
    $html = str_replace('%RechnungsKürzelNummer%', $RechnungsKürzelNummer, $html);
    $html = str_replace('%Anrede%', $Anrede, $html);
    $html = str_replace('%TABLE_ROWS%', $TABLE_ROWS, $html);
    $html = str_replace('%RechnungsMonatJahr_ggfVertragsDatum%', $RechnungsMonatJahr_ggfVertragsDatum, $html);
    $html = str_replace('%gesamtBetragMwSt%', $MwSt, $html);
    $html = str_replace('%gesamtBetragBrutto%', $GesamtBetrag, $html);


    $dompdf->setPaper("A4", "portrait");
    $dompdf->loadHtml($html);
    //Create PDF
    $dompdf->render();

    if ($FirmenName != "") {
        $KundenName = $FirmenName;
    } elseif ($Ansprechpartner != "") {
        $KundenName = $Ansprechpartner;
    } else {
        $KundenName = "ERROR" . $RechnungsID;
    }

    $filename = $KundenName . " RechnungNr. " . $RechnungsNumer . " " . $Monat_Jahr . ".pdf";

    $path_pdf = $downloadPath . $filename;

    global $generatedFiles;
    $generatedFiles[] = $path_pdf;


    // Save the File to the Server, so we can create multiple pdf files. 
    $output = $dompdf->output();
    file_put_contents($path_pdf, $output);


    include('../../dbPhp/dbCloseConnection.php');
}

function displayStringBR($String)
{
    if (!empty($String)) {
        return $String . "<br>";
    }
}

function displayVertragsdatum($vertragsDatum)
{
    if (!empty($vertragsDatum)) {
        return " laut Vertrag vom " . $vertragsDatum;
    }
}

function AbrechnungsArtPauschalStundenGutschrift($abrechnungsart)
{
    $html = '';
    if ($abrechnungsart == 'Pauschal' || $abrechnungsart == 'Gutschrift') {
        $html .= '<td></td>';
        $html .= '<td>' . $abrechnungsart . '</td>';
    } else {
        $abrechnungsart = str_replace(' Stunden', ' Std.', $abrechnungsart);
        $html .= '<td>' . $abrechnungsart . '</td>';
        $html .= '<td></td>';
    }
    return $html;
}

function createFolderIfNotExists($path)
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}
