const WebSocket = require('ws');
const server = new WebSocket.Server({ port: 8080 });
const mysql = require('mysql2');
const poker = require('./poker');

(async function () {

    const connection = await mysql.createConnection({
        host: 'casino_db',
        user: 'root',
        password: 'Ckg8XqnXu5FWq5Hq5KlOpF6Bo3095GSV',
        database: 'casino'
    });

    class User {
        constructor(row) {
            this.attributes = { ...row };
        }
    }

    server.on('connection', (ws) => {
        let user = null;
        console.log('Cliente conectado');
        routes = {
            "poker.get_tables": () => {
                return [true, poker.tables];
            },
            "auth.login": async (token) => {
                const [rows, fields] = await connection.promise().query('SELECT * FROM users WHERE access_id=?', [token]);
                if (rows.length === 0) {
                    ws.send(JSON.stringify({
                        "success": false,
                        "response": "No te encuentras autenticado correctamente"
                    }));
                    ws.close();
                    return [false, false];
                }
                
                connection.execute("UPDATE users SET access_id='' WHERE access_id=?", [token]);
                user = new User(rows[0]);
                return [true, "auth.login"];
            }

        }

        ws.on('message', async (message) => {
            // try {
                const data = JSON.parse(message);
                const fn = routes[data.request];
                if (!fn)
                    ws.send(JSON.stringify({
                        "success": false,
                        "response": "El método no está definido"
                    }));
                if (!data.request.startsWith('auth.') && !user)
                    ws.send(JSON.stringify({
                        "success": false,
                        "response": "No te encuentras autenticado correctamente"
                    }));
                const [success, response] = await fn(data.data);
                ws.send(JSON.stringify({
                    "success": success,
                    "response": response
                }));
            // } catch (err) {
            //     ws.send(JSON.stringify({
            //         "success": false,
            //         "response": err.message
            //     }));
            // }
        });

        ws.on('close', () => {
            console.log('Cliente desconectado');
        });
    });

    console.log('Servidor WebSocket corriendo en ws://localhost:8080');

}())