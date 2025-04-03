const urlBase = 'http://159.203.173.251/LAMPAPI';
const extension = 'php';

// Function to get events
function doGetEvents() {
    // Retrieve universityID and rsoIDs from localStorage
    let universityID = parseInt(sessionStorage.getItem("universityID"));
    let rsoIDs = sessionStorage.getItem("rsoIDs").split(",").map(Number);;
    
    // Ensure both universityID and rsoIDs are available
    if (!universityID || rsoIDs.length === 0) {
        console.error("Missing required parameters: universityID or rsoIDs");
        return; // Stop execution if missing necessary data
    }

    // Build the URL for the GetEvents endpoint
    let url = urlBase + '/GetEvents.' + extension;

    // Create the data object with universityID and rsoIDs
    let requestData = {
        universityID: universityID,
        rsoIDs: rsoIDs
    };

    // Create a new XMLHttpRequest to make a POST request
    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        // Define the onreadystatechange function to process the response
        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let jsonObject = JSON.parse(xhr.responseText);
				if (jsonObject.error === "")
				{
					// Check if there are results in the response
					if (jsonObject.results && jsonObject.results.length > 0) {
						populateEvents(jsonObject.results);
					} else {
						document.getElementById("event-details").innerHTML = "<p>No events found.</p>";
					}
				}
            }
        };

        // Convert the request data to JSON and send the request
        let jsonPayload = JSON.stringify(requestData);
        xhr.send(jsonPayload);
    } catch (err) {
        console.error("Error fetching events:", err.message);
    }
}

// Function to populate events into the #event-details element
function populateEvents(events) {
    let eventContainer = document.getElementById("event-details");
    eventContainer.innerHTML = ""; // Clear previous events

    for (let i = 0; i < events.length; i++) {
        let event = events[i];

        // Create a container for each event
        let eventDiv = document.createElement("div");
        eventDiv.className = "event"; // Make sure to style this in your CSS if needed

        // Build the inner HTML for the event details
        eventDiv.innerHTML = 
            "<h2>" + event.eventName + "</h2>" +
            "<p><strong>University:</strong> " + (event.universityName || "N/A") + "</p>" +
            "<p><strong>RSO:</strong> " + (event.rsoName || "N/A") + "</p>" +
            "<p><strong>Location:</strong> " + event.locationName + "</p>" +
            "<p><strong>Category:</strong> " + event.category + "</p>" +
            "<p><strong>Description:</strong> " + event.description + "</p>" +
            "<p><strong>Time:</strong> " + event.time + "</p>" +
            "<p><strong>Date:</strong> " + event.date + "</p>" +
            "<p><strong>Contact:</strong> " + event.phoneNumber + " | " + event.email + "</p>";

        // Append the event to the container
        eventContainer.appendChild(eventDiv);
    }
}

// Call the doGetEvents function on page load
document.addEventListener("DOMContentLoaded", function() {
    doGetEvents();
});

function doLogout()
{
    sessionStorage.clear();
    window.location.href = "login.html";
}
