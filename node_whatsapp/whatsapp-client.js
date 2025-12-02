// whatsapp-client.js
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const qrcodeGenerator = require('qrcode');

class WhatsAppClient {
    constructor(clientId = "nilai-mahasiswa") {
        this.client = null;
        this.qrCode = null;
        this.isConnected = false;
        this.clientId = clientId;
        this.eventListeners = {};
    }

    initialize() {
        console.log('🚀 Initializing WhatsApp Client...');
        
        this.client = new Client({
            authStrategy: new LocalAuth({
                clientId: this.clientId
            }),
            puppeteer: {
                headless: true,
                args: [
                    '--no-sandbox', 
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage'
                ]
            }
        });

        this.setupEventHandlers();
        this.client.initialize();
        
        return this;
    }

    setupEventHandlers() {
        this.client.on('qr', (qr) => {
            console.log('📱 QR Code received - Scan with WhatsApp');
            this.qrCode = qr;
            qrcode.generate(qr, { small: true });
            
            // Trigger custom event
            this.emit('qr', qr);
        });

        this.client.on('ready', () => {
            console.log('✅ WhatsApp Client is ready!');
            this.isConnected = true;
            this.emit('ready');
        });

        this.client.on('authenticated', () => {
            console.log('🔑 WhatsApp authenticated!');
            this.emit('authenticated');
        });

        this.client.on('auth_failure', (msg) => {
            console.error('❌ Auth failure:', msg);
            this.isConnected = false;
            this.emit('auth_failure', msg);
        });

        this.client.on('disconnected', (reason) => {
            console.log('❌ WhatsApp disconnected:', reason);
            this.isConnected = false;
            this.qrCode = null;
            this.emit('disconnected', reason);
            
            this.restart(3000);
        });
    }

    // Event emitter pattern
    on(event, listener) {
        if (!this.eventListeners[event]) {
            this.eventListeners[event] = [];
        }
        this.eventListeners[event].push(listener);
    }

    emit(event, ...args) {
        if (this.eventListeners[event]) {
            this.eventListeners[event].forEach(listener => {
                listener(...args);
            });
        }
    }

    async restart(delay = 3000) {
        console.log(`🔄 Restarting WhatsApp client in ${delay}ms...`);
        
        if (this.client) {
            await this.client.destroy();
        }
        
        setTimeout(() => {
            this.initialize();
        }, delay);
    }

    async destroy() {
        if (this.client) {
            await this.client.destroy();
            this.client = null;
            this.isConnected = false;
            this.emit('destroyed');
        }
        return true;
    }

    async getQRCodeImage() {
        if (!this.qrCode) return null;
        
        try {
            return await qrcodeGenerator.toDataURL(this.qrCode);
        } catch (error) {
            console.error('QR generation error:', error);
            throw error;
        }
    }

    formatPhoneNumber(phone) {
        if (!phone) throw new Error('Phone number is required');
        
        let formattedPhone = phone.replace(/[^0-9]/g, '');
        
        // Format untuk Indonesia (62)
        if (formattedPhone.startsWith('0')) {
            formattedPhone = '62' + formattedPhone.substring(1);
        }
        
        // Pastikan ada kode negara
        if (!formattedPhone.startsWith('62')) {
            formattedPhone = '62' + formattedPhone;
        }
        
        return formattedPhone + '@c.us';
    }

    async sendMessage(phone, message, fileData = null) {
        if (!this.isConnected || !this.client) {
            throw new Error('WhatsApp not connected. Please scan QR code first.');
        }

        console.log('📤 Sending to phone:', phone);
        const chatId = this.formatPhoneNumber(phone);
        console.log('Formatted chatId:', chatId);

        let result;
        
        if (fileData) {
            const media = new MessageMedia(
                fileData.mimeType,
                fileData.base64,
                fileData.fileName
            );
            
            result = await this.client.sendMessage(chatId, media, {
                caption: message || 'Laporan Nilai Mahasiswa'
            });
        } else {
            result = await this.client.sendMessage(
                chatId, 
                message || 'Laporan Nilai Mahasiswa'
            );
        }

        console.log('✅ Message sent:', result.id.id);
        
        this.emit('message_sent', {
            success: true,
            messageId: result.id.id,
            chatId: chatId,
            phone: phone
        });
        
        return {
            success: true,
            messageId: result.id.id,
            chatId: chatId
        };
    }

    async sendTextMessage(phone, message) {
        return this.sendMessage(phone, message);
    }

    async sendFileMessage(phone, message, filePath, mimeType, fileName) {
        const fs = require('fs');
        const fileBuffer = fs.readFileSync(filePath);
        
        const fileData = {
            mimeType: mimeType || 'application/octet-stream',
            base64: fileBuffer.toString('base64'),
            fileName: fileName || 'file.xlsx'
        };
        
        return this.sendMessage(phone, message, fileData);
    }

    getStatus() {
        return {
            connected: this.isConnected,
            qrCodeAvailable: !!this.qrCode,
            clientId: this.clientId,
            timestamp: new Date().toISOString()
        };
    }
}

module.exports = WhatsAppClient;