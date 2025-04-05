const urlBase = 'http://159.203.173.251/LAMPAPI';
const extension = 'php';

document.addEventListener("DOMContentLoaded", function () {
    loadRSOs();
});

function loadRSOs() {
    const userID = sessionStorage.getItem("userID");
    const universityID = sessionStorage.getItem("universityID");

    if (!userID || !universityID) {
        console.error("Missing userID or universityID");
        return;
    }

    getJoinedRSOs(userID, function (joinedRSOs) {
        getAllRSOs(universityID, function (allRSOs) {
            const joinedIDs = joinedRSOs.map(rso => rso.rsoID);
            const availableRSOs = allRSOs.filter(rso => !joinedIDs.includes(rso.rsoID));

            populateRSOSection("RSO-list", joinedRSOs, true);
            populateRSOSection("RSO-avalible", availableRSOs, false);
        });
    });
}

function getJoinedRSOs(userID, callback) {
    const url = urlBase + '/GetJoinedRSOs.' + extension;
    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json");

    const requestData = { userID: userID };

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const jsonObject = JSON.parse(xhr.responseText);
            if (jsonObject.error === "") {
                callback(jsonObject.rsoArray || []);
            } else {
                console.error("Error fetching joined RSOs:", jsonObject.error);
                callback([]);
            }
        }
    };

    xhr.send(JSON.stringify(requestData));
}

function getAllRSOs(universityID, callback) {
    const url = urlBase + '/GetAllRSOs.' + extension;
    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json");

    const requestData = { universityID: universityID };

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const jsonObject = JSON.parse(xhr.responseText);
            if (jsonObject.error === "") {
                callback(jsonObject.rsoArray || []);
            } else {
                console.error("Error fetching all RSOs:", jsonObject.error);
                callback([]);
            }
        }
    };

    xhr.send(JSON.stringify(requestData));
}

function populateRSOSection(containerID, rsos, isJoined) {
    const container = document.getElementById(containerID);
    container.innerHTML = "";

    rsos.forEach(rso => {
        const rsoDiv = document.createElement("div");
        rsoDiv.className = "rso-entry";

        const rsoInfo = document.createElement("p");
        rsoInfo.innerHTML = `<strong>${rso.name}</strong><br>${rso.active ? 'Active' : 'Not Active'}`;
        rsoDiv.appendChild(rsoInfo);

        const button = document.createElement("button");
        button.textContent = isJoined ? "Leave" : "Join";
        button.onclick = function () {
            isJoined ? leaveRSO(rso.rsoID) : joinRSO(rso.rsoID);
        };
        rsoDiv.appendChild(button);

        container.appendChild(rsoDiv);
    });
}

function joinRSO(rsoID) {
    const url = urlBase + '/JoinRSO.' + extension;
    const userID = sessionStorage.getItem("userID");

    const requestData = { userID: userID, rsoID: rsoID };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json");

    xhr.onreadystatechange = function ()
    {
        if (xhr.readyState === 4 && xhr.status === 200)
        {
            const jsonObject = JSON.parse(xhr.responseText);
            if (jsonObject.error === "")
            {
                let rsoIDs = sessionStorage.getItem('rsoIDs');

                rsoIDs = rsoIDs ? rsoIDs.split(',') : [];

                if (!rsoIDs.includes(rsoID)) {
                    rsoIDs.push(rsoID);
                }

                // Update sessionStorage with the new list of rsoIDs
                sessionStorage.setItem('rsoIDs', rsoIDs.join(','));
                loadRSOs();
            }
            else
            {
                alert("Failed to join RSO: " + jsonObject.error);
            }
        }
    };

    xhr.send(JSON.stringify(requestData));
}

function leaveRSO(rsoID) {
    const url = urlBase + '/LeaveRSO.' + extension;
    const userID = sessionStorage.getItem("userID");

    const requestData = { userID: userID, rsoID: rsoID };

    const xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json");

    xhr.onreadystatechange = function ()
    {
        if (xhr.readyState === 4 && xhr.status === 200)
        {
            const jsonObject = JSON.parse(xhr.responseText);
            if (jsonObject.error === "")
            {
                let rsoIDs = sessionStorage.getItem('rsoIDs');

                rsoIDs = rsoIDs ? rsoIDs.split(',') : [];

                rsoIDs = rsoIDs.filter(id => id !== String(rsoID));

                if (rsoIDs.length === 0)
                    sessionStorage.removeItem('rsoIDs');
                else
                    sessionStorage.setItem('rsoIDs', rsoIDs.join(','));
                
                loadRSOs();
            }
            else
            {
                alert("Failed to leave RSO: " + jsonObject.error);
            }
        }
    };

    xhr.send(JSON.stringify(requestData));
}
