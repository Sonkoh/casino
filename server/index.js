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
        constructor(row, ws) {
            this.attributes = { ...row };
            this.socket = ws;
        }
        toJSON() {
            return this.attributes;
        }
        async updateBalance() {
            await connection.promise().execute("UPDATE users SET balance=? WHERE id=?", [this.attributes.balance, this.attributes.id]);
        }
    }

    function broadcast(data) {
        server.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(data);
            }
        });
    }

    server.on('connection', (ws) => {
        ws.user = 0;
        let user = null;
        console.log('Cliente conectado');
        routes = {
            "poker.get_tables": () => {
                return [true, poker.tables];
            },
            "poker.create_table": (data) => {
                is_in_any_table = false;
                poker.tables.forEach(table => {
                    table.members.forEach(member => {
                        if (member.user.id == ws.user.id)
                            is_in_any_table = true;
                    });
                });
                if (is_in_any_table) {
                    ws.send(JSON.stringify({
                        "request": "notification",
                        "success": false,
                        "response": "Ya te encuentras en una mesa"
                    }));
                    return [false, false];
                }
                table = new poker.Table(data.name);
                poker.tables.push(table);
                return [true, table.id];
            },
            "poker.bet": async (data) => {
                tables = poker.tables.filter((table) => {
                    return table.id == data.table
                });
                if (tables.length == 0) {
                    return [false, false];
                }
                table = tables[0];
                let member = table.members.find(m => {
                    return m.user.id == ws.user.id;
                });
                console.log("+======================+")
                console.log("table.game.turn: " + table.game.turn)
                console.log("member.position: " + member.position)
                console.log("+======================+")
                await table.game.bet(member, table.game.currentBet - member.bet);
                table.game.nextTurn();
                return [true, true];
            },
            "poker.check": (data) => {
                tables = poker.tables.filter((table) => {
                    return table.id == data.table
                });
                if (tables.length == 0) {
                    return [false, false];
                }
                table = tables[0];
                let member = table.members.find(m => {
                    return m.user.id == ws.user.id;
                });
                console.log("+======================+")
                console.log("table.game.turn: " + table.game.turn)
                console.log(member)
                console.log("+======================+")
                if (table.game.turn == member.position) 
                    table.game.nextTurn();
                return [true, true];
            },
            "poker.fold": (data) => {
                tables = poker.tables.filter((table) => {
                    return table.id == data.table
                });
                if (tables.length == 0) {
                    return [false, false];
                }
                table = tables[0];
                let member = table.members.filter(m => {
                    return m.user.id == ws.user.id;
                })[0];
                if (table.game.turn == member.position) {
                    table.game.nextTurn();
                } else {
                    member.folded = true;
                    table.update();
                }
                return [true, true];
            },
            "poker.show_cards": (data) => {
                tables = poker.tables.filter((table) => {
                    return table.id == data.table
                });
                if (tables.length == 0) {
                    return [false, false];
                }
                table = tables[0];
                let member = table.members.filter(m => {
                    return m.user.id == ws.user.id;
                })[0];
                member.show_cards = true;
                table.update();
                return [true, true];
            },
            "poker.join_table": async (data) => {
                tables = poker.tables.filter((table) => {
                    return table.id == data.table
                });
                if (tables.length == 0) {
                    return [false, false];
                }
                table = tables[0];
                let tb = table.members.filter(member => {
                    return member.user.attributes.id == ws.user.attributes.id;
                });
                if (tb.length == 0) {
                    table.join(ws.user);
                } else {
                    tb[0].user.socket.close();
                    tb[0].user = ws.user;
                }
                await broadcast(JSON.stringify({
                    "request": "poker.get_tables",
                    "success": true,
                    "response": poker.tables
                }));
                table.update();
                return [true, {
                    id: table.id,
                    name: table.name,
                    members: table.members.map((m) => {
                        if (m.user.attributes.id == ws.user.attributes.id) {
                            return m;
                        } else {
                            return {
                                user: m.user,
                                position: m.position,
                                folded: m.folded,
                                dealer: m.dealer,
                                bet: m.bet,
                                me: false,
                            };
                        }
                    }),
                    description: table.description,
                    playing: table.playing
                }
                ];
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

                connection.execute("UPDATE users SET access_id=NULL WHERE access_id=?", [token]);
                ws.user = new User(rows[0], ws);
                return [true, "Autenticación exitosa"];
            }

        }

        ws.on('message', async (message) => {
            // try {
            const data = JSON.parse(message);
            const fn = routes[data.request];
            if (!fn) {
                ws.send(JSON.stringify({
                    "request": data.request,
                    "success": false,
                    "response": "El método no está definido"
                }));
                return;
            }
            if (!data.request.startsWith('auth.') && !ws.user) {
                ws.send(JSON.stringify({
                    "request": data.request,
                    "success": false,
                    "response": "No te encuentras autenticado correctamente"
                }));
                return;
            }
            const [success, response] = await fn(data.data);
            ws.send(JSON.stringify({
                "request": data.request,
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