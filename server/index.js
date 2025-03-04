const WebSocket = require('ws');

const server = new WebSocket.Server({ port: 8080 });

server.on('connection', (ws) => {
    console.log('Cliente conectado');

    ws.on('message', (message) => {
        console.log(`Mensaje recibido: ${message}`);
        ws.send(`Eco: ${message}`);
    });

    ws.on('close', () => {
        console.log('Cliente desconectado');
    });
});

console.log('Servidor WebSocket corriendo en ws://localhost:8080');
