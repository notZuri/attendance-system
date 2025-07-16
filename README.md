<div align="center">
  <img src="assets/images/capslogo.png" alt="Attendance System Logo" width="200" height="auto">
</div>

# 🎓 Smart Attendance System - Real-Time RFID & Fingerprint Attendance 🕒

A modern, responsive web application for automated attendance using RFID and fingerprint hardware. This system features real-time hardware communication, session-based enrollment, and a polished, accessible UI/UX for both professors and students.

📍 **Live Demo**: [Local Development Required]  
🛠️ **Tech Stack**: PHP 7.4+, MySQL 5.7+, HTML5, CSS3, JavaScript (ES6+), WebSocket, ESP32  
🎯 **Focus**: Hardware integration, real-time data, responsive design, secure and scalable architecture

---

## 🔑 Key Features

- 🕒 **Real-Time Hardware Integration**: ESP32-based RFID and fingerprint modules, live WebSocket updates
- 📝 **Session-Based Enrollment**: Professors enroll student RFID/fingerprint via web UI with real-time feedback
- 📋 **Automated Attendance**: Students marked present by scanning during active schedules
- 📊 **Live Dashboard**: Professors see real-time attendance and hardware status
- 🎨 **Modern UI/UX**: Unified tables, dropdown status, color-coded badges, sticky headers, zebra striping, tooltips, and bulk edit
- 🔍 **Enhanced Search Bars**: Search with icon, clear (×) button, and accessibility improvements
- 🔌 **Hardware Status**: Real-time device status, auto-refresh, emoji device icons
- ♿ **Accessibility**: ARIA labels, keyboard navigation, and focus states
- 📱 **Responsive Design**: Fully functional on mobile, tablet, desktop

---

<details>
<summary>📚 Full Project Documentation</summary>

## 🖐️ Hardware Module
- **ESP32 Microcontroller**: WiFi, RFID (RC522), Fingerprint (R305), LCD/LED feedback
- **Session-Based Enrollment**: Secure, real-time linking of scans to students
- **REST & WebSocket**: Hardware sends scan data to backend for attendance/enrollment

## 🌐 WebSocket & API Integration
- **WebSocket Server (PHP)**: Real-time, bidirectional communication for scan events and device status
- **REST API Endpoints**: Modular, secure, role-based for all attendance and enrollment actions

## 🎨 User Experience & Design
- **Unified Attendance Table**: All students, dropdown status, color-coded badges, sticky header, zebra striping, tooltips, bulk edit
- **Modern Search Bars**: Icon, clear button, accessible and responsive
- **Hardware Status**: Real-time device status, auto-refresh, emoji device icons
- **Accessibility**: ARIA labels, keyboard navigation, focus states
- **Responsive UI**: Mobile-first, swipeable tables, touch-friendly controls

## ⚡ Performance & Technical Excellence
- **Clean Architecture**: Modular folders, separation of concerns
- **Optimized Performance**: Efficient queries, real-time updates
- **Progressive Enhancement**: Basic functionality without JS

## 🔐 Security Features
- **Authentication & Authorization**: Secure login, session management, role-based access
- **Input Validation & Sanitization**: Prevents SQL injection and XSS
- **Data Protection**: Secure handling of biometric and personal data

## 🛠️ Technologies Used

### Frontend:
- **HTML5**: Semantic layout & structure
- **CSS3**: Custom properties, Flexbox, Grid
- **JavaScript (ES6+)**: UI logic, AJAX, WebSocket client

### Backend:
- **PHP 7.4+**: API endpoints, session management
- **MySQL 5.7+**: Database
- **WebSocket (Ratchet)**: Real-time server
- **ESP32**: Hardware integration

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

### Option 1: XAMPP Setup
1. Install XAMPP and start Apache/MySQL
2. Clone the repo and copy to `htdocs/attendance-system/`
3. Import `sql/attendance_system.sql` via phpMyAdmin
4. Run `composer require cboden/ratchet`
5. Start the WebSocket server: `php websocket_server.php`
6. Access at [http://localhost/attendance-system/](http://localhost/attendance-system/)

### Option 2: Local Dev Server
1. Install PHP/MySQL, clone repo, run `composer install`
2. Update `backend/config/config.php` with your DB credentials
3. Start PHP server: `php -S localhost:8000`
4. Start WebSocket server: `php websocket_server.php`
5. Access at [http://localhost:8000](http://localhost:8000)

---

## 🔐 Security Features

- **Role-Based Access**: Professors and students have separate dashboards and permissions
- **Input Validation**: All forms and API endpoints are validated and sanitized
- **Session Security**: Secure session management and logout
- **Data Protection**: Secure handling of personal and biometric data
- **Cross-browser Tested**: Chrome, Firefox, Edge

---

## 🔧 Customization Tips

- **API/DB**: Update `backend/config/config.php` for your environment
- **Hardware**: Edit WiFi and server config in `hardware/esp32_attendance_template.ino`
- **Styling**: Tweak CSS variables in `assets/css/style.css`
- **Branding**: Replace images and update UI as needed

---

## 🎯 Future Enhancements

- Progressive Web App (PWA) for offline support
- Native mobile app integration
- Advanced analytics and reporting
- Multi-campus/location support
- Push notifications and real-time alerts

---

## 👨‍💻 About the Developer

**Ian Christian Amistoso**  
🧑‍🎓 BSIT Student  
🌐 Focus: Full-stack & IoT Development  
🛠️ Tools: PHP, MySQL, JS, Arduino, ESP32, Git/GitHub

</details> 