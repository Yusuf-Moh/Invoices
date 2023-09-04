<?php
session_start();
unset($_SESSION["benutzername-Login"]);
header("location: login.php");
?>