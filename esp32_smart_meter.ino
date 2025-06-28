#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <EEPROM.h>

// WiFi Configuration
const char* ssid = "STUDENT";
const char* password = "cosmos1213";
const char* serverURL = "http://172.16.22.116/smart";

// Pin Definitions
#define CURRENT_SENSOR_PIN 34
#define RELAY_PIN 26
#define LED_PIN 2
#define BUZZER_PIN 25

// Sensor Configuration
float threshold = 0.3;
float calibrationOffset = 2.5;
float sensitivity = 0.1;

// System Variables
String lastCommand = "OFF";
String deviceStatus = "OFF";
unsigned long lastUpload = 0;
unsigned long lastCommandCheck = 0;
unsigned long uploadInterval = 5000;  // 5 seconds
unsigned long commandInterval = 3000; // 3 seconds
int reconnectAttempts = 0;
bool systemEnabled = true;

// Statistics
unsigned long totalReadings = 0;
float totalEnergy = 0;
float maxCurrent = 0;
float minCurrent = 999;

void setup() {
  Serial.begin(115200);
  Serial.println("=== ESP32 Smart Meter Starting ===");
  
  // Initialize pins
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  
  // Initial state
  digitalWrite(RELAY_PIN, HIGH); // Relay OFF (assuming active LOW)
  digitalWrite(LED_PIN, LOW);
  
  // Initialize EEPROM for storing settings
  EEPROM.begin(512);
  
  // Startup beep
  beep(2);
  
  // Connect to WiFi
  connectWiFi();
  
  Serial.println("=== System Ready ===");
  beep(1);
}

void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    handleWiFiReconnect();
    return;
  }
  
  // Read current sensor
  float current = readCurrent();
  updateStatistics(current);
  
  // Check for commands periodically
  if (millis() - lastCommandCheck >= commandInterval) {
    getCommand();
    lastCommandCheck = millis();
  }
  
  // Control relay based on command
  controlRelay(lastCommand);
  
  // Upload data periodically
  if (millis() - lastUpload >= uploadInterval) {
    sendData(current, deviceStatus);
    lastUpload = millis();
  }
  
  // Status LED
  updateStatusLED();
  
  // Serial output for debugging
  printStatus(current);
  
  delay(1000); // Main loop delay
}

float readCurrent() {
  // Take multiple readings for accuracy
  float sum = 0;
  int samples = 10;
  
  for (int i = 0; i < samples; i++) {
    int adc = analogRead(CURRENT_SENSOR_PIN);
    float voltage = adc * (3.3 / 4095.0);
    float current = (voltage - calibrationOffset) / sensitivity;
    
    // Filter noise
    if (abs(current) < threshold) current = 0;
    sum += abs(current);
    delay(10);
  }
  
  float avgCurrent = sum / samples;
  
  // Limit maximum reading (safety)
  if (avgCurrent > 50.0) avgCurrent = 50.0;
  
  return avgCurrent;
}

void controlRelay(String cmd) {
  bool newState = (cmd == "ON");
  bool currentState = (digitalRead(RELAY_PIN) == LOW);
  
  if (newState != currentState) {
    digitalWrite(RELAY_PIN, newState ? LOW : HIGH);
    deviceStatus = newState ? "ON" : "OFF";
    
    // Feedback
    beep(newState ? 1 : 2);
    Serial.println("Relay " + String(deviceStatus));
    
    // Send immediate status update
    sendStatusUpdate();
  }
}

void getCommand() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  http.begin(String(serverURL) + "/get_status.php");
  http.setTimeout(5000);
  
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    
    // Parse JSON response
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error && doc["status"] == "success") {
      String newCommand = doc["data"]["last_command"];
      
      if (newCommand != lastCommand && (newCommand == "ON" || newCommand == "OFF")) {
        lastCommand = newCommand;
        Serial.println("New command received: " + newCommand);
      }
    } else {
      // Fallback: parse simple response
      int cmdIndex = response.indexOf("\"command\":");
      if (cmdIndex > 0) {
        int startQuote = response.indexOf("\"", cmdIndex + 10);
        int endQuote = response.indexOf("\"", startQuote + 1);
        if (startQuote > 0 && endQuote > 0) {
          lastCommand = response.substring(startQuote + 1, endQuote);
        }
      }
    }
  } else {
    Serial.println("Command fetch failed: " + String(httpCode));
  }
  
  http.end();
}

void sendData(float current, String status) {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  String url = String(serverURL) + "/upload_current.php?current=" + 
               String(current, 2) + "&relay=" + status + 
               "&device_id=ESP32_001&firmware=v2.0";
  
  http.begin(url);
  http.setTimeout(10000);
  
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    
    // Parse response for feedback
    if (response.indexOf("\"status\":\"success\"") > 0) {
      totalReadings++;
      reconnectAttempts = 0; // Reset on successful upload
    }
  } else {
    Serial.println("Upload failed: " + String(httpCode));
    reconnectAttempts++;
  }
  
  http.end();
}

void sendStatusUpdate() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  String url = String(serverURL) + "/device_status.php?device_id=ESP32_001&status=" + 
               deviceStatus + "&uptime=" + String(millis()/1000);
  
  http.begin(url);
  http.GET();
  http.end();
}

void connectWiFi() {
  Serial.println("Connecting to WiFi: " + String(ssid));
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
    
    // Blink LED during connection
    digitalWrite(LED_PIN, !digitalRead(LED_PIN));
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
    Serial.println("IP Address: " + WiFi.localIP().toString());
    Serial.println("Signal Strength: " + String(WiFi.RSSI()) + " dBm");
    digitalWrite(LED_PIN, HIGH);
  } else {
    Serial.println("\nWiFi Connection Failed!");
    digitalWrite(LED_PIN, LOW);
  }
}

void handleWiFiReconnect() {
  Serial.println("WiFi disconnected. Attempting reconnection...");
  digitalWrite(LED_PIN, LOW);
  
  WiFi.disconnect();
  delay(1000);
  connectWiFi();
}

void updateStatistics(float current) {
  if (current > maxCurrent) maxCurrent = current;
  if (current < minCurrent && current > 0) minCurrent = current;
  
  // Calculate energy (simplified)
  totalEnergy += (current * 220 * 1) / (1000 * 3600); // kWh per second
}

void updateStatusLED() {
  static unsigned long lastBlink = 0;
  static bool ledState = false;
  
  if (WiFi.status() == WL_CONNECTED) {
    if (deviceStatus == "ON") {
      // Solid ON when relay is active
      digitalWrite(LED_PIN, HIGH);
    } else {
      // Slow blink when connected but relay OFF
      if (millis() - lastBlink > 1000) {
        ledState = !ledState;
        digitalWrite(LED_PIN, ledState);
        lastBlink = millis();
      }
    }
  } else {
    // Fast blink when disconnected
    if (millis() - lastBlink > 200) {
      ledState = !ledState;
      digitalWrite(LED_PIN, ledState);
      lastBlink = millis();
    }
  }
}

void beep(int times) {
  for (int i = 0; i < times; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < times - 1) delay(100);
  }
}

void printStatus(float current) {
  static unsigned long lastPrint = 0;
  
  if (millis() - lastPrint >= 5000) { // Print every 5 seconds
    Serial.println("=== Status Report ===");
    Serial.printf("Current: %.2f A\n", current);
    Serial.printf("Relay: %s\n", deviceStatus.c_str());
    Serial.printf("WiFi: %s (RSSI: %d dBm)\n", 
                  WiFi.status() == WL_CONNECTED ? "Connected" : "Disconnected",
                  WiFi.RSSI());
    Serial.printf("Uptime: %lu seconds\n", millis()/1000);
    Serial.printf("Total Readings: %lu\n", totalReadings);
    Serial.printf("Max Current: %.2f A\n", maxCurrent);
    Serial.printf("Total Energy: %.6f kWh\n", totalEnergy);
    Serial.println("====================");
    
    lastPrint = millis();
  }
}

// Emergency stop function (can be called via serial)
void emergencyStop() {
  digitalWrite(RELAY_PIN, HIGH); // Turn OFF
  deviceStatus = "OFF";
  lastCommand = "OFF";
  beep(3);
  Serial.println("EMERGENCY STOP ACTIVATED!");
  sendStatusUpdate();
}

// Serial command handler
void serialEvent() {
  if (Serial.available()) {
    String command = Serial.readString();
    command.trim();
    
    if (command == "STOP") {
      emergencyStop();
    } else if (command == "ON") {
      lastCommand = "ON";
    } else if (command == "OFF") {
      lastCommand = "OFF";
    } else if (command == "STATUS") {
      printStatus(readCurrent());
    } else if (command == "RESET") {
      ESP.restart();
    }
  }
}
