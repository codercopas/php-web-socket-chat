<?php
	
	$address = "0.0.0.0";
	$port = 8090;
	$null = NULL;
	
	$server_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_bind($server_socket, $address, $port);
	socket_listen($server_socket);
	
	echo "Listening for connection on port " . $port . "\n";
	
	$connections[] = $server_socket;

	while(true) {
		
		$reads = $connections;
		socket_select($reads, $null, $null, $null);
		
		if(in_array($server_socket, $reads)) {
			$new_client = socket_accept($server_socket);
			
			handshake($new_client);
			
			$connections[] = $new_client;
			$reply = "Connected to the chat socket server \n";
			$reply = encode($reply);
			socket_write($new_client, $reply, strlen($reply));
			
			$index = array_search($server_socket, $reads);
			unset($reads[$index]);
		}
		

		foreach($reads as $ridx => $rsocket) {
			$data = socket_read($rsocket, 1024);
			
			if ($data === false || $data === "" || ord($data[0]) === 0x88) {
				echo "Disconnecting client" . $ridx . "\n";
				unset($connections[$ridx]);
				socket_close($rsocket);
			} else {
				$message = decode($data);
				if(mb_check_encoding($message, "UTF-8")) {
					$encoded_message = encode($message);
					foreach($connections as $cidx => $csocket) {
						if($cidx === 0) continue;
						socket_write($csocket, $encoded_message . "\r\n", strlen($encoded_message));
					}
				}
			}

		}
		
	}
	
	function decode($input) {
		$length = @ord($input[1]) & 127;
		if($length == 126) {
			$mask = substr($input, 4, 4);
			$data = substr($input, 8);
		}
		else if($length == 127) {
			$mask = substr($input, 10, 4);
			$data = substr($input, 14);
		}
		else {
			$mask = substr($input, 2, 4);
			$data = substr($input, 6);
		}
		$text = "";
		for($i = 0; $i < strlen($data); $i++) {
			$text .= $data[$i] ^ $mask[$i % 4];
		}
		return $text;
	}
	
	
	function encode($input) {
		$b1 = 0x81;
		$length = strlen($input);
		
		if($length <= 125) {
			$header = pack('CC', $b1, $length);
		}
		else if($length > 125 && $length < 65536) {
			$header = pack("CCn", $b1, 126, $length);
		}
		else if($length >= 65536) {
			$header = pack("CCNN", $b1, 127, $length);
		}
		return $header . $input;
	}
	
	
	function handshake($socket) {
		$request = socket_read($socket, 1024);
		if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $request, $matches)) {
			$key = trim($matches[1]);
			$accept_key = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			$headers = "HTTP/1.1 101 Switching Protocols\r\n";
			$headers .= "Upgrade: websocket\r\n";
			$headers .= "Connection: Upgrade\r\n";
			$headers .= "Sec-WebSocket-Accept: " . $accept_key . "\r\n\r\n";
			socket_write($socket, $headers, strlen($headers));
		}
	}
	
	socket_close($server_socket);
	
?>
