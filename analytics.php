<?php
session_start();
require_once('db_connection.php');

// Check if the user is not logged in
if (!isset($_SESSION['id'])) {
    // Redirect to the login page if the user is not logged in
    header('Location: adminlogin.php');
    exit();
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Your SQL query for the year 2023, grouped by season
$sql = "WITH SeasonalData AS (
    SELECT
        Month,
        Year,
        Disease,
        Seasonality,
        CASE
            WHEN Month IN ('March', 'April', 'May') THEN 'Spring'
            WHEN Month IN ('June', 'July', 'August') THEN 'Summer'
            WHEN Month IN ('September', 'October', 'November') THEN 'Fall'
            WHEN Month IN ('December', 'January', 'February') THEN 'Winter'
            END AS Season
    FROM catdiseases
    WHERE
        (
            (Year = YEAR(NOW()) AND Season IN ('Fall')) OR
            (Year = YEAR(NOW()) + 1 AND Month IN ('January', 'February'))
        )
        OR
        (
            (Year = YEAR(NOW()) + 1 AND Season = 'Winter') OR
            (Year = YEAR(NOW()) AND Month = 'December' AND Season IN ('Fall', 'Winter', 'Spring'))
        )
),
RankedDiseasesPerYearSeason AS (
    SELECT
        Year,
        Season,
        Disease,
        MAX(CAST(Seasonality AS DECIMAL(10, 2))) AS MaxSeasonality,
        RANK() OVER (PARTITION BY Year, Season ORDER BY MAX(CAST(Seasonality AS DECIMAL(10, 2))) DESC) AS Rank
    FROM SeasonalData
    GROUP BY Year, Season, Disease
)
SELECT Year, Season, Disease, MaxSeasonality, Rank
FROM RankedDiseasesPerYearSeason
ORDER BY Year, Season, Rank;";

// Execute the SQL query
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Group the result by season
$seasons = array("Spring", "Summer", "Fall", "Winter");
$tables = array_fill_keys($seasons, array());

while ($row = $result->fetch_assoc()) {
    $tables[$row['Season']][] = $row;
}

// Filter the result to get only the top 3 diseases per season
$filteredTables = array();

foreach ($tables as $season => $data) {
    // Sort the data by MaxSeasonality in descending order
    usort($data, function($a, $b) {
        return $b['MaxSeasonality'] <=> $a['MaxSeasonality'];
    });

    // Take only the top 3 diseases
    $top3 = array_slice($data, 0, 3);
    $filteredTables[$season] = $top3;
}
?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-wvmzv6MI7Sk63jQ+15fjgBIEtj0B1F2PLD9j/5lWbCjsYQLxuStQlGQB9qm8b+2v/fVL5smIicTkbr4wDlQvIw==" crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
</head>

<body>
   

    <div class="wrapper">
        <div class="sidebar">
            <h2>Sidebar</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>
</i>Dashboard</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i>
</i>Analytics</a></li>
                <li><a href="scheduled_apointments.php"><i class="far fa-calendar"></i>
</i>Scheduled Appointments</a></li>
                <li><a href="requested_apointments.php"><i class="far fa-calendar"></i>
</i>Requested Appointments</a></li>
                <li><a href="registered_clients.php"> <i class="fas fa-users"></i></i>Registered Clients</a></li>
                <li><a href="out.php"><i class="fas fa-sign-out-alt"></i></i>Logout</a></li>
            </ul>
           
        </div>
        <div class="main_content">
        <div class="header">Welcome!! Have a nice day. <div class="toggle_btn" onclick="toggleSidebar()">☰</div></div>  
        <div class="info">
        <div class="row">
          
<div class="container col-md-3 custom-container">
    <h6>Pet Disease</h6>
    <div class="form-group">
        <label for="petType">Select Pet Type</label>
        <select class="custom-select" id="petType">
            <option value="cat">Cat</option>
            <option value="dog">Dog</option>
        </select>
    </div> 
    <div class="form-group">
        <label for="diseaseList">Select Disease</label>
        <select class="custom-select" id="diseaseList">
            <!-- Options will be dynamically populated here -->
        </select>
    </div>
</div>
<!-- Updated HTML for the radio buttons -->
<div class="container col-md-2 custom-container">
    <h6>Select Year</h6>
    <form id="yearForm">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="radioGroup" id="radioOption1" value="2021">
            <label class="form-check-label" for="radioOption1">Year 2021</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="radioGroup" id="radioOption2" value="2022">
            <label class="form-check-label" for="radioOption2">Year 2022</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="radioGroup" id="radioOption3" value="2023">
            <label class="form-check-label" for="radioOption3">Year 2023</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="radioGroup" id="radioOption4" value="2024">
            <label class="form-check-label" for="radioOption3">Year 2024</label>
        </div>
    </form>
</div>



            <!--Sum of cases of the desease by year-->
            <div class="container col-md-6 custom-container">
            <h6>Monthly Cases of <span id="lineChartDisease"></span> in <span id="lineChartYear"></span></h6>
            <canvas id="myLineChart" width="400" height="200"></canvas>
            </div>
        </div>
        <div class="row">
    <!-- Sum of cases of the disease by month -->
    <div class="col-md-3">
        <div class="container custom-container">
            <h6>Total Cases of <span id="donutChartDisease"></span></h6>
            <canvas id="myDonutChart" width="400" height="200"></canvas>
        </div>
    </div>

    <div class="col-md-8">
        <div class="container custom-container">
            <canvas id="myForecastedAndSeasonalityLineChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

            
         
 <div class="container-fluid">
    <div class="row">
        <!-- Current Season Top 3 Diseases Pie Chart -->
        <div class="col-md-6">
            <div class="container custom-container">
                <?php
                $currentMonth = date('F'); // Get the current month
                $currentSeason = '';

                // Determine the current season based on the current month
                if (in_array($currentMonth, ['March', 'April', 'May'])) {
                    $currentSeason = 'Spring';
                } elseif (in_array($currentMonth, ['June', 'July', 'August'])) {
                    $currentSeason = 'Summer';
                } elseif (in_array($currentMonth, ['September', 'October', 'November'])) {
                    $currentSeason = 'Fall';
                } else {
                    $currentSeason = 'Winter';
                }

                // Loop modified to display only the current season
                foreach ($seasons as $season) :
                    if ($season === $currentSeason) :
                ?>
                        <h5>Top 3 Diseases in <span id="pieChartSeason"><?php echo $season; ?></span></h5>
                        <canvas id="myPieChart_<?php echo $season; ?>" width="400" height="200"></canvas>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>

        <!-- Next Season Top 3 Diseases Pie Chart -->
        <div class="col-md-6">
            <div class="container custom-container">
                <h5>Top 3 Diseases in Next Season</h5>
                <canvas id="myNextSeasonPieChart" width="400" height="900"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="buttoncontainer">
    <button class="btn btn-primary btn-lg btnMedicine" data-species="cat" active>Medicine For Cats</button>
    <button class="btn btn-primary btn-lg btnMedicine" data-species="dog">Medicine For Dogs</button>
</div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Inventory for General Items -->
                    <div class="report-container">
                        <div class="report-body">
                            <div class="report-header">
                                <h4 class="recent-Articles">Medicine to Buy Next Season</h4>
                            </div>
                           <!-- Add this to your HTML -->
                           <div class="container">
    <div class="row">
        <!-- First Table -->
        <div class="col-md-6">
            <table class="table table-custom table-responsive">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Disease</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody id="medicineTableBody">
                    <!-- Medicines will be displayed here -->
                </tbody>
            </table>
        </div>

        <!-- Second Table -->
        <div class="col-md-6">
            <div class="report-container">
                <div class="report-body">
                    <div class="report-header">
                        <h4 id="medicineHeader" class="recent-Articles">Medicine</h4>
                    </div>
                    <table class="table table-custom table-responsive">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Image</th>
                            </tr>
                        </thead>
                        <tbody id="medicineforallTableBody">
                            <!-- Medicines will be displayed here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

        
<script  src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script defer src="dashboard.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js" integrity="sha512-R/QOHLpV1Ggq22vfDAWY0aMd5RopHгJNMx18/1Ju80ihwi4H04BRFeiMiCefn9rasajKjnx9/fTQ/xkWnkDACg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

   

  
 <script>
 $(document).ready(function () {
    var allSeasonsData = <?php echo json_encode($filteredTables); ?>;
    console.log("Data for all seasons:", allSeasonsData);

    var currentSeason = '<?php echo $currentSeason; ?>';
    var currentSeasonData = allSeasonsData[currentSeason];

    

    // Create table and pie chart only for the current season
    createTableAndPieChartForSeason(currentSeason, currentSeasonData);

    function createTableAndPieChartForSeason(season, data) {
        console.log("Creating table and pie chart for " + season + " season with data:", data);

        // Add your code here to create a table for the season
        // You can use the existing table creation logic and modify it to display all seasons in a single table

        // Add your code here to create a pie chart for the season
        createPieChartForSeason(season, data);
    }var nextSeason = '';
var currentYear = new Date().getFullYear();
if (currentSeason !== 'Winter') {
    switch (currentSeason) {
        case 'Spring':
            nextSeason = 'Summer';
            break;
        case 'Summer':
            nextSeason = 'Fall';
            break;
        case 'Fall':
            nextSeason = 'Winter';
            break;
    }

    // Fetch data for the next season
    var nextSeasonData = allSeasonsData[nextSeason];
    // Create pie chart for the next season
    createNextSeasonPieChart(nextSeason, nextSeasonData);
} else {
    // If the current season is Winter, fetch only December data for the current year
    var winterData = allSeasonsData['Winter'].filter(item => item.Month === 'December' && item.Year === currentYear);
    createNextSeasonPieChart('Winter', winterData);
}

    
    var myChart;
    var myDonutChart;
    var myForecastedAndSeasonalityLineChart = null;
    var myPieChart;

    var selectedYear = '2021';
    document.getElementById('radioOption1').checked = true;
    var selectedDisease = diseases.cat[0];


    updateChart(selectedYear, selectedDisease);

    $('input[type=radio][name=radioGroup]').change(function () {
        selectedYear = this.value;
        updateChart(selectedYear, selectedDisease); 
    });

    $('#diseaseList').change(function () {
        selectedDisease = this.value;
        updateChart(selectedYear, selectedDisease);
    });

    function updateChart(selectedYear, selectedDisease) {
        fetchDataForLineChart(selectedYear, selectedDisease);
        fetchDataForDonutChart(selectedDisease);
        createForecastedAndSeasonalityLineChart(selectedYear, selectedDisease); 
        // Move this line to update the forecast chart.
    }

    function fetchDataForLineChart(selectedYear, selectedDisease) {
        var url = 'year.php?year=' + selectedYear;

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                if (Array.isArray(data)) {
                    createLineChart(data, selectedDisease);
                } else {
                    console.error("Data is not an array:", data);
                }
            },
            error: function (error) {
                console.error("Error fetching data:", error);
            }
        });
    }

    function fetchDataForDonutChart(selectedDisease) {
        var url = 'donut.php';

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                try {
                    data = JSON.parse(data);
                    createDonutChart(data, selectedDisease);
                } catch (error) {
                    console.error("Error parsing data:", error);
                }
            },
            error: function (error) {
                console.error("Error fetching data:", error);
            }
        });
    }
    function createLineChart(data, selectedDisease) {
        console.log("Received data for line chart creation:", data);

        var filteredData = data.filter(item => item.Disease === selectedDisease);

        if (myChart) {
            myChart.destroy();
        }

        var dataset = [];
        var months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        var monthlyCases = new Array(12).fill(0); 

        filteredData.forEach(function (item) {
            var monthIndex = months.indexOf(item.Month);

            if (monthIndex !== -1) {
                monthlyCases[monthIndex] += item.NumberOfCases;
            }
        });

        dataset.push({
            label: selectedDisease,
            data: monthlyCases,
            fill: false
        });

        var ctx = document.getElementById('myLineChart').getContext('2d');
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: dataset,
            },
            options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1000,
            easing: 'linear',
        },  plugins: {
            datalabels: { // Enable data labels
                display: true,
                color: '#626262', // You can adjust the color
                font: {
                    weight: 'bold'
                },
                anchor: 'end',
                align: 'start',
                offset: -13,
            }
        }
      
            },
            plugins:[ChartDataLabels]
        }); 
        document.getElementById('lineChartYear').innerText = selectedYear;
        document.getElementById('lineChartDisease').innerText = selectedDisease;

        createForecastedAndSeasonalityLineChart(selectedYear, selectedDisease); 
    }

    function createDonutChart(data, selectedDisease) {
        console.log("Received data for donut chart creation:", data);

        var filteredData = data.filter(item => item.Disease === selectedDisease);

        var yearData = {};
        filteredData.forEach(function (item) {
            var year = item.Year;
            var cases = item.TotalCases;
            if (yearData[year]) {
                yearData[year] += cases;
            } else {
                yearData[year] = cases;
            }
        });

        var years = Object.keys(yearData);
        var totalCases = Object.values(yearData);

        if (myDonutChart) {
            myDonutChart.destroy();
        }

        var colors = generateRandomColors(years.length);

        var ctxDonut = document.getElementById('myDonutChart').getContext('2d');
myDonutChart = new Chart(ctxDonut, {
    type: 'doughnut',
    data: {
        labels: years,
        datasets: [
            {
                label: selectedDisease,
                data: totalCases,
                backgroundColor: colors,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1000,
            easing: 'linear',
        },
        plugins: {
            datalabels: { // Enable data labels
                display: true,
                color: '#626262', // You can adjust the color
                font: {
                    weight: 'bold'
                },
                anchor: 'end',
                align: 'start',
                offset: -10,
            }
        }
        
    },
     plugins:[ChartDataLabels]
    
});
document.getElementById('donutChartDisease').innerText = selectedDisease;

    }
    function createForecastedAndSeasonalityLineChart(selectedYear, selectedDisease) {
        var canvas = document.getElementById('myForecastedAndSeasonalityLineChart');
        var ctx = canvas.getContext('2d');
        

        $.ajax({
            url: 'forecast.php?year=' + selectedYear + '&disease=' + selectedDisease,
            method: 'GET',
            success: function (data) {
                if (Array.isArray(data)) {
                    var forecastData = {
                        label: 'Forecasted Data for ' + selectedYear + ' - ' + selectedDisease,
                        data: data.map(item => item.Forecast),
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 2,
                        fill: false,
                    };

                    var seasonalityData = {
                        label: 'Seasonality Data for ' + selectedYear + ' - ' + selectedDisease,
                        data: data.map(item => item.Seasonality),
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 2,
                        fill: false,
                    };

                    if (myForecastedAndSeasonalityLineChart) {
                        myForecastedAndSeasonalityLineChart.destroy();
                    }

                    myForecastedAndSeasonalityLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [forecastData, seasonalityData],
                        },
                        options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000,
                            easing: 'linear',
                        },
                        plugins: {
                            datalabels: {
                                display: true,
                                color: '#626262',
                                font: {
                                    weight: 'bold'
                                },
                                anchor: 'end',
                align: 'start',
                offset: -13,
                            }
                        }
                       
                    },
     plugins:[ChartDataLabels]
     
                    
                });
                


                    console.log("Received data for forecast chart creation:", data);

                } else {
                    console.error("Data is not an array:", data);
                }
            },
            error: function (error) {
                console.error("Error fetching data:", error);
            }
            
            
        });
        
    }
 
    function createPieChartForSeason(season, data) {
        console.log("Creating pie chart for " + season + " season with data:", data);

        // Sort the data by MaxSeasonality in descending order
        data.sort(function (a, b) {
            return b.MaxSeasonality - a.MaxSeasonality;
        });

        // Take only the top 3 diseases
        var top3Data = data.slice(0, 3);

        var canvasId = 'myPieChart_' + season;
        var ctx = document.getElementById(canvasId).getContext('2d');

        // Extract data for the pie chart
        var labels = top3Data.map(item => item.Disease);
        var values = top3Data.map(item => parseFloat(item.MaxSeasonality));

        // Create the pie chart
        var myPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: generateRandomColors(top3Data.length),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'linear',
                },
                plugins: {
                    datalabels: {
                        display: true,
                        color: '#626262',
                        font: {
                            weight: 'bold',
                        },
                        anchor: 'end',
                        align: 'start',
                        offset: -10,
                    },
                },
                title: {
                display: true,
                text: 'Top 3 Diseases with Max Seasonality in ' + season,
            },
            },
        });
        document.getElementById('pieChartSeason').innerText = season;
    }

    function createNextSeasonPieChart(season, data) {
    console.log("Creating pie chart for " + season + " season with data:", data);

    // Sort the data by MaxSeasonality in descending order
    data.sort(function (a, b) {
        return b.MaxSeasonality - a.MaxSeasonality;
    });

    // Take only the top 3 diseases
    var top3Data = data.slice(0, 3);

    // Specify the ID of the canvas for the next season's pie chart
    var canvasId = 'myNextSeasonPieChart';

    // Use the specified canvas ID to get the context
    var ctx = document.getElementById(canvasId).getContext('2d');

    // Extract data for the pie chart
    var labels = top3Data.map(item => item.Disease);
    var values = top3Data.map(item => parseFloat(item.MaxSeasonality));

    // Create the pie chart for the next season
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: generateRandomColors(top3Data.length),
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000,
                easing: 'linear',
            },
            plugins: {
                datalabels: {
                    display: true,
                    color: '#626262',
                    font: {
                        weight: 'bold',
                    },
                    anchor: 'end',
                    align: 'start',
                    offset: -10,
                },
            },
        },
    });
    var selectedDiseases = top3Data.map(item => item.Disease);

    // Use AJAX to send the selected diseases to the server
$.ajax({
    type: 'POST',
    url: 'medicine.php', // Update with the correct server-side file
    data: { diseases: selectedDiseases },
    success: function (response) {
        // Handle the response from the server, e.g., display medicines
        $("#medicineTableBody").html(response);
    },
    error: function (error) {
        console.log("Error:", error);
        console.log("Selected Diseases:", top3Data);
    }
});
}

        // Function to generate random colors
        function generateRandomColors(num) {
            var colors = [];
            for (var i = 0; i < num; i++) {
                var color = 'rgba(' + getRandomInt(200, 255) + ',' + getRandomInt(200, 255) + ',' + getRandomInt(200, 255) + ', 0.7)';
                colors.push(color);
            }
            return colors;
        }

        // Function to get a random integer in a specified range
        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

    function generateRandomColors(num) {
        var colors = [];
        for (var i = 0; i < num; i++) {
            var color = 'rgba(' + getRandomInt(200, 255) + ',' + getRandomInt(200, 255) + ',' + getRandomInt(200, 255) + ', 0.7)';
            colors.push(color);
        }
        return colors;
    }

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

  
    // Initial call with the current year, disease, and automatically determined next season
   
})
$(document).ready(function () {
        // Click event for all buttons with class 'btnMedicine'
        $('.btnMedicine').click(function () {
            // Extract species from the clicked button's data attribute
            var species = $(this).data('species');

            // Update the header based on the species
            $('#medicineHeader').text('Medicine for ' + capitalizeFirstLetter(species));

            // Make AJAX request to fetch medicines based on the species
            $.ajax({
                type: 'POST',
                url: 'getmedicine.php', // Update the URL to the PHP file handling the request
                data: { species: species },
                success: function (response) {
                    // Update the content of the table body in the specified div
                    $('#medicineforallTableBody').html(response);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Function to capitalize the first letter of a string
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    });
const diseases = {
   cat : [
    "ROUND WORMS",
    "RABIES",
    "CAT FLU",
    "HEART WORM",
    "RING WORM",
    "DIARRHOEA"
],


dog : [
    "DISTEMPER",
    "PARVO VIRUS",
    "PARAINFLUENZA",
    "HEART WORM",
    "RING WORM",
    "RABIES"
]
};


// Function to populate the disease list based on the selected pet type
function populateDiseaseList() {
    const petTypeSelect = document.getElementById("petType");
    const diseaseListSelect = document.getElementById("diseaseList");
    const selectedPetType = petTypeSelect.value;

    // Clear existing options
    diseaseListSelect.innerHTML = "";

    // Populate options based on the selected pet type
    for (const disease of diseases[selectedPetType]) {
        const option = document.createElement("option");
        option.value = disease;
        option.text = disease;
        diseaseListSelect.appendChild(option);
    }
}

// Add an event listener to the petType select to update the disease list
document.getElementById("petType").addEventListener("change", populateDiseaseList);

// Initial population of the disease list based on the default selected pet type
populateDiseaseList();
</script>


</body>
</html>