<?php

	$inData = getRequestInfo();

	$Email = $inData["email"]; // string
	$Password = $inData["password"]; // string
	$userLevel = $inData['userLevel']; // string ('student' or 'admin')
	
	$searchCount = 0;

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
        // Grab number of users with the same email
		$stmt = $conn->prepare("select * from Users where email=?");
		$stmt->bind_param("s", $Email);
		$stmt->execute();
		$result = $stmt->get_result();

		while ($row = $result->fetch_assoc())
		{
			$searchCount++;
		}		
		
        // If unique email allow register; if not deny register
		if ($searchCount == 0)
		{
            // Grab the university suffix from email
            $suffix = substr($Email, strpos($Email, "@") + 1);
            $universityID = 0;

            $stmt = $conn->prepare("Select university_id from Universities where email_suffix=?");
            $stmt->bind_param("s", $suffix);
            $stmt->execute();
            $result = $stmt->get_result();

            // If the user if from an established university set variable to reflect that
            while ($row = $result->fetch_assoc())
            {
                $universityID = $row['university_id'];
            }

            // Register user
			$stmt = $conn->prepare("insert into Users (university_id, email, password_hash, user_level) VALUES (?, ?, ?, ?)");
			$stmt->bind_param("ssss", $universityID, $Email, $Password, $userLevel);

			if ($stmt->execute())
			{
				returnWithInfo( $conn->insert_id, $universityID, $Email, $userLevel);
			}
			else
			{
				returnWithError("Failed to add User");
			}
		}
		else
		{
			returnWithError("User Already Exists");
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
	
	function returnWithInfo( $userID, $universityID, $email, $user_level )
	{
		$retValue = '{"userID": "'. $userID .'", "user_level":"' . $user_level . '","email":"' . $email . '","universityID":' . $universityID . ',"error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>