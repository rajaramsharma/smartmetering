# smartmetering
⚡ ESP32 Smart Energy Metering & Load Management System

A real-time IoT-based system using **ESP32**, **current sensors (ACS712 or SCT-013 with EmonLib)**, and **PHP + MySQL backend** to measure current, calculate power consumption, and control electrical loads (relay) remotely via a web dashboard.

---

## 🚀 Features

✅ Real-time current measurement  
✅ Power calculation (kW)  
✅ Live web dashboard with auto-refresh  
✅ Relay (load) ON/OFF control  
✅ RESTful API using JSON  
✅ Data logging to PHP server with MySQL  
✅ Auto-reconnect Wi-Fi with static IP  
✅ Mobile & desktop responsive web UI  

---

## 🛠️ Hardware Requirements

| Component          | Description                            |
|-------------------|----------------------------------------|
| ESP32 Dev Board    | Wi-Fi-enabled microcontroller          |
| ACS712             | Current sensor module (30A)            |
| Relay Module       | 1-channel or 2-channel (for 220V load) |
| Bulb (220V)        | For demo electrical load               |
| Power Supply       | For ESP32 & sensor modules             |

---

## 🧪 Sensor Connections

| ESP32 Pin | Connected To     | Description            |
|-----------|------------------|------------------------|
| GPIO 34   | ACS712 OUT       | Analog current reading |
| GPIO 26    | Relay IN         | Control relay state    |
| GPIO      | Onboard LED      | Wi-Fi/relay feedback   |

---

## 🌐 Software Stack

| Layer       | Tech                      |
|-------------|---------------------------|
| Firmware    | Arduino (C++)             |
| Frontend    | HTML, CSS, JavaScript     |
| Backend     | PHP, MySQL (XAMPP)        |
| Communication | REST API (JSON + HTTP) |

---

## 📦 Folder Structure

smart-metering/
│
├── 📁 api/ # API endpoints (e.g., readings_api.php, upload_current.php)
├── 📁 app/ # Application logic (JS/TS code)
├── 📁 assets/ # Static assets (images, icons, fonts)
├── 📁 components/ # Reusable UI components (if using frontend framework)
├── 📁 controlhome/ # Pages/scripts to control home appliances
├── 📁 database setup/ # SQL files or database initialization scripts
├── 📁 hooks/ # Custom hooks or backend logic extensions
├── 📁 includes/ # PHP includes (like db connection or config files)
├── 📁 lib/ # Third-party libraries
├── 📁 public/ # Public assets (index.html, favicon, etc.)
├── 📁 scripts/ # Utility or automation scripts
├── 📁 styles/ # CSS/Tailwind styles
│
├── 📄 .gitignore
├── 📄 README.md
├── 📄 api_test.php
├── 📄 auto_calculate.php
├── 📄 command.php
├── 📄 components.json
├── 📄 costs.php
├── 📄 dashboard.php
├── 📄 device_status.php
├── 📄 energy_calculations.php
├── 📄 esp32_smart_meter.ino # ESP32 Arduino sketch for metering
├── 📄 get_status.php
├── 📄 home.php
├── 📄 login.php
├── 📄 logincheck.php
├── 📄 readings.php
├── 📄 readings_api.php
├── 📄 settings.php
├── 📄 signup.php
├── 📄 tailwind.config.ts
├── 📄 tsconfig.json
├── 📄 next.config.mjs
├── 📄 postcss.config.mjs
├── 📄 package.json
├── 📄 pnpm-lock.yaml
├── 📄 voice_control.php

## 🧑‍💻 Firmware Setup (Arduino IDE)

1. Open `SmartMeter.ino` in Arduino IDE
2. Install required libraries:
   - `WiFi.h` (built-in)
   - `WebServer.h`
   - `HTTPClient.h`
   - `ArduinoJson` by Benoît Blanchon
   - `EmonLib` by OpenEnergyMonitor
3. Set your Wi-Fi credentials and server IP
4. Select Board: **ESP32 Dev Module**
5. Upload via USB
6. Open Serial Monitor to verify connection

---

## 🖥️ Server Setup (XAMPP)

1. Copy the `server/` folder to your `htdocs/`
2. Start Apache and MySQL via XAMPP Control Panel
3. Import `database.sql` in **phpMyAdmin**
4. Update the `upload.php` and `endpoints.php` with correct DB credentials

---

## 🔌 Live Web Dashboard

- ESP32 hosts its own HTML page accessible via local IP (e.g. `http://172.16.22.116`)
- Use it to:
  - View current, voltage, power
  - Toggle relay
  - Auto-refresh every 5 seconds

---

## 📱 API Endpoints

| Method | Endpoint        | Description              |
|--------|------------------|--------------------------|
| GET    | `/data`         | Returns live sensor JSON |
| POST   | `/relay`        | Turn relay ON/OFF        |
| GET    | `/relay`        | Get current relay state  |

---

## 🧠 Future Enhancements

- Add voltage sensing (ZMPT101B)
- Solar panel integration
- Daily/weekly/monthly energy charts
- User authentication (login system)
- Mobile app via Flutter or React Native

---

## 🧑‍💻 Developed By

- **Rajaram Sharma**
- **Dharmendra Kumar Mandal**
- **Laxman Mandal**
- **Sharada Kumari Chaudhary**

---
---

## 📸 Preview

![dashboard](https://github.com/user-attachments/assets/abeb08d7-c43d-44a3-84d6-9a90e85ec966)
![control](https://github.com/user-attachments/assets/bee23720-5f2d-40e3-b579-63c777106adf)
![homecontrol](https://github.com/user-attachments/assets/5a9fe5ff-bd2a-45a0-8cb6-dcf6fdc16ecb)
![voice control](https://github.com/user-attachments/assets/6e2a9713-0275-4f94-89fe-b27722171a3b)
![energy](https://github.com/user-attachments/assets/ce9d370a-e2d2-4a90-8d62-22b5badbcd3c)
![cost](https://github.com/user-attachments/assets/201b4d69-01d5-410d-9d4b-e3dcde8758f1)
![settings](https://github.com/user-attachments/assets/50f67760-0c76-40e0-9ed7-b7c237a7fa5f)









