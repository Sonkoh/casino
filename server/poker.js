const { v4: uuidv4 } = require('uuid');


class Member {
    constructor(user, position) {
        this.user = user;
        this.position = position;
        this.folded = true;
        this.dealer = false;
        this.starting = false;
    }

    assign() {

    }
}

class PokerGame {
    constructor(table) {
        this.id = uuidv4();
        this.table = table;
        this.pot = 0;
        this.currentBet = 0;
        this.turn = 0;
        this.deck = this.createDeck();
        this.communityCards = [];
        this.started = false;
    }

    createDeck() {
        const suits = ['♠', '♥', '♦', '♣'];
        const values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
        let deck = [];
        suits.forEach(suit => {
            values.forEach(value => {
                deck.push({ suit, value });
            });
        });
        return this.shuffle(deck);
    }

    shuffle(deck) {
        for (let i = deck.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [deck[i], deck[j]] = [deck[j], deck[i]];
        }
        return deck;
    }

    dealCards() {
        this.table.members.forEach(member => {
            setTimeout(() => {
                member.hand = [this.deck.pop(), this.deck.pop()];
                member.user.socket.send(JSON.stringify({
                    "request": "poker.deal_cards",
                    "success": true,
                    "response": member.position
                }));
            }, 500);
        });
    }

    start() {
        this.started = true;
        this.dealCards();
        this.table.playing = true;
        this.table.description = "Juego en progreso";
        this.table.update();
    }

    placeBet(member, amount) {
        if (member.user.attributes.balance >= amount) {
            member.user.attributes.balance -= amount;
            this.pot += amount;
            this.currentBet = Math.max(this.currentBet, amount);
            this.table.update();
        } else {
            console.log("Saldo insuficiente para apostar.");
        }
    }

    nextTurn() {
        this.turn = (this.turn + 1) % this.table.members.length;
        this.table.update();
    }
}

class Table {
    constructor(name) {
        this.id = uuidv4();
        this.name = name;
        this.members = [];
        this.playing = false;
        this.description = "Esperando Jugadores";
        this.game = null;
    }

    toJSON() {
        return {
            id: this.id,
            name: this.name,
            members: this.members,
            description: this.description,
            playing: this.playing
        };
    }

    join(user) {
        let available_slots = [5, 6, 2, 1, 4, 3];
        this.members.forEach(member => {
            available_slots = available_slots.filter(slot => { return slot != member.position });
        });

        let member = new Member(user, available_slots[0]);
        this.members.push(member);
        this.prepareGame();
    }

    prepareGame() {
        if (this.playing)
            return;
        if (this.members.length < 2) {
            this.description = "Esperando Jugadores";
            this.starting = false;
            this.update();
            return;
        }
        if (this.starting)
            return;
        this.starting = true;
        let seconds = 10;

        const interval = setInterval(() => {
            this.description = `Comenzando Juego en ${seconds} segundos`;
            this.update();

            seconds--;

            if (seconds < 0) {
                clearInterval(interval);
                if (this.members.length < 2) {
                    this.description = "No hay suficientes jugadores";
                    this.starting = false;
                    this.update();
                    return;
                }
                this.description = "Partida en Progreso";
                this.update();
                this.game = new PokerGame(this);
                this.game.start();
            }
        }, 1000);
    }

    update() {
        this.members.forEach(member => {
            member.user.socket.send(JSON.stringify({
                "request": "poker.table_status",
                "success": true,
                "response": this
            }))
        });
    }
}

tables = [];

module.exports.tables = tables;
module.exports.Table = Table;