<?php
function getAIReply($payload) {
    // Load system instruction
    $instruction = file_get_contents("bot_role.txt");
    
    return getGeminiResponse($payload, $instruction);
}

function translateText($text) {
    // Translation system instruction
    $instruction = "Your role is to translate messages:
    - If the message is in Hindi, Urdu, or Punjabi, translate it into Roman Urdu.
    - If the message is in any other language, translate it into English.
    **Note**: Do not add any extra information other than the translated content.";

    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => ["text" => $text]
            ]
        ]
    ];

    return getGeminiResponse($payload, $instruction);
}

function getGeminiResponse($payload, $instruction = "") {
    // Configure API endpoint and headers
    $apiUrl = $GLOBALS["gemini_endpoint"];
    $payload["safetySettings"] = $GLOBALS["gemini_settings"];
    $payload["system_instruction"] = ["parts" => ["text" => $instruction]];
    
    $data = json_encode($payload);
    $headers = ["Content-Type: application/json"];

    // Send POST request and process the response
    $response = json_decode(postRequest($apiUrl, $data, $headers));
    if (!empty($response->candidates[0]->content->parts[0]->text)) {
        return $response->candidates[0]->content->parts[0]->text;
    }

    return "";
}

function transcribeAudio($mp3File) {
    debug("Transcribing Audio..");
    // Check if the audio file exists
    if (!file_exists($mp3File)) {
        debug("File not found.");
        return false;
    }
    
    // Configure API endpoint and headers
    $apiUrl = $GLOBALS["hugging_face_end_point"];
    $apiToken = $GLOBALS["hugging_face_api_key"];
    $audioData = file_get_contents($mp3File);
    $headers = [
        "Authorization: Bearer $apiToken",
        "Content-Type: application/octet-stream"
    ];

    // Retry logic with a maximum of 3 attempts
    $maxAttempts = 3;
    $attempt = 0;
    $responseData = false;

    while ($attempt < $maxAttempts) {
        $attempt++;
        debug("Processing audio, attempt: " . $attempt);
        $response = postRequest($apiUrl, $audioData, $headers);
        $responseData = json_decode($response, true);

        if (isset($responseData['text'])) {
            // Successful transcription
            debug("Audio processed: " . $responseData['text']);
            return $responseData['text'];
        }
    }

    // Return false if all attempts fail
    return false;
}

function send_text($number, $message) {
    // Define API endpoint and necessary variables
    $url = $GLOBALS["whatsapp_endpoint"];
    $whatsappSenderId = $GLOBALS["whatsapp_sender_id"];
    $apiKey = $GLOBALS["whatsapp_api_key"];

    // Prepare request data
    $data = http_build_query([
        "api_key" => $apiKey,
        "sender" => $whatsappSenderId,
        "number" => $number,
        "message" => $message
    ]);

    // Define headers
    $headers = ["Content-Type: application/x-www-form-urlencoded"];

    // Send the POST request and return the decoded response
    $response = postRequest($url, $data, $headers);
    return json_decode($response);
}

function reply_text($message,$quoted=true){
    echo json_encode(["text" => $message, "quoted" => $quoted]);
}

function postRequest($url, $data, $headers) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    debug("Sending post request to " . $url);
    $response = curl_exec($curl);
    curl_close($curl);

    debug($response);

    return $response;
}

function debug($msg) {
    if (!isset($_GET["debug"]) && !$GLOBALS["debug"]) {
        return;
    }

    if ($_GET["debug"] === "show") {
        echo $msg;
    }
    
    if($GLOBALS["debug"]){
        file_put_contents("debug.log", date("d-m-Y h:i:s") . ": " . $msg . "\n", FILE_APPEND);
    }
}

?>