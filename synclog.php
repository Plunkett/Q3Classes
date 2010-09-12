#!/usr/bin/php -q
<?php
/*
 * File: synclog.php
 * Description: Keep a local text file synchronized with a file on a remote FTP server.
 * Author: Sean Cline (MajinCline)
 * License: You may do whatever you please with this file and any code it contains. It is to be considered public domain.
 */
 
// ##########################################
// Define some vars we will need later
// ##########################################
$ftp_server = "208.43.49.161";                  // The ftp server your game log is stored on
$ftp_user = "username";                         // The user to sign into the ftp server as
$ftp_pass = "password";                         // The corresponding password
$ftp_file = "urbanterror/q3ut4/games.log";      // The path relative to the ftp server's root of your game log
$local_file = "games.log";                      // The path of our local file (either absolute or relative to the working dir)
$loggingenabled = true;                         // Output log strings?
$update_interval = 1000000;                     // How long between checking for an updated file in MICRO seconds (1000000 = 1second)
$downloads_until_fullDL = 1200;                 // After this many downloads the script will reconnect to the server and do a full download

// ##########################################
// Set up timestamps that we can output for logging purposes
// ##########################################
$time = time();
log("Starting up: " . date(DATE_RFC822, $time));

// ##########################################
// Start the real work...
// ##########################################

logstr("Creating local $local_file file");
$filehandle = fopen($local_file, "w");

logstr("Connecting to ftp://$ftp_user@$ftp_server/");
$ftp_con = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");


if (@ftp_login($ftp_con, $ftp_user, $ftp_pass)) {
        logstr("Logging into to ftp://$ftp_user@$ftp_server/");
} else {
        logstr("Couldn't log into ftp://$ftp_user@$ftp_server/");
        die();
}

$resume_pos = 0; // This variable will hold at what position we currently are in the file so we can only download what has changed.
$ftpfilesize = -1;
$num_downloads_since_reconnect = 0;
logstr("Starting loop...");
while(1) {
        if ($num_downloads_since_reconnect < $downloads_until_fullDL && @ftp_fget($ftp_con, $filehandle , $ftp_file, FTP_BINARY, $resume_pos)) {
                logstr("Successfully updated $ftp_file to $local_file");
                $num_downloads_since_reconnect++;
        } else {
                // At this point we need to drop the connection, reconnect, and redownload from the beginning.
                if($num_downloads_since_reconnect < $downloads_until_fullDL) {
                        logstr("We have synched to the server's log file $num_downloads_since_reconnect so it is time to reconnect...");
                } else {
                        logstr("There was a problem while downloading $remote_file to $local_file");
                }

                logstr("Closing and reopening $local_file and the connection to ftp://$ftp_user@$ftp_server/");
                ftp_close($ftp_con);
                logstr("Trying to reconnect to ftp://$ftp_user@$ftp_server/");
                $ftp_con = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");
                ftp_login($ftp_con, $ftp_user, $ftp_pass);
                $resume_pos = 0;
                fclose($filehandle);
                $filehandle = fopen($local_file, "w");
                ftruncate($filehandle, 0);
                sleep(15); // This can probably be removed but just incase there is an error here we don't want to bombard the server with login attempts
                ftp_fget($ftp_con, $filehandle , $ftp_file, FTP_BINARY, $resume_pos);
                $num_downloads_since_reconnect = 0;
        }
        $resume_pos = filesize($local_file);

        // Wait for a change in file size
        list($originalsize, $newsize) = ftp_waitfilechange($ftp_con, $ftp_file, $update_interval);
        if ($newsize < $originalsize) { // In the event that the file is smaller (purged log?) redownload starting at begining
                $resume_pos = 0;
                ftruncate($filehandle, 0);
        }
}

ftp_close($ftp_con);
fclose($filehandle);
logstr("Script closed -- This should probably not have happened...");

// ##########################################
// Define some helpful functions
// ##########################################

// This function will take the the open connection and the name of the file and wait for its size to change
function ftp_waitfilechange($ftp_con, $ftp_file, $update_int) {
        $originalsize = ftp_size($ftp_con, $ftp_file);

        if ($originalsize == -1) {
                sleep(2);
                logstr("The FTP server does not support the file_size() function. This will result in a very slow B3 and a lot of bandwidth usage. Continuing anyway...");
                return array(-1, -1);
        }

        $newsize = ftp_size($ftp_con, $ftp_file);
        while($originalsize == $newsize) {
                logstr("File $ftp_file has not changed size since last check");
                usleep($update_int);
                $newsize = ftp_size($ftp_con, $ftp_file);
        }

        // The filesize on the server must have changed so return the original size of the file and the new size
        return array($originalsize, $newsize);
}

// All this function does is provide some logging functionality by outputting a timestamp.
// At the moment it just echos the string but this can be easily modified to save to a file...
function logstr($logstr) {
        global $loggingenabled, $time;
        if($loggingenabled) {
                echo (time() - $time) . " - " . $logstr . "\n";
        }
}
?>
