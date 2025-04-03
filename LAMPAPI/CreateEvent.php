<?php
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$inData = getRequestInfo();

	$userID = $inData['userID']; // int
	$universityID = $inData['universityID']; // int

	$locationName = $inData['locationName']; // string
	$locationLat = $inData['locationLat']; // float
	$locationLong = $inData['locationLong']; // float

	$rsoID = $inData['rsoID']; // int
	$name = $inData['name']; // string
	$category = $inData['category']; // string
	$description = $inData['description']; // string
	$time = $inData['time']; // string ('hh:mm:ss')
	$date = $inData['date']; // string ('yyyy-mm-dd')
	$contactPhone = $inData['contactPhone']; // string
 	$contactEmail = $inData['contactEmail']; // string
	$visibility = $inData['visibility']; // string ('public', 'private', or 'rso')


	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$eventID = 0;

		// Make new location entry
		$stmt = $conn->prepare("insert into Locations (name, latitude, longitude) values (?, ?, ?)");
		$stmt->bind_param("sdd", $locationName, $locationLat, $locationLong);

		
		try
		{
			$stmt->execute();
			$locationID = $conn->insert_id;

			// Make new event entry
			$stmt = $conn->prepare("insert into Events (university_id, location_id, rso_id, name, category, description, time, date, contact_phone, contact_email, visibility, approval_status)
			                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, true)");
			$stmt->bind_param("iiissssssss",
							  $universityID, $locationID, $rsoID, $name, $category, $description, $time, $date,
							  $contactPhone, $contactEmail, $visibility);

			$stmt->execute();
			$eventID = $conn->insert_id;

			// Make sure junction tables are updated
			if ($visibility === 'public')
			{
				$stmt = $conn->prepare("insert into Public_Events (event_id, created_by) values (?, ?)");
				$stmt->bind_param("ii", $eventID, $userID);
				$stmt->execute();
			}
			else if ($visibility === 'private')
			{
				$stmt = $conn->prepare("insert into Private_Events (event_id, created_by) values (?, ?)");
				$stmt->bind_param("ii", $eventID, $userID);
				$stmt->execute();
			}
			else if ($visibility === 'rso')
			{
				$stmt = $conn->prepare("insert into RSO_Events (event_id, rso_id, created_by) values (?, ?, ?)");
				$stmt->bind_param("iii", $eventID, $rsoID, $userID);
				$stmt->execute();
			}
		}
		catch (mysqli_sql_exception $e)
		{
			returnWithError($e->getMessage());
			$stmt->close();
			$conn->close();
			return;
		}

		returnWithInfo($eventID, $visibility);
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
	
	function returnWithInfo( $eventID, $visibility )
	{
		$retValue = '{"eventID":"' . $eventID . '", "visibility": "'. $visibility .'", "error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>