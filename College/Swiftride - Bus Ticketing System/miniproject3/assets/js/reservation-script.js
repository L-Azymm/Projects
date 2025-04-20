let map = L.map('map').setView([2.9331845848408, 101.79748023549], 16); // Malaysia Coordinates

// Set up the map with OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
}).addTo(map);

let routeControl;
const origin = [2.9331845848408, 101.79748023549]; // Default starting location
let marker;

// Add click event for selecting destination
map.on('click', function (e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;
    const destination = [lat, lon];

    // Fetch destination address using Nominatim
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('destination').value = data.display_name || `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
        });

    // Add a marker to the destination
    if (marker) {
        marker.setLatLng(destination);
    } else {
        marker = L.marker(destination).addTo(map);
    }

    // Add or update the route
    updateRoute(destination);
});

// Function to add or update the route
function updateRoute(destination) {
    // Remove existing route
    if (routeControl) {
        map.removeControl(routeControl);
    }

    // Add new route
    routeControl = L.Routing.control({
        waypoints: [
            L.latLng(origin),
            L.latLng(destination)
        ],
        routeWhileDragging: false,
        createMarker: () => null // Disable default markers
    })
        .on('routesfound', function (e) {
            const routes = e.routes;
            const summary = routes[0].summary; // Get the first route's summary
            const distance = summary.totalDistance / 1000; // Distance in kilometers
            const duration = summary.totalTime; // Duration in seconds

            // Convert duration to hours and minutes
            const hours = Math.floor(duration / 3600);
            const minutes = Math.round((duration % 3600) / 60);

            // Update form fields
            document.getElementById('distance').value = distance.toFixed(2);
            document.getElementById('eta').value = `${hours}h ${minutes}m`;

            document.getElementById('destination_latitude').value = destination[0]; // Latitude
            document.getElementById('destination_longitude').value = destination[1]; // Longitude
            
            calculateAssemblyTime();
        })
        .addTo(map);
}

// Function to clear the form and map markers/routes
function clearForm() {
    document.getElementById('bookingForm').reset();
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    if (routeControl) {
        map.removeControl(routeControl);
        routeControl = null;
    }
}

// Function to calculate assembly time
function calculateAssemblyTime() {
    // Get the arrival time and ETA (in hours and minutes)
    const arrivalTimeInput = document.getElementById('event_start').value; // "2024-12-10T10:00"
    const eta = document.getElementById('eta').value; // Format: "1h 3m" or similar

    const etaParts = eta.split('h').map(part => part.trim());
    const etaHours = parseInt(etaParts[0], 10) || 0; // Extract hours
    const etaMinutes = parseInt(etaParts[1]?.replace('m', ''), 10) || 0; // Extract minutes

    // Convert the arrival time to a Date object
    const arrivalTime = new Date(arrivalTimeInput);

    // Convert ETA (hours and minutes) to total minutes
    const totalEtaMinutes = (etaHours * 60) + etaMinutes;

    // Subtract ETA + 20 minutes (early arrival)
    const earlyArrivalTime = new Date(arrivalTime);
    earlyArrivalTime.setMinutes(earlyArrivalTime.getMinutes() - (totalEtaMinutes + 20));

    // Format the resulting assembly time to "YYYY-MM-DD HH:MM"
    const year = earlyArrivalTime.getFullYear();
    const month = (earlyArrivalTime.getMonth() + 1).toString().padStart(2, '0');
    const day = earlyArrivalTime.getDate().toString().padStart(2, '0');
    const hours = earlyArrivalTime.getHours().toString().padStart(2, '0');
    const minutes = earlyArrivalTime.getMinutes().toString().padStart(2, '0');

    const assemblyTime = `${year}-${month}-${day} ${hours}:${minutes}`;
    console.log("Assembly Time: " + assemblyTime);

    // Display the result in the form field
    document.getElementById('assembly_time').value = assemblyTime;
}

// Event listeners for recalculating Assembly Time
document.getElementById('event_start').addEventListener('change', calculateAssemblyTime);
document.getElementById('eta').addEventListener('input', calculateAssemblyTime);



// Fetch suggestions for destination based on user input
document.getElementById('destination').addEventListener('input', function () {
    const query = this.value.trim();
    if (query.length > 2) { // Trigger suggestions after 3 characters
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
            .then(response => response.json())
            .then(data => {
                const suggestionsList = document.getElementById('suggestions');
                suggestionsList.innerHTML = '';
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item.display_name;
                    li.addEventListener('click', function () {
                        document.getElementById('destination').value = item.display_name;
                        updateRoute([item.lat, item.lon]);
                        suggestionsList.innerHTML = ''; // Clear suggestions after selection
                    });
                    suggestionsList.appendChild(li);
                });
            });
    } else {
        document.getElementById('suggestions').innerHTML = ''; // Clear suggestions
    }
});
