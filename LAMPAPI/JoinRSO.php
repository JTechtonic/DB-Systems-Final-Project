<?php
	$inData = getRequestInfo();

	$rsoID = $inData['rsoID']; // int
	$userID = $inData['userID']; // int

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("insert into RSO_Members (rso_id, user_id) values (?, ?)");
		$stmt->bind_param("ii", $rsoID, $userID);

		if ( $stmt->execute() )
		{
			returnWithInfo();
		}
		else
		{
			returnWithError("Failed to add into RSO");
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