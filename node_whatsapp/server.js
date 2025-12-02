// server.js
const express = require('express');
const multer = require('multer');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

class WhatsAppAPIServer {
    constructor(port = 3000, whatsappClient) {
        this.port = port;
        this.app = express();
        this.whatsappClient = whatsappClient;
        this.upload = multer({ 
            dest: 'uploads/',
            limits: { fileSize: 10 * 1024 * 1024 }
        });
        
        this.setupMiddleware();
        this.setupRoutes();
    }

    setupMiddleware() {
        this.app.use(cors());
        this.app.use(express.json());
        this.app.use(express.urlencoded({ extended: true }));
        this.app.use(express.static('public'));
        
        // Error handling middleware
        this.app.use((error, req, res, next) => {
            console.error('Server Error:', error);
            res.status(500).json({
                status: 'error',
                message: 'Internal server error',
                error: error.message
            });
        });
    }

    setupRoutes() {
        this.app.get('/', this.handleRoot.bind(this));
        this.app.get('/qr', this.handleQR.bind(this));
        this.app.get('/status', this.handleStatus.bind(this));
        this.app.post('/send', this.upload.single('file'), this.handleSend.bind(this));
        this.app.post('/send-message', this.handleSendMessage.bind(this));
        
        // Health check endpoint
        this.app.get('/health', (req, res) => {
            res.json({ 
                status: 'healthy', 
                timestamp: new Date().toISOString(),
                uptime: process.uptime()
            });
        });
    }

    handleRoot(req, res) {
        const isConnected = this.whatsappClient.isConnected;
        res.send(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>WhatsApp API</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
                    .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
                    .connected { background: #4CAF50; color: white; }
                    .disconnected { background: #f44336; color: white; }
                    button { padding: 10px 15px; margin: 5px; cursor: pointer; background: #2196F3; color: white; border: none; border-radius: 4px; }
                    button:hover { background: #0b7dda; }
                    #qrImage { max-width: 300px; margin: 20px 0; border: 1px solid #ddd; padding: 10px; }
                    .container { background: #f5f5f5; padding: 20px; border-radius: 8px; }
                    .endpoints { margin-top: 20px; }
                    .endpoint { background: white; padding: 10px; margin: 5px 0; border-left: 4px solid #2196F3; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>WhatsApp API Server</h1>
                    <div class="status ${isConnected ? 'connected' : 'disconnected'}">
                        Status: ${isConnected ? '✅ CONNECTED' : '❌ DISCONNECTED'}
                    </div>
                    <button onclick="getQR()">Get QR Code</button>
                    <button onclick="checkStatus()">Check Status</button>
                    <button onclick="checkHealth()">Health Check</button>
                    
                    <div id="result"></div>
                    
                    <div class="endpoints">
                        <h3>Available Endpoints:</h3>
                        <div class="endpoint"><strong>GET /</strong> - This dashboard</div>
                        <div class="endpoint"><strong>GET /qr</strong> - Get QR code</div>
                        <div class="endpoint"><strong>GET /status</strong> - WhatsApp status</div>
                        <div class="endpoint"><strong>GET /health</strong> - Server health</div>
                        <div class="endpoint"><strong>POST /send</strong> - Send message with file</div>
                        <div class="endpoint"><strong>POST /send-message</strong> - Send text only</div>
                    </div>
                </div>
                <script>
                    async function getQR() {
                        const res = await fetch('/qr');
                        const data = await res.json();
                        const resultDiv = document.getElementById('result');
                        
                        if(data.qr) {
                            resultDiv.innerHTML = 
                                '<h3>Scan QR Code:</h3><img id="qrImage" src="' + data.qr + '">' +
                                '<p>' + data.message + '</p>';
                        } else {
                            resultDiv.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                        }
                    }
                    
                    async function checkStatus() {
                        const res = await fetch('/status');
                        const data = await res.json();
                        document.getElementById('result').innerHTML = 
                            '<h3>Status:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                    
                    async function checkHealth() {
                        const res = await fetch('/health');
                        const data = await res.json();
                        document.getElementById('result').innerHTML = 
                            '<h3>Health:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    }
                </script>
            </body>
            </html>
        `);
    }

    async handleQR(req, res) {
        try {
            if (this.whatsappClient.qrCode) {
                const qrImageUrl = await this.whatsappClient.getQRCodeImage();
                res.json({ 
                    status: 'success', 
                    qr: qrImageUrl,
                    message: 'Scan this QR code with WhatsApp'
                });
            } else if (this.whatsappClient.isConnected) {
                res.json({ 
                    status: 'connected', 
                    message: 'WhatsApp is already connected',
                    qr: null
                });
            } else {
                res.json({ 
                    status: 'waiting', 
                    message: 'Waiting for QR code. Please wait...',
                    qr: null
                });
            }
        } catch (error) {
            console.error('QR generation error:', error);
            res.status(500).json({ 
                status: 'error', 
                message: 'Failed to generate QR code',
                error: error.message
            });
        }
    }

    handleStatus(req, res) {
        const status = this.whatsappClient.getStatus();
        res.json({ 
            status: status.connected ? 'connected' : 'disconnected',
            connected: status.connected,
            message: status.connected ? 
                'WhatsApp is connected and ready to send messages' : 
                'WhatsApp is not connected. Please scan QR code first.',
            timestamp: status.timestamp,
            qrCodeAvailable: status.qrCodeAvailable,
            clientId: status.clientId
        });
    }

    async handleSend(req, res) {
        let tempFile = null;
        
        try {
            const { phone, message } = req.body;
            const file = req.file;

            if (!phone) {
                throw new Error('Phone number is required');
            }

            let fileData = null;
            
            if (file) {
                tempFile = file.path;
                const fileBuffer = fs.readFileSync(file.path);
                fileData = {
                    mimeType: file.mimetype || 'application/octet-stream',
                    base64: fileBuffer.toString('base64'),
                    fileName: file.originalname || 'file.xlsx'
                };
            }

            const result = await this.whatsappClient.sendMessage(phone, message, fileData);
            
            res.json({ 
                status: 'success', 
                message: 'Message sent successfully to ' + phone,
                messageId: result.messageId,
                chatId: result.chatId,
                timestamp: new Date().toISOString()
            });

        } catch (error) {
            console.error('❌ Error sending message:', error);
            
            let errorMessage = 'Failed to send message';
            if (error.message.includes('not registered')) {
                errorMessage = 'Nomor WhatsApp tidak terdaftar';
            } else if (error.message.includes('timed out')) {
                errorMessage = 'Timeout saat mengirim pesan';
            }
            
            res.status(500).json({ 
                status: 'error', 
                message: errorMessage,
                error: error.message
            });
        } finally {
            // Clean up temp file jika ada
            if (tempFile && fs.existsSync(tempFile)) {
                fs.unlinkSync(tempFile);
            }
        }
    }

    async handleSendMessage(req, res) {
        try {
            const { phone, message } = req.body;
            
            if (!phone) {
                throw new Error('Phone number is required');
            }
            
            const result = await this.whatsappClient.sendTextMessage(
                phone, 
                message || 'Test message from WhatsApp API'
            );
            
            res.json({ 
                status: 'success', 
                message: 'Message sent successfully',
                messageId: result.messageId,
                chatId: result.chatId,
                timestamp: new Date().toISOString()
            });
        } catch (error) {
            console.error('Error sending message:', error);
            res.status(500).json({ 
                status: 'error', 
                message: error.message 
            });
        }
    }

    start() {
        return new Promise((resolve) => {
            this.server = this.app.listen(this.port, () => {
                console.log(`🚀 WhatsApp API server running on http://localhost:${this.port}`);
                console.log(`📱 Get QR code: http://localhost:${this.port}/qr`);
                console.log(`📊 Check status: http://localhost:${this.port}/status`);
                console.log(`🏥 Health check: http://localhost:${this.port}/health`);
                resolve(this.server);
            });
        });
    }

    stop() {
        return new Promise((resolve) => {
            if (this.server) {
                this.server.close(() => {
                    console.log('Server stopped');
                    resolve();
                });
            } else {
                resolve();
            }
        });
    }
}

module.exports = WhatsAppAPIServer;