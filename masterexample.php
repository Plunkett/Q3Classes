<?php
/*
 * File: masterexample.php
 * Description: Example usage of the q3master class.
 * Author: Sean Cline (MajinCline)
 * License: You may do whatever you please with this file and any code it contains. It is to be considered public domain.
 */
require("q3master.php");

$m = new q3master("master.urbanterror.net", 27950); // Create a connection with the UrT master server.
$serverlist = $m->getServers(); // Get an array of all the servers on the master.

foreach($serverlist as $server) {
	echo $server . "\n"; // Print out the IP:Port of each server.
}

?>