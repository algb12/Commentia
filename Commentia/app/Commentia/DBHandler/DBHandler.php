<?php

namespace Commentia\DBHandler;

use SQLite3;

class DBHandler extends SQLite3
{
    public function __construct($dbfile)
    {
        if (!is_writable(dirname($dbfile))) {
            exit('Error: Directory not writable.');
        }

        if (!isset($dbfile)) {
            exit('Error: DB file not set. Set it in the config.php.');
        }

        $this->open($dbfile);

        $this->exec('CREATE TABLE IF NOT EXISTS comments(
             ucid INT PRIMARY KEY,
             content TEXT,
             timestamp DATETIME,
             creator_username TEXT,
             is_deleted BOOLEAN,
             children TEXT,
             pageid INT)'
        );

        $this->exec('CREATE TABLE IF NOT EXISTS members(
             username TEXT PRIMARY KEY,
             password_hash TEXT,
             email TEXT,
             avatar_file TEXT,
             is_banned BOOLEAN,
             role TEXT,
             member_since DATETIME)'
        );
    }
}
