# Hardware Integration Setup Guide

## Overview
This guide will help you set up the RFID-RC522 and R305 fingerprint scanner with ESP32 for the Automated Attendance System.

## Prerequisites

### Hardware Requirements
- ESP32 Development Board
- RFID-RC522 Module
- R305 Fingerprint Scanner
- LCD Display (I2C PCF8574)
- Buzzer
- Green and Red LEDs
- Breadboard and jumper wires

### Software Requirements
- Arduino IDE 2.0 or later
- Required Arduino Libraries:
  - `MFRC522` by GithubCommunity
  - `Adafruit Fingerprint Sensor Library` by Adafruit
  - `LiquidCrystal_PCF8574` by Mathias Munk Hansen
  - `WebSocketsClient` by Markus Sattler
  - `ArduinoJson` by Benoit Blanchon

### Web Server Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (for WebSocket dependencies)

## Hardware Connections

### ESP32 Pin Connections

| Component | ESP32 Pin | Description |
|-----------|-----------|-------------|
| RFID-RC522 SDA | GPIO 5 | SPI Data |
| RFID-RC522 SCK | GPIO 18 | SPI Clock |
| RFID-RC522 MOSI | GPIO 23 | SPI MOSI |
| RFID-RC522 MISO | GPIO 19 | SPI MISO |
| RFID-RC522 RST | GPIO 4 | Reset |
| RFID-RC522 VCC | 3.3V | Power |
| RFID-RC522 GND | GND | Ground |

| Component | ESP32 Pin | Description |
|-----------|-----------|-------------|
| R305 TX | GPIO 15 | Fingerprint TX |
| R305 RX | GPIO 13 | Fingerprint RX |
| R305 VCC | 3.3V | Power |
| R305 GND | GND | Ground |

| Component | ESP32 Pin | Description |
|-----------|-----------|-------------|
| LCD SDA | GPIO 21 | I2C Data |
| LCD SCL | GPIO 22 | I2C Clock |
| LCD VCC | 3.3V | Power |
| LCD GND | GND | Ground |

| Component | ESP32 Pin | Description |
|-----------|-----------|-------------|
| Buzzer | GPIO 2 | Audio output |
| Green LED | GPIO 26 | Success indicator |
| Red LED | GPIO 14 | Error indicator |

## Software Setup

### 1. Arduino IDE Setup

1. **Install Arduino IDE 2.0+**
   - Download from: https://www.arduino.cc/en/software

2. **Add ESP32 Board Support**
   - Open Arduino IDE
   - Go to File > Preferences
   - Add this URL to "Additional Board Manager URLs":
     ```
     https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
     ```
   - Go to Tools > Board > Boards Manager
   - Search for "ESP32" and install "ESP32 by Espressif Systems"

3. **Install Required Libraries**
   - Go to Tools > Manage Libraries
   - Install the following libraries:
     - `MFRC522` by GithubCommunity
     - `Adafruit Fingerprint Sensor Library` by Adafruit
     - `LiquidCrystal_PCF8574` by Mathias Munk Hansen
     - `WebSocketsClient` by Markus Sattler
     - `ArduinoJson` by Benoit Blanchon

### 2. Arduino Code Setup

1. **Open the Enhanced Arduino Code**
   - Open `hardware/esp32_attendance_enhanced.ino` in Arduino IDE

2. **Configure WiFi Settings**
   ```cpp
   const char* ssid = "prototype";
   const char* password = "prototype";
   ```

3. **Configure WebSocket Server**
   ```cpp
   const char* wsHost = "192.168.1.100"; // Change to your computer's IP
   const int wsPort = 8080;
   ```

4. **Upload Code to ESP32**
   - Select your ESP32 board from Tools > Board
   - Select the correct COM port
   - Click Upload

### 3. Web Server Setup

1. **Install Composer Dependencies**
   ```bash
   cd /path/to/attendance-system
   composer require cboden/ratchet
   ```

2. **Run Database Migration**
   ```sql
   -- Execute the SQL in sql/hardware_integration.sql
   -- This adds the necessary tables for hardware integration
   ```

3. **Start WebSocket Server**
   ```bash
   php websocket_server.php
   ```

4. **Configure Web Server**
   - Ensure your web server (XAMPP) is running
   - Make sure the attendance system is accessible

## Testing the Setup

### 1. Hardware Testing

1. **Power on the ESP32**
   - The LCD should display "Initializing..."
   - After initialization, it should show "Scan card/finger"

2. **Test RFID Reader**
   - Place an RFID card on the reader
   - The LCD should display the card UID
   - Check Serial Monitor for detailed output

3. **Test Fingerprint Scanner**
   - Place a finger on the scanner
   - The LCD should display the fingerprint ID
   - Check Serial Monitor for detailed output

### 2. Network Testing

1. **Check WiFi Connection**
   - The LCD should show "WiFi OK" if connected
   - Check Serial Monitor for connection status

2. **Test WebSocket Connection**
   - The LCD should show "WS Connected" if successful
   - Check Serial Monitor for WebSocket status

### 3. Integration Testing

1. **Test RFID Attendance**
   - Create a schedule in the web interface
   - Scan an RFID card during active schedule
   - Check if attendance is recorded in the database

2. **Test Fingerprint Attendance**
   - Scan a fingerprint during active schedule
   - Check if attendance is recorded in the database

3. **Test Real-time Updates**
   - Open professor dashboard in browser
   - Scan RFID/fingerprint
   - Check if real-time updates appear

## Troubleshooting

### Common Issues

1. **ESP32 Not Connecting to WiFi**
   - Check WiFi credentials
   - Ensure ESP32 is within range
   - Check Serial Monitor for error messages

2. **WebSocket Connection Failed**
   - Check if WebSocket server is running
   - Verify IP address in Arduino code
   - Check firewall settings

3. **RFID Reader Not Working**
   - Check wiring connections
   - Verify SPI pins are correct
   - Check if RFID card is properly placed

4. **Fingerprint Scanner Not Working**
   - Check wiring connections
   - Verify Serial2 pins are correct
   - Check if finger is properly placed

5. **Attendance Not Recording**
   - Check if schedule is active
   - Verify database connection
   - Check WebSocket server logs

### Debug Commands

Use these commands in Arduino Serial Monitor:

- `enroll` - Enroll a new fingerprint
- `delete` - Delete a fingerprint
- `status` - Request active schedules

### Log Files

Check these log files for errors:
- `logs/websocket.log` - WebSocket server logs
- `logs/attendance.log` - Attendance recording logs
- Arduino Serial Monitor - Hardware debug information

## Security Considerations

1. **Network Security**
   - Use WPA2 or WPA3 WiFi encryption
   - Consider using a dedicated network for the attendance system
   - Implement proper firewall rules

2. **Data Security**
   - Encrypt sensitive data in transit
   - Use HTTPS for web interface
   - Implement proper access controls

3. **Hardware Security**
   - Secure physical access to the hardware
   - Consider tamper detection
   - Regular security updates

## Maintenance

### Regular Tasks

1. **Hardware Maintenance**
   - Clean RFID reader and fingerprint scanner
   - Check wiring connections
   - Update Arduino code as needed

2. **Software Maintenance**
   - Update PHP and dependencies
   - Backup database regularly
   - Monitor log files

3. **Network Maintenance**
   - Monitor WiFi signal strength
   - Check for network interference
   - Update network security

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review log files
3. Test individual components
4. Contact technical support

## Next Steps

After successful setup:
1. Enroll all students' RFID cards and fingerprints
2. Create class schedules
3. Test the complete attendance flow
4. Train users on the system
5. Monitor system performance 