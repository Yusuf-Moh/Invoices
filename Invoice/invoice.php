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
}


global $restart;
$restart = false;


if ($_SESSION['sql_query_invoice'] == "") {
    $_SESSION['sql_query_invoice'] = "SELECT r.*, k.FirmenName, k.Adresse, k.PLZ, k.Ort, k.Name_Ansprechpartner FROM Rechnung r JOIN Kunden k ON r.KundenID = k.KundenID";
    $restart = true;
}

if ($_SESSION['param_invoice'] == "") {
    $_SESSION['param_invoice'] = [];
    $restart = true;
}

if ($restart) {
    header("Refresh:0");
}

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

                break;

            case 'search':
                reset_vars();
                $contentSearchbar = '%' . $_POST['Search-Input'] . '%';

                $param_invoice = ['search_string' => $contentSearchbar];
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
        $_SESSION['sql_query_invoice'] = $sql_query_invoice;
        $_SESSION['param_invoice'] = $param_invoice;
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
    if ($_SESSION[$session] != "false" && $_SESSION[$session] != "true") {
        $_SESSION[$session] = "false";
        $restart = true;
    }
}

function KundenInformationen($FirmenName, $Ansprechpartner, $Adresse, $PLZ, $Ort)
{
    $KontaktInformationen = displayStringBR($FirmenName) . displayStringBR($Ansprechpartner) . displayStringBR($Adresse) . $PLZ . " " . $Ort;

    return $KontaktInformationen;
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

function parseSerializedDataAbrechnungsart($serializedData)
{
    $dataArray = unserialize($serializedData);
    $lengthArray = count($dataArray);

    foreach ($dataArray as $index => $data) {

        if ($data != "Pauschal") {
            $data = $data . ' Stunden';
        }

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
                        <th>MonatlicheRechnung</th>
                        <!-- <th>RechnungsID</th> -->
                        <th>Action</th>
                    </thead>
                    <tbody>
                        <!-- PHP to load all rows of the contacts -->
                        <?php

                        foreach ($result as $row) {
                        ?>
                            <tr>
                                <td><?php echo KundenInformationen($row['FirmenName'], $row['Name_Ansprechpartner'], $row['Adresse'], $row['PLZ'], $row['Ort']); ?></td>
                                <td><?php parseSerializedDataLeistung($row['Leistung']); ?></td>
                                <td><?php parseSerializedDataAbrechnungsart($row['Abrechnungsart']); ?></td>
                                <td><?php parseSerializedData($row['NettoPreis']); ?></td>
                                <td><?php echo htmlspecialchars($row['MwSt']); ?></td>
                                <td><?php echo htmlspecialchars($row['GesamtBetrag']); ?></td>
                                <td><?php echo htmlspecialchars($row['RechnungsDatum']); ?></td>
                                <td><?php echo htmlspecialchars($row['Monat_Jahr']); ?></td>
                                <!-- <td><?php // echo htmlspecialchars($row['RechnungsNummer']); 
                                            ?></td> -->
                                <td><?php echo htmlspecialchars($row['RechnungsKürzelNummer']); ?></td>
                                <td><?php echo htmlspecialchars($row['MonatlicheRechnungBool']); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="RechnungsID" value=<?php echo htmlspecialchars($row['RechnungsID']); ?>>
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
    <!-- Rich Text Editor ckEditor 5 CustomBuild -->
    <script src="../ckeditor/build/ckeditor.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./index.js"></script>
</body>

</html>