<?php
session_start();

if (!isset($_SESSION['id'])) {
  // Redirect to the login page if the user is not logged in
  header('Location: adminlogin.php');
  exit();
}

// Dynamically determine the current season and year
$currentMonth = date('n');
$seasons = array('Winter', 'Spring', 'Summer', 'Fall');
$currentSeason = $seasons[floor(($currentMonth % 12) / 3)];
$currentYear = date('Y');

$sql = "WITH SeasonalData AS (
    SELECT
        Month,
        Year,
        Disease,
        Seasonality,  -- Replace with your actual seasonality measure
        CASE
            WHEN Month IN ('March', 'April', 'May') THEN 'Spring'
            WHEN Month IN ('June', 'July', 'August') THEN 'Summer'
            WHEN Month IN ('September', 'October', 'November') THEN 'Fall'
            WHEN Month IN ('December', 'January', 'February') THEN 'Winter'
        END AS Season
    FROM catdiseases
),
RankedDiseasesPerYearSeason AS (
    SELECT
        Year,
        Season,
        Disease,
        MAX(CAST(Seasonality AS DECIMAL(10, 2))) AS MaxSeasonality,  -- Assuming DECIMAL(10, 2) data type
        RANK() OVER (PARTITION BY Year, Season ORDER BY MAX(CAST(Seasonality AS DECIMAL(10, 2))) DESC) AS Rank
    FROM SeasonalData
    GROUP BY Year, Season, Disease
)
SELECT Year, Season, Disease, MaxSeasonality
FROM RankedDiseasesPerYearSeason
WHERE Rank <= 3 AND Year = $currentYear AND Season = '$currentSeason'
ORDER BY Year, Season, Rank;";
?>
