/*
 * ESP32 Attendance System - Template
 * 
 * IMPORTANT: This is a template file. Copy this to esp32_attendance_enhanced.ino
 * and update the configuration values below with your actual settings.
 * 
 * DO NOT commit the actual esp32_attendance_enhanced.ino file to version control
 * as it contains sensitive WiFi credentials and server information.
 */

#include <WiFi.h>
#include <WebSocketsClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Adafruit_Fingerprint.h>
#include <LiquidCrystal_PCF8574.h>

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES FOR YOUR SETUP
// ============================================================================

// WiFi Configuration
const char* ssid = "YOUR_WIFI_SSID_HERE";
const char* password = "YOUR_WIFI_PASSWORD_HERE";

// WebSocket Server Configuration
const char* wsHost = "YOUR_SERVER_IP_HERE";  // e.g., "192.168.1.100"
const int wsPort = 8080;

// Hardware Pin Configuration
#define SS_PIN 5
#define RST_PIN 4
#define BUZZER_PIN 2
#define LED_GREEN 26
#define LED_RED 14

// ============================================================================
// HARDWARE INITIALIZATION
// ============================================================================

WebSocketsClient webSocket;
MFRC522 rfid(SS_PIN, RST_PIN);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&Serial2);
LiquidCrystal_PCF8574 lcd(0x27);

bool isConnected = false;
unsigned long lastHeartbeat = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000; // 30 seconds

// ============================================================================
// SETUP FUNCTION
// ============================================================================

void setup() {
  Serial.begin(115200);
  Wire.begin(21, 22);
  lcd.begin(16, 2);
  lcd.setBacklight(255);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Initializing...");

  // Initialize pins
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);

  // Initialize RFID
  SPI.begin(18, 19, 23, SS_PIN);
  rfid.PCD_Init();
  delay(100);

  // Initialize fingerprint sensor
  Serial2.begin(57600);
  finger.begin(57600);
  delay(100);

  // Connect to WiFi
  connectToWiFi();
  
  // Setup WebSocket
  webSocket.begin(wsHost, wsPort, "/");
  webSocket.onEvent(webSocketEvent);
  webSocket.setReconnectInterval(5000);
}

// ============================================================================
// MAIN LOOP
// ============================================================================

void loop() {
  webSocket.loop();
  
  // Send heartbeat
  if (millis() - lastHeartbeat > HEARTBEAT_INTERVAL) {
    sendHeartbeat();
    lastHeartbeat = millis();
  }
  
  // Check for RFID card
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    handleRFIDScan();
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }
  
  // Check for fingerprint
  uint8_t result = finger.getImage();
  if (result == FINGERPRINT_OK) {
    handleFingerprintScan();
  }
  
  delay(100);
}

// ============================================================================
// WIFI CONNECTION
// ============================================================================

void connectToWiFi() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting to WiFi");
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    lcd.setCursor(0, 1);
    lcd.print("Attempt " + String(attempts + 1));
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    digitalWrite(LED_GREEN, HIGH);
    delay(1000);
    digitalWrite(LED_GREEN, LOW);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Failed");
    digitalWrite(LED_RED, HIGH);
  }
}

// ============================================================================
// WEBSOCKET HANDLERS
// ============================================================================

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
  switch(type) {
    case WStype_DISCONNECTED:
      Serial.println("WebSocket Disconnected!");
      isConnected = false;
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WS Disconnected");
      lcd.setCursor(0, 1);
      lcd.print("Reconnecting...");
      break;
      
    case WStype_CONNECTED:
      Serial.println("WebSocket Connected!");
      isConnected = true;
      
      // Register as hardware device
      DynamicJsonDocument doc(1024);
      doc["type"] = "hardware_register";
      doc["device_type"] = "esp32_attendance";
      doc["hardware_info"] = "RFID-RC522 + R305";
      
      String message;
      serializeJson(doc, message);
      webSocket.sendTXT(message);
      
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WS Connected");
      lcd.setCursor(0, 1);
      lcd.print("Ready for scan");
      break;
      
    case WStype_TEXT:
      handleWebSocketMessage(payload, length);
      break;
  }
}

void handleWebSocketMessage(uint8_t * payload, size_t length) {
  String message = String((char*)payload);
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, message);
  
  if (error) {
    Serial.println("JSON parsing failed");
    return;
  }
  
  String type = doc["type"];
  
  if (type == "attendance_response") {
    handleAttendanceResponse(doc);
  } else if (type == "schedule_info") {
    handleScheduleInfo(doc);
  } else if (type == "error") {
    handleError(doc);
  }
}

// ============================================================================
// RFID HANDLING
// ============================================================================

void handleRFIDScan() {
  String cardUID = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    cardUID += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
    cardUID += String(rfid.uid.uidByte[i], HEX);
  }
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Card: " + cardUID.substring(0, 8));
  lcd.setCursor(0, 1);
  lcd.print("Sending...");
  
  sendRFIDScan(cardUID);
  playSuccessSound();
}

void sendRFIDScan(String cardUID) {
  if (!isConnected) {
    Serial.println("Not connected to server");
    return;
  }
  
  DynamicJsonDocument doc(512);
  doc["type"] = "rfid_scan";
  doc["card_uid"] = cardUID;
  doc["timestamp"] = getCurrentTimestamp();
  
  String message;
  serializeJson(doc, message);
  webSocket.sendTXT(message);
  
  Serial.println("Sent RFID scan: " + message);
}

// ============================================================================
// FINGERPRINT HANDLING
// ============================================================================

void handleFingerprintScan() {
  uint8_t result = finger.image2Tz();
  if (result != FINGERPRINT_OK) {
    playErrorSound();
    return;
  }
  
  result = finger.fingerFastSearch();
  if (result == FINGERPRINT_OK) {
    int template_id = finger.fingerID;
    int confidence = finger.confidence;
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Finger ID: " + String(template_id));
    lcd.setCursor(0, 1);
    lcd.print("Conf: " + String(confidence));
    
    sendFingerprintScan(template_id);
    playSuccessSound();
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Finger not found");
    playErrorSound();
  }
}

void sendFingerprintScan(int template_id) {
  if (!isConnected) {
    Serial.println("Not connected to server");
    return;
  }
  
  DynamicJsonDocument doc(512);
  doc["type"] = "fingerprint_scan";
  doc["template_id"] = template_id;
  doc["timestamp"] = getCurrentTimestamp();
  
  String message;
  serializeJson(doc, message);
  webSocket.sendTXT(message);
  
  Serial.println("Sent fingerprint scan: " + message);
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

String getCurrentTimestamp() {
  // This is a simplified timestamp - in production, you'd want to sync with NTP
  return "2025-01-15 08:30:00"; // Placeholder
}

void sendHeartbeat() {
  if (!isConnected) return;
  
  DynamicJsonDocument doc(256);
  doc["type"] = "heartbeat";
  doc["device_id"] = "esp32_attendance";
  doc["timestamp"] = getCurrentTimestamp();
  
  String message;
  serializeJson(doc, message);
  webSocket.sendTXT(message);
}

void playSuccessSound() {
  digitalWrite(LED_GREEN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(200);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN, LOW);
}

void playErrorSound() {
  digitalWrite(LED_RED, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);
  delay(100);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_RED, LOW);
}

void handleAttendanceResponse(JsonDocument& doc) {
  bool success = doc["success"];
  String message = doc["message"];
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(success ? "Success!" : "Error");
  lcd.setCursor(0, 1);
  lcd.print(message.substring(0, 16));
  
  if (success) {
    playSuccessSound();
  } else {
    playErrorSound();
  }
}

void handleScheduleInfo(JsonDocument& doc) {
  // Handle schedule information from server
  Serial.println("Received schedule info");
}

void handleError(JsonDocument& doc) {
  String error = doc["error"];
  Serial.println("Server error: " + error);
  playErrorSound();
}

/*
 * SETUP INSTRUCTIONS:
 * 
 * 1. Copy this template to esp32_attendance_enhanced.ino
 * 2. Update the configuration values at the top:
 *    - WiFi SSID and password
 *    - WebSocket server IP address
 *    - Pin assignments if different
 * 3. Upload to your ESP32
 * 4. Test the hardware connections
 * 
 * SECURITY NOTES:
 * - Never commit the actual esp32_attendance_enhanced.ino file
 * - Keep your WiFi credentials secure
 * - Use a dedicated network for the attendance system
 * - Regularly update passwords and firmware
 */ 