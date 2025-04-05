<?php
	include_once('cors.php');
	$inData = getRequestInfo();
	
	$userID = $inData['userID']; // int

	$conn = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		$stmt = $conn->prepare("select r.* FROM RSOs r JOIN RSO_Members rm ON r.rso_id = rm.rso_id WHERE rm.user_id = ?");
		$stmt->bind_param("i", $userID);
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