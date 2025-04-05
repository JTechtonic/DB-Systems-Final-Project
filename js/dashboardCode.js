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

        // Create a clickable event name (using a button here)
        let eventName = document.createElement("button");
        eventName.innerHTML = event.eventName;
        eventName.className = "event-name";  // Add CSS class for styling
        eventName.onclick = function() {
            // When clicked, fetch comments for this event
            sessionStorage.setItem("lastClickedEventID", event.eventID);
            doGetComments(event.eventID);
        };

        // Append the event name button to the event div
        eventDiv.appendChild(eventName);

        // Create the rest of the event details
        let eventDetails = document.createElement("div");

        eventDetails.innerHTML = 
            "<p><strong>University:</strong> " + (event.universityName || "N/A") + "</p>" +
            "<p><strong>RSO:</strong> " + (event.rsoName || "N/A") + "</p>" +
            "<p><strong>Location:</strong> " + event.locationName + "</p>" +
            "<p><strong>Category:</strong> " + event.category + "</p>" +
            "<p><strong>Description:</strong> " + event.description + "</p>" +
            "<p><strong>Time:</strong> " + event.time + "</p>" +
            "<p><strong>Date:</strong> " + event.date + "</p>" +
            "<p><strong>Contact:</strong> " + event.phoneNumber + " | " + event.email + "</p>" + 
            "<p><strong>Visibility:</strong> " + event.visibility + "</p>";

        // Append the event details to the event div
        eventDiv.appendChild(eventDetails);

        // Append the event to the container
        eventContainer.appendChild(eventDiv);
    }
}

// Function to get comments for a specific event
function doGetComments(eventID) {
    let url = urlBase + '/GetComments.' + extension;

    let requestData = {
        eventID: eventID
    };

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        // Define the onreadystatechange function to process the response
        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let jsonObject = JSON.parse(xhr.responseText);
                if (jsonObject.error === "") {
                    if (jsonObject.comments && jsonObject.comments.length > 0) {
                        populateComments(jsonObject.comments);
                    } else {
                        document.getElementById("comment-list").innerHTML = "<p>No comments found.</p>";
                    }
                }
            }
        };

        // Convert the request data to JSON and send the request
        let jsonPayload = JSON.stringify(requestData);
        xhr.send(jsonPayload);
    } catch (err) {
        console.error("Error fetching comments:", err.message);
    }
}

// Function to populate comments into the #comment-list element on the left side
function populateComments(comments) {
    let commentContainer = document.getElementById("comment-list");
    commentContainer.innerHTML = ""; // Clear previous comments

    const currentUserEmail = sessionStorage.getItem("email");

    for (let i = 0; i < comments.length; i++) {
        let comment = comments[i];
        let commentDiv = document.createElement("div");
        commentDiv.className = "comment";

        // Build comment content
        let content = `
            <p><strong>Email:</strong> ${comment.email}</p>
            <p><strong>Comment:</strong> <span id="comment-text-${comment.id}">${comment.text}</span></p>
            <p><strong>Time:</strong> ${comment.time}</p>
            <p><strong>Date:</strong> ${comment.date}</p>`;

        // If the comment email matches the session email, show edit/delete buttons
        if (comment.email === currentUserEmail) {
            content += `
                <button onclick="editComment(${comment.id})">Edit</button>
                <button onclick="deleteComment(${comment.id})">Delete</button>
            `;
        }

        commentDiv.innerHTML = content;
        commentContainer.appendChild(commentDiv);
    }
}

function addComment()
{
    let url = urlBase + '/CreateComment.' + extension;

    let lastClickedEventID = sessionStorage.getItem("lastClickedEventID");
    
    if (!lastClickedEventID) {
        alert("Please select an event first!");
        return;
    }

    let commentText = document.getElementById("commentInput").value;
    if (!commentText.trim()) {
        alert("Comment cannot be empty!");
        return;
    }

    let userID = sessionStorage.getItem("userID");

    let commentData = {
        eventID: lastClickedEventID,
        userID: userID,
        text: commentText
    };

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let jsonObject = JSON.parse(xhr.responseText);
            if (jsonObject.error === "")
            {
                console.log("Comment added:", xhr.responseText);
                document.getElementById("commentInput").value = ""; // Clear input
                doGetComments(lastClickedEventID); // Refresh comments
            } 
            else
            {
                console.error("Error adding comment: ", jsonObject.error);
            }
        }
    };

    xhr.send(JSON.stringify(commentData));
}

function editComment(commentID) {
    const textSpan = document.getElementById(`comment-text-${commentID}`);
    const currentText = textSpan.innerText;
    const newText = prompt("Edit your comment:", currentText);

    if (newText !== null && newText.trim() !== "") {
        let url = urlBase + '/EditComment.' + extension;

        const requestData = {
            commentID: commentID,
            newText: newText
        };

        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const jsonObject = JSON.parse(xhr.responseText);
                if (jsonObject.error === "") {
                    doGetComments(sessionStorage.getItem("lastClickedEventID"));
                } else {
                    alert("Error editing comment: " + jsonObject.error);
                }
            }
        };
        xhr.send(JSON.stringify(requestData));
    }
}

function deleteComment(commentID) {
    if (confirm("Are you sure you want to delete this comment?")) {
        let url = urlBase + '/DeleteComment.' + extension;

        const requestData = {
            commentID: commentID
        };

        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const jsonObject = JSON.parse(xhr.responseText);
                if (jsonObject.error === "") {
                    doGetComments(sessionStorage.getItem("lastClickedEventID"));
                } else {
                    alert("Error deleting comment: " + jsonObject.error);
                }
            }
        };
        xhr.send(JSON.stringify(requestData));
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

function toRSO()
{
    sessionStorage.removeItem("lastClickedEventID");
	window.location.href = "RSODashboard.html";
}

function toHome()
{
    sessionStorage.removeItem("lastClickedEventID");
	window.location.href = "dashboard.html";
}