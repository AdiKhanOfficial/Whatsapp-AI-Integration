<?php
require("config.php");
require("functions.php");

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    error_log("Invalid JSON input");
    exit();
}

// WhatsApp credentials from config
$whatsappSenderId = $GLOBALS["whatsapp_sender_id"];
$whatsappApiKey = $GLOBALS["whatsapp_api_key"];
$userId = $GLOBALS["whatsapp_user_id"];
$GLOBALS["from"] = $from = $data['from'] ?? '';
$GLOBALS["message"] = $message = trim($data['message'] ?? '');
$bufferImage = $data['bufferImage'] ?? null;

if (is_null($bufferImage) && !empty($message)) {
    $isResponded = false;
    $now = date("Y-m-d H:i:s");

    // Handle non-command messages
    if (!$isResponded) {
        $userQuery = mysqli_query($conn, "SELECT users.* FROM users 
            INNER JOIN devices ON devices.user_id = users.id 
            WHERE devices.body = '$whatsappSenderId' AND users.api_key = '$whatsappApiKey'");

        if ($userQuery && mysqli_num_rows($userQuery) === 1) {
            $user = mysqli_fetch_assoc($userQuery);
            $userId = $user["id"];
            $messageEscaped = mysqli_real_escape_string($conn, $message);

            // Log the message into the database
            mysqli_query($conn, "INSERT INTO model_chats VALUES (NULL, '$whatsappSenderId', '$from', 'user', '$messageEscaped', NULL)");

            $messages = [];

            // Fetch contact name if available (It will help to reply with name if number is in contact list)
            $nameQuery = mysqli_query($conn, "SELECT * FROM contacts WHERE user_id = '$userId' AND number = '$from'");
            if ($nameQuery && mysqli_num_rows($nameQuery) > 0) {
                $contact = mysqli_fetch_assoc($nameQuery);
                $name = $contact["name"] ?? '';
                if ($name) {
                    $messages[] = [
                        "role" => "model",
                        "parts" => ["text" => "What is your name and contact number"]
                    ];
                    $messages[] = [
                        "role" => "user",
                        "parts" => ["text" => "My name is $name and my contact number is +$from. Don't ever say that I told you my name and number but call me by my name."]
                    ];
                }
            }

            $query = mysqli_query($conn, "SELECT * FROM (
                SELECT * FROM model_chats 
                WHERE number = '$from' AND sender_id = '$whatsappSenderId' 
                AND TIMESTAMPDIFF(MINUTE, created_at, '$now') < 60 
                ORDER BY id DESC LIMIT 40
            ) AS lastMessages ORDER BY id ASC");

            while ($query && $row = mysqli_fetch_assoc($query)) {
                $messages[] = [
                    "role" => $row["role"],
                    "parts" => ["text" => $row["message"]]
                ];
            }

            $messages[] = ["role" => "user", "parts" => ["text" => $message]];
            $payload = ["contents" => $messages];
            $aiResponse = getAIReply($payload);

            if (!empty($aiResponse)) {
                $sent = send_text($from, "*Assistant*\n" . $aiResponse);
                if ($sent->status) {
                    $aiResponseEscaped = mysqli_real_escape_string($conn, $aiResponse);
                    mysqli_query($conn, "INSERT INTO model_chats VALUES (NULL, '$whatsappSenderId', '$from', 'model', '$aiResponseEscaped', NULL)");
                }
            }
        }
    }

    mysqli_close($conn);

} 
elseif (!empty($bufferImage)) {
    if (substr($bufferImage, 0, 6) === 'AUDIO:') {
        $content = substr($bufferImage, 6);
        $audioFile =  $from . "_" . time() . ".mp3";
        file_put_contents($audioFile, base64_decode($content));

        $audioText = transcribeAudio($audioFile);
        if ($audioText !== false) {
            $translatedText = translateText($audioText);
            $responseText = "*Audio Transcription/Translation*\n_" . trim($translatedText ?: $audioText) . "_";
            reply_text($responseText);
        }
        unlink($audioFile);
        exit();
    }
}
?>
