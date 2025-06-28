# ⚡ smartmetering  
**ESP32 Smart Energy Metering & Load Management System**

A real-time IoT-based system using **ESP32**, **current sensors (ACS712/SCT-013 with EmonLib)**, and a **PHP + MySQL backend** to measure current, calculate power consumption, and control electrical loads (relay) remotely via a web dashboard.

---

## 🚀 Features

- ✅ Real-time current measurement  
- ✅ Power calculation (kW)  
- ✅ Live web dashboard with auto-refresh  
- ✅ Relay (load) ON/OFF control  
- ✅ RESTful API using JSON  
- ✅ Data logging to PHP server with MySQL  
- ✅ Auto-reconnect Wi-Fi with static IP  
- ✅ Mobile & desktop responsive web UI  

---

## 🛠️ Hardware Requirements

| Component        | Description                            |
|------------------|----------------------------------------|
| ESP32 Dev Board  | Wi-Fi-enabled microcontroller          |
| ACS712           | Current sensor module (30A)            |
| Relay Module     | 1 or 2-channel (for 220V load control) |
| Bulb (220V)      | Demo electrical load                   |
| Power Supply     | 5V or 12V for ESP32 & relay             |

---

## 🧪 Sensor Connections

| ESP32 Pin | Connected To | Description              |
|-----------|--------------|--------------------------|
| GPIO 34   | ACS712 OUT   | Analog current reading   |
| GPIO 26   | Relay IN     | Relay control pin        |
| GPIO 2    | Onboard LED  | Wi-Fi/relay status       |

---

## 🌐 Software Stack

| Layer         | Tech                      |
|---------------|---------------------------|
| Firmware      | Arduino (C++)             |
| Frontend      | HTML, CSS, JavaScript     |
| Backend       | PHP, MySQL (XAMPP)        |
| Communication | REST API (JSON + HTTP)    |

---

## 📦 Folder Structure

![folder](https://github.com/user-attachments/assets/25889e2c-771e-4494-bd17-e0d6b3911f22)


---

## 🧑‍💻 Firmware Setup (Arduino IDE)

1. Open `esp32_smart_meter.ino` in Arduino IDE  
2. Install libraries:
   - `WiFi.h`
   - `WebServer.h`
   - `HTTPClient.h`
   - `ArduinoJson` by Benoît Blanchon
   - `EmonLib` by OpenEnergyMonitor  
3. Set your Wi-Fi SSID, password, and PHP server IP  
4. Select board: `ESP32 Dev Module`  
5. Upload via USB  
6. Monitor output via Serial Monitor  

---

## 🖥️ Server Setup (XAMPP)

1. Copy this project folder to `htdocs/`  
2. Start Apache and MySQL using XAMPP  
3. Open phpMyAdmin → Import `database.sql` from `database setup/`  
4. Edit `includes/config.php` and API files with DB details  
5. Open `http://localhost/smart-metering/home.php` to start  

---

## 🔌 Live Web Dashboard

- Access dashboard via ESP32 IP: `http://<your_local_ip>`  
- Features:
  - View live current, power, cost
  - Toggle relay ON/OFF
  - View device status
  - Auto-refresh every 5 seconds

---

## 📱 API Endpoints

| Method | Endpoint         | Description              |
|--------|------------------|--------------------------|
| GET    | `/readings_api.php` | Returns sensor JSON     |
| POST   | `/command.php?relay=ON/OFF` | Toggle relay   |
| GET    | `/get_status.php`  | Get current relay state  |

---

## 💡 Future Enhancements

- Voltage measurement with ZMPT101B  
- Solar power monitoring integration  
- Energy consumption charts (daily/monthly)  
- Mobile app interface (Flutter/React Native)  
- Advanced analytics & user roles  

---

## 👨‍💻 Developed By

- **Rajaram Sharma**  
- **Dharmendra Kumar Mandal**  
- **Laxman Mandal**  
- **Sharada Kumari Chaudhary**


## 📸 Preview

![dashboard](https://github.com/user-attachments/assets/abeb08d7-c43d-44a3-84d6-9a90e85ec966)
![control](https://github.com/user-attachments/assets/bee23720-5f2d-40e3-b579-63c777106adf)
![homecontrol](https://github.com/user-attachments/assets/5a9fe5ff-bd2a-45a0-8cb6-dcf6fdc16ecb)
![voice control](https://github.com/user-attachments/assets/6e2a9713-0275-4f94-89fe-b27722171a3b)
![energy](https://github.com/user-attachments/assets/ce9d370a-e2d2-4a90-8d62-22b5badbcd3c)
![cost](https://github.com/user-attachments/assets/201b4d69-01d5-410d-9d4b-e3dcde8758f1)
![settings](https://github.com/user-attachments/assets/50f67760-0c76-40e0-9ed7-b7c237a7fa5f)









