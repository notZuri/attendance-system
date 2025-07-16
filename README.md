# 🎓 Smart Attendance System - Real-Time RFID & Fingerprint Attendance 🕒

A modern, responsive web application for automated attendance using RFID and fingerprint hardware. Built with PHP, MySQL, JavaScript (ES6+), and a native WebSocket server, this system demonstrates advanced full-stack and IoT integration skills, including real-time hardware communication, and a session-based enrollment.

📍 **Live Demo:** [Local Development Required]
🛠️ **Tech Stack:** PHP 7.4+, MySQL 5.7+, HTML5, CSS3, JavaScript (ES6+), WebSocket, ESP32
🎯 **Focus:** Hardware integration, real-time data, responsive design, secure and scalable architecture

---

## 🔑 Key Features

- **Real-Time Hardware Integration:**
  - Seamless communication with ESP32-based RFID and fingerprint modules via WebSocket and REST API.
- **Session-Based Enrollment:**
  - Professors enroll student RFID cards and fingerprints directly from the web UI, with real-time feedback and session tracking.
- **Automated Attendance:**
  - Students are marked present by scanning their RFID card or fingerprint during active schedules.
- **Responsive Design:**
  - Fully functional on mobile, tablet, and desktop with swipeable tables and touch-friendly UI.
- **Accessibility:**
  - Keyboard navigation, ARIA labels, and WCAG compliance.
- **Security:**
  - Role-based access, input validation, prepared statements, and secure session management.
- **Live Dashboard:**
  - Professors see real-time attendance updates and hardware status.

---

## 📚 Full Project Documentation

### 🖐️ Hardware Module
- **ESP32 Microcontroller:**
  - Connects to WiFi and the PHP WebSocket server.
  - Reads RFID cards (RC522) and fingerprints (R305).
  - Sends scan data to the backend for both attendance and enrollment.
- **Session-Based Enrollment:**
  - Enrollment sessions are created in the database; hardware scans are linked to students via unique session IDs.
- **Real-Time Feedback:**
  - LCD, LEDs, and buzzer provide instant feedback on scan status.

### 🌐 WebSocket & API Integration
- **WebSocket Server (PHP, Ratchet):**
  - Acts as middleware for real-time, bidirectional communication between hardware and web app.
  - Handles scan events, device registration, and live updates.
- **REST API Endpoints:**
  - For enrollment and attendance scan data.
  - Secure, role-based, and modular.

### 🎨 User Experience & Design
- **Responsive UI:**
  - Mobile-first approach with breakpoints for all device sizes.
  - Swipeable tables, touch-friendly buttons, and adaptive forms.
- **Modern UI/UX:**
  - Smooth transitions, modal dialogs, and real-time feedback.
- **Accessibility:**
  - ARIA labels, keyboard navigation, and WCAG compliance.

### ⚡ Performance & Technical Excellence
- **Clean Architecture:**
  - Modular folder structure, separation of concerns, and MVC-inspired backend.
- **Optimized Performance:**
  - Efficient queries, caching, and real-time updates.
- **Progressive Enhancement:**
  - Works without JavaScript for basic functionality.

### 🔐 Security Features
- **Authentication & Authorization:**
  - Secure login, session management, and role-based access.
- **Input Validation & Sanitization:**
  - Prevents SQL injection and XSS.
- **Data Protection:**
  - Encrypted communication, secure handling of biometric data.

---

## 🛠️ Technologies Used

**Frontend:**  
HTML5, CSS3 (Flexbox, Grid, custom properties), JavaScript (ES6+), AJAX, WebSocket client

**Backend:**  
PHP 7.4+, MySQL 5.7+, Ratchet WebSocket server, Composer, Apache/Nginx

**Hardware:**  
ESP32, RFID-RC522, R305 Fingerprint Scanner, Arduino IDE, WiFi

---

## 📁 Folder Structure

```
attendance-system/
├── assets/           # CSS, JS, images
├── backend/          # PHP APIs, WebSocket, hardware integration
├── frontend/         # Professor/student dashboards, UI components
├── hardware/         # ESP32 Arduino code
├── sql/              # Database schema and migrations
├── websocket_server.php  # WebSocket server entry point
├── HARDWARE_SETUP.md     # Hardware setup guide
└── index.php         # Main entry point
```

---

## 🧪 How to Run

**Option 1: XAMPP Setup**
1. Install XAMPP and start Apache/MySQL.
2. Clone the repo and copy to `htdocs/attendance-system/`.
3. Import `sql/attendance_system.sql` via phpMyAdmin.
4. Run `composer require cboden/ratchet`.
5. Start the WebSocket server: `php websocket_server.php`.
6. Access at [http://localhost/attendance-system/](http://localhost/attendance-system/).

**Option 2: Local Dev Server**
1. Install PHP/MySQL, clone repo, run `composer install`.
2. Update `backend/config/config.php` with your DB credentials.
3. Start PHP server: `php -S localhost:8000`.
4. Start WebSocket server: `php websocket_server.php`.
5. Access at [http://localhost:8000](http://localhost:8000).

---

## 📱 Responsive Design

- **Desktop (1200px+):** Full layout, optimal spacing.
- **Tablet (768px-1200px):** Touch-friendly, adjusted layout.
- **Mobile (480px-768px):** Stacked, swipeable tables.
- **Small Mobile (480px-):** Compact, minimal padding.

---

## 🔧 Customization Tips

- **API/DB:** Update `backend/config/config.php` for your environment.
- **Hardware:** Edit WiFi and server config in `hardware/esp32_attendance_template.ino`.
- **Styling:** Tweak CSS variables in `assets/css/style.css`.
- **Branding:** Replace images and update UI as needed.

---

## 🔐 Security & Privacy

- **API Security:** Role-based access, input validation, and prepared statements.
- **XSS Prevention:** Output encoding and content security policies.
- **Data Protection:** Secure handling of personal and biometric data.
- **Network Security:** WPA2/WPA3 WiFi, firewall, and HTTPS recommended.

---

## 🎯 Future Enhancements

- Progressive Web App (PWA) for offline support.
- Native mobile app integration.
- Advanced analytics and reporting.
- Multi-campus/location support.
- Push notifications and real-time alerts.

---

## 🐛 Known Issues & Limitations

- Requires ESP32 hardware for full functionality.
- WebSocket server must be running for real-time features.
- Some features require modern browsers.

---

## 👨‍💻 About the Developer

**Ian Christian Amistoso**  
🧑‍🎓 BSIT Student  
🌐 Focus: Full-stack & IoT Development  
🛠️ Tools: PHP, MySQL, JS, Arduino, ESP32, Git/GitHub

---

**⭐ Star this repository if you find it helpful!**  
**🔄 Stay updated with the latest features and improvements!** 