PRAGMA encoding = "UTF-8";

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
    name varchar(30) NOT NULL,
    email varchar(100) NOT NULL UNIQUE,
    pass_hash char(128) NOT NULL,
    salt char(128) NOT NULL,
    reg_date timestamp DEFAULT CURRENT_TIMESTAMP
);
