<?php

	$inData = getRequestInfo();

	$Email = $inData["email"]; // string
	$Password = $inData["password"]; // string


	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");

	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("SELECT * FROM Users WHERE email=? AND password_hash=?");
		$stmt->bind_param("ss", $Email, $Password);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $row = $result->fetch_assoc()  )
		{
			$userID = $row['user_id'];
			$universityID = $row['university_id'];
			$email = $row['email'];
			$user_level = $row['user_level'];

			// Get RSO memberships
			$rsoStmt = $conn->prepare("SELECT rso_id FROM RSO_Members WHERE user_id=?");
			$rsoStmt->bind_param("i", $userID);
			$rsoStmt->execute();
			$rsoResult = $rsoStmt->get_result();

			$rsoArray = [];
			while ($rsoRow = $rsoResult->fetch_assoc()) {
				$rsoArray[] = $rsoRow['rso_id'];
			}

			$rsoStmt->close();
			returnWithInfo($userID, $universityID, $email, $user_level, $rsoArray);
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
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

	function returnWithInfo($userID, $universityID, $email, $user_level, $rsoArray)
	{
		$retValue = json_encode([
			"userID" => $userID,
			"user_level" => $user_level,
			"email" => $email,
			"universityID" => $universityID,
			"rsoIDs" => $rsoArray,
			"error" => ""
		]);
		
		sendResultInfoAsJson($retValue);
	}
?>
