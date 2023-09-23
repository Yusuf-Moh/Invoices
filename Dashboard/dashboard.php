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

// Ausgaben

include "../dbPhp/dbCloseConnection.php";
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
                                    <option value="<?php echo $row['Jahr']; ?>" <?php echo 'data-BezahltBetrag = "' . $row['BezahltBetrag'] . '"';
                                                                                echo  ' data-NichtBezahltBetrag ="' . $row['NichtBezahltBetrag'] . '"';
                                                                                echo ' data-GesamtBetrag ="' . $row['GesamtBetrag'] . '"'; ?>><?php echo $row['Jahr']; ?></option>
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
                            <h2>Ausgaben</h2>
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
                            <div class="OffeneRechnungRow">
                                <div class="Jahr">2022</div>
                                <div class="AnzahlOffeneRechnungen">20</div>
                            </div>
                            <div class="OffeneRechnungRow">
                                <div class="Jahr">2023</div>
                                <div class="AnzahlOffeneRechnungen">23</div>
                            </div>
                        </div>
                    </div>
                    <div class="FaelligeRechnung">
                        <div class="Header">
                            <h2>Fällige Rechnungen</h2>
                        </div>
                        <div class="FaelligeRechnungContainer">
                            <div class="FaelligeRechnungRow">
                                <div class="MonatJahr">Januar 2023</div>
                                <div class="AnzahlFaelligeRechnung">12</div>
                            </div>
                            <div class="FaelligeRechnungRow">
                                <div class="MonatJahr">Mai 2023</div>
                                <div class="AnzahlFaelligeRechnung">22</div>
                            </div>
                        </div>
                    </div>
                    <div class="Belege">

                    </div>
                </div>

                <div class="Angebotsstatistik">
                    <div class="Header">
                        <h2>Angebotsstatistik</h2>
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

                </div>
                <div class="FaelligeAngebote">

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
        }

        UmsatzJahr.addEventListener('change', aktualisiereUmsatzDaten);
        aktualisiereUmsatzDaten();
    </script>
</body>

</html>