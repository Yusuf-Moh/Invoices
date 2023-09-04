<?php
session_start();

if (!isset($_SESSION["benutzername-Login"])) {
    header("location: ../loginSystem/login.php");
}
?>