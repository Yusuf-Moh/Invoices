<?php

$RechnungsDatum_MonatlicheRechnungen = $_POST['RechnungsDatum-MonatlicheRechnungen'];
$RechnungsMonatJahr_MonatlicheRechnungen = $_POST['RechnungsMonatJahr-MonatlicheRechnungen'];

// Converting the html format Date to European/German Format
$RechnungsDatum_MonatlicheRechnungen = formatDate($RechnungsDatum_MonatlicheRechnungen);
$RechnungsMonatJahr_MonatlicheRechnungen = formatMonthYear($RechnungsMonatJahr_MonatlicheRechnungen);

// Array mit allen MonatlicheRechnungsNr die checked wurden
$checkedCheckboxes = $_POST['erstelleMonatlicheRechnung'];
$countCheckedCheckboxes = count($checkedCheckboxes);

// PDF generieren
require __DIR__ . "/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Generated PDF-Filenames stored in a array to access the files in ajax later
$generatedFiles = [];


// Erstellen der Monatliche Rechnung
for ($i = 0; $i < $countCheckedCheckboxes; $i++) {
    include('../../dbPhp/dbOpenConnection.php');
    $query = "SELECT 
        MR.monatlicheRechnungsID, MR.RechnungsID,
        R.Leistung, R.Abrechnungsart, R.NettoPreis,
        K.*
    FROM monatliche_rechnung MR
    INNER JOIN Rechnung R ON MR.RechnungsID = R.RechnungsID
    INNER JOIN Kunden K ON R.KundenID = K.KundenID
    WHERE MR.monatlicheRechnungsID = :monatlicheRechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam('monatlicheRechnungsID', $checkedCheckboxes[$i], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    include('../../dbPhp/dbCloseConnection.php');

    $LeistungArray = unserialize($result['Leistung']);
    $AbrechnungsartArray = unserialize($result['Abrechnungsart']);
    $NettoPreisArray = unserialize($result['NettoPreis']);

    $gesamtNettoPreis = 0;
    $gesamtBetragMwSt = 0;
    $gesamtBetragBrutto = 0;

    //Calculation MwSt and Gesamtbetrag
    $MwSt_Percentage = 19;

    $MwStArray = [];
    $GesamtBetragArray = [];
    foreach ($NettoPreisArray as $nettoBetrag) {
        //always round up after the 2nd decimal places 
        $MwSt = ceil($nettoBetrag * ($MwSt_Percentage / 100) * 100) / 100;
        $GesamtBetrag = $nettoBetrag + $MwSt;
        $MwStArray[] = $MwSt;
        $GesamtBetragArray[] = $GesamtBetrag;
    }

    $KundenID = $result['KundenID'];
    $RechnungsNr = lastRechnungsNr($KundenID);
    $RechnungsKürzel = $result['RechnungsKürzel'];
    $RechnungsKürzelNummer = $RechnungsKürzel . convertToMMYY($RechnungsMonatJahr_MonatlicheRechnungen) . "/" . formatRechnungsNr($RechnungsNr);

    // KundenInformationen
    $FirmenName = "";
    if ($result['organization'] == '1') {
        $FirmenName = $result['FirmenName'];
    }
    $Adresse = $result['Adresse'];
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

    $countLeistungen = count($LeistungArray);

    $KontaktInformationen = displayStringBR($FirmenName) . displayStringBR($Ansprechpartner) . displayStringBR($Adresse) . $PLZ . " " . $Ort;
    $RechnungsMonatJahr_ggfVertragsDatum =  $RechnungsMonatJahr_MonatlicheRechnungen . displayVertragsdatum($VertragsDatum);

    $TABLE_ROWS = '';
    for ($m = 0; $m < $countLeistungen; $m++) {
        //format the number from for example, 1000 to 1.000,00
        $TABLE_ROWS .= '<tr>';
        $TABLE_ROWS .= '<td style="text-align: left;">' . $LeistungArray[$m] . '</td>';
        $TABLE_ROWS .= AbrechnungsArtPauschalStunden($AbrechnungsartArray[$m]);
        $TABLE_ROWS .= '<td>' . number_format($NettoPreisArray[$m], 2, ',', '.') . ' Euro</td>';
        $TABLE_ROWS .= '</tr>';
        $gesamtNettoPreis += $NettoPreisArray[$m];
        $gesamtBetragMwSt += $MwStArray[$m];
        $gesamtBetragBrutto += $GesamtBetragArray[$m];
    }
    //format the number from for example, 1000 to 1.000,00
    $gesamtNettoPreis = number_format($gesamtNettoPreis, 2, ',', '.');
    $gesamtBetragMwSt = number_format($gesamtBetragMwSt, 2, ',', '.');
    $gesamtBetragBrutto = number_format($gesamtBetragBrutto, 2, ',', '.');

    // If there is more than one "Leistung", the total Netto amount will be shown in a seperate row
    if (count($NettoPreisArray) > 1) {
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
    $html = str_replace('%Rechnungsdatum%', $RechnungsDatum_MonatlicheRechnungen, $html);
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
    // $dompdf->addInfo("Title", "An Example PDF");

    //Open new Tab with PDF
    // $dompdf->stream("invoice.pdf", ["Attachment" => 0]);
    if ($FirmenName != "") {
        $KundenName = $FirmenName;
    } elseif ($Ansprechpartner != "") {
        $KundenName = $Ansprechpartner;
    } else {
        $KundenName = "ERROR" . $checkedCheckboxes[$i];
    }

    $filename = $KundenName . " RechnungNr. " . $RechnungsNr . " " . $RechnungsMonatJahr_MonatlicheRechnungen . ".pdf";

    $generatedFiles = $filename;
    // Save the File to the Server, so we can create multiple pdf files. 
    $output = $dompdf->output();
    file_put_contents($filename, $output);
    // $dompdf->stream($filename, ["Attachment" => 0]);
}

// Download the files from the serverside
echo '<script type = "text/javascript">';
for ($i = 0; $i < count($generatedFiles); $i++) {
    echo 'window.open("' . $generatedFiles[$i] . '");';
}
echo '</script>';

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

// Convert RechnungsNr to a format from 1 to 0001
function formatRechnungsNr($rechnungsNr)
{
    return str_pad($rechnungsNr, 4, '0', STR_PAD_LEFT);
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
