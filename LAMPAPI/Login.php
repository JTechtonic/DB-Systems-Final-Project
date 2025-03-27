<?php

	$inData = getRequestInfo();

	$Email = $inData["email"];
	$Password = $inData["password"];


	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");

	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("SELECT university_id, email, user_level FROM Users WHERE email=? AND password_hash=?");
		$stmt->bind_param("ss", $Email, $Password);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $row = $result->fetch_assoc()  )
		{
			returnWithInfo( $row['university_id'], $row['email'], $row['user_level'] );
		}
		else
		{
			returnWithError("No Records Found");
		}

		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}

	function returnWithError( $err )
	{
		$retValue = '{"user_level":-1, "email":"", "universityID":"", "error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

	function returnWithInfo( $universityID, $email, $user_level )
	{
		$retValue = '{"user_level":"' . $user_level . '","email":"' . $email . '","universityID":' . $universityID . ',"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>
