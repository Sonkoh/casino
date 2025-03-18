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

class Card {
    constructor(suit, value) {
        this.suit = suit;
        this.value = value;
    }

    setPosition(position) {
        this.position = position;
    }
}

class PokerGame {
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
        let cards = member.hand.concat(this.communityCards);
        const valuesMap = {
            '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7,
            '8': 8, '9': 9, '10': 10, 'J': 11, 'Q': 12, 'K': 13, 'A': 14
        };
    
        const sortedCards = cards
            .map(card => ({ ...card, numericValue: valuesMap[card.value] }))
            .sort((a, b) => b.numericValue - a.numericValue);
    
        const countBy = (key) => {
            return sortedCards.reduce((acc, card) => {
                acc[card[key]] = (acc[card[key]] || 0) + 1;
                return acc;
            }, {});
        };
    
        const valuesCount = countBy('numericValue');
        const suitsCount = countBy('suit');
    
        // 🃏 Revisar escalera
        const isStraight = () => {
            const uniqueValues = [...new Set(sortedCards.map(c => c.numericValue))];
            for (let i = 0; i <= uniqueValues.length - 5; i++) {
                if (uniqueValues[i] - uniqueValues[i + 4] === 4) {
                    return sortedCards.filter(c => 
                        uniqueValues.slice(i, i + 5).includes(c.numericValue)
                    ).slice(0, 5);
                }
            }
            // Caso especial para la escalera baja (A, 2, 3, 4, 5)
            const lowStraight = [14, 2, 3, 4, 5];
            if (lowStraight.every(v => uniqueValues.includes(v))) {
                return sortedCards.filter(c => lowStraight.includes(c.numericValue)).slice(0, 5);
            }
            return null;
        };
    
        // 🃏 Revisar color
        const isFlush = () => {
            for (const suit in suitsCount) {
                if (suitsCount[suit] >= 5) {
                    return sortedCards.filter(c => c.suit === suit).slice(0, 5);
                }
            }
            return null;
        };
    
        // 🃏 Revisar pares, tríos y poker
        const findPairs = () => {
            const pairs = [];
            const trips = [];
            let four = null;
            for (const value in valuesCount) {
                if (valuesCount[value] === 2) pairs.push(parseInt(value));
                if (valuesCount[value] === 3) trips.push(parseInt(value));
                if (valuesCount[value] === 4) four = parseInt(value);
            }
            return { pairs, trips, four };
        };
    
        // Evaluar combinaciones
        const { pairs, trips, four } = findPairs();
        const flush = isFlush();
        const straight = isStraight();
    
        // ♠️♣️♥️♦️ Escalera de color
        if (flush && straight) {
            return {
                hand: 'Straight Flush',
                cards: straight
            };
        }
    
        // 👑 Poker
        if (four) {
            return {
                hand: 'Four of a Kind',
                cards: sortedCards.filter(c => c.numericValue === four)
            };
        }
    
        // 🏠 Full House
        if (trips.length && pairs.length) {
            return {
                hand: 'Full House',
                cards: sortedCards.filter(c =>
                    c.numericValue === trips[0] || c.numericValue === pairs[0]
                )
            };
        }
    
        // 🌈 Color
        if (flush) {
            return {
                hand: 'Flush',
                cards: flush
            };
        }
    
        // ➡️ Escalera
        if (straight) {
            return {
                hand: 'Straight',
                cards: straight
            };
        }
    
        // 👌 Trío
        if (trips.length) {
            return {
                hand: 'Three of a Kind',
                cards: sortedCards.filter(c => c.numericValue === trips[0])
            };
        }
    
        // ✌️ Doble par
        if (pairs.length >= 2) {
            return {
                hand: 'Two Pair',
                cards: sortedCards.filter(c =>
                    c.numericValue === pairs[0] || c.numericValue === pairs[1]
                )
            };
        }
    
        // ➡️ Par
        if (pairs.length) {
            return {
                hand: 'Pair',
                cards: sortedCards.filter(c => c.numericValue === pairs[0])
            };
        }
    
        // 🃏 Carta más alta
        return {
            hand: 'High Card',
            cards: [sortedCards.slice(0, 5)[0]]
        };
    }

    createDeck() {
        const suits = ["H", "C", "D", "S"];
        const values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
        let deck = [];
        suits.forEach(suit => {
            values.forEach(value => {
                deck.push(new Card(suit, value));
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
            member.hand[0].setPosition(`hand.${member.position}.left`);
            member.hand[1].setPosition(`hand.${member.position}.right`);
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
            let nextMember = this.table.members[(dealerIndex + (this.table.members.length == 2 ? 0 : 3)) % this.table.members.length];
            this.turn = nextMember.position;
            this.table.description = `Es el turno de ${nextMember.user.attributes.username}`;
            this.table.update();
        }, 2000);
    }

    nextGameStep() {
        let newRound = true;
        if (this.communityCards.length == 0) {
            this.communityCards = [this.deck.pop(), this.deck.pop(), this.deck.pop()];
            this.communityCards[0].setPosition(`table.1`);
            this.communityCards[1].setPosition(`table.2`);
            this.communityCards[2].setPosition(`table.3`);
        } else if (this.communityCards.length < 5) {
            this.communityCards.push(this.deck.pop());
            this.communityCards[this.communityCards.length - 1].setPosition(`table.${this.communityCards.length}`);
        } else {
            newRound = false;
            this.turn = false;
            this.table.description = `Partida en Juego`;
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
                    let nextMember = this.table.members.filter(member => {
                        return member.dealer
                    })[0];
                    this.turn = nextMember.position;
                    this.table.description = `Es el turno de ${nextMember.user.attributes.username}`;
                    this.table.update();
                    resolve();
                }, 2000);
            })
    }

    nextTurn() {
        const members = this.table.members.sort((a, b) => a.position - b.position);
        const nextMember = members.find(member => member.position > this.turn) || members[0];
        if (nextMember.dealer && members.filter(member => {
            return member.bet < this.currentBet && !member.folded;
        }).length == 0) {
            this.table.description = `Partida en Juego`;
            this.turn = false;
            this.nextGameStep();
            return;
        }
        if (nextMember.folded) {
            this.nextTurn();
        }
        
        this.table.description = `Es el turno de ${nextMember.user.attributes.username}`;
        this.turn = nextMember.position;
        this.table.update();
    }

    async bet(member, bet) {
        this.pot += bet;
        member.bet += bet;
        this.currentBet = this.currentBet > member.bet ? this.currentBet : member.bet;
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
            big: 100,
            small: 50
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
                            hand_value: (m.show_cards || m.user.attributes.id == member.user.attributes.id) && this.game ? this.game.checkHand(m) : undefined
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