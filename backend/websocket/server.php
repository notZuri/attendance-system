<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../attendance/auto_record.php';

class AttendanceWebSocketServer {
    private $clients = [];
    private $hardware_clients = [];
    private $professor_clients = [];
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function onConnect($client) {
        $this->clients[] = $client;
        echo "New client connected. Total clients: " . count($this->clients) . "\n";
        
        // Send initial status
        $this->sendToClient($client, [
            'type' => 'connection',
            'status' => 'connected',
            'message' => 'Connected to Attendance System'
        ]);
    }
    
    public function onMessage($client, $message) {
        $data = json_decode($message, true);
        
        if (!$data || !isset($data['type'])) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Invalid message format'
            ]);
            return;
        }
        
        switch ($data['type']) {
            case 'hardware_register':
                $this->handleHardwareRegistration($client, $data);
                break;
                
            case 'professor_register':
                $this->handleProfessorRegistration($client, $data);
                break;
                
            case 'rfid_scan':
                $this->handleRFIDScan($client, $data);
                break;
                
            case 'fingerprint_scan':
                $this->handleFingerprintScan($client, $data);
                break;
                
            case 'hardware_status':
                $this->handleHardwareStatus($client, $data);
                break;
                
            case 'schedule_request':
                $this->handleScheduleRequest($client, $data);
                break;
                
            default:
                $this->sendToClient($client, [
                    'type' => 'error',
                    'message' => 'Unknown message type: ' . $data['type']
                ]);
        }
    }
    
    public function onDisconnect($client) {
        // Remove from all client lists
        $this->clients = array_filter($this->clients, function($c) use ($client) {
            return $c !== $client;
        });
        
        $this->hardware_clients = array_filter($this->hardware_clients, function($c) use ($client) {
            return $c !== $client;
        });
        
        $this->professor_clients = array_filter($this->professor_clients, function($c) use ($client) {
            return $c !== $client;
        });
        
        echo "Client disconnected. Total clients: " . count($this->clients) . "\n";
    }
    
    private function handleHardwareRegistration($client, $data) {
        $this->hardware_clients[] = $client;
        
        $this->sendToClient($client, [
            'type' => 'hardware_registered',
            'status' => 'success',
            'message' => 'Hardware registered successfully'
        ]);
        
        // Notify professors about hardware connection
        $this->broadcastToProfessors([
            'type' => 'hardware_status',
            'status' => 'connected',
            'message' => 'Hardware device connected'
        ]);
    }
    
    private function handleProfessorRegistration($client, $data) {
        $this->professor_clients[] = $client;
        
        $this->sendToClient($client, [
            'type' => 'professor_registered',
            'status' => 'success',
            'message' => 'Professor registered successfully'
        ]);
    }
    
    private function handleRFIDScan($client, $data) {
        $card_uid = $data['card_uid'] ?? null;
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
        
        if (!$card_uid) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Missing card UID'
            ]);
            return;
        }
        
        try {
            $autoRecorder = new AutoAttendanceRecorder($this->pdo);
            $result = $autoRecorder->recordRFIDAttendance($card_uid, $timestamp);
            
            // Send response to hardware
            $this->sendToClient($client, [
                'type' => 'rfid_response',
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
            
            // Broadcast to professors
            if ($result['success']) {
                $this->broadcastToProfessors([
                    'type' => 'attendance_recorded',
                    'method' => 'rfid',
                    'student_name' => $result['data']['student_name'] ?? 'Unknown',
                    'student_number' => $result['data']['student_number'] ?? 'Unknown',
                    'status' => $result['data']['status'] ?? 'present',
                    'timestamp' => $timestamp
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function handleFingerprintScan($client, $data) {
        $template_id = $data['template_id'] ?? null;
        $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
        
        if (!$template_id) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Missing template ID'
            ]);
            return;
        }
        
        try {
            $autoRecorder = new AutoAttendanceRecorder($this->pdo);
            $result = $autoRecorder->recordFingerprintAttendance($template_id, $timestamp);
            
            // Send response to hardware
            $this->sendToClient($client, [
                'type' => 'fingerprint_response',
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
            
            // Broadcast to professors
            if ($result['success']) {
                $this->broadcastToProfessors([
                    'type' => 'attendance_recorded',
                    'method' => 'fingerprint',
                    'student_name' => $result['data']['student_name'] ?? 'Unknown',
                    'student_number' => $result['data']['student_number'] ?? 'Unknown',
                    'status' => $result['data']['status'] ?? 'present',
                    'timestamp' => $timestamp
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }
    
    private function handleHardwareStatus($client, $data) {
        $status = $data['status'] ?? 'unknown';
        $message = $data['message'] ?? '';
        
        // Broadcast hardware status to professors
        $this->broadcastToProfessors([
            'type' => 'hardware_status',
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function handleScheduleRequest($client, $data) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, subject, room, date, start_time, end_time, late_threshold, is_active, current_status
                FROM schedules 
                WHERE date = CURDATE() 
                AND current_status = 'active'
                ORDER BY start_time ASC
            ");
            $stmt->execute();
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendToClient($client, [
                'type' => 'schedule_response',
                'schedules' => $schedules
            ]);
            
        } catch (Exception $e) {
            $this->sendToClient($client, [
                'type' => 'error',
                'message' => 'Failed to fetch schedules: ' . $e->getMessage()
            ]);
        }
    }
    
    private function sendToClient($client, $data) {
        $message = json_encode($data);
        $client->send($message);
    }
    
    private function broadcastToProfessors($data) {
        $message = json_encode($data);
        foreach ($this->professor_clients as $client) {
            $client->send($message);
        }
    }
    
    public function broadcastToHardware($data) {
        $message = json_encode($data);
        foreach ($this->hardware_clients as $client) {
            $client->send($message);
        }
    }
} 