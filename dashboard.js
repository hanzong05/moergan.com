

document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.toggle-button');
    const sidebar = document.querySelector('.sidebar');

    toggleButton.addEventListener('click', function () {
        // Toggle the 'sidebar-open' class on the sidebar
        sidebar.classList.toggle('sidebar-open');
    });
});
// Add click event listener to the user profile icon link
profileIconLink.addEventListener("click", function (event) {
    // Prevent the link from navigating to a new page
    event.preventDefault();

    // Toggle the "show" class to control the visibility of the dropdown
    profileDropdown.classList.toggle("show");
});

// ... (Other JavaScript functions, such as submitAppointment and other form functionalities)
function openMessageBox() {
    const messageBox = document.getElementById('messageBox');
    messageBox.style.display = 'block';
    openTab('chat');
}

function closeMessageBox() {
    const messageBox = document.getElementById('messageBox');
    messageBox.style.display = 'none';
}
function openTab(tabName) {
    // Existing code to switch between tabs and content sections

    // Add code to handle the "Video Chat" tab
    if (tabName === 'video') {
        // Hide other content sections
        contentSections.forEach(content => {
            content.style.display = 'none';
        });

        // Show the "Video Chat" content
        document.getElementById('videoContent').style.display = 'block';

        // Update the active tab
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector('[onclick="openTab(\'video\')"]').classList.add('active');
    }

    // Add code to handle the "FAQs" tab
    if (tabName === 'faqs') {
        // Hide other content sections
        contentSections.forEach(content => {
            content.style.display = 'none';
        });

        // Show the "FAQs" content
        document.getElementById('faqsContent').style.display = 'block';

        // Update the active tab
        tabs.forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector('[onclick="openTab(\'faqs\')"]').classList.add('active');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const animalDropdown = document.getElementById("animalDropdown");
    const diseaseDropdown = document.getElementById("diseaseDropdown");
    const infoDiv = document.querySelector(".info");

    // Define disease information for cats and dogs
    const diseases = {
        cat: [
            "Cat Disease 1",
            "Cat Disease 2",
            "Cat Disease 3",
        ],
        dog: [
            "Dog Disease 1",
            "Dog Disease 2",
            "Dog Disease 3",
        ],
    };

    // Function to populate the disease dropdown based on the selected animal
    function populateDiseaseDropdown(animal) {
        diseaseDropdown.innerHTML = ""; // Clear previous options

        for (const disease of diseases[animal]) {
            const option = document.createElement("option");
            option.value = disease;
            option.textContent = disease;
            diseaseDropdown.appendChild(option);
        }
    }

    // Event listener for the animal dropdown
    animalDropdown.addEventListener("change", function () {
        const selectedAnimal = animalDropdown.value;
        populateDiseaseDropdown(selectedAnimal);
        infoDiv.textContent = "Common disease information will be displayed here.";
    });

    // Event listener for the disease dropdown
    diseaseDropdown.addEventListener("change", function () {
        const selectedDisease = diseaseDropdown.value;
        infoDiv.textContent = `Information about ${selectedDisease} goes here.`;
    });
});

$(document).ready(function() {
    $("#sidebar-toggle").click(function() {
        $(".sidebar").toggleClass("active");
    });
});

const appointmentTable = document.getElementById("appointment-table");

function scheduleAppointment() {
    const datetimeInput = document.getElementById("datetime");
    const reasonInput = document.getElementById("reason");
    const datetime = datetimeInput.value;
    const reason = reasonInput.value;

    if (datetime && reason) {
        const [date, time] = datetime.split("T");
        const appointmentRow = document.createElement("tr");
        appointmentRow.innerHTML = `
            <td>${date}</td>
            <td>${time}</td>
            <td>${reason}</td>
            <td>
                <button onclick="acceptAppointment(this)">Accept</button>
                <button onclick="rejectAppointment(this)">Reject</button>
            </td>
        `;
        appointmentTable.appendChild(appointmentRow);
        datetimeInput.value = "";
        reasonInput.value = "";
    }
}


const searchBar = document.querySelector(".search input"),
searchIcon = document.querySelector(".search button"),
usersList = document.querySelector(".users-list");

searchIcon.onclick = ()=>{
  searchBar.classList.toggle("show");
  searchIcon.classList.toggle("active");
  searchBar.focus();
  if(searchBar.classList.contains("active")){
    searchBar.value = "";
    searchBar.classList.remove("active");
  }
}

searchBar.onkeyup = ()=>{
  let searchTerm = searchBar.value;
  if(searchTerm != ""){
    searchBar.classList.add("active");
  }else{
    searchBar.classList.remove("active");
  }
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "php/search.php", true);
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          usersList.innerHTML = data;
        }
    }
  }
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("searchTerm=" + searchTerm);
}

setInterval(() =>{
  let xhr = new XMLHttpRequest();
  xhr.open("GET", "php/dashboardss.php", true);
  xhr.onload = ()=>{
    if(xhr.readyState === XMLHttpRequest.DONE){
        if(xhr.status === 200){
          let data = xhr.response;
          if(!searchBar.classList.contains("active")){
            usersList.innerHTML = data;
          }
        }
    }
  }
  xhr.send();
}, 500);
function toggleSidebar() {
    var sidebar = document.querySelector('.wrapper .sidebar');
    var mainContent = document.querySelector('.wrapper .main_content');
  
    if (sidebar.style.marginLeft === '0px' || sidebar.style.marginLeft === '') {
      sidebar.style.marginLeft = '-200px';
      mainContent.style.marginLeft = '0';
    } else {
      sidebar.style.marginLeft = '0';
      mainContent.style.marginLeft = '200px';
    }
  }