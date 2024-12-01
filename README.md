
# WhatsApp AI Integration

This project integrates AI functionality with WhatsApp using **WA Gateway | Multi-device BETA | MPWA MD**. It utilizes services like Hugging Face for audio transcription and Google Gemini for AI-driven responses, enabling intelligent and dynamic interactions.

## Features
- **AI-Powered Responses**: Handles text queries and generates intelligent replies.
- **Audio Transcription & Translation**: Converts audio to text and translates it if needed.
- **Database Logging**: Logs all user interactions for tracking and personalization.
- **Language Support**: Handles multilingual inputs with role-based translations.

---

## Prerequisites
- **PHP**: Version 7.4 or higher.
- **MySQL**: For data storage.
- **WA Gateway | Multi-device BETA | MPWA MD**: To connect with WhatsApp.
- **Hugging Face API Key**: For audio transcription.
- **Google Gemini API Key**: For AI responses.

---

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/adikhanofficial/whatsapp-ai-integration.git
cd whatsapp-ai-integration
```

### 2. Configure the Application
Edit the `config.php` file:
- Set your **database credentials**:
  ```php
  $host = "localhost";
  $username = "DATABASE_USERNAME";
  $password = "DATABASE_PASSWORD";
  $databse = "DATABASE_NAME";
  ```
- Add your **WhatsApp API** configuration:
  ```php
  $GLOBALS["whatsapp_sender_id"] = "923XXXXXXXXX";
  $GLOBALS["whatsapp_api_key"] = "YOUR_API_KEY";
  $GLOBALS["whatsapp_endpoint"] = "https://YOUR_DOMAIN.COM/send-message";
  ```
- Configure your **Hugging Face API**:
  ```php
  $GLOBALS["hugging_face_api_key"] = "HUGGING_FACE_API_KEY";
  $GLOBALS["hugging_face_end_point"] = "https://api-inference.huggingface.co/models/openai/whisper-large-v3";
  ```
- Add your **Google Gemini API**:
  ```php
  $GLOBALS["gemini_api_key"] = "GEMINI_API_KEY";
  ```

### 3. Set Up Database
- Create the necessary tables in your MySQL database:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(255),
    name VARCHAR(255)
);

CREATE TABLE model_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id VARCHAR(50),
    number VARCHAR(20),
    role ENUM('user', 'model'),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255),
    number VARCHAR(20)
);
```
- OR import 'data/tables.sql' in your database

### 4. Update the existing Code
- Change method "parseIncomingMessage" in "server/lib/helper.js" as below 
```Javascript
async function parseIncomingMessage(msg) {
	const type = Object.keys(msg.message || {})[0];
	const body =
		type === "conversation" && msg.message.conversation
			? msg.message.conversation
			: type == "imageMessage" && msg.message.imageMessage.caption
			? msg.message.imageMessage.caption
			: type == "videoMessage" && msg.message.videoMessage.caption
			? msg.message.videoMessage.caption
			: type == "extendedTextMessage" &&
			  msg.message.extendedTextMessage.text
			? msg.message.extendedTextMessage.text
			: type == "messageContextInfo" &&
			  msg.message.listResponseMessage?.title
			? msg.message.listResponseMessage.title
			: type == "messageContextInfo"
			? msg.message.buttonsResponseMessage.selectedDisplayText
			: "";
	const d = body.toLowerCase();
	const command = await removeForbiddenCharacters(d);
	const senderName = msg?.pushName || "";
	const from = msg.key.remoteJid.split("@")[0];
	let bufferImage;
	console.log(type);
	if (type === "imageMessage") {
		const stream = await downloadContentFromMessage(
			msg.message.imageMessage,
			"image"
		);
		let buffer = Buffer.from([]);
		for await (const chunk of stream) {
			buffer = Buffer.concat([buffer, chunk]);
		}
		bufferImage = buffer.toString("base64");
	}
	else if(type === "audioMessage"){
		const stream = await downloadContentFromMessage(
			msg.message.audioMessage,
			"audio"
		);
		let buffer = Buffer.from([]);
		for await (const chunk of stream) {
			buffer = Buffer.concat([buffer, chunk]);
		}
		bufferImage = "AUDIO:" + buffer.toString("base64");
	}
	else {
		urlImage = null;
	}

	return {  command, bufferImage, from };
}
```
- Or Replace "data/helpers.js" in repository with "server/lib/helper/helpers.js" in your Application.

### 5. Deploy the Application
- Place the project files on a PHP-enabled server.
- Ensure `index.php` is accessible to receive Webhook calls from the WhatsApp gateway.

---

## Usage

### Incoming Messages
1. **Text Messages**:  
   - Users send a message via WhatsApp.
   - `index.php` processes the message and logs it in the database.
   - AI generates a response using `getAIReply()` and sends it back via WhatsApp.

2. **Audio Messages**:  
   - Users send an audio file via WhatsApp.
   - The audio is transcribed using Hugging Face (`transcribeAudio()`).
   - If needed, the transcription is translated before being sent back.

### Database Logging
- All interactions are stored in the `model_chats` table.
- Contacts are managed in the `contacts` table for personalized replies.

### Customization
- Modify AI behavior by editing `bot_role.txt` to redefine the system instructions.
- Adjust translation rules in `translateText()` within `functions.php`.

---

## Debugging
Enable debugging to monitor issues:
1. Set `$GLOBALS["debug"] = true;` in `config.php`.
2. Debug logs will be saved in `debug.log`.

You can also pass `?debug=show` in the URL to view logs directly in the browser.

---

## API Details

### WhatsApp API
- The `send_text()` function sends messages using the configured WA Gateway.

### Hugging Face API
- The `transcribeAudio()` function sends audio files to Hugging Face for transcription.

### Google Gemini API
- The `getGeminiResponse()` function interacts with Google Gemini to generate AI responses based on the conversation context.

---

## Example Scenarios

1. **Text Interaction**:
   - User sends: "What's the weather today?"
   - AI responds: "I'm not equipped with weather data, but I can help with general information!"

2. **Audio Interaction**:
   - User sends an audio message in Urdu.
   - The system transcribes and translates the audio, then sends back the text.

---

## Contributing
Contributions are welcome!  
Fork this repository, make your changes, and create a pull request.  

---

## License
This project is licensed under the **MIT License**.

---

## Contact
For further queries or assistance, contact:

**Adil Khan**  
- [Website](https://adikhanofficial.com)  
- [GitHub](https://github.com/AdiKhanOfficial)  
