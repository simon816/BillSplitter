PRAGMA encoding = "UTF-8";

-- All 3 passwords are 'qwerty'

INSERT INTO users (name, email, pass_hash, salt) VALUES ('Test', 'test@example.com',
    '6bc75984a07d8047999e1c1f32a1fa8007758b8d27c9d4b20283b772e89b87fcaae546a6a97ea6ff23893d37b58951d5e1426fa351cd75d87d005a92503b2242',
    '958576c2ea8078716a76de13ea99473cefb275efa23ca214ec77d676ba2833799e5b28bd919c11fe51108aab76d6f062e4e0c250816942e56cb29235d1d7b760'
);

INSERT INTO households (name, owner) VALUES ('Test Household', 1);
UPDATE users SET hh_id = 1 WHERE id = 1;

INSERT INTO users (name, email, pass_hash, salt, hh_id) VALUES ('Simon', 'simonbarnes1@googlemail.com',
    '6bc75984a07d8047999e1c1f32a1fa8007758b8d27c9d4b20283b772e89b87fcaae546a6a97ea6ff23893d37b58951d5e1426fa351cd75d87d005a92503b2242',
    '958576c2ea8078716a76de13ea99473cefb275efa23ca214ec77d676ba2833799e5b28bd919c11fe51108aab76d6f062e4e0c250816942e56cb29235d1d7b760',
    1
);

INSERT INTO users (name, email, pass_hash, salt, hh_id) VALUES ('Qwerty', 'qwerty@example.com',
    '6bc75984a07d8047999e1c1f32a1fa8007758b8d27c9d4b20283b772e89b87fcaae546a6a97ea6ff23893d37b58951d5e1426fa351cd75d87d005a92503b2242',
    '958576c2ea8078716a76de13ea99473cefb275efa23ca214ec77d676ba2833799e5b28bd919c11fe51108aab76d6f062e4e0c250816942e56cb29235d1d7b760',
    1
);

INSERT INTO bills (hh_id, total_payable, description, payable_to, collector) VALUES (1, 900, 'Some bill', 'Some company', 2);

INSERT INTO payments (user_id, bill_id, qty_owed) VALUES (1, 1, 300);
INSERT INTO payments (user_id, bill_id, qty_owed) VALUES (2, 1, 300);
INSERT INTO payments (user_id, bill_id, qty_owed) VALUES (3, 1, 300);
