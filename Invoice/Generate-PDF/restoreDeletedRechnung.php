<?php

// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../../loginSystem/checkLogin.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('../../dbPhp/dbOpenConnection.php');


    include('../../dbPhp/dbCloseConnection.php');
    header("location: ../invoice.php");
} else {
    header("location: ../invoice.php");
}
