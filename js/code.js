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


function doRegister(role)
{
	let email = document.getElementById("email").value;
	let password = document.getElementById("password").value;
	//	var hash = md5( password );

	document.getElementById("registerResult").innerHTML = "";

	let tmp = {
		email: email,
		password: password,
		userLevel: role
	};

	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/Register.' + extension;

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
				if (jsonObject.error === "")
				{
					if (role === 'student')
					{
						sessionStorage.setItem("userID", jsonObject.userID);
						sessionStorage.setItem("userLevel", jsonObject.user_level);
						sessionStorage.setItem("email", jsonObject.email);
						sessionStorage.setItem("universityID", jsonObject.universityID);
						sessionStorage.setItem("rsoIDs", "");
					}
					
					window.alert("Successful Registeration");

					if (role === 'student')
						window.location.href = "dashboard.html";
				}
				else
				{
					document.getElementById("registerResult").innerHTML = "<span>Error: " + jsonObject.error +"</span>";
					return;
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("registerResult").innerHTML = err.message;
	}
}

function doLogout()
{
	sessionStorage.clear();
	window.location.href = "index.html";
}

function toRSO()
{
	window.location.href = "RSODashboard.html";
}

function toAdminCreate()
{
	window.location.href = "createAdmin.html";
}

function toHome()
{
	window.location.href = "dashboard.html";
}