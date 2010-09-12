<?php
/*
 * File: statusexample.php
 * Description: Example usage of the q3status class.
 * Author: Sean Cline (MajinCline)
 * License: You may do whatever you please with this file and any code it contains. It is to be considered public domain.
 */
require('q3status.php'); // Include the necesary class to send rcons.

$s = new q3status("208.43.49.161", 27960); // Create a new q3status with the server IP, Port.
$result = $s->update_status(); // Get the status from the server so we can go through cvars and players.

if (!$result) {
      echo "There was a problem getting the server status.\n";
}

// Display the server's name and number of players.
echo "Server name: " . strip_colors($s->cvarlist['sv_hostname']) . "\n";
echo "Players: " . $s->get_num_players() . "/" . $s->cvarlist['sv_maxclients'] . "\n";

// Dump out all the players on the server.
foreach ($s->playerlist as $playernumber => $playerinfo) {
      echo "Player number: " . $playernumber . "\n";
      echo "Player name: " . $playerinfo['name'] . "\n";
      echo "Player name(no colors): " . $playerinfo['strippedname'] . "\n";
      echo "Player score: " . $playerinfo['score'] . "\n";
      echo "Player ping: " . $playerinfo['ping'] . "\n";
      echo "\n";
}

// Dump out all the cvars reported to us.
foreach ($s->cvarlist as $cvarname => $cvarvalue) {
      echo $cvarname . " = " . $cvarvalue . "\n";
}

?>
