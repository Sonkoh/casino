const { v4: uuidv4 } = require('uuid');


class Member {
    constructor(user, position) {
        this.user = user;
        this.position = position;
        this.folded = true;
        this.dealer = false;
        this.me = true;
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
        const suits = ["H", "C", "D", "S"];
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
            member.hand = [this.deck.pop(), this.deck.pop()];
            member.folded = false;
            member.user.socket.send(JSON.stringify({
                "request": "poker.deal_cards",
                "success": true,
                "response": this.table.members.map((member) => { return member.position; })
            }));
        });
    }

    start() {
        this.started = true;
        this.dealCards();
        this.table.playing = true;
        this.table.description = "Juego en progreso";
        let dealerIndex = null
        this.table.members.forEach(function (member, index) {
            if (member.dealer)
                dealerIndex = index;
        });

        if (dealerIndex == null) {
            dealerIndex = Math.floor(Math.random() * this.table.members.length);
            this.table.members[dealerIndex].dealer = true;
        }

        if (this.table.members.length == 2) {
            this.bet(this.table.members[dealerIndex], this.table.blinds.small);
            this.bet(this.table.members[(dealerIndex + 1) % this.table.members.length], this.table.blinds.big);
        } else {
            this.bet(this.table.members[(dealerIndex + 1) % this.table.members.length], this.table.blinds.small);
            this.bet(this.table.members[(dealerIndex + 2) % this.table.members.length], this.table.blinds.big);
        }

        this.table.update();
    }

    bet(member, bet) {
        this.pot += bet;
        member.bet += bet;
        member.user.attributes.balance -= bet;
        member.user.updateBalance();
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
        this.blinds = {
            "big": 0.1,
            "small": 0.05
        }
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
        let available_slots = [1,2,3,4,5,6,7];
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
                "response": {
                    id: this.id,
                    name: this.name,
                    members: this.members.map((m) => {
                        if (m.user.attributes.id == member.user.attributes.id) {
                            return m;
                        } else {
                            return {
                                me: false,
                                user: m.user,
                                position: m.position,
                                folded: m.folded,
                                dealer: m.dealer,
                            };
                        }
                    }),
                    description: this.description,
                    playing: this.playing
                }
            }));
        });
    }
}

tables = [];

module.exports.tables = tables;
module.exports.Table = Table;