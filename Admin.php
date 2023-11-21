<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible"
		content="IE=edge">
	<meta name="viewport"
		content="width=device-width,
				initial-scale=1.0">
	<title>Moergan&Friends</title>
	<link rel="stylesheet"
		href="Admin.css">
	<link rel="stylesheet"
		href="responsive.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>

	<!-- for header part -->
	<header>

		<div class="logosec">
			<div class="logo">Moergan&Friends</div>
			<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210182541/Untitled-design-(30).png"
				class="icn menuicn"
				id="menuicn"
				alt="menu-icon">
		</div>

		<div class="searchbar">
			<input type="text"
				placeholder="Search">
			<div class="searchbtn">
			<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210180758/Untitled-design-(28).png"
					class="icn srchicn"
					alt="search-icon">
			</div>
		</div>

		<div class="message">
			<div class="circle"></div>
			<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183322/8.png"
				class="icn"
				alt="">
			<div class="dp">
			<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210180014/profile-removebg-preview.png"
					class="dpicn"
					alt="dp">
			</div>
		</div>

	</header>

		<div class="navcontainer">
			<nav class="nav">
				<div class="nav-upper-options">
					<div class="nav-option option1">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210182148/Untitled-design-(29).png"
							class="nav-img"
							alt="dashboard">
						<h3> Dashboard</h3>
					</div>

					<div class="nav-option option3">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183320/5.png"
							class="nav-img"
							alt="report">
						<h3> Visualization</h3>
					</div>

					<div class="nav-option option4">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183321/6.png"
							class="nav-img"
							alt="institution">
						<h3> Institution</h3>
					</div>

					<div class="nav-option option5">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183323/10.png"
							class="nav-img"
							alt="blog">
						<h3> Profile</h3>
					</div>

					<div class="nav-option option6">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183320/4.png"
							class="nav-img"
							alt="settings">
						<h3> Settings</h3>
					</div>

					<div class="nav-option logout">
						<img src=
"https://media.geeksforgeeks.org/wp-content/uploads/20221210183321/7.png"
							class="nav-img"
							alt="logout">
						<h3>Logout</h3>
					</div>

				</div>
			</nav>
		</div>
		<div class="main">
			<div class="report-container">
				<div class="report-header">
					<h1 class="recent-Articles">Registered Clients</h1>
				</div>

				<div class="report-body">
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col">ID</th>
								<th scope="col">Firstname</th>
								<th scope="col">Lastname</th>
								<th scope="col">Address</th>
								<th scope="col">Email</th>
								<th scope="col">Password</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$conn = mysqli_connect('localhost','root','','project');
							$userdata = "SELECT * FROM users";
							$userdata_run = mysqli_query($conn, $userdata);

							if(mysqli_num_rows($userdata_run) > 0) {
								foreach($userdata_run as $row) {
									echo '<tr>';
									echo '<td>' . $row['Id'] . '</td>';
									echo '<td>' . $row['Firstname'] . '</td>';
									echo '<td>' . $row['Lastname'] . '</td>';
									echo '<td>' . $row['Address'] . '</td>';
									echo '<td>' . $row['Email'] . '</td>';
									echo '<td>' . $row['Password'] . '</td>';
									echo '</tr>';
								}
							} else {
								echo '<tr><td colspan="6">No record found</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			
		</div>
	</div>
	<script src="Admin.js"></script>
</body>
</html>
