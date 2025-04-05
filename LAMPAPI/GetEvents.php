<?php
	include_once('cors.php');
	$inData = getRequestInfo();

	$universityID = $inData['universityID']; // int
	$rsoIDs = $inData['rsoIDs']; // array of ints

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		// Check if the rsoIDs array is empty
        if (empty($rsoIDs))
		{
            // If empty, skip the rso_id filter in the SQL query
            $sql = "SELECT * FROM Events 
                    WHERE (visibility = 'public' OR (university_id = ? AND rso_id IS NULL)) 
                    AND approval_status = true";
					
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $universityID);
        }
		else
		{
            // Prepare placeholders for rsoIDs array
            $rsoPlaceholders = implode(',', array_fill(0, count($rsoIDs), '?'));

            // Prepare SQL query with university and rso filter
            $sql = "SELECT * FROM Events 
                    WHERE (visibility = 'public' OR (university_id = ? AND (rso_id IN ($rsoPlaceholders) OR rso_id IS NULL))) 
                    AND approval_status = true";

            $stmt = $conn->prepare($sql);
            
            // Bind parameters dynamically
            $paramTypes = str_repeat('i', count($rsoIDs) + 1); // 'i' for each integer
            $stmt->bind_param($paramTypes, $universityID, ...$rsoIDs);
        }

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
			$searchResults .= '{"eventID": "'. $row['event_id'] .'", "universityName" : "'. $universityName .'", "rsoName" : "'. $rsoName .'", "locationName" : "'. $locationName .'", "eventName" : "'. $row['name'] .'", "category" : "'. $row['category'] .'", "description" : "'. $row['description'] .'", "time" : "'. $row['time'] .'", "date" : "'. $row['date'] .'", "phoneNumber" : "'. $row['contact_phone'] .'", "email" : "'. $row['contact_email'] .'", "visibility": "'. $row['visibility'] .'"}';
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