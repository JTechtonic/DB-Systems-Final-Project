<?php
	$inData = getRequestInfo();

	$commentID = $inData['commentID']; // int
	$newText = $inData['newText']; // String

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("update Comments set text=? where comment_id=?");
		$stmt->bind_param("si", $newText, $commentID);

		if ( $stmt->execute() )
		{
			returnWithInfo();
		}
		else
		{
			returnWithError("Failed to edit Comment");
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
		$retValue = '{"message": "Edit success", "error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>