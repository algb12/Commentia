<?php

# Commentia phrase lexica
# Format: $phrases['locale']['category_name']['phrase_name'] = 'localized_phrase';
# Don't forget to escape the single quotes (') using a backslash, such as 'J\'ai' instead of 'J'ai'
# Call constructor of Lexicon class with locale as argument, then get phrase with getPhrase($category, $object)
# Author: Alexander Gilburg
# Last updated: 15th of July 2016

namespace Commentia\Lexicon;

class Lexicon
{
    public static function load($locale)
    {
        require __DIR__.'/locale/'.$locale.'.php';
    }
}
