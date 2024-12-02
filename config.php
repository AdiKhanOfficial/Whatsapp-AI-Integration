<?php
//Database Configuration
$host = "localhost";
$username = "DATABASE_USERNAME";
$password = "DATEBASE_PASSWORD";
$databse = "DATABASE_NAME";
$conn = mysqli_connect($host,$username,$password,$databse) or die("Database Connection Failed..");
        mysqli_set_charset($conn, "utf8mb4");
$GLOBALS["conn"] = $conn;
$GLOBALS["debug"] = false;

//WhatsApp API Configuration
$GLOBALS["whatsapp_sender_id"] = "923XXXXXXXXX"; 
$GLOBALS["whatsapp_api_key"] = "YOUR_API_KEY";
$GLOBALS["whatsapp_endpoint"] = "https://YOUR_DOMAIN.COM/send-message"; // Put your server url to send message. 

//Hugging Facae Configuration
$GLOBALS["hugging_face_api_key"] = "HUGGING_FACE_API_KEY"; // Api key with read permission
$GLOBALS["hugging_face_end_point"] = "https://api-inference.huggingface.co/models/openai/whisper-large-v3-turbo";

//Gemini Configuration
$GLOBALS["gemini_api_key"] = $gemini_api_key = "GEMINI_API_KEY";
$GLOBALS["gemini_endpoint"] = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=".$gemini_api_key;
$GLOBALS["gemini_settings"] = array(array("category"=>"HARM_CATEGORY_HARASSMENT","threshold"=>"BLOCK_NONE"), array("category"=>"HARM_CATEGORY_HATE_SPEECH","threshold"=>"BLOCK_NONE"), array("category"=>"HARM_CATEGORY_SEXUALLY_EXPLICIT","threshold"=>"BLOCK_NONE"), array("category"=>"HARM_CATEGORY_DANGEROUS_CONTENT","threshold"=>"BLOCK_NONE"), array("category"=>"HARM_CATEGORY_CIVIC_INTEGRITY","threshold"=>"BLOCK_NONE") );

?>
