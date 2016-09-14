<?php

////////////////////////////////////////////////////////////////////
// Commentia config                                               //
// These are the settings to be adjusted by the webmaster.        //
// Member role settings can be found in the MembersRoles.php file //
// Author: Alexander Gilburg                                      //
////////////////////////////////////////////////////////////////////



// Locale for language to be used by lexicon (default: en-US)
// Available languages are in the /app/Commentia/Lexicon/locale directory
define('LEX_LOCALE', 'en-US');

// Timezone used by PHP (default: UTC; display time only, all times stored in UTC)
define('TIMEZONE', 'Europe/Berlin');

// Path to JSON file containing comments
define('JSON_FILE_COMMENTS', __DIR__.'/db/comments.example.json');

// Path to JSON file containing members
define('JSON_FILE_MEMBERS', __DIR__.'/db/members.example.json');

// Path to JSON file containing metadata for comments, members and Commentia itself
define('JSON_FILE_METADATA', __DIR__.'/db/metadata.example.json');

// Path to SQLite DB containing comments
define('DB', __DIR__.'/db/commentia.db');

// Relative path to avatar directory from this file (with trailing slash)
define('AVATAR_DIR', 'app/data/avatars/');

// Minimum password length for sign up
define('MIN_PASSWORD_LEN', 8);