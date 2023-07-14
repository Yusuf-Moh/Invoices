<?php
function reset_vars(){

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

    global $Ansprechpartner_Person, $Adresse_Person, $rechnungsKuerzel_Person, $PLZ_Person, $Ort_Person, $Vertragsdatum_Person, $gender_Person;
    $Ansprechpartner_Person = null;
    $Adresse_Person = null;
    $rechnungsKuerzel_Person = null;
    $PLZ_Person = null;
    $Ort_Person = null;
    $Vertragsdatum_Person = null;
    $gender_Person = null;
    
    global $sql_query;
    $sql_query = "SELECT * FROM `kunden`";

    global $message, $messageType, $showMessage;
    $message = "";
    $messageType = "";
    $showMessage = "";
    
    global $saveUpdate_Organization, $saveUpdate_Person;
    $saveUpdate_Organization = "saveOrganization";
    $saveUpdate_Person = "savePerson";
}


//reset of every variables.
reset_vars();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['button'])) {
        $action = $_POST['button'];
        switch ($action) {
            case 'saveOrganization':
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
                    $insertOrganizationDataIntoJS = false;
                } elseif (checkIfValueExists('FirmenName', $firmenName_organization) && checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_organization)) {
                    $messageType = "error";
                    $message = "Fehler: Firmenname und Rechnungskürzel existieren bereits in der Datenbank.";
                    $insertOrganizationDataIntoJS = true;
                } elseif (checkIfValueExists('FirmenName', $firmenName_organization)) {
                    $messageType = "error";
                    $message = "Fehler: Firmenname existiert bereits in der Datenbank.";
                    $insertOrganizationDataIntoJS = true;
                } elseif (checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_organization)) {
                    $messageType = "error";
                    //Extension: Which company the existing Rechnungskürzel was assigned to for $message
                    $message = "Fehler: Rechnungskürzel exisitiert bereits in der Datenbank.";
                    $insertOrganizationDataIntoJS = true;
                }
        
                if ($insertOrganizationDataIntoJS) {
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
                }
                $showMessage = "flex";

                break;
            
            case 'savePerson':
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
                    $insertPersonDataIntoJS = false;
                } else if (checkIfValueExists('RechnungsKürzel', $rechnungsKuerzel_Person)) {
                    $messageType = "error";
                    $message = "Fehler: Rechnungskürzel exisitiert bereits in der Datenbank.";
                    $insertPersonDataIntoJS = true;
                }
        
                if ($insertPersonDataIntoJS) {
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
                }
                $showMessage = "flex";
                break;

            case 'edit':
                echo  $_POST['KundenID']; //gibt kundenid vom entsprechenden clicked btn
                // $messageType = "edit";
                // $message = "Editieren Sie Ihre Daten";

                // query = Select * from Kunden where KundenID ....
                // spalte Organization und person sollen überprüft werden, welche von denen true sind. Nur eins von beiden kann true sein.
                // zwei if abfragen, in welches Modal (organization oder Person) anzeigt werden soll. Die Daten sollen in das Modal eingefügt werden. Anschließend soll der Button Senden zu Update umgeändert werden.
                // saveUpdate_Person und Organization müssen also einen anderen wert bekommen zb UpdatePerson oder so.
                // dann in Update soll überprüft werden ob es die geänderten werte bereits gibt in der DB. Wichtig zu beachten ist das der nicht geänderte wert bei der überprüfung nicht mit eingeschlossen werden soll
                break;
            
            case 'update':
                break;
            
            case 'search':
                //sql_query umändern
                break;

            case 'delete':
                break;
        }
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

        // header("Refresh:0");
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

        // header("Refresh:0");
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

                            <button type="submit" name="button" value = "<?php echo $saveUpdate_Organization; ?>" class="sendNewContactData-Btn" id="organizationSubmitBtn"><?php if($saveUpdate_Organization == "saveOrganization"){echo "Senden";}?></button>
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

                            <button type="submit" name="button" value = "<?php echo $saveUpdate_Person; ?>" class="sendNewContactData-Btn"><?php if($saveUpdate_Person == "savePerson"){echo "Senden";} ?></button>
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
                        include('../dbPhp/dbOpenConnection.php'); // dbConnection open
                        $stmt = $conn->prepare($sql_query);
                        $stmt->execute();
                        $result = $stmt->fetchAll();
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
                                <td><?php
                                    if ($row['Gender'] == "M") {
                                        echo htmlspecialchars("Male");
                                    } elseif ($row['Gender'] == "F") {
                                        echo htmlspecialchars("Female");
                                    } else {
                                        echo htmlspecialchars($row['Gender']);
                                    }
                                    ?></td>
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
    <script src="./index.js"></script>
</body>

</html>