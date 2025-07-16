<?php
require_once __DIR__ . '/backend/config/config.php';
require_once __DIR__ . '/backend/websocket/server.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketHandler implements MessageComponentInterface {
    private $clients;
    private $server;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->server = new AttendanceWebSocketServer($GLOBALS['pdo']);
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->server->onConnect($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $this->server->onMessage($from, $msg);
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $this->server->onDisconnect($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Check if Ratchet is installed
if (!class_exists('Ratchet\Server\IoServer')) {
    echo "Ratchet WebSocket library not found. Installing...\n";
    
    // Try to install via Composer
    if (file_exists('composer.json')) {
        exec('composer require cboden/ratchet');
    } else {
        echo "Please install Ratchet manually:\n";
        echo "composer require cboden/ratchet\n";
        echo "Or download from: https://github.com/cboden/Ratchet\n";
        exit(1);
    }
}

// Start the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketHandler()
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
echo "Press Ctrl+C to stop the server\n";

$server->run(); 