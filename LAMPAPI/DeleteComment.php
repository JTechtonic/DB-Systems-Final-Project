<?php
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
		$stmt = $conn->prepare("delete from Comments where event_id=? and user_id=? and text=?");
		$stmt->bind_param("iis", $eventID, $userID, $text);

		if ( $stmt->execute() )
		{
			returnWithInfo();
		}
		else
		{
			returnWithError("Failed to delete Comment");
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
		$retValue = '{"message": "Deletion success", "error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>