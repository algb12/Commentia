<?php

// English (USA) lexicon
// Format: define('PHRASE_NAME', 'localized phrase');
// Don't forget to escape the single quotes (') using a backslash, such as 'J\'ai' instead of 'J'ai'
// Author: algb12.19@gmail.com

define('LANG_NAME', 'English (USA)');
define('LANG_NAME_LOCALIZED', 'English (USA)');

// Different options accepted by date(): http://php.net/manual/en/function.date.php
define('DATETIME_LOCALIZED', 'm/d/Y h:i:s A');

define('TITLES_NEW_COMMENT', 'New comment');
define('TITLES_AUTH_FORM', 'Authentication');
define('TITLES_SIGN_UP_FORM', 'Sign up');

define('COMMENT_INFO_COMMENT_BY', 'Comment by:');
define('COMMENT_INFO_POSTED_AT', 'Posted:');

define('COMMENT_CONTROLS_REPLY', 'reply');
define('COMMENT_CONTROLS_EDIT', 'edit');
define('COMMENT_CONTROLS_DELETE', 'delete');
define('COMMENT_CONTROLS_PUBLISH', 'publish');

define('DIALOGS_DELETE_COMMENT', 'Are you sure that you want to delete this comment?');

define('AUTH_FORM_BUTTONS_LOG_IN', 'Log in');
define('AUTH_FORM_BUTTONS_LOG_OUT', 'Log out');

define('AUTH_FORM_LABELS_USERNAME', 'Username:');
define('AUTH_FORM_LABELS_PASSWORD', 'Password:');

define('SIGN_UP_FORM_BUTTONS_SIGN_UP', 'Sign up');

define('SIGN_UP_FORM_LABELS_USERNAME', 'Username:');
define('SIGN_UP_FORM_LABELS_PASSWORD', 'Password:');
define('SIGN_UP_FORM_LABELS_RETYPE_PASSWORD', 'Retype password:');
define('SIGN_UP_FORM_LABELS_EMAIL', 'Email:');
define('SIGN_UP_FORM_LABELS_AVATAR', 'Avatar:');

define('ERROR_LOG_IN_WRONG_CREDENTIALS', 'Error: Invalid log in credentials.');

define('NOTICE_LOG_IN_SUCCESS', 'Notice: User logged in successfully.');

define('ERROR_SIGN_UP_MISSING_USERNAME', 'Error: Please enter a username.');
define('ERROR_SIGN_UP_MISSING_PASSWORD', 'Error: Please enter a password.');
define('ERROR_SIGN_UP_PASSWORD_MISMATCH', 'Error: Passwords do not match. Please re-enter your password.');
define('ERROR_SIGN_UP_PASSWORD_INSECURE', 'Error: Password must be at least ' . COMMENTIA_MIN_PASSWORD_LEN . ' characters long. Please choose a longer password.');
define('ERROR_SIGN_UP_INVALID_EMAIL', 'Error: Please enter a valid email.');
define('ERROR_SIGN_UP_USERNAME_TAKEN', 'Error: Username already taken. Please enter a different username.');
define('ERROR_SIGN_UP_AVATAR_UPLOAD', 'Error: Something went wrong with the avatar image-upload.');

define('NOTICE_SIGN_UP_SUCCESS', 'Notice: You\'ve been successfully signed up!');
