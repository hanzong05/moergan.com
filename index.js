let petCount = 0;
const registeredPets = {};



function submitRegistration(event) {
    event.preventDefault(); // Prevent default form submission

    // Collect form data
    const petName = document.getElementById("petName").value;
    const species = document.getElementById("species").value;
    const breed = document.getElementById("breed").value;
    const age = document.getElementById("age").value;
    const gender = document.querySelector('input[name="gender"]:checked').value;
    const birthdate = document.getElementById("birthdate").value;
    console.log("Gender:", gender);
    

    // Create a FormData object to send the data as a POST request
    const formData = new FormData();
    formData.append("pet_registration", "register_pet");
    formData.append("petName", petName);
    formData.append("species", species);
    formData.append("breed", breed);
    formData.append("age", age);
    formData.append("gender", gender);
    formData.append("birthdate", birthdate);

    // Send the data to the server using fetch
    fetch("", {
        method: "POST",
        body: formData,
    })
    .then(response => {
        if (response.ok) {
            // Registration was successful, redirect to home.php
            window.location.href = "home.php";
        } else {
            // Handle registration failure here
            console.error("Registration failed.");
        }
    })
    .catch(error => {
        console.error("Error occurred:", error);
    });
}





function cancelRegistration() {
    const registrationForm = document.getElementById('registrationForm');
     window.location.href = "home.php";
}

function createPetButton(petId, buttonName) {
    const petButton = document.createElement('button');
    petButton.textContent = buttonName;
    petButton.onclick = function () {
        showPetInfo(petId);
    };

    const homeContent = document.getElementById('homeContent');
    homeContent.appendChild(petButton);
}

function showPetInfo(petId) {
    const petInfo = registeredPets[petId];
    const sliderContent = document.querySelector('.sliding-content .slider');
    sliderContent.innerHTML = `
        <div class="content-box active">
            <h1>Pet: ${petInfo.name}</h1>
            <p>Species: ${petInfo.species}</p>
            <p>Breed: ${petInfo.breed}</p>
            <p>Age: ${petInfo.age}</p>
            <p>Gender: ${petInfo.gender}</p>
            <p>Birthdate: ${petInfo.birthdate}</p>
            <!-- Add more pet information here -->
        </div>
        
    `;
    document.querySelector('.sliding-content').style.display = 'block';
}


// JavaScript code (index.js)
const appointmentForm = document.getElementById('appointmentForm');
const modalOverlay = document.createElement('div');
modalOverlay.classList.add('modal-overlay');

function showAppointmentForm() {
    appointmentForm.classList.add('modal-active');
    document.body.appendChild(modalOverlay);
}

function cancelAppointment() {
    appointmentForm.classList.remove('modal-active');
    document.body.removeChild(modalOverlay);
}
function showRegistrationForm() {
    document.getElementById('homeContent').style.display = 'none';
    document.getElementById('registrationForm').style.display = 'block';
    
}

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
    const tabContents = document.querySelectorAll('.tab-content');
    const tabs = document.querySelectorAll('.tab');

    tabContents.forEach(content => {
        content.style.display = 'none';
    });

    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    document.getElementById(tabName + 'Tab').style.display = 'block';
    document.querySelector('[onclick="openTab(\'' + tabName + '\')"]').classList.add('active');
}
function openChat() {
    const chatBox = document.getElementById('chatBox');
    chatBox.style.display = 'block';
}

function closeChat() {
    const chatBox = document.getElementById('chatBox');
    chatBox.style.display = 'none';
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
// Toggle the profile settings dropdown
function toggleProfileSettings() {
    const profileDropdown = document.querySelector('.profile-dropdown');
    profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
}


function toggleRegistrationForm() {
    var registrationForm = document.getElementById("registrationForm");
    if (registrationForm.style.display === "none" || registrationForm.style.display === "") {
        registrationForm.style.display = "block";
    } else {
        registrationForm.style.display = "none";
    }
}

 // Function to toggle the button's class based on screen size

 function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const adjust = document.querySelector(".adjust");

    sidebar.classList.toggle("sidebar-open");
    adjust.classList.toggle("sidebar-open");
}

// Call the function when the toggle button is clicked


// Call the function to hide the sidebar initially on page load
function hideSidebarOnLoad() {
    const toggleButton = document.getElementById("sidebar-toggle");
    const sidebar = document.querySelector(".sidebar");
    const adjust = document.querySelector(".adjust");

    if (window.innerWidth < 1080) {
        sidebar.classList.remove("sidebar-open");
        adjust.classList.remove("sidebar-open");
      
    } else {
       
    }
}

// Call the function initially and add a listener for window resize
hideSidebarOnLoad();
window.addEventListener("resize", hideSidebarOnLoad);




function updateBreedOptions() {
    // Get the selected species value
    
    
    var selectedSpecies = document.getElementById("species").value;
    
    // Get the breed select element
    var breedSelect = document.getElementById("breed");
    
    // Clear existing options
    breedSelect.innerHTML = '';
    
    // Create an object to store breed options for both cat and dog
    var breedOptions = {
        cat: [
            "Persian", "Siamese", "Maine Coon", "Bengal", "Ragdoll", "British Shorthair",
            "Sphynx", "Abyssinian", "Scottish Fold", "Birman", "Oriental Shorthair", "Devon Rex",
            "American Shorthair", "Exotic Shorthair", "Turkish Van", "Russian Blue", "Cornish Rex",
            "Himalayan", "Manx", "Tonkinese", "Siberian", "Balinese", "Burman", "Egyptian Mau","other"
        ],
        dog: [
            "Labrador Retriever", "German Shepherd", "Golden Retriever", "Bulldog", "Beagle",
            "Poodle", "Rottweiler", "Yorkshire Terrier", "Boxer", "Dachshund", "Shih Tzu",
            "Great Dane", "Siberian Husky", "Doberman Pinscher", "Pomeranian", "Cocker Spaniel",
            "Miniature Schnauzer", "French Bulldog", "Border Collie", "Chihuahua", "Pug",
            "English Setter", "Shiba Inu", "Basset Hound", "Papillon", "Greyhound", "Akita","other"
        ]
    };
    // Populate the breed select element with options based on the selected species
    breedOptions[selectedSpecies].forEach(function (breed) {
        var option = document.createElement("option");
        option.value = breed;
        option.text = breed;
        breedSelect.appendChild(option);
    });
}
// Get references to the birthdate and age input fields
var birthdateInput = document.getElementById("birthdate");
function calculateAge() {
    var birthdateInput = document.getElementById("birthdate");
    var ageInput = document.getElementById("age");

    // Get the selected birthdate
    var selectedDate = new Date(birthdateInput.value);

    // Calculate the current date
    var currentDate = new Date();

    // Calculate the difference in milliseconds
    var ageInMillis = currentDate - selectedDate;

    // Calculate years and months
    var ageInYears = Math.floor(ageInMillis / (365 * 24 * 60 * 60 * 1000));
    var ageInMonths = Math.floor(ageInMillis / (30 * 24 * 60 * 60 * 1000));

    // Create a string to display the age
    var ageString = "";

    if (ageInYears > 0) {
        ageString += ageInYears + (ageInYears === 1 ? " year" : " years");

        if (ageInMonths > 0) {
            ageString += " and " + (ageInMonths % 12) + (ageInMonths % 12 === 1 ? " month" : " months");
        }
    } else if (ageInMonths > 0) {
        ageString += ageInMonths + (ageInMonths === 1 ? " month" : " months");
    }

    // Update the age input field
    ageInput.value = ageString;
}function enableEdit() {
    // Enable input fields for editing
    document.querySelectorAll('.form-control').forEach(function (input) {
        input.removeAttribute('disabled');
    });

    // Show the Save Profile button
    document.getElementById('saveProfileButtonContainer').style.display = 'block';
}
