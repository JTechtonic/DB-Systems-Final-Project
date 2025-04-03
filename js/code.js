const urlBase = 'http://159.203.173.251/LAMPAPI';
const extension = 'php';

function doLogin()
{
  	//gets the username and password from the input tag on the .html that calls it
	let login = document.getElementById("loginEmail").value;
	let password = document.getElementById("loginPassword").value;
	//	var hash = md5( password );

	document.getElementById("loginResult").innerHTML = "";

  	//creates javascript struct
  	//this is a struct
	let tmp = {email:login,password:password};
  	//	var tmp = {login:login,password:hash};

  	// converts the struct to a json blob
	let jsonPayload = JSON.stringify( tmp );

  	//this picks the php file
	let url = urlBase + '/Login.' + extension;

  	//this is what actually creates the post request (AJAX)
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
    	//this defines the reponse processing functions
		xhr.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{

				let jsonObject = JSON.parse( xhr.responseText );

				if (jsonObject.error === "")
				{
					sessionStorage.setItem("userID", jsonObject.userID);
					sessionStorage.setItem("userLevel", jsonObject.user_level);
					sessionStorage.setItem("email", jsonObject.email);
					sessionStorage.setItem("universityID", jsonObject.universityID);
					sessionStorage.setItem("rsoIDs", jsonObject.rsoIDs);
				}
				else
				{
					document.getElementById("loginResult").innerHTML = "<span>User/Password combination incorrect</span>";
					return;
				}

				//saveCookie();

				window.location.href = "dashboard.html";
			}
		};
    	//This sends the message to the url (Login.php)
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

// Not ready yet
/*function doRegister()
{
	console.log("doRegister working");
	let firstName = document.getElementById("registerFirstName").value;
	let lastName = document.getElementById("registerLastName").value;
	let login = document.getElementById("registerUsername").value;
	let password = document.getElementById("registerPassword").value;
	//	var hash = md5( password );

	document.getElementById("registerResult").innerHTML = "";

	let tmp = {FirstName:firstName,LastName:lastName,login:login,password:password};

	//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/AddUser.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function()
		{
			if (this.readyState == 4 && this.status == 200)
			{
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;

				if( jsonObject.error !== "" )
				{
					document.getElementById("registerResult").innerHTML = jsonObject.error;
					return;
				}

				window.alert("Successful Registeration");

				//saveCookie();

				window.location.href = "dashboard.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("registerResult").innerHTML = err.message;
		window.alert(err.message);
	}

}*/

// function saveCookie() {
//     let minutes = 30;
//     let date = new Date();
//     date.setTime(date.getTime() + minutes * 60 * 1000);
    
//     // Encode all values before saving the cookie
//     let encodedUserID = encodeURIComponent(userID);
//     let encodedUserLevel = encodeURIComponent(userLevel);
//     let encodedEmail = encodeURIComponent(email);
//     let encodedUniversityID = encodeURIComponent(universityID);
//     let encodedRSOIDs = encodeURIComponent(JSON.stringify(rsoIDs));

//     document.cookie = "userID=" + encodedUserID +
//                       ";userLevel=" + encodedUserLevel +
//                       ";email=" + encodedEmail +
//                       ";universityID=" + encodedUniversityID +
//                       ";rsoIDs=" + encodedRSOIDs +
//                       ";expires=" +
//                       ";path=/";  // Ensure it applies to the whole site
// }

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "login.html";
}

function toRSO()
{
	window.location.href = "RSODashboard.html";
}

function toHome()
{
	window.location.href = "dashboard.html";
}


// function readCookie() {
//     let data = document.cookie;
//     console.log("Cookies:", data);

//     let cookieObj = {};
//     let cookieArr = data.split(";");

//     for (let i = 0; i < cookieArr.length; i++) {
//         let thisOne = cookieArr[i].trim();
//         let tokens = thisOne.split("=");
//         cookieObj[tokens[0]] = tokens[1];
//     }

//     // Assign values if they exist
//     userID = cookieObj["userID"] ? parseInt(cookieObj["userID"]) : -1;
//     userLevel = cookieObj["userLevel"] || "";
//     email = cookieObj["email"] || "";
//     universityID = cookieObj["universityID"] || "";

//     // Decode and parse rsoIDs
//     if (cookieObj["rsoIDs"]) {
//         try {
//             rsoIDs = JSON.parse(decodeURIComponent(cookieObj["rsoIDs"]));
//         } catch (error) {
//             console.error("Error parsing rsoIDs:", error);
//             rsoIDs = [];
//         }
//     } else {
//         rsoIDs = [];
//     }

//     if (userID < 0) {
//         window.location.href = "login.html";
//     } else {
//         console.log("User ID found:", userID);
//         return userID;
//     }
// }