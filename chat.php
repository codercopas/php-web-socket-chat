<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>PHP Websocket Chat</title>
		<style>
			body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        #chat {
            width: 90%;
            max-width: 500px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
			height: 70%;
			margin-top:60px;
        }
        #header {
            background-color: #007bff;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }
        #messages {
            flex: 1;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            border-bottom: 1px solid #ddd;
        }
        #messages div {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f1f1f1;
            border-radius: 4px;
        }
        #message-input {
            display: flex;
            border-top: 1px solid #ddd;
        }
        #nickname {
            padding: 10px;
            border: none;
            outline: none;
			width: 15%;
			border-right: 1px solid #e1e1e1;
			text-align: center;
			font-weight: bold;
        }
        #message {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            outline: none;
            flex: 0 0 60px; /* Fixed width for button */
        }
        button:hover {
            background-color: #0056b3;
        }
		</style>
	</head>
<body>
	 <div id="chat">
        <div id="header">PHP WebSocket Chat</div>
        <div id="messages"></div>
        <div id="message-input">
            <input type="text" id="nickname" placeholder="Nama">
            <input type="text" id="message" placeholder="Ketik pesan mu..." onkeypress="if(event.keyCode == 13) sendMessage()">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>
	
	<script>
        const ws = new WebSocket('ws://localhost:8090');

        ws.onopen = () => {
            console.log('Terhubung ke server');
        };

        ws.onmessage = (event) => {
            const messages = document.getElementById('messages');
            const message = document.createElement('div');
            message.innerHTML = event.data;
            messages.appendChild(message);
            messages.scrollTop = messages.scrollHeight; // Auto-scroll ke bawah
        };

        ws.onclose = () => {
            console.log('Terputus dari server');
        };

        function sendMessage() {
            const nickname = document.getElementById('nickname').value.trim();
            const input = document.getElementById('message');

            if (nickname && input.value.trim()) {
                const message = "<b>" + nickname + "</b>: " + input.value;
				//const message = input.value;
                ws.send(message);
                input.value = '';
            } else {
                alert('Isi nama dan ketik pesan mu dulu.');
            }
        }
    </script>
</body>
</html>
