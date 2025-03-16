const { v4: uuidv4 } = require('uuid');


class Member {
    constructor(user, position) {
        this.user = user;
        this.position = position;
        this.folded = true;
        this.dealer = false;
        this.me = true;
        this.show_cards = false;
        this.bet = 0;
    }
}

class PokerGame {
    hands = {
        10: 'Escalera Real',
        9: 'Escalera de Color',
        8: 'Poker',
        7: 'Full House',
        6: 'Color',
        5: 'Escalera',
        4: 'Trío',
        3: 'Doble pareja',
        2: 'Pareja',
        1: 'Carta alta'
    };

    constructor(table) {
        this.id = uuidv4();
        this.table = table;
        this.pot = 0;
        this.currentBet = this.table.blinds.big;
        this.turn = false;
        this.deck = this.createDeck();
        this.communityCards = [];
        this.started = false;
    }

    toJSON() {
        return {
            id: this.id,
            pot: this.pot,
            currentBet: this.currentBet,
            turn: this.turn,
            started: this.started,
            communityCards: this.communityCards,
        };
    }

    checkHand(member) {
        cards = member.hand.concat(this.communityCards);
        const values = cards.map(card => card.value);
        const suits = cards.map(card => card.suit);

        const sortedValues = values.slice().sort((a, b) => a - b);
        const uniqueValues = [...new Set(sortedValues)];

        const isFlush = suits.every(suit => suit === suits[0]);
        const isStraight = uniqueValues.length === 5 &&
            uniqueValues[4] - uniqueValues[0] === 4;

        const counts = values.reduce((acc, value) => {
            acc[value] = (acc[value] || 0) + 1;
            return acc;
        }, {});

        const pairs = Object.values(counts).filter(count => count === 2).length;
        const threeOfKind = Object.values(counts).includes(3);
        const fourOfKind = Object.values(counts).includes(4);

        let handRank = 0;
        let highCards = [];

        if (isFlush && isStraight && sortedValues[0] === 10) {
            handRank = 10; // Escalera Real
            highCards = [14];
        } else if (isFlush && isStraight) {
            handRank = 9; // Escalera de Color
            highCards = [Math.max(...uniqueValues)];
        } else if (fourOfKind) {
            handRank = 8; // Poker
            const fourValue = Object.keys(counts).find(v => counts[v] === 4);
            highCards = [Number(fourValue), Math.max(...uniqueValues.filter(v => v != fourValue))];
        } else if (threeOfKind && pairs === 1) {
            handRank = 7; // Full House
            const threeValue = Object.keys(counts).find(v => counts[v] === 3);
            const pairValue = Object.keys(counts).find(v => counts[v] === 2);
            highCards = [Number(threeValue), Number(pairValue)];
        } else if (isFlush) {
            handRank = 6; // Color
            highCards = sortedValues.slice().reverse();
        } else if (isStraight) {
            handRank = 5; // Escalera
            highCards = [Math.max(...uniqueValues)];
        } else if (threeOfKind) {
            handRank = 4; // Trío
            const threeValue = Object.keys(counts).find(v => counts[v] === 3);
            highCards = [Number(threeValue), ...sortedValues.filter(v => v != threeValue).reverse()];
        } else if (pairs === 2) {
            handRank = 3; // Doble pareja
            const pairValues = Object.keys(counts)
                .filter(v => counts[v] === 2)
                .map(Number)
                .sort((a, b) => b - a);
            const kicker = Math.max(...uniqueValues.filter(v => !pairValues.includes(v)));
            highCards = [...pairValues, kicker];
        } else if (pairs === 1) {
            handRank = 2; // Pareja
            const pairValue = Object.keys(counts).find(v => counts[v] === 2);
            highCards = [Number(pairValue), ...sortedValues.filter(v => v != pairValue).reverse()];
        } else {
            handRank = 1; // Carta alta
            highCards = sortedValues.slice().reverse();
        }

        return { rank: handRank, highCards };
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
        setTimeout(() => {
            this.turn = this.table.members[(dealerIndex + (this.table.members.length == 2 ? 0 : 3)) % this.table.members.length].position;
            this.table.update();
        }, 2000);
    }

    nextGameStep() {
        let newRound = true;
        if (this.communityCards.length == 0) {
            this.communityCards = [this.deck.pop(), this.deck.pop(), this.deck.pop()];
        } else if (this.communityCards.length < 5) {
            this.communityCards.push(this.deck.pop());
        } else {
            newRound = false;
            this.turn = false;
            this.table.members.forEach(member => {
                if (!member.folded)
                    member.user.socket.send(JSON.stringify({
                        "request": "poker.finish_game",
                        "success": true
                    }));
            });
        }
        this.table.update();
        if (newRound)
            new Promise((resolve) => {
                setTimeout(() => {
                    this.turn = this.table.members.filter(member => {
                        return member.dealer
                    })[0].position;
                    this.table.update();
                    resolve();
                }, 2000);
            })
    }

    nextTurn() {
        console.log("next_turn")
        const members = this.table.members.sort((a, b) => a.position - b.position);
        const nextMember = members.find(member => member.position > this.turn) || members[0];
        if (nextMember.dealer) {
            this.turn = false;
            this.nextGameStep();
            return;
        }
        if (nextMember.folded)
            this.nextTurn();
        this.table.description = `Es el turno de ${nextMember.user.attributes.username}`;
        // console.log(nextMember)
        this.turn = nextMember.position;
        console.log("Turno de: " + nextMember.position)
        this.table.update();
    }

    async bet(member, bet) {
        this.pot += bet;
        member.bet += bet;
        member.user.attributes.balance -= bet;
        await member.user.updateBalance();
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
            big: 20,
            small: 10
        }
    }

    toJSON() {
        return {
            id: this.id,
            name: this.name,
            members: this.members,
            description: this.description,
            playing: this.playing,
            game: this.game
        };
    }

    join(user) {
        let available_slots = [1, 2, 3, 4, 5, 6, 7];
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
                        return {
                            me: m.user.attributes.id == member.user.attributes.id,
                            user: m.user,
                            bet: m.bet,
                            show_cards: m.show_cards,
                            hand: m.show_cards || m.user.attributes.id == member.user.attributes.id ? m.hand : undefined,
                            position: m.position,
                            folded: m.folded,
                            dealer: m.dealer,
                        }
                    }),
                    description: this.description,
                    playing: this.playing,
                    game: this.game
                }
            }));
        });
    }
}

tables = [];

module.exports.tables = tables;
module.exports.Table = Table;