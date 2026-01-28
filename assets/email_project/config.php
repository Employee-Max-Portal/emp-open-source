<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'superman');
define('DB_PASSWORD', 'Sup3rM@n_2025!');
define('DB_NAME', 'sohub_emp');
 
/* Attempt to connect to MySQL database */
$link = $con = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


$sql = "SELECT * FROM email_config where id=1";
$result = mysqli_query($link, $sql);

if ($result) {
    // Check if there are rows in the result set
    if (mysqli_num_rows($result) > 0) {
        // Fetch and display each row's data
        while ($row = mysqli_fetch_assoc($result)) {
            $email = $row['email'];
            $email_protocol = $row['email_protocol'];
            $smtp_host = $row['smtp_host'];
            $smtp_user = $row['smtp_user'];
            $smtp_pass = $row['smtp_pass'];
            $smtp_port = $row['smtp_port'];
            $smtp_encryption = $row['smtp_encryption'];
        }
    } else {
        echo "No rows found in email_config.";
    }

    // Free result set
    mysqli_free_result($result);
} 

?>
