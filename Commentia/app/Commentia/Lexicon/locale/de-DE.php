<?php

// German lexicon (Germany)/Deutsches Lexicon (Deutschland)
// Format: define('PHRASE_NAME', 'localized phrase');
// Don't forget to escape the single quotes (') using a backslash, such as 'J\'ai' instead of 'J'ai'
// Author: algb12.19@gmail.com

define('LANG_NAME', 'German (Germany)');
define('LANG_NAME_LOCALIZED', 'Deutsch (Deutschland)');

// Different options accepted by date(): http://php.net/manual/en/function.date.php
define('DATETIME_LOCALIZED', 'd.m.Y H:i:s');

define('TITLES_NEW_COMMENT', 'Neues Kommentar:');
define('TITLES_AUTH_FORM', 'Authentifizierung');
define('TITLES_SIGN_UP_FORM', 'Registrierung');

define('COMMENT_INFO_COMMENT_BY', 'Kommentar von:');
define('COMMENT_INFO_POSTED_AT', 'Veröffentlicht:');

define('COMMENT_CONTROLS_REPLY', 'antworten');
define('COMMENT_CONTROLS_EDIT', 'bearbeiten');
define('COMMENT_CONTROLS_DELETE', 'entfernen');
define('COMMENT_CONTROLS_PUBLISH', 'veröffentlichen');

define('DIALOGS_DELETE_COMMENT', 'Sind sie sicher, dass Sie dieses Kommentar entfernen möchten?');

define('AUTH_FORM_BUTTONS_LOG_IN', 'Einloggen');
define('AUTH_FORM_BUTTONS_LOG_OUT', 'Ausloggen');

define('AUTH_FORM_LABELS_USERNAME', 'Benutzername:');
define('AUTH_FORM_LABELS_PASSWORD', 'Passwort:');

define('SIGN_UP_FORM_BUTTONS_SIGN_UP', 'Registrieren');

define('SIGN_UP_FORM_LABELS_USERNAME', 'Benutzername:');
define('SIGN_UP_FORM_LABELS_PASSWORD', 'Passwort:');
define('SIGN_UP_FORM_LABELS_RETYPE_PASSWORD', 'Passwort wiederholen:');
define('SIGN_UP_FORM_LABELS_EMAIL', 'E-Mail:');
define('SIGN_UP_FORM_LABELS_AVATAR', 'Benutzerbild:');

define('ERROR_LOG_IN_WRONG_CREDENTIALS', 'Fehler: Ungültige Anmeldedaten .');

define('NOTICE_LOG_IN_SUCCESS', 'Hinweis: Benutzer erfolgreich angemeldet.');

define('ERROR_SIGN_UP_MISSING_USERNAME', 'Fehler: Bitte geben Sie einen Benutzernamen ein.');
define('ERROR_SIGN_UP_MISSING_PASSWORD', 'Fehler: Bitte geben Sie ein Passwort ein.');
define('ERROR_SIGN_UP_PASSWORD_MISMATCH', 'Fehler: Die Passwörter stimmen nicht überein. Bitte geben Sie Ihr Passwort erneut ein.');
define('ERROR_SIGN_UP_PASSWORD_INSECURE', 'Fehler: Das Passwort ist muss mindestens ' . COMMENTIA_MIN_PASSWORD_LEN . ' zeichen lang sein. Bitte wählen Sie ein längeres Passwort.');
define('ERROR_SIGN_UP_INVALID_EMAIL', 'Fehler: Bitte geben Sie eine gültige E-Mail Addresse ein.');
define('ERROR_SIGN_UP_USERNAME_TAKEN', 'Fehler: Benutzername bereits vergeben. Bitte wählen Sie einen anderen Benutzernamen.');
define('ERROR_SIGN_UP_AVATAR_UPLOAD', 'Fehler: Irgendetwas ist beim Hochladen des Benutzerbildes schief gelaufen.');

define('NOTICE_SIGN_UP_SUCCESS', 'Hinweis: Sie wurder erfolgreich registriert!');
