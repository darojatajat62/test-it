// index.js
const WhatsAppClient = require('./whatsapp-client');
const WhatsAppAPIServer = require('./server');

class WhatsAppApplication {
    constructor(port = 3000, clientId = "nilai-mahasiswa") {
        this.port = port;
        this.clientId = clientId;
        this.whatsappClient = null;
        this.server = null;
    }

    async initialize() {
        try {
            console.log('🚀 Starting WhatsApp Application...');
            
            // Initialize WhatsApp client
            this.whatsappClient = new WhatsAppClient(this.clientId);
            this.setupClientEvents();
            this.whatsappClient.initialize();
            
            // Initialize HTTP server
            this.server = new WhatsAppAPIServer(this.port, this.whatsappClient);
            await this.server.start();
            
            this.setupGracefulShutdown();
            
            console.log('✅ WhatsApp Application started successfully!');
            
        } catch (error) {
            console.error('❌ Failed to initialize application:', error);
            throw error;
        }
    }

    setupClientEvents() {
        this.whatsappClient.on('ready', () => {
            console.log('📱 WhatsApp client is ready for messages');
        });
        
        this.whatsappClient.on('disconnected', (reason) => {
            console.log('⚠️ WhatsApp disconnected:', reason);
        });
        
        this.whatsappClient.on('message_sent', (data) => {
            console.log(`📨 Message sent to ${data.phone}: ${data.messageId}`);
        });
        
        this.whatsappClient.on('qr', (qr) => {
            console.log('🔑 New QR code generated');
        });
    }

    setupGracefulShutdown() {
        const shutdown = async (signal) => {
            console.log(`\n${signal} received. Shutting down gracefully...`);
            
            try {
                // Stop HTTP server
                if (this.server) {
                    await this.server.stop();
                }
                
                // Destroy WhatsApp client
                if (this.whatsappClient) {
                    await this.whatsappClient.destroy();
                }
                
                console.log('✅ Application shutdown complete');
                process.exit(0);
                
            } catch (error) {
                console.error('❌ Error during shutdown:', error);
                process.exit(1);
            }
        };

        // Handle different shutdown signals
        process.on('SIGINT', () => shutdown('SIGINT'));
        process.on('SIGTERM', () => shutdown('SIGTERM'));
        process.on('SIGQUIT', () => shutdown('SIGQUIT'));
    }

    getClient() {
        return this.whatsappClient;
    }

    getServer() {
        return this.server;
    }
}

// Jika file ini dijalankan langsung (bukan di-require)
if (require.main === module) {
    const app = new WhatsAppApplication(3000);
    
    app.initialize().catch(error => {
        console.error('Failed to start application:', error);
        process.exit(1);
    });
}

// Export untuk digunakan sebagai module
module.exports = {
    WhatsAppApplication,
    WhatsAppClient,
    WhatsAppAPIServer
};// index.js
const WhatsAppClient = require('./whatsapp-client');
const WhatsAppAPIServer = require('./server');

class WhatsAppApplication {
    constructor(port = 3000, clientId = "nilai-mahasiswa") {
        this.port = port;
        this.clientId = clientId;
        this.whatsappClient = null;
        this.server = null;
    }

    async initialize() {
        try {
            console.log('🚀 Starting WhatsApp Application...');
            
            // Initialize WhatsApp client
            this.whatsappClient = new WhatsAppClient(this.clientId);
            this.setupClientEvents();
            this.whatsappClient.initialize();
            
            // Initialize HTTP server
            this.server = new WhatsAppAPIServer(this.port, this.whatsappClient);
            await this.server.start();
            
            this.setupGracefulShutdown();
            
            console.log('✅ WhatsApp Application started successfully!');
            
        } catch (error) {
            console.error('❌ Failed to initialize application:', error);
            throw error;
        }
    }

    setupClientEvents() {
        this.whatsappClient.on('ready', () => {
            console.log('📱 WhatsApp client is ready for messages');
        });
        
        this.whatsappClient.on('disconnected', (reason) => {
            console.log('⚠️ WhatsApp disconnected:', reason);
        });
        
        this.whatsappClient.on('message_sent', (data) => {
            console.log(`📨 Message sent to ${data.phone}: ${data.messageId}`);
        });
        
        this.whatsappClient.on('qr', (qr) => {
            console.log('🔑 New QR code generated');
        });
    }

    setupGracefulShutdown() {
        const shutdown = async (signal) => {
            console.log(`\n${signal} received. Shutting down gracefully...`);
            
            try {
                // Stop HTTP server
                if (this.server) {
                    await this.server.stop();
                }
                
                // Destroy WhatsApp client
                if (this.whatsappClient) {
                    await this.whatsappClient.destroy();
                }
                
                console.log('✅ Application shutdown complete');
                process.exit(0);
                
            } catch (error) {
                console.error('❌ Error during shutdown:', error);
                process.exit(1);
            }
        };

        // Handle different shutdown signals
        process.on('SIGINT', () => shutdown('SIGINT'));
        process.on('SIGTERM', () => shutdown('SIGTERM'));
        process.on('SIGQUIT', () => shutdown('SIGQUIT'));
    }

    getClient() {
        return this.whatsappClient;
    }

    getServer() {
        return this.server;
    }
}

// Jika file ini dijalankan langsung (bukan di-require)
if (require.main === module) {
    const app = new WhatsAppApplication(3000);
    
    app.initialize().catch(error => {
        console.error('Failed to start application:', error);
        process.exit(1);
    });
}

// Export untuk digunakan sebagai module
module.exports = {
    WhatsAppApplication,
    WhatsAppClient,
    WhatsAppAPIServer
};