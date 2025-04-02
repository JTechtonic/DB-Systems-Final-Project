<?php
	$inData = getRequestInfo();

	$eventID = $inData['eventID'];
	$userID = $inData['userID'];
	$text = $inData['text'];

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

		$numComments = 0;
		$commentsArray = "";

		while ($row = $comments->fetch_assoc())
		{
			if ($numComments > 0)
				$commentsArray .= ',';
			
			$numComments++;
			$commentsArray .= '{"text": "'. $row['text'] .'", "time": "'. $row['time'] .'", "date":"'. $row['date'] .'"}';
		}

		returnWithInfo($eventID, $commentsArray);
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