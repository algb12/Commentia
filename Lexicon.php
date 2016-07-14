<?php

// Commentia phrase lexica
// Format: $phrases['locale']['category_name']['phrase_name'] = 'localized_phrase';
// Call constructor of Lexicon class with locale as argument, then get phrase with getPhrase($category, $object)
// Author: Alexander Gilburg
// Last updated: 14th of July 2016

class Lexicon
{
    public $locale;
    public $phrases = array();

    public function __construct($locale)
    {
        $this->locale = $locale;
        $this->loadPhrases();
    }

    public function getPhrase($cat, $obj)
    {
        return $this->phrases["$this->locale"]["$cat"]["$obj"];
    }

    private function loadPhrases()
    {
        // ====ENGLISH (US)====
    $this->phrases['en_US'] = array();
        $this->phrases['en_US']['comment_info'] = array();
        $this->phrases['en_US']['comment_controls'] = array();

    // Titles
    $this->phrases['en_US']['titles']['new_comment'] = 'New comment';

    // Comment info
    $this->phrases['en_US']['comment_info']['comment_by'] = 'Comment by:';
        $this->phrases['en_US']['comment_info']['posted_at'] = 'Posted:';

    // Comment controls
    $this->phrases['en_US']['comment_controls']['reply'] = 'reply';
        $this->phrases['en_US']['comment_controls']['edit'] = 'edit';
        $this->phrases['en_US']['comment_controls']['delete'] = 'delete';
        $this->phrases['en_US']['comment_controls']['publish'] = 'publish';

    // Dialogs
    $this->phrases['en_US']['dialogs']['delete'] = 'Are you sure that you want to delete this comment?';

    // Auth form buttons
    $this->phrases['en_US']['auth_form_buttons']['log_in'] = 'Log in';
        $this->phrases['en_US']['auth_form_buttons']['log_out'] = 'Log out';

    // Auth form labels
    $this->phrases['en_US']['auth_form_labels']['username'] = 'Username:';
        $this->phrases['en_US']['auth_form_labels']['password'] = 'Password:';

    // ====GERMAN (GERMANY)====
    $this->phrases['de_DE'] = array();
        $this->phrases['de_DE']['comment_info'] = array();
        $this->phrases['de_DE']['comment_controls'] = array();

    // Titles
    $this->phrases['de_DE']['titles']['new_comment'] = 'Neues Kommentar:';

    // Comment info
    $this->phrases['de_DE']['comment_info']['comment_by'] = 'Kommentar von:';
        $this->phrases['de_DE']['comment_info']['posted_at'] = 'Veröffentlicht:';

    // Comment controls
    $this->phrases['de_DE']['comment_controls']['reply'] = 'antworten';
        $this->phrases['de_DE']['comment_controls']['edit'] = 'bearbeiten';
        $this->phrases['de_DE']['comment_controls']['delete'] = 'entfernen';
        $this->phrases['de_DE']['comment_controls']['publish'] = 'veröffentlichen';

    // Dialogs
    $this->phrases['de_DE']['dialogs']['delete'] = 'Sind sie sicher, dass Sie dieses Kommentar entfernen möchten?';

    // Auth form buttons
    $this->phrases['de_DE']['auth_form_buttons']['log_in'] = 'Einloggen';
        $this->phrases['de_DE']['auth_form_buttons']['log_out'] = 'Ausloggen';

    // Auth form labels
    $this->phrases['de_DE']['auth_form_labels']['username'] = 'Benutzername:';
        $this->phrases['de_DE']['auth_form_labels']['password'] = 'Passwort:';
    }
}
