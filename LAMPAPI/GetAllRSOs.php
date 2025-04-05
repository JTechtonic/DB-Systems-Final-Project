<?php
	include_once('cors.php');
	$inData = getRequestInfo();
	
	$universityID = $inData['universityID']; // int

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select rso_id, name, active from RSOs where university_id=?");
		$stmt->bind_param("i", $universityID);
		$stmt->execute();
		$result = $stmt->get_result();

		$numRSO = 0;
		$rsoArray = "";

		while ($row = $result->fetch_assoc())
		{
			if ($numRSO > 0)
				$rsoArray .= ',';
			
			$numRSO++;
			$rsoArray .= '{"rsoID": '. $row['rso_id'] .', "name": "'. $row['name'] . '", "active": '. $row['active'] .'}';
		}

		returnWithInfo($rsoArray);
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
	
	function returnWithInfo( $rsoArray )
	{
		$retValue = '{"rsoArray":[' . $rsoArray . '],"error":""}';
		sendResultInfoAsJson( $retValue );
	}
?>