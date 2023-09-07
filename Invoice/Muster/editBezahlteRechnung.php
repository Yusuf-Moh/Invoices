<?php

// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../../loginSystem/checkLogin.php";


$RechnungsID = $_POST['RechnungsID'];
// Storing the value of the checkbox MonatlicheRechnung 
$monatlicheRechnung = "0";
if (isset($_POST['monatlicheRechnung'])) {
    $monatlicheRechnung = "1";
}
include('../../dbPhp/dbOpenConnection.php');

$query = "UPDATE rechnung SET MonatlicheRechnungBool = :monatlicheRechnung WHERE RechnungsID = :rechnungsID;";
$stmt = $conn->prepare($query);

$stmt->bindParam(':monatlicheRechnung', $monatlicheRechnung);
$stmt->bindParam(':rechnungsID', $RechnungsID);
$stmt->execute();

include('../../dbPhp/dbCloseConnection.php');

header("location: ../invoice.php");