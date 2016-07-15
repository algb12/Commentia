<?php

# Commentia phrase lexica
# Format: $phrases['locale']['category_name']['phrase_name'] = 'localized_phrase';
# Call constructor of Lexicon class with locale as argument, then get phrase with getPhrase($category, $object)
# Author: Alexander Gilburg
# Last updated: 15th of July 2016

namespace Commentia\Lexicon;

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
        $this->phrases['en_US'] = array();
        $this->phrases['en_US']['comment_info'] = array();
        $this->phrases['en_US']['comment_controls'] = array();

        $this->phrases['en_US']['titles']['new_comment'] = 'New comment';

        $this->phrases['en_US']['comment_info']['comment_by'] = 'Comment by:';
        $this->phrases['en_US']['comment_info']['posted_at'] = 'Posted:';

        $this->phrases['en_US']['comment_controls']['reply'] = 'reply';
        $this->phrases['en_US']['comment_controls']['edit'] = 'edit';
        $this->phrases['en_US']['comment_controls']['delete'] = 'delete';
        $this->phrases['en_US']['comment_controls']['publish'] = 'publish';

        $this->phrases['en_US']['dialogs']['delete'] = 'Are you sure that you want to delete this comment?';

        $this->phrases['en_US']['auth_form_buttons']['log_in'] = 'Log in';
        $this->phrases['en_US']['auth_form_buttons']['log_out'] = 'Log out';

        $this->phrases['en_US']['auth_form_labels']['username'] = 'Username:';
        $this->phrases['en_US']['auth_form_labels']['password'] = 'Password:';

        $this->phrases['de_DE'] = array();
        $this->phrases['de_DE']['comment_info'] = array();
        $this->phrases['de_DE']['comment_controls'] = array();

        $this->phrases['de_DE']['titles']['new_comment'] = 'Neues Kommentar:';

        $this->phrases['de_DE']['comment_info']['comment_by'] = 'Kommentar von:';
        $this->phrases['de_DE']['comment_info']['posted_at'] = 'Veröffentlicht:';

        $this->phrases['de_DE']['comment_controls']['reply'] = 'antworten';
        $this->phrases['de_DE']['comment_controls']['edit'] = 'bearbeiten';
        $this->phrases['de_DE']['comment_controls']['delete'] = 'entfernen';
        $this->phrases['de_DE']['comment_controls']['publish'] = 'veröffentlichen';

        $this->phrases['de_DE']['dialogs']['delete'] = 'Sind sie sicher, dass Sie dieses Kommentar entfernen möchten?';

        $this->phrases['de_DE']['auth_form_buttons']['log_in'] = 'Einloggen';
        $this->phrases['de_DE']['auth_form_buttons']['log_out'] = 'Ausloggen';

        $this->phrases['de_DE']['auth_form_labels']['username'] = 'Benutzername:';
        $this->phrases['de_DE']['auth_form_labels']['password'] = 'Passwort:';
    }
}
