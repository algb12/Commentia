<?php

////////////////////////////////////////////////////////////////////
// Commentia config                                               //
// These are the settings to be adjusted by the webmaster.        //
// Member role settings can be found in the MembersRoles.php file //
// Author: Alexander Gilburg                                      //
////////////////////////////////////////////////////////////////////



// Locale for language to be used by lexicon
// Available languages are in the /app/Commentia/Lexicon/locale directory
define('LEX_LOCALE', 'en-US');

// Relative path to JSON file containing comments
define('JSON_FILE_COMMENTS', __DIR__.'/db/comments.example.json');

// Relative path to JSON file containing members
define('JSON_FILE_MEMBERS', __DIR__.'/db/members.example.json');

// Relative path to avatar directory (with trailing slash)
define('AVATAR_DIR', 'app/data/avatars/');
