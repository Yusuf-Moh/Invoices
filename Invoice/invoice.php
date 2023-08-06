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
                $RechnungsID = $_POST['RechnungsID'];
                $query = "SELECT * FROM Rechnung WHERE RechnungsID = :RechnungsID";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':RechnungsID', $RechnungsID, PDO::PARAM_INT);
                $stmt->execute();

                $result = [];
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Storing the data from the selected Rechnung into the inputfields of the modal

                $saveUpdate = "update";
                $showMessage = "flex";

                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;

            case 'update':
                
                //values of the DB
                $KundenID = $_POST['kID'];
                
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open

                $showMessage = "flex";
                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
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
                                <td><?php parseSerializedData($row['Abrechnungsart']); ?></td>
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

                // for (i = 0; i < editorArray.length; i++) {
                //     if (editorArray[i].getData().trim() == '') {
                //         allCkEditorFilled = false;
                //         break;
                //     }
                // }

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
                            allCkEditorFilled = false;
                            return; // Stop checking other rows once one empty editor is found
                        }
                    }
                }

                // all ckEditors are filled => reload website
                if (allCkEditorFilled) {
                    location.reload();
                }
            }
        }
    </script>
</body>

</html>