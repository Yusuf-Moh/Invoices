<?php
// Session Start and check for a current Login
// Otherwise, you will get redirected to the login page
include "../loginSystem/checkLogin.php";

include "../dbPhp/dbOpenConnection.php";

// Umsatz
$sql = "SELECT SUBSTRING_INDEX(Monat_Jahr, ' ', -1) AS Jahr,
SUM(CASE WHEN bezahlt = 1 THEN CAST(REPLACE(REPLACE(GesamtBetrag, '.', ''), ',', '.') AS DECIMAL(10, 2)) ELSE 0 END) AS BezahltBetrag,
SUM(CASE WHEN bezahlt = 0 THEN CAST(REPLACE(REPLACE(GesamtBetrag, '.', ''), ',', '.') AS DECIMAL(10, 2)) ELSE 0 END) AS NichtBezahltBetrag,
SUM(CAST(REPLACE(REPLACE(GesamtBetrag, '.', ''), ',', '.') AS DECIMAL(10, 2))) AS GesamtBetrag
FROM
rechnung
GROUP BY
SUBSTRING_INDEX(Monat_Jahr, ' ', -1)
ORDER BY
Jahr;";
$stmt = $conn->prepare($sql);
$stmt->execute();
global $Umsatz;
$Umsatz = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ausgaben; Feature in Bearbeitung

// Offene Rechnungen
$sql = 'SELECT SUBSTRING_INDEX(Monat_Jahr, " ",-1) as Jahr,
        SUM(CASE WHEN Bezahlt = 0 THEN 1 ELSE 0 END) AS AnzahlNichtBezahlt,
        SUM(CASE WHEN Bezahlt = 1 THEN 1 ELSE 0 END) AS AnzahlBezahlt,
        COUNT(*) as AnzahlRechnungen
        FROM `rechnung` GROUP by Jahr;';
$stmt = $conn->prepare($sql);
$stmt->execute();
global $offeneRechnung;
$offeneRechnung = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fällige Rechnungen
$sql = "SELECT Monat_Jahr,
    COUNT(*) AS AnzahlFaelligeRechnungen
    FROM rechnung
    WHERE Bezahlt = 0 AND DATEDIFF(NOW(), STR_TO_DATE(RechnungsDatum, '%d.%m.%Y')) > 10
    GROUP BY Monat_Jahr
    ORDER BY ConvertGermanDateToDate(Monat_Jahr) ASC;";

$stmt = $conn->prepare($sql);
$stmt->execute();
global $faelligeRechnungen;
$faelligeRechnungen = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Umsatzentwicklung:

# Rausfiltern von allen UmsatzJahren
$sql = 'SELECT SUBSTRING_INDEX(Monat_Jahr, " ", -1) AS UmsatzJahr FROM rechnung GROUP BY UmsatzJahr ASC;';
$stmt = $conn->prepare($sql);
$stmt->execute();
global $UmsatzJahr;
$UmsatzJahr = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Umsatzdaten von dem jeweiligen Jahr
// Werte: UmsatzMonat & GesamtUmsatzMonat

global $UmsatzDaten;
$Umsatzdaten = array();

$sql = 'SELECT SUBSTRING_INDEX(Monat_Jahr, " ", 1) AS UmsatzMonat, SUM(CAST(REPLACE(REPLACE(GesamtBetrag, ".", ""), ",", ".") AS DECIMAL(10, 2))) AS GesamtUmsatzMonat
        FROM rechnung 
        WHERE SUBSTRING_INDEX(Monat_Jahr, " ", -1) = :UmsatzJahr
        GROUP BY UmsatzMonat
        ORDER by MONTH(UmsatzMonat);';

foreach ($UmsatzJahr as $row) {
    $Jahr = $row['UmsatzJahr'];
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':UmsatzJahr', $Jahr, PDO::PARAM_STR);
    $stmt->execute();

    $UmsatzDaten[$Jahr] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


include "../dbPhp/dbCloseConnection.php";


// Function to convert numbers 1000.00 to 1.000,00
function prizeFormat($prize)
{
    $prize = number_format($prize, 2, ',', '.');
    return $prize;
}

// If the Value is higher then 1, then the color danger(red) should be added to the given text. Otherwise the color should be success (green)
function addColor($number)
{
    if ($number > 0) {
        return ' class= "danger"';
    } else {
        return ' class = "success"';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="dashboard.css">
    <!--Link to Material Icons-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">

    <!-- ChartJS for a DoughnutChart and Graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <header> <?php include '../NavBar/navbar.html'; ?></header>
    <div class="container">
        <div class="dashboard">
            <!-- Content of the Dashboard -->
            <div class="dashboard-top">
                <!-- Content of dashboard-top -->
                <div class="Umsatz-Ausgaben">
                    <div class="Umsatz">
                        <div class="Header">
                            <h2>Umsatz nach Status</h2>
                            <select name="UmsatzJahr" id="UmsatzJahr">
                                <?php
                                foreach ($Umsatz as $row) {
                                ?>
                                    <option value="<?php echo $row['Jahr']; ?>" <?php echo 'data-BezahltBetrag = "' . prizeFormat($row['BezahltBetrag']) . '"';
                                                                                echo  ' data-NichtBezahltBetrag ="' . prizeFormat($row['NichtBezahltBetrag']) . '"';
                                                                                echo ' data-GesamtBetrag ="' . prizeFormat($row['GesamtBetrag']) . '"';
                                                                                echo addColor($row['NichtBezahltBetrag']); ?>>
                                        <?php echo $row['Jahr']; ?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="UmsatzContainer">
                            <div class="UmsatzRow">
                                <h3 class="warning">Offen</h3>
                                <h3 class="UmsatzOffen" id="UmsatzOffen"></h3>
                            </div>
                            <div class="UmsatzRow">
                                <h3 class="success">Bezahlt</h3>
                                <h3 class="UmsatzBezahlt" id="UmsatzBezahlt"></h3>
                            </div>
                            <div class="UmsatzRow">
                                <h3>Summe</h3>
                                <h3 class="UmsatzSumme" id="UmsatzSumme">Keinen Umsatz generiert</h3>
                            </div>
                        </div>
                    </div>

                    <div class="Ausgaben">
                        <div class="Header">
                            <h2>Ausgaben; <br>Feature in Bearbeitung</h2>
                            <select name="" id="">
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                        <div class="AusgabenContainer">
                            <div class="AusgabenRow">
                                <h3>Juni</h3>
                                <h3>10.000 Euro</h3>
                            </div>
                            <div class="AusgabenRow">
                                <h3>Juni</h3>
                                <h3>10.000 Euro</h3>
                            </div>
                            <div class="AusgabenRow">
                                <h3>Juni</h3>
                                <h3>10.000 Euro</h3>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="OffeneRechnung-FaelligeRechnung-Belege">
                    <div class="OffeneRechnung">
                        <div class="Header">
                            <h2>Offene Rechnungen</h2>
                        </div>
                        <div class="OffeneRechnungContainer">
                            <table class="table-OffeneRechnung">
                                <thead>
                                    <th class="cell-left">Jahr</th>
                                    <th class="cell-center warning">Offen</th>
                                    <th class="cell-center success">Bezahlt</th>
                                    <th class="cell-center">Summe</th>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($offeneRechnung)) {
                                        foreach ($offeneRechnung as $row) {
                                    ?>
                                            <tr>
                                                <td class="cell-left"><?php echo $row['Jahr']; ?></td>
                                                <td class="cell-center"><?php echo $row['AnzahlNichtBezahlt'] ?></td>
                                                <td class="cell-center"><?php echo $row['AnzahlBezahlt'] ?></td>
                                                <td class="cell-center"><?php echo $row['AnzahlRechnungen'] ?></td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>

                                        <tr>
                                            <td colspan="4">Keine erstellten Rechnungen vorhanden</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="FaelligeRechnung">
                        <div class="Header">
                            <h2>Fällige Rechnungen</h2>
                        </div>
                        <div class="FaelligeRechnungContainer">
                            <?php foreach ($faelligeRechnungen as $row) {
                            ?>
                                <div class="FaelligeRechnungRow">
                                    <div class="MonatJahr"><?php echo $row['Monat_Jahr']; ?></div>
                                    <div class="AnzahlFaelligeRechnung"><?php echo $row['AnzahlFaelligeRechnungen']; ?></div>
                                </div>
                            <?php
                            }
                            ?>

                        </div>
                    </div>
                    <div class="Belege">

                    </div>
                </div>

                <div class="Angebotsstatistik">
                    <div class="Header">
                        <h2>Angebotsstatistik; Feature in Bearbeitung</h2>
                        <select name="" id="">
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <div class="AngebotsstatistikContainer">
                        <div class="DoughnutCircle-Angebotsstatistik">
                            <canvas id="DoughnutCircle" width="260" height="260"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Content of dashboard-bottom -->
            <div class="dashboard-bottom">
                <div class="Umsatzentwicklung">
                    <div class="Header">
                        <h2>Umsatzentwicklung</h2>
                        <!-- Selection for the given UmsatzJahr -->
                        <select name="UmsatzentwicklungJahr" id="UmsatzentwicklungJahr">
                            <?php
                            foreach ($UmsatzJahr as $row) {
                                $Jahr = $row['UmsatzJahr'];
                                $UmsatzDatenJahr = $UmsatzDaten[$Jahr];

                                $UmsatzDatenJahrJSON = json_encode($UmsatzDatenJahr);

                            ?>
                                <option value="<?php echo $Jahr; ?>" data-umsatzdaten='<?php echo htmlspecialchars_decode($UmsatzDatenJahrJSON); ?>'>
                                    <?php echo $Jahr; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>

                    </div>
                    <div class="UmsatzentwicklungContainer">
                        <canvas class="UmsatzentwicklungChart" id="UmsatzentwicklungChart"></canvas>
                    </div>
                </div>
                <div class="FaelligeAngebote">
                    <div class="Header">
                        <h2>FälligeAngebote</h2>
                        <h2>Feature in Bearbeitung</h2>
                    </div>
                    <div class="FaelligeAngeboteContainer">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Daten für den DoughnutCircle
        var dataChart = {
            labels: ['Gewonnen', 'Offen', 'Verloren'],
            datasets: [{
                label: '# von Angeboten',
                data: [5, 3, 2],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(255, 99, 132, 0.5)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255,99,132,1)'
                ],
                borderWidth: 2,
                datalabels: {
                    color: 'green'
                }
            }]
        };

        // Berechnung der insgesamten Anzahl der Angebote
        var totalAngebote = 0;
        for (var i = 0; i < dataChart.datasets[0].data.length; i++) {
            totalAngebote += dataChart.datasets[0].data[i];
        }

        // Insgesamte Anzahl an Angeboten in der Mitte des DoughnutCircle
        const doughnutLabel = {
            id: 'DoughnutCircle',
            beforeDatasetsDraw(chart, args, pluginOptions) {
                const {
                    ctx,
                    data
                } = chart;

                ctx.save();
                const xCoord = chart.getDatasetMeta(0).data[0].x;
                const yCoord = chart.getDatasetMeta(0).data[0].y;
                ctx.font = 'bold 30px poppins';
                ctx.fillStyle = 'rgba(255,99,132,1)';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(totalAngebote, xCoord, yCoord);
            }
        };

        // Anzahl des Zustands wird in dem jeweiligen Bereich angezeigt
        const customDataLabels = {
            id: 'customDataLabels',
            afterDatasetsDraw(chart, args, pluginOptions) {
                const {
                    ctx,
                    data,
                    chartArea: {
                        top,
                        bottom,
                        left,
                        right,
                        width,
                        height
                    }
                } = chart;
                ctx.save();

                data.datasets[0].data.forEach((datapoint, index) => {
                    const {
                        x,
                        y
                    } = chart.getDatasetMeta(0).data[index].tooltipPosition();

                    ctx.font = 'bold 12px sans-serif';
                    ctx.fillStyle = data.datasets[0].borderColor[index];
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(datapoint, x, y);
                });
            }
        };

        // DoughnutCircle wird erstellt
        var promisedDeliveryChart = new Chart(document.getElementById('DoughnutCircle'), {
            type: 'doughnut',
            data: dataChart,
            options: {
                responsive: false,
                legend: {
                    display: true
                },
            },
            plugins: [doughnutLabel, customDataLabels]
        });



        const UmsatzJahr = document.getElementById('UmsatzJahr');

        const UmsatzOffen = document.getElementById('UmsatzOffen');
        const UmsatzBezahlt = document.getElementById('UmsatzBezahlt');
        const UmsatzSumme = document.getElementById('UmsatzSumme');

        function aktualisiereUmsatzDaten() {
            const ausgewähltesJahr = UmsatzJahr.options[UmsatzJahr.selectedIndex];

            const bezahltBetrag = ausgewähltesJahr.getAttribute('data-BezahltBetrag');
            const nichtBezahltBetrag = ausgewähltesJahr.getAttribute('data-NichtBezahltBetrag');
            const gesamtBetrag = ausgewähltesJahr.getAttribute('data-GesamtBetrag');

            UmsatzOffen.textContent = nichtBezahltBetrag + ' Euro';
            UmsatzBezahlt.textContent = bezahltBetrag + ' Euro';
            UmsatzSumme.textContent = gesamtBetrag + ' Euro';

            if (ausgewähltesJahr.classList.contains("danger")) {
                UmsatzJahr.classList.add("danger");
                UmsatzJahr.classList.remove("success");
            } else {
                UmsatzJahr.classList.remove("danger");
                UmsatzJahr.classList.add("success");
            }
        }

        UmsatzJahr.addEventListener('change', aktualisiereUmsatzDaten);
        // If there is any options available, execute the function, to get the first data into the Rows
        if (UmsatzJahr.options.length > 0) {
            aktualisiereUmsatzDaten();
        }

        // ================ Linear Chart for the Umsatzentwicklung ================
        let chartUmsatzEntwicklung = null;
        var months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

        var dataUmsatzentwicklungChart = {
            labels: months,
            datasets: [{
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                label: 'Umsatz',
                borderColor: '#98FB98',
                borderWidth: 3
            }]
        };

        function createBaseLinearChart() {
            const ctx = document.getElementById('UmsatzentwicklungChart').getContext('2d');

            chartUmsatzEntwicklung = new Chart(ctx, {
                type: 'line',
                data: dataUmsatzentwicklungChart,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateLinearChart(UmsatzDaten) {
            // Reset the datasets
            for (var i = 0; i < 12; i++) {
                chartUmsatzEntwicklung.data.datasets[0].data[i] = 0;
            }

            // Elements of the given Array
            for (var i = 0; i < UmsatzDaten.length; i++) {
                // 12 Months              
                for (var j = 0; j < 12; j++) {
                    // Add the data from the given Month to the dataset
                    if (UmsatzDaten[i].UmsatzMonat == months[j]) {
                        chartUmsatzEntwicklung.data.datasets[0].data[j] = parseNumberWithCommas(UmsatzDaten[i].GesamtUmsatzMonat);
                    }
                }
            }
            chartUmsatzEntwicklung.update();

        }

        function createLinearChart() {
            // Destroy the existing Chart, to create a new one specialized for the selected year
            if (chartUmsatzEntwicklung) {
                chartUmsatzEntwicklung.destroy();
            }

            const selectedOption_UmsatzEntwicklung = selectElement_UmsatzEntwicklung.options[selectElement_UmsatzEntwicklung.selectedIndex];
            const umsatzdaten = JSON.parse(selectedOption_UmsatzEntwicklung.getAttribute('data-umsatzdaten'));

            createBaseLinearChart();
            updateLinearChart(umsatzdaten);

        }

        function parseNumberWithCommas(string) {
            return parseFloat(string.replace(/,/g, '').replace('.', '.'));
        }

        // Event-Listener für Auswahl des Jahres
        const selectElement_UmsatzEntwicklung = document.getElementById('UmsatzentwicklungJahr');
        selectElement_UmsatzEntwicklung.addEventListener('change', createLinearChart);

        if (selectElement_UmsatzEntwicklung.options.length > 0) {
            createLinearChart();
        } else {
            const canvasElement = document.getElementById('UmsatzentwicklungChart');
            canvasElement.remove();

            const messageElement = document.createElement('h1');
            messageElement.textContent = 'Keine Umsatzdaten verfügbar.';

            const containerElement = document.querySelector('.UmsatzentwicklungContainer');
            containerElement.appendChild(messageElement);
        }
    </script>
</body>

</html>