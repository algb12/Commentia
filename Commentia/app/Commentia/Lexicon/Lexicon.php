<?php

///////////////////////////////
// Commentia phrase lexica   //
// Author: algb12.19@gmail.com //
///////////////////////////////


namespace Commentia\Lexicon;

class Lexicon
{
    /**
     * Loads up a language file into the code.
     *
     * @param string $locale Language file name in the locale folder
     *
     * @return [type] [description]
     */
    public static function load($locale)
    {
        if (!require __DIR__.'/locale/'.$locale.'.php') {
            $this->load('en_US');
        }
    }

    /**
     * Returns a specified localised phrase using phrase identifier.
     *
     * @param string $phrase The phrase identifier, which is the same for every language
     *
     * @return string The localised phrase
     */
    public static function getPhrase($phrase)
    {
        return constant($phrase) ? constant($phrase) : 'Error: Undefined phrase in lexicon '."'$phrase'";
    }
}
