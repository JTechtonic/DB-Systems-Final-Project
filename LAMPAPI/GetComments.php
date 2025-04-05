<?php
	include_once('cors.php');
	$inData = getRequestInfo();

	$eventID = $inData['eventID']; // int

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select * from Comments where event_id = ?");
		$stmt->bind_param("i", $eventID);
		$stmt->execute();
		$comments = $stmt->get_result();

		$numComments = 0;
		$commentsArray = "";

		while ($row = $comments->fetch_assoc())
		{
			$userStmt = $conn->prepare("select email from Users where user_id=?");
			$userStmt->bind_param("i", $row['user_id']);
			$userStmt->execute();
			$email = ($userStmt->get_result()->fetch_assoc())['email'];

			if ($numComments > 0)
				$commentsArray .= ',';
			
			$numComments++;
			$commentsArray .= '{"id": '. $row['comment_id'] .', "email": "'. $email .'", "text": "'. $row['text'] .'", "time": "'. $row['time'] .'", "date":"'. $row['date'] .'"}';
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
	
	function returnWithInfo( $eventID, $commentsArray )
	{
		$retValue = '{"eventID": "'. $eventID .'", "comments":[' . $commentsArray . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>