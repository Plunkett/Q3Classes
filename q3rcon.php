<?php
/*
 * File: q3rcon.php
 * Description: Quake 3 Engine rcon class for PHP
 * Author: Sean Cline (MajinCline)
 * License: You may do whatever you please with this file and any code it contains. It is to be considered public domain.
 */

class q3rcon {

        // Some vars needed for rcon info
        private $password;

        // Some vars to store what server we will connect to
        private $address;
        private $port;
        private $socket_connection;

        // Misc. other vars
        private $last_socket_err_num;
        private $last_socket_err_str;

        // Constructor: takes the IP address (or hostname), port, and rcon password of
        // the server and opens a connection.
        public function __construct($serv_address, $serv_port, $serv_password, $timeout=30) {
                $this->address = $serv_address;
                $this->port = intval($serv_port);
                $this->password = $serv_password;

                $this->last_socket_err_num = -1;
                $this->last_socket_err_str = "";

                // Open up the connection wih the given address and port
                $this->socket_connection = fsockopen("udp://" . $this->address, $this->port, $this->last_socket_err_num, $this->last_socket_err_str, $timeout);
                if (!$this->socket_connection) {
                        die("Could not connect with given ip:port\n<br>errno: $this->last_socket_err_num\n<br>errstr: $this->last_socket_err_str");
                }

        }

        // Precondition: A socket has been opened without error by the constructor
        // Postcondidtion: The given command will be sent and a response will be
        //      recoverable from the function get_response()
        public function send_command($cmd) {
                fwrite($this->socket_connection, str_repeat(chr(255), 4) . "rcon " . $this->password . " " . $cmd . "\n");
        }

        // Get the server's response to our previous query.
        // Precondition: A command should have already been sent with send_command($cmd).
        // Postcondidtion: The server's response string will be returned.
        public function get_response() {
                stream_set_timeout($this->socket_connection, 0, 500000);
                $buffer = "";
                while ($buff = fread($this->socket_connection, 9999)) {
                        list($header, $contents) = explode("\n", $buff, 2); // Trim off the header of each packet we receive.
                        $buffer .= $contents;
                }
                return $buffer;
        }

        public function close() {
                fclose($this->socket_connection);
        }
}
?>
