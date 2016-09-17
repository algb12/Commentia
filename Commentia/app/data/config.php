<?php

////////////////////////////////////////////////////////////////////
// Commentia config                                               //
// These are the settings to be adjusted by the webmaster.        //
// Member role settings can be found in the MembersRoles.php file //
// Author: algb12.19@gmail.com                                      //
////////////////////////////////////////////////////////////////////



// Locale for language to be used by lexicon (default: en-US)
// Available languages are in the /app/Commentia/Lexicon/locale directory
define('COMMENTIA_LEX_LOCALE', 'en-US');

// Timezone used by PHP (default: UTC; display time only, all times stored in UTC)
define('COMMENTIA_TIMEZONE', 'Europe/Berlin');

// Path to SQLite DB containing comments
define('COMMENTIA_DB', __DIR__.'/db/commentia.db');

// Whether to enable guest mode or not
define('COMMENTIA_ENABLE_GUEST', TRUE);

// Root-relative path to default avatar file
define('COMMENTIA_AVATAR_DEFAULT', 'app/data/avatars/placeholder/avatar_placeholder.jpg');

// Relative path to avatar directory from this file (with trailing slash)
define('COMMENTIA_AVATAR_DIR', 'app/data/avatars/');

// Minimum password length for sign up
define('COMMENTIA_MIN_PASSWORD_LEN', 8);
