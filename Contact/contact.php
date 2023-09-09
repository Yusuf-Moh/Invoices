<?php
// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../loginSystem/checkLogin.php";

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

    global $Ansprechpartner_Person, $Adresse_Person, $rechnungsKuerzel_Person, $PLZ_Person, $Ort_Person, $Vertragsdatum_Person, $gender_Person;
    $Ansprechpartner_Person = null;
    $Adresse_Person = null;
    $rechnungsKuerzel_Person = null;
    $PLZ_Person = null;
    $Ort_Person = null;
    $Vertragsdatum_Person = null;
    $gender_Person = null;

    global $sql_query;
    // $sql_query = "SELECT * FROM `kunden`";

    global $param;
    // $param = [];

    global $message, $messageType, $showMessage;
    $message = "";
    $messageType = "";
    $showMessage = "";

    global $saveUpdate_Organization, $saveUpdate_Person;
    $saveUpdate_Organization = "saveOrganization";
    $saveUpdate_Person = "savePerson";

    global $modalHeadline;
    $modalHeadline = "Erstelle Kontakt";

    global $showMessage;
    $showMessage = "none";
}

global $Firmenname_StateSearchButton, $Adresse_StateSearchButton, $RechnungsKürzel_StateSearchButton, $PLZ_StateSearchButton, $Ort_StateSearchButton, $Vertragsdatum_StateSearchButton, $Ansprechpartner_StateSearchButton, $Gender_StateSearchButton;

global $restart;
$restart = false;

setSessionVariableFalse('Firmenname_StateSearchButton');
setSessionVariableFalse('Adresse_StateSearchButton');
setSessionVariableFalse('RechnungsKürzel_StateSearchButton');
setSessionVariableFalse('PLZ_StateSearchButton');
setSessionVariableFalse('Ort_StateSearchButton');
setSessionVariableFalse('Vertragsdatum_StateSearchButton');
setSessionVariableFalse('Ansprechpartner_StateSearchButton');
setSessionVariableFalse('Gender_StateSearchButton');

if (!isset($_SESSION['sql_query'])) {
    $_SESSION['sql_query'] = "SELECT * FROM `kunden`";
    $restart = true;
}

if (!isset($_SESSION['param'])) {
    $_SESSION['param'] = [];
    $restart = true;
}

// If there is a value for the color of the Search-Icon stored in Session then it should be written in the variable search_Color_Contact,
// Otherwise the session should be decleared the value black and the website should be restarted to get the information out of session to avoid errors
if (isset($_SESSION['search_Color_Contact'])) {
    $search_Color_Contact = $_SESSION['search_Color_Contact'];
} else {
    $_SESSION['search_Color_Contact'] = 'black';
    $restart = true;
}

if ($restart) {
    header("Refresh:0");
}

$Firmenname_StateSearchButton = $_SESSION['Firmenname_StateSearchButton'];
$Adresse_StateSearchButton = $_SESSION['Adresse_StateSearchButton'];
$RechnungsKürzel_StateSearchButton = $_SESSION['RechnungsKürzel_StateSearchButton'];
$PLZ_StateSearchButton = $_SESSION['PLZ_StateSearchButton'];
$Ort_StateSearchButton = $_SESSION['Ort_StateSearchButton'];
$Vertragsdatum_StateSearchButton = $_SESSION['Vertragsdatum_StateSearchButton'];
$Ansprechpartner_StateSearchButton = $_SESSION['Ansprechpartner_StateSearchButton'];
$Gender_StateSearchButton = $_SESSION['Gender_StateSearchButton'];

$sql_query = $_SESSION['sql_query'];
$param = $_SESSION['param'];

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
                if (isset($_POST['gender_organization'])) {
                    $gender_organization = $_POST['gender_organization'];
                } else {
                    $gender_organization = "";
                }


                //assigning null to the not required input fields if its empty, so the DB gets the value Null. 
                if ($Vertragsdatum_organization == "") {
                    $Vertragsdatum_organization = null;
                }
                if ($Ansprechpartner_organization == "") {
                    $Ansprechpartner_organization = null;
                }
                if ($gender_organization != "Male" && $gender_organization != "Female") {
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
                    insertPersonDataIntoKundenTable($Adresse_Person, $rechnungsKuerzel_Person, $PLZ_Person, $Ort_Person, $Vertragsdatum_Person, $Ansprechpartner_Person, $gender_Person);
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
                    $saveUpdate_Organization = "updateOrganization";

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
                } elseif ($result['person'] == '1') {
                    $saveUpdate_Person = "updatePerson";

                    $Ansprechpartner_Person = $result['Name_Ansprechpartner'];
                    $Adresse_Person = $result['Adresse'];
                    $rechnungsKuerzel_Person = $result['RechnungsKürzel'];
                    $PLZ_Person = $result['PLZ'];
                    $Ort_Person = $result['Ort'];
                    $Vertragsdatum_Person = $result['VertragsDatum'];
                    $gender_Person = $result['Gender'];

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

                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;

            case 'updateOrganization':

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
                if (isset($_POST['gender_organization'])) {
                    $updated_gender_organization = $_POST['gender_organization'];
                } else {
                    $updated_gender_organization = "";
                }

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

            case 'updatePerson':
                $KundenID = $_POST['kID'];
                include('../dbPhp/dbOpenConnection.php'); // dbConnection open

                $query = "SELECT * FROM Kunden WHERE KundenID = :KundenID";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(':KundenID', $KundenID, PDO::PARAM_INT);
                $stmt->execute();

                $result = [];
                $result = $stmt->fetch(PDO::FETCH_ASSOC);


                $Ansprechpartner_Person = $result['Name_Ansprechpartner'];
                $Adresse_Person = $result['Adresse'];
                $rechnungsKuerzel_Person = $result['RechnungsKürzel'];
                $PLZ_Person = $result['PLZ'];
                $Ort_Person = $result['Ort'];
                $Vertragsdatum_Person = $result['VertragsDatum'];
                $gender_Person = $result['Gender'];

                $updated_Ansprechpartner_Person = $_POST['Ansprechpartner_Person'];
                $updated_Adresse_Person = $_POST['Adresse_Person'];
                $updated_rechnungsKuerzel_Person = $_POST['rechnungsKuerzel_Person'];
                $updated_PLZ_Person = $_POST['PLZ_Person'];
                $updated_Ort_Person = $_POST['Ort_Person'];
                $updated_Vertragsdatum_Person = $_POST['Vertragsdatum_Person'];
                $updated_gender_Person = $_POST['gender_person'];

                if ($updated_Vertragsdatum_Person == "") {
                    $updated_Vertragsdatum_Person = null;
                }

                if (checkPersonDataChangedValues($Ansprechpartner_Person, $updated_Ansprechpartner_Person, $Adresse_Person, $updated_Adresse_Person, $rechnungsKuerzel_Person, $updated_rechnungsKuerzel_Person, $PLZ_Person, $updated_PLZ_Person, $Ort_Person, $updated_Ort_Person, $Vertragsdatum_Person, $updated_Vertragsdatum_Person, $gender_Person, $updated_gender_Person)) {

                    if ($rechnungsKuerzel_Person != $updated_rechnungsKuerzel_Person) {
                        if (!checkIfValueExists('RechnungsKürzel', $updated_rechnungsKuerzel_Person)) {
                            $messageType = "success";
                            $message = "Daten wurden erfolgreich bearbeitet!";
                            updatePersonDataIntoKundenTable($KundenID, $updated_Adresse_Person, $updated_rechnungsKuerzel_Person, $updated_PLZ_Person, $updated_Ort_Person, $updated_Vertragsdatum_Person, $updated_Ansprechpartner_Person, $updated_gender_Person);
                        } else {
                            $messageType = "errorUpdate";
                            $message = "Fehler: Geänderter Rechnungskürzel ($updated_rechnungsKuerzel_Person) exisitiert bereits in der Datenbank. ";
                        }
                    } else {
                        $messageType = "success";
                        $message = "Daten wurden erfolgreich bearbeitet!";
                        updatePersonDataIntoKundenTable($KundenID, $updated_Adresse_Person, $updated_rechnungsKuerzel_Person, $updated_PLZ_Person, $updated_Ort_Person, $updated_Vertragsdatum_Person, $updated_Ansprechpartner_Person, $updated_gender_Person);
                    }
                } // The data in the input fields match the data in the database
                else {
                    $messageType = "edit";
                    $message = "Daten wurden nicht geändert!";
                }

                if ($messageType == "errorUpdate") {
                    echo "<script>";
                    echo "var messageType = '$messageType';";
                    echo "</script>";
                }
                $showMessage = "flex";
                include('../dbPhp/dbCLoseConnection.php'); // dbConnection close
                break;


            case 'Search_FirmenName':
                $_SESSION['Firmenname_StateSearchButton'] = stateSearchButton($Firmenname_StateSearchButton);
                $Firmenname_StateSearchButton = $_SESSION['Firmenname_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Adresse':
                $_SESSION['Adresse_StateSearchButton'] = stateSearchButton($Adresse_StateSearchButton);
                $Adresse_StateSearchButton = $_SESSION['Adresse_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_RechnungsKürzel':
                $_SESSION['RechnungsKürzel_StateSearchButton'] = stateSearchButton($RechnungsKürzel_StateSearchButton);
                $RechnungsKürzel_StateSearchButton = $_SESSION['RechnungsKürzel_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_PLZ':
                $_SESSION['PLZ_StateSearchButton'] = stateSearchButton($PLZ_StateSearchButton);
                $PLZ_StateSearchButton = $_SESSION['PLZ_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Ort':
                $_SESSION['Ort_StateSearchButton'] = $Ort_StateSearchButton;
                $Ort_StateSearchButton = $_SESSION['Ort_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Vertragsdatum':
                $_SESSION['Vertragsdatum_StateSearchButton'] = stateSearchButton($Vertragsdatum_StateSearchButton);
                $Vertragsdatum_StateSearchButton = $_SESSION['Vertragsdatum_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Ansprechpartner':
                $_SESSION['Ansprechpartner_StateSearchButton'] = stateSearchButton($Ansprechpartner_StateSearchButton);
                $Ansprechpartner_StateSearchButton = $_SESSION['Ansprechpartner_StateSearchButton'];
                resetSearch();
                break;
            case 'Search_Gender':
                $_SESSION['Gender_StateSearchButton'] = stateSearchButton($Gender_StateSearchButton);
                $Gender_StateSearchButton = $_SESSION['Gender_StateSearchButton'];
                resetSearch();
                break;

            case 'search':
                reset_vars();
                $contentSearchbar = '%' . $_POST['Search-Input'] . '%';

                //If any of the Filter Buttons are active/clicked
                if ($_SESSION['Firmenname_StateSearchButton'] || $_SESSION['Adresse_StateSearchButton'] || $_SESSION['RechnungsKürzel_StateSearchButton'] || $_SESSION['PLZ_StateSearchButton'] || $_SESSION['Ort_StateSearchButton'] || $_SESSION['Vertragsdatum_StateSearchButton'] || $_SESSION['Ansprechpartner_StateSearchButton'] || $_SESSION['Gender_StateSearchButton']) {
                    $sql_query = "SELECT * FROM `kunden` WHERE";


                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Firmenname_StateSearchButton'], " Firmenname LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Adresse_StateSearchButton'], " Adresse LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['RechnungsKürzel_StateSearchButton'], " RechnungsKürzel LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['PLZ_StateSearchButton'], " PLZ LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Ort_StateSearchButton'], " Ort LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Vertragsdatum_StateSearchButton'], " Vertragsdatum LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Ansprechpartner_StateSearchButton'], " Name_Ansprechpartner LIKE :search_string OR");
                    $sql_query .= updateSearchQueryStateSearchButtons($_SESSION['Gender_StateSearchButton'], " Gender LIKE :search_string OR");

                    // Delete the last "OR" of the Query
                    $sql_query = rtrim($sql_query, "OR");
                } else {
                    $sql_query = "SELECT * FROM `kunden` WHERE Firmenname LIKE :search_string OR Adresse LIKE :search_string OR RechnungsKürzel LIKE :search_string OR PLZ LIKE :search_string OR Ort LIKE :search_string OR Vertragsdatum LIKE :search_string OR Name_Ansprechpartner LIKE :search_string OR Gender LIKE :search_string";
                }

                if ($_POST['Search-Input'] != "") {
                    $search_Color_Contact = "#F62217";
                } else {
                    $search_Color_Contact = 'black';
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
        $_SESSION['search_Color_Contact'] = $search_Color_Contact;
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

function insertPersonDataIntoKundenTable($Adresse, $rechnungsKuerzel, $PLZ, $Ort, $Vertragsdatum, $Ansprechpartner, $gender)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection open

    try {
        // Prepeare the INSERT-query
        $stmt = $conn->prepare("INSERT INTO kunden (Adresse, RechnungsKürzel, PLZ, Ort, VertragsDatum, Name_Ansprechpartner, Gender, person)
                               VALUES (:Adresse, :rechnungsKuerzel, :plz, :ort, :vertragsDatum, :ansprechpartner, :gender, 1)");

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


function updatePersonDataIntoKundenTable($id, $Adresse, $rechnungsKuerzel, $PLZ, $Ort, $Vertragsdatum, $Ansprechpartner, $gender)
{
    include('../dbPhp/dbOpenConnection.php'); // dbConnection öffnen

    try {
        // Prepare the UPDATE-Abfrage
        $stmt = $conn->prepare("UPDATE kunden SET Adresse = :Adresse, RechnungsKürzel = :rechnungsKuerzel, PLZ = :plz, Ort = :ort, VertragsDatum = :vertragsDatum, Name_Ansprechpartner = :ansprechpartner, Gender = :gender WHERE KundenID = :id");

        // Bind the variables on parameters
        $stmt->bindParam(':id', $id);
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

function notEqualString($string0, $string1)
{
    if ($string0 == $string1) {
        return false;
    } else {
        return true;
    }
}

function checkOrganizationDataChangedValues($firmenName_organization, $updated_firmenName_organization, $firmenAdresse_organization, $updated_firmenAdresse_organization, $rechnungsKuerzel_organization, $updated_rechnungsKuerzel_organization, $PLZ_organization, $updated_PLZ_organization, $Ort_organization, $updated_Ort_organization, $Vertragsdatum_organization, $updated_Vertragsdatum_organization, $Ansprechpartner_organization, $updated_Ansprechpartner_organization, $gender_organization, $updated_gender_organization)
{
    if (notEqualString($firmenName_organization, $updated_firmenName_organization) || notEqualString($firmenAdresse_organization, $updated_firmenAdresse_organization) || notEqualString($rechnungsKuerzel_organization, $updated_rechnungsKuerzel_organization) || notEqualString($PLZ_organization, $updated_PLZ_organization) || notEqualString($Ort_organization, $updated_Ort_organization) || notEqualString($Vertragsdatum_organization, $updated_Vertragsdatum_organization) || notEqualString($Ansprechpartner_organization, $updated_Ansprechpartner_organization) || notEqualString($gender_organization, $updated_gender_organization)) {
        return true;
    } else {
        return false;
    }
}

function checkPersonDataChangedValues($Ansprechpartner_Person, $updated_Ansprechpartner_Person, $Adresse_Person, $updated_Adresse_Person, $rechnungsKuerzel_Person, $updated_rechnungsKuerzel_Person, $PLZ_Person, $updated_PLZ_Person, $Ort_Person, $updated_Ort_Person, $Vertragsdatum_Person, $updated_Vertragsdatum_Person, $gender_Person, $updated_gender_Person)
{
    if (notEqualString($Ansprechpartner_Person, $updated_Ansprechpartner_Person) || notEqualString($Adresse_Person, $updated_Adresse_Person) || notEqualString($rechnungsKuerzel_Person, $updated_rechnungsKuerzel_Person) || notEqualString($PLZ_Person, $updated_PLZ_Person) || notEqualString($Ort_Person, $updated_Ort_Person) || notEqualString($Vertragsdatum_Person, $updated_Vertragsdatum_Person) || notEqualString($gender_Person, $updated_gender_Person)) {
        return true;
    } else {
        return false;
    }
}

function setSessionVariableFalse($session)
{
    if (($_SESSION[$session] != 0 && $_SESSION[$session] != 1) || !isset($_SESSION[$session])) {
        $_SESSION[$session] = 0;
        global $restart;
        $restart = true;
    }
}

function stateSearchButton($currentState)
{
    if ($currentState) {
        return 0;
    } else {
        return 1;
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
    global $sql_query, $param, $search_Color_Contact;
    $sql_query = "SELECT * FROM `kunden`";
    $param = [];
    $search_Color_Contact = 'black';
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
    <title>Kontakt</title>

    <!--Link to Kontakt.css | Stylesheet-->
    <link rel="stylesheet" href="./contact.css">
    <!--Link to Material Icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">

</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="WebsiteHeadline">Kontakte</h1>
            <div class="header-search">

                <div class="search-container">
                    <form method="POST" class="search-form">
                        <div class="search">
                            <button type="submit" name="button" value="search" class="Search-Btn" id="searchButton"><span class="material-icons-sharp" style="color: <?php echo $search_Color_Contact; ?>">search</span></button>
                            <input type="search" id="search" name="Search-Input" class="Search-Input" placeholder="Search..." autocomplete="off">
                        </div>
                        <div class="buttons-container">
                            <div class="search-buttons">
                                <button type="submit" name="button" value="Search_FirmenName" class="<?php changeBackgroundSearchButton($Firmenname_StateSearchButton); ?>">FirmenName</button>
                                <button type="submit" name="button" value="Search_Adresse" class="<?php changeBackgroundSearchButton($Adresse_StateSearchButton); ?>">Adresse</button>
                                <button type="submit" name="button" value="Search_RechnungsKürzel" class="<?php changeBackgroundSearchButton($RechnungsKürzel_StateSearchButton); ?>">RechnungsKürzel</button>
                                <button type="submit" name="button" value="Search_PLZ" class="<?php changeBackgroundSearchButton($PLZ_StateSearchButton); ?>">PLZ</button>
                                <button type="submit" name="button" value="Search_Ort" class="<?php changeBackgroundSearchButton($Ort_StateSearchButton); ?>">Ort</button>
                                <button type="submit" name="button" value="Search_Vertragsdatum" class="<?php changeBackgroundSearchButton($Vertragsdatum_StateSearchButton); ?>">Vertragsdatum</button>
                                <button type="submit" name="button" value="Search_Ansprechpartner" class="<?php changeBackgroundSearchButton($Ansprechpartner_StateSearchButton); ?>">Ansprechpartner</button>
                                <button type="submit" name="button" value="Search_Gender" class="<?php changeBackgroundSearchButton($Gender_StateSearchButton); ?>">Gender</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
                    <h2><?php echo $modalHeadline; ?></h2>
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

                                <div class="male_female-container">

                                    <div class="male_organization">
                                        <input type="radio" name="gender_organization" id="male_organization" value="Male">
                                        <label for="male_organization">Male</label>
                                    </div>

                                    <div class="female_organization">
                                        <input type="radio" name="gender_organization" id="female_organization" value="Female">
                                        <label for="female_organization">Female</label>
                                    </div>
                                </div>


                                <div class="uncheck_gender">
                                    <button type="button" class="uncheck_gender_radioBtns" onclick="uncheck_gender_organization()">Uncheck</button>
                                </div>
                            </div>

                            <!-- Store KundenID in hidden Inputfield to get access in update Switch Case-->
                            <input type="hidden" name="kID" value="<?php echo htmlspecialchars($KundenID); ?>">

                            <button type="submit" name="button" value="<?php echo $saveUpdate_Organization; ?>" class="sendNewContactData-Btn" id="organizationSubmitBtn"><?php if ($saveUpdate_Organization == "saveOrganization") {
                                                                                                                                                                                echo "Senden";
                                                                                                                                                                            } elseif ($saveUpdate_Organization == "updateOrganization") {
                                                                                                                                                                                echo "Update";
                                                                                                                                                                            } ?></button>
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
                                <div class="male_female-container">

                                    <div class="male_person">
                                        <input type="radio" name="gender_person" id="male_Person" value="Male" required>
                                        <label for="male_Person">Male*</label>
                                    </div>

                                    <div class="female_person">
                                        <input type="radio" name="gender_person" id="female_Person" value="Female">
                                        <label for="female_Person">Female*</label>
                                    </div>
                                </div>
                                <div class="uncheck_gender">
                                    <button type="button" class="uncheck_gender_radioBtns" onclick="uncheck_gender_person()">Uncheck</button>
                                </div>
                            </div>

                            <!-- Store KundenID in hidden Inputfield to get access in update Switch Case-->
                            <input type="hidden" name="kID" value=<?php echo htmlspecialchars($KundenID); ?>>

                            <button type="submit" name="button" value="<?php echo $saveUpdate_Person; ?>" class="sendNewContactData-Btn"><?php if ($saveUpdate_Person == "savePerson") {
                                                                                                                                                echo "Senden";
                                                                                                                                            } elseif ($saveUpdate_Person == "updatePerson") {
                                                                                                                                                echo "Update";
                                                                                                                                            } ?></button>
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