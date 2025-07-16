// Real-time WebSocket communication for attendance system
class AttendanceWebSocket {
    constructor() {
        this.ws = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectInterval = 5000;
        this.serverUrl = 'ws://localhost:8080/ws';
        this.messageHandlers = new Map();
        
        this.init();
    }
    
    init() {
        this.connect();
        this.setupEventHandlers();
    }
    
    connect() {
        try {
            console.log('Connecting to WebSocket server...');
            this.ws = new WebSocket(this.serverUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.registerAsProfessor();
                this.showConnectionStatus('Connected', 'success');
            };
            
            this.ws.onmessage = (event) => {
                this.handleMessage(event.data);
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.showConnectionStatus('Disconnected', 'error');
                this.attemptReconnect();
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showConnectionStatus('Connection Error', 'error');
            };
            
        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
            this.showConnectionStatus('Connection Failed', 'error');
        }
    }
    
    registerAsProfessor() {
        if (this.isConnected) {
            const message = {
                type: 'professor_register',
                role: 'professor'
            };
            this.send(message);
        }
    }
    
    handleMessage(data) {
        try {
            const message = JSON.parse(data);
            console.log('Received message:', message);
            
            const handler = this.messageHandlers.get(message.type);
            if (handler) {
                handler(message);
            } else {
                console.log('No handler for message type:', message.type);
            }
            
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }
    
    send(data) {
        if (this.isConnected && this.ws) {
            try {
                this.ws.send(JSON.stringify(data));
            } catch (error) {
                console.error('Error sending WebSocket message:', error);
            }
        } else {
            console.warn('WebSocket not connected, cannot send message');
        }
    }
    
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectInterval);
        } else {
            console.error('Max reconnection attempts reached');
            this.showConnectionStatus('Connection Lost', 'error');
        }
    }
    
    showConnectionStatus(message, type) {
        // Create or update connection status indicator
        let statusElement = document.getElementById('connection-status');
        if (!statusElement) {
            statusElement = document.createElement('div');
            statusElement.id = 'connection-status';
            statusElement.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                padding: 8px 12px;
                border-radius: 4px;
                color: white;
                font-size: 12px;
                z-index: 1000;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(statusElement);
        }
        
        statusElement.textContent = message;
        statusElement.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
        
        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                if (statusElement.style.backgroundColor === '#28a745') {
                    statusElement.style.opacity = '0';
                    setTimeout(() => {
                        if (statusElement.parentNode) {
                            statusElement.parentNode.removeChild(statusElement);
                        }
                    }, 300);
                }
            }, 3000);
        }
    }
    
    // Register message handlers
    onAttendanceRecorded(handler) {
        this.messageHandlers.set('attendance_recorded', handler);
    }
    
    onHardwareStatus(handler) {
        this.messageHandlers.set('hardware_status', handler);
    }
    
    onScheduleUpdate(handler) {
        this.messageHandlers.set('schedule_update', handler);
    }
    
    // Utility methods
    isConnected() {
        return this.isConnected;
    }
    
    disconnect() {
        if (this.ws) {
            this.ws.close();
        }
    }
}

// Global WebSocket instance
let attendanceWebSocket = null;

// Initialize WebSocket when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on professor pages
    if (document.body.classList.contains('professor-dashboard') || 
        window.location.pathname.includes('/professor/')) {
        
        attendanceWebSocket = new AttendanceWebSocket();
        
        // Set up attendance recording handler
        attendanceWebSocket.onAttendanceRecorded((message) => {
            handleAttendanceRecorded(message);
        });
        
        // Set up hardware status handler
        attendanceWebSocket.onHardwareStatus((message) => {
            handleHardwareStatus(message);
        });
        
        // Set up schedule update handler
        attendanceWebSocket.onScheduleUpdate((message) => {
            handleScheduleUpdate(message);
        });
    }
});

// Handle real-time attendance recording
function handleAttendanceRecorded(message) {
    console.log('Attendance recorded:', message);
    
    // Show notification
    showToast('Attendance Recorded', 
        `${message.student_name} (${message.student_number}) - ${message.status.toUpperCase()}`, 
        'success');
    
    // Update attendance count if on dashboard
    updateAttendanceCount();
    
    // Add to real-time attendance feed if exists
    addToAttendanceFeed(message);
    
    // Play notification sound
    playNotificationSound();
}

// Handle hardware status updates
function handleHardwareStatus(message) {
    console.log('Hardware status:', message);
    
    // Update hardware status indicator
    updateHardwareStatusIndicator(message);
    
    // Show notification for important status changes
    if (message.status === 'error' || message.status === 'disconnected') {
        showToast('Hardware Alert', message.message, 'error');
    }
}

// Handle schedule updates
function handleScheduleUpdate(message) {
    console.log('Schedule update:', message);
    
    // Refresh schedule data
    if (typeof loadDashboardStats === 'function') {
        loadDashboardStats();
    }
    
    // Show notification for schedule activation
    if (message.status === 'activated') {
        showToast('Schedule Activated', 
            `${message.subject} in ${message.room}`, 
            'info');
    }
}

// Update attendance count on dashboard
function updateAttendanceCount() {
    const countElement = document.getElementById('today-attendance');
    if (countElement) {
        const currentCount = parseInt(countElement.textContent) || 0;
        countElement.textContent = currentCount + 1;
        
        // Add animation
        countElement.style.transform = 'scale(1.2)';
        setTimeout(() => {
            countElement.style.transform = 'scale(1)';
        }, 200);
    }
}

// Add to real-time attendance feed
function addToAttendanceFeed(message) {
    const feedElement = document.getElementById('recent-activity');
    if (feedElement) {
        const timestamp = new Date().toLocaleTimeString();
        const statusClass = message.status === 'present' ? 'success' : 'warning';
        
        const feedItem = document.createElement('div');
        feedItem.className = `feed-item ${statusClass}`;
        feedItem.innerHTML = `
            <div class="feed-time">${timestamp}</div>
            <div class="feed-content">
                <strong>${message.student_name}</strong> (${message.student_number})
                <br><small>${message.method.toUpperCase()} - ${message.status.toUpperCase()}</small>
            </div>
        `;
        
        // Add to top of feed
        feedElement.insertBefore(feedItem, feedElement.firstChild);
        
        // Remove old items if more than 10
        const items = feedElement.querySelectorAll('.feed-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }
}

// Update hardware status indicator
function updateHardwareStatusIndicator(message) {
    const statusElement = document.getElementById('hardware-status');
    if (statusElement) {
        statusElement.textContent = message.status;
        statusElement.className = `status-indicator ${message.status}`;
    }
}

// Play notification sound
function playNotificationSound() {
    // Create audio element for notification sound
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
    audio.volume = 0.3;
    audio.play().catch(e => console.log('Audio play failed:', e));
}

// Enhanced toast notification
function showToast(title, message, type = 'info', duration = 4000) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        `;
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#17a2b8'};
        color: white;
        padding: 12px 16px;
        margin-bottom: 8px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        font-size: 14px;
    `;
    
    toast.innerHTML = `
        <div style="font-weight: bold; margin-bottom: 4px;">${title}</div>
        <div>${message}</div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

// Export for use in other scripts
window.AttendanceWebSocket = AttendanceWebSocket;
window.attendanceWebSocket = attendanceWebSocket; 