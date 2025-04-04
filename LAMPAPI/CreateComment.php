<?php
	header("Access-Control-Allow-Origin: *"); // Allow all domains — for development only!
	header("Access-Control-Allow-Headers: Content-Type");
	header("Access-Control-Allow-Methods: POST, OPTIONS");
	
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
		http_response_code(200);
		exit();
	}

	$inData = getRequestInfo();

	$eventID = $inData['eventID']; // int
	$userID = $inData['userID']; // int
	$text = $inData['text']; // string

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("insert into Comments (event_id, user_id, text, time, date) values (?, ?, ?, CURTIME(), CURDATE())");
		$stmt->bind_param("iis", $eventID, $userID, $text);

		if ( $stmt->execute() )
		{
			returnWithInfo();
		}
		else
		{
			returnWithError("Failed to add Comment");
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
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo()
	{
		$retValue = '{"message": "Success", "error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>