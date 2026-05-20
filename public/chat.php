<?php
require_once '../app/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>AI Auto Assistant</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #0b0b0b;
            color: #fff;
            font-family: Arial;
        }

        .chat-box {
            height: 500px;
            overflow-y: auto;
            background: #111;
            border: 1px solid #333;
            padding: 15px;
            border-radius: 12px;
        }

        .msg {
            margin: 10px 0;
            padding: 10px 14px;
            border-radius: 10px;
            max-width: 70%;
        }

        .user {
            background: #800039;
            margin-left: auto;
            text-align: right;
        }

        .bot {
            background: #222;
        }

        .input-box {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        input {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
        }

        button {
            background: #800039;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
        }
    </style>
</head>

<body class="p-4">

    <h3 class="text-center mb-3">🚗 AI Auto Assistant</h3>

    <div class="chat-box" id="chat"></div>

    <div class="input-box">
        <input type="text" id="text" placeholder="Например: хочу экономичную и высокую машину">
        <button onclick="send()">Отправить</button>
    </div>

    <script>
        function addMsg(text, type) {
            let div = document.createElement("div");
            div.classList.add("msg", type);
            div.innerText = text;
            document.getElementById("chat").appendChild(div);
            document.getElementById("chat").scrollTop = 999999;
        }

        function send() {
            let input = document.getElementById("text");
            let text = input.value;

            if (!text) return;

            addMsg(text, "user");

            fetch("chat_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "message=" + encodeURIComponent(text)
            })
                .then(res => res.json())
                .then(data => {
                    addMsg(data.reply, "bot");
                });

            input.value = "";
        }
    </script>

</body>

</html>