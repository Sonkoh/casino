const { v4: uuidv4 } = require('uuid');


class Table {
    constructor(name) {
        this.id = uuidv4();
        this.name = name;
        this.members = [];
    }
    toJSON() {
        return {
            id: this.id,
            name: this.name,
            members: this.members
        };
    }
}

tables = [
    new Table("Sonkoh Mesa")
];

module.exports.tables = tables;