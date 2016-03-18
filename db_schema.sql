PRAGMA encoding = "UTF-8";

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    name varchar NOT NULL,
    email varchar NOT NULL UNIQUE,
    pass_hash varchar NOT NULL,
    salt varchar NOT NULL,
    hh_id integer NULL,

    FOREIGN KEY (hh_id) REFERENCES households(id)
);

DROP TABLE IF EXISTS bills;
CREATE TABLE bills (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    hh_id integer NOT NULL,
    total_payable float NOT NULL,
    description text NOT NULL,
    payable_to text NOT NULL,
    collector integer NOT NULL,
    paid_date timestamp NULL DEFAULT NULL,

    FOREIGN KEY (hh_id) REFERENCES households(id),
    FOREIGN KEY (collector) REFERENCES users(id)
);

DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    user_id integer NOT NULL,
    bill_id integer NOT NULL,
    qty_paid float NOT NULL DEFAULT 0,
    qty_owed float NOT NULL,
    paid_date timestamp NULL DEFAULT NULL,
    status integer NOT NULL DEFAULT 0,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (bill_id) REFERENCES bills(id)
);

DROP TABLE IF EXISTS households;
CREATE TABLE households (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    name varchar NOT NULL,
    owner integer NOT NULL,

    FOREIGN KEY (owner) REFERENCES users(id)
);

DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    receiver_id integer NOT NULL,
    message text NOT NULL,
    type_id integer NOT NULL, -- Tells PHP what this message is about

    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
