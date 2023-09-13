# Invoices
1. XAMPP Installieren: Installiere XAMPP, um PHP und eine Datenbank (phpMyAdmin) zu verwenden. XAMPP Download Webseite: https://www.apachefriends.org/de/download.html
2. Repositorium Herunterladen: Lade das Repository als ZIP-Datei herunter und extrahiere es. Du erhältst den Ordner "Invoices-main".
3. XAMPP-Ordner: Stelle sicher, dass du XAMPP unter "C:\xampp" installiert hast.
4. Projekteordner Erstellen: Erstelle unter "C:\xampp\htdocs" einen Ordner namens "Projekte".
5. Repository Verschieben: Kopiere oder füge den Ordner "Invoices-main" in den Ordner Projekte aus Schritt 4 ein.
6. XAMPP Starten: Öffne das "XAMPP Control Panel" und starte die Module "Apache" und "MySQL".
7. Datenbank Importieren: Gehe im Browser zu "http://localhost/phpmyadmin/" und klicke auf "Importieren". Wähle die Datei "software_DatabaseTables" im Pfad "C:\xampp\htdocs\Projekte\Invoices-main\sql_file" aus und importiere sie.
8. Login: Wenn du "http://localhost/Projekte/Invoices-main/Invoice/invoice.php" öffnest, wirst du zur Login-Seite "login.php" weitergeleitet. Melde dich mit Benutzername "admin" und Passwort "admin" an. Nach dem Login solltest du erfolgreich auf Invoice.php weitergeleitet werden. Nun kannst du über der Navbar entweder die Webseite Kontakt besuchen, oder dich abmelden.
9. Pfad für Rechnungen: Du musst den Speicherpfad für Rechnungen in den Dateien "generate-pdf.php" und "generate-monatlicheRechnung-pdf.php" bearbeiten (Pfad zu den beiden Datein: C:\xampp\htdocs\Projekte\Invoices-main\Invoice\Generate-PDF). Finde die Zeile $UserPath = "C:/Users/yusuf/OneDrive/Desktop/Rechnung/"; (in Zeile 4) und ändere den Pfad entsprechend deinem Speicherort.
10. Wichtig: Achte darauf, dass beide Dateien denselben korrekten Pfad haben, um Fehler zu vermeiden.
11. Information: Du musst den Ordner 'Projekte' anlegen, da sonst das Formular von 'Invoice.php' zu 'generate-pdf.php' nach der Erstellung einer Rechnung über die Webseite nicht weitergeleitet wird. Weitere Informationen findest du im Quellcode der Datei 'index.js' im Ordner 'Invoice'.





