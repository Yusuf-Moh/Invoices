<?php

// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../../loginSystem/checkLogin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

$RechnungsID = $_POST['RechnungsID'];
// Storing the value of the checkbox MonatlicheRechnung 
$monatlicheRechnung = "0";
if (isset($_POST['monatlicheRechnung'])) {
    $monatlicheRechnung = "1";
}
include('../../dbPhp/dbOpenConnection.php');

$query = "SELECT MonatlicheRechnungBool from rechnung WHERE RechnungsID = :rechnungsID;";
$stmt = $conn->prepare($query);

$stmt->bindParam(':rechnungsID', $RechnungsID);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

$monatlicheRechnung_old = $result['MonatlicheRechnungBool'];


$query = "UPDATE rechnung SET MonatlicheRechnungBool = :monatlicheRechnung WHERE RechnungsID = :rechnungsID;";
$stmt = $conn->prepare($query);

$stmt->bindParam(':monatlicheRechnung', $monatlicheRechnung);
$stmt->bindParam(':rechnungsID', $RechnungsID);
$stmt->execute();

// monatlicheRechnung from 1 (checked) to 0 (unchecked): Delete Data-Record from MonatlicheRechnung Table 
if ($monatlicheRechnung_old == "1" && $monatlicheRechnung == "0") {
    $query = "DELETE FROM monatliche_rechnung WHERE RechnungsID = :rechnungsID;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':rechnungsID', $RechnungsID);
    $stmt->execute();
}
// monatlicheRechnung from 0 (unchecked) to 1 (checked): Insert into MonatlicheRechnung Table 
else if ($monatlicheRechnung_old == "0" && $monatlicheRechnung == "1") {
    $query = "INSERT INTO monatliche_rechnung (RechnungsID) VALUES (:rechnungsID);";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':rechnungsID', $RechnungsID);
    $stmt->execute();
}

include('../../dbPhp/dbCloseConnection.php');

header("location: ../invoice.php");
}else{
    header("location: ../invoice.php");
}