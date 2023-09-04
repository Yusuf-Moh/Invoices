<?php
session_start();

include("../dbPhp/dbOpenConnection.php");
$message = "";

try {
    if (isset($_POST['login'])) {
        $benutzername = $_POST['Benutzername'];
        $passwort = $_POST['Passwort'];
        $passwort = md5($passwort);

        $query = "SELECT * FROM users WHERE Benutzername = :benutzername AND Passwort = :passwort;";
        $stmt = $conn->prepare($query);
        // BindParam benutzername und passwort
        $stmt->bindParam(':benutzername', $benutzername);
        $stmt->bindParam(':passwort', $passwort);
        $stmt->execute();
        $count = $stmt->rowCount();

        if ($count > 0) {
            $_SESSION["benutzername-Login"] = $_POST["Benutzername"];

            // Header to Invoice.php
            header("location: ../Invoice/invoice.php");
        } else {
            $message = "Wrong Username or Passwort";
        }
    }
} catch (PDOException $error) {
    $message = $error;
}

include("../dbPhp/dbCloseConnection.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="login">
            <div class="form-container">
                <div class="header">
                    <h3 class="login-Header">Login</h3>
                </div>

                <div class="message">
                    <label class="danger"><?php echo $message; ?></label>
                </div>
                <form method="POST">
                    <input type="text" name="Benutzername" placeholder="Benutzername" required>
                    <input type="password" name="Passwort" placeholder="Passwort" required>
                    <button type="submit" name="login" class="Login-Button">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>