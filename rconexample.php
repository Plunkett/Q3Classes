<?php
/*
 * File: rconexample.php
 * Description: Example usage of the q3rcon class.
 * Author: Sean Cline (MajinCline)
 * License: You may do whatever you please with this file and any code it contains. It is to be considered public domain.
 */
require('q3rcon.php'); // Include the necesary class to send rcons.

$r = new q3rcon("208.43.49.161", 27960, "rconpassword"); // Create a new q3rcon with the server IP, Port, and rconpassword.

$r->send_command("cvarlist"); // Send a cvarlist command to the server we defined above.
echo $r->get_response(); // Print out the results of the cvarlist command.

sleep(1); // Sleep a bit to make sure the server doesn't drop our rcon.

$r->send_command("status"); // Send a status command.
echo $r->get_response(); // Print out the results of the status command.

?>
