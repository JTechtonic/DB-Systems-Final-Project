<?php
	$inData = getRequestInfo();

	$universityID = $inData['university_id'];
	$rsoID = $inData['rso_id'];

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select * from Events where (visibility = 'public' or (university_id = ? and (rso_id = ? or rso_id is null))) and approval_status = true");
		$stmt->bind_param("ii", $universityID, $rsoID);
		$stmt->execute();
		$result = $stmt->get_result();

		$searchResults = "";
		$searchCount = 0;
		
		while ($row = $result->fetch_assoc())
		{
			// Get university name
			$stmt = $conn->prepare("select name from Universities where university_id=?");
			$stmt->bind_param("i", $row['university_id']);
			$stmt->execute();
			$retVal = $stmt->get_result()->fetch_assoc();
			$universityName = $retVal['name'];

			// Get RSO name
			$rsoName = '';
			
			// If the event is associated with an RSO then get name if not then leave blank
			if (!($row['rso_id'] == NULL || $row['rso_id'] == 0))
			{
				$stmt = $conn->prepare("select name from RSOs where rso_id=?");
				$stmt->bind_param("i", $row['rso_id']);
				$stmt->execute();
				$retVal = $stmt->get_result()->fetch_assoc();
				$rsoName = $retVal['name'];	
			}
			
			// Get Location Name
			$stmt = $conn->prepare("select name from Locations where location_id=?");
			$stmt->bind_param("i", $row['location_id']);
			$stmt->execute();
			$retVal = $stmt->get_result()->fetch_assoc();
			$locationName = $retVal['name'];

			// Add onto resultArray
			if ($searchCount > 0)
				$searchResults .= ',';

			$searchCount++;
			$searchResults .= '{"universityName" : "'. $universityName .'", "rsoName" : "'. $rsoName .'", "locationName" : "'. $locationName .'", "eventName" : "'. $row['name'] .'", "category" : "'. $row['category'] .'", "description" : "'. $row['description'] .'", "time" : "'. $row['time'] .'", "date" : "'. $row['date'] .'", "phoneNumber" : "'. $row['contact_phone'] .'", "email" : "'. $row['contact_email'] .'"}';
		}

		returnWithInfo($searchResults);
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
	
	function returnWithInfo( $searchResults )
	{
		$retValue = '{"results":[' . $searchResults . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>