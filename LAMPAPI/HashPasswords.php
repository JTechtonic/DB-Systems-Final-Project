<?php
// Uncomment if needed
// // Assuming you already have a connection to MySQL:
// $mysqli = new mysqli("localhost", "developer", "jSn3ir6qAvNzffJ", "mainDB");

// if ($mysqli->connect_error) {
//     die("Connection failed: " . $mysqli->connect_error);
// }

// // Fetch all users from the table
// $result = $mysqli->query("SELECT user_id, password_hash FROM Users");

// // Iterate over each user and hash the password
// while ($row = $result->fetch_assoc()) {
//     $userId = $row['user_id'];
//     $plainPassword = $row['password_hash'];  // Original plain password

//     // Hash the password using bcrypt
//     $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

//     // Update the password_hash column in the Users table
//     $stmt = $mysqli->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
//     $stmt->bind_param("si", $hashedPassword, $userId);
//     $stmt->execute();
// }

// echo "Password hashes updated successfully.";
// $mysqli->close();
?>
