PRAGMA encoding = "UTF-8";

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    name varchar NOT NULL,
    email varchar NOT NULL UNIQUE,
    pass_hash varchar NOT NULL,
    salt varchar NOT NULL
);

DROP TABLE IF EXISTS bills;
CREATE TABLE bills (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT NOT NULL,
    total_payable float NOT NULL,
    description text NOT NULL,
    payable_to text NOT NULL,
    paid boolean NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
    user_id integer NOT NULL,
    bill_id integer NOT NULL,
    qty_paid float NOT NULL,
    proportion float NOT NULL DEFAULT 1,
    payment_received boolean NOT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (bill_id) REFERENCES bills(id)
);

DROP TABLE IF EXISTS households;
CREATE TABLE households (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    name varchar NOT NULL
);

DROP TABLE IF EXISTS household_bill;
CREATE TABLE household_bill (
    hh_id integer NOT NULL,
    bill_id integer NOT NULL,

    FOREIGN KEY (hh_id) REFERENCES households(id),
    FOREIGN KEY (bill_id) REFERENCES bills(id)
);

DROP TABLE IF EXISTS household_member;
CREATE TABLE household_member (
    user_id integer NOT NULL,
    hh_id integer NOT NULL,
    default_proportion float NOT NULL DEFAULT 1,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hh_id) REFERENCES households(id)
);
