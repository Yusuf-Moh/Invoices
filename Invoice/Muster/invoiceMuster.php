<?php

// ============ Values from invoice.php ============

$KundenID = $_POST['selectedKundenID'];

// Rechnungsdatum
$Rechnungsdatum = $_POST['RechnungsDatum'];
$Rechnungsdatum = formatDate($Rechnungsdatum);
$RechnungsMonatJahr = $_POST['RechnungsMonatJahr'];
$RechnungsMonatJahr = formatMonthYear($RechnungsMonatJahr);

// Storing the content from the LeistungEditor
$Leistung = $_POST['leistungEditor'];

//Abrechnungsart
$AbrechnungsartList = $_POST['AbrechnungsartList'];
$AbrechnungsartStunden = $_POST['Stunden'];
// Replace Stunden with the inputfield number
for ($i = 0; $i < count($AbrechnungsartList); $i++) {
    if ($AbrechnungsartList[$i] != "Pauschal") {
        $AbrechnungsartList[$i] = $AbrechnungsartStunden[$i];
    }
}

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

// ============ Last RechnungsNR of DB-Table Rechnung ============

try {
    $query = "SELECT MAX(RechnungsNummer) AS MaxRechnungsNR FROM rechnung WHERE KundenID = :KundenID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam('KundenID', $KundenID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $RechnungsNr = $result['MaxRechnungsNR'] + 1;
} catch (PDOException) {
    $RechnungsNr = 1;
}


$RechnungsKürzelNummer = $RechnungsKürzel . convertToMMYY($RechnungsMonatJahr) . "/" . formatRechnungsNr($RechnungsNr);

$gesamtNettoPreis = 0;
$gesamtBetragMwSt = 0;
$gesamtBetragBrutto = 0;

include('../../dbPhp/dbCloseConnection.php');


function displayStringBR($String)
{
    if (!empty($String)) {
        echo $String . "<br>";
    }
}

function displayString($String)
{
    if (!empty($String)) {
        echo $String;
    }
}

function displayVertragsdatum($vertragsDatum)
{

    if (!empty($vertragsDatum)) {
        echo "laut Vertrag vom " . $vertragsDatum;
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

function formatRechnungsNr($rechnungsNr)
{
    return str_pad($rechnungsNr, 4, '0', STR_PAD_LEFT);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            /* aspect ratios of the page */
            margin: 3cm 2cm 2cm 2cm;
        }

        * {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        header {
            position: fixed;
            top: -60px;
            height: 50px;
            line-height: 35px;
            width: 100%;
        }

        footer {
            position: fixed;
            bottom: -50px;
            height: 50px;
            text-align: center;
            line-height: 35px;
            width: 100%;
        }

        .pagenum:before {
            content: counter(page);
        }

        /* display flex and spacebetween not working because of domPDF */
        .ÜberschriftFirma {
            /* position of the div left*/
            float: left;
        }

        .FirmenLogo {
            /* position of the div right */
            float: right;

            position: absolute;
            right: 0;
            /* change this to get the right position for the logo */
            top: -56px;
            /* height: 100px; */
        }

        .FirmenLogo img {
            height: auto;
            width: 100%;
        }

        footer table {
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        footer table td {
            font-family: Arial, sans-serif;
            font-size: 10px;
            border: 0;
            line-height: 10px;
            text-align: center;
            vertical-align: bottom;
        }

        footer table td:first-child {
            text-align: left;
        }

        footer table td:last-child {
            text-align: right;
        }

        .Rechnungs-Tabelle table {
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        .Rechnungs-Tabelle table td {
            border: solid black 1.0pt;
            text-align: center;
            padding: 6.5pt 6.5pt 6.5pt 6.5pt;
        }
    </style>
    <title>MusterRechnung</title>
</head>

<body>
    <header>
        <div class="ÜberschriftFirma">
            <p><u><span style='font-size:10pt;'>Firmenname - Firmenstr. 0 -
                        50000 Köln</span></u></p>
        </div>
        <div class="FirmenLogo">
            <img src="images/logo.jpg">
        </div>

    </header>

    <footer>
        <!-- Horizontal line -->
        <hr>
        <table>
            <tbody>
                <tr>
                    <td>Firmenname</td>
                    <td>Bank</td>
                    <td>+49 (0) 01234567891</td>
                    <td>www.firma.de</td>
                </tr>
                <tr>
                    <td>Inh. Vorname Nachname</td>
                    <td>Sparkasse KölnBonn</td>
                    <td>+49 (0) 012 /3456789</td>
                    <td>info@firma.de</td>
                </tr>
                <tr>
                    <td>Firmenstr. 0 - 50000 Köln</td>
                    <td>DE00000000000000000000</td>
                    <td></td>
                    <td>St.-Nr.: 000/0000/0000</td>
                </tr>
            </tbody>
        </table>
    </footer>

    <main>
        <div class="content">

            <div class="Kontaktinformationen">
                <p> <?php displayStringBR($FirmenName);
                    displayStringBR($Ansprechpartner);
                    displayStringBR($Adresse);
                    echo $PLZ . " " . $Ort ?>
                </p>
                <!-- <p>{{ FirmenName }} <br> {{ Name_Ansprechpartner }} <br> {{ Adresse }} <br> {{ PLZ }} {{ Ort }}</p> -->
            </div>
            <div class="RechnungsDatum">
                <p style="text-align:right;">Rechnungsdatum: <?php echo $Rechnungsdatum; ?></p>
            </div>
            <div class="RechnungsNr">
                <br>
                <p><strong>RECHNUNG NR.: <?php displayString($RechnungsKürzelNummer); ?></strong><br><span style="font-size: 10pt;">Bitte bei
                        Zahlungen angeben</span></p>
            </div>

            <div class="Anrede">
                <br>
                <p><?php displayString($Anrede); ?></p>
                <p>hiermit übersenden wir Ihnen die Rechnung v. <?php displayString($RechnungsMonatJahr); ?> <?php displayVertragsdatum($VertragsDatum); ?> für folgende
                    Leistungen:</p>
            </div>

            <div class="Rechnungs-Tabelle">
                <table>

                    <tbody>
                        <tr>
                            <td style="text-align: left;">Leistung</td>
                            <td style="width: 100px;">Stunden</td>
                            <td style="width: 100px;">Abrechnungsart</td>
                            <td style="width: 140px;">Preis ohne MwSt.</td>
                        </tr>

                        <!-- Leistung and the given values -->
                        <?php

                        for ($i = 0; $i < count($nettoPreis); $i++) {
                            echo "<tr>";
                            echo "<td style='text-align: left;'>{$Leistung[$i]}</td>";
                            if ($AbrechnungsartList[$i] == "Pauschal") {
                                echo "<td></td>";
                                echo "<td>{$AbrechnungsartList[$i]}</td>";
                            } else {
                                echo "<td>{$AbrechnungsartList[$i]} Std.</td>";
                                echo "<td></td>";
                            }
                            echo "<td>{$nettoPreis[$i]} Euro</td>";
                            echo "</tr>";
                            $gesamtNettoPreis += $nettoPreis[$i];
                            $gesamtBetragMwSt += $MwStArray[$i];
                            $gesamtBetragBrutto += $GesamtBetragArray[$i];
                        }

                        // If there is more than one "Leistung", the total Netto amount will be added together
                        if (count($nettoPreis) > 1) {
                            echo "<tr>";
                            echo "<td colspan='3' style='text-align: left;'>";
                            echo "Gesamtbetrag Netto";
                            echo "</td>";
                            echo "<td>";
                            echo $gesamtNettoPreis . " Euro";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>


                        <tr>
                            <td colspan="3" style="text-align: left;">
                                Zzgl. Gesetzlicher Mehrwertsteuer {{ ProzentMwSt }}
                            </td>
                            <td>
                                <?php displayString($gesamtBetragMwSt); ?> Euro
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: left;">
                                <strong>Gesamtbetrag inkl. MwSt.</strong>
                            </td>
                            <td>
                                <strong><?php displayString($gesamtBetragBrutto); ?> Euro</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="Zahlungsaufforderung">
                <p>Wir möchten Sie bitten, den Rechnungsbetrag in Höhe von <?php displayString($gesamtBetragBrutto); ?> Euro innerhalb von 10 Tage nach
                    Rechnungszustellung auf unser unten genanntes Bankonto zu überweisen.
                </p>
                <br>
                <p>Mit freundlichen Grüßen <br> Vorname Nachname</p>
            </div>

        </div>

    </main>
</body>

</html>

<?php
header("Location: generate-pdf.php");
// require __DIR__ . "/vendor/autoload.php";

// use Dompdf\Dompdf;
// use Dompdf\Options;

// /**
//  * Set the Dompdf options
//  */
// $options = new Options;
// $options->setChroot(__DIR__);
// $options->setIsRemoteEnabled(true);

// $dompdf = new Dompdf($options);

// /**
//  * Set the paper size and orientation
//  */
// $dompdf->setPaper("A4", "portrait");

// /**
//  * Load the HTML and replace placeholders with values from the form
//  */
// $html = file_get_contents("invoiceMuster.php");

// $dompdf->loadHtml($html);

// /**
//  * Create the PDF and set attributes
//  */
// $dompdf->render();

// $dompdf->addInfo("Title", "An Example PDF"); // "add_info" in earlier versions of Dompdf

// /**
//  * Send the PDF to the browser
//  */
// $dompdf->stream("invoice.pdf", ["Attachment" => 0]);

// $pdf_output = $dompdf->output();
// file_put_contents('output.pdf', $pdf_output);
?>