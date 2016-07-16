# Commentia – Documentation

Commentia is a plug-in which adds a comment functionality to any webpage, and does not require any database. It is flat file, uses JSON files to store data, and only requires PHP, GD, the JSON module, and Composer for PHP (both, GD and php-json usually come preinstalled by default, Composer is easy to install).

One may wonder, what is the point of a comment plugin, if CMSes with commenting functionality already exist? Some people prefer to have a static website without any CMS, and still want commenting functionality. This plugin caters for these kinds of people.

The following is a short documentation for Commentia, serving as a quick getting started guide, and providing some background information on its technical workings:

## Dependencies

### Composer

The only real dependency (besides php-gd and php-json, but they're probably installed already anyways) is the Composer dependency manager – it will manage all needed dependencies all by itself.

The user issues one command (`composer install`), and composer will automatically download all dependencies and dump the PSR-4 autoloader.

If need be, please read more on how to [install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx 'Installing composer').

## Installation

### Unpacking onto website

Unpack commentia into a separate folder (such as `/var/www/example.org/public_html/commentia`) or into the web-root, although a separate directory is recommended for security purposes.

### Running Composer's install

In a command line, switch to the Commentia directory with the `composer.json` file. Then, run either `composer self-update` followed by `composer install`, or replace the command `composer` with `composer.phar`, depending on whether the `.phar` version of Composer is used or not.

This should install all the needed dependencies. A `/vendor` directory and `composer.lock` file will be created. Do not delete them, they are very important.

### Modification of website code

#### Initialization

Once Composer has run, modify the website template or website to have the following code at the top of the page:

```php
<?php
  // Composer's autoload
  require 'commentia-dir/vendor/autoload.php';

  use Commentia\Controllers\CommentiaController;

  // Include and initiate Commentia with unique page-id
  $pageid = x;
  $commentia = new CommentiaController($pageid);
?>
```

Where `x` is the right side of the assignment for the `$pageid`, which can be any function/shortcode returning an ID for the page, such as:

- Wordpress's `get_the_ID()`
- Drupal's `$node->nid`
- MODX's `[[*id]]` template tag.

And `commentia-dir` is the path to the directory Commentia is in.

Throughout other parts of this README, the term `commentia-dir` will be referred to exactly as that.

The above just describes some of the popular CMS's ways of getting the page ID. For static blogs, `x` can also be a manually entered page ID. Make sure to replace `x` with the relevant way of getting the page ID.

It is irrelevant whether `$pageid` is a number or an alphanumeric string, as Commentia is just checking under the given page ID for comments to display.

The only thing that _does_ matter is that the page ID should be unique.

#### Modification of relevant tags

_NOTE: Page ID may soon not need to be defined in html-tag. Revise README.md when due time._

Then, modify the HTML tag to read:

```html
<html data-pageid="<?=$pageid;?>">
```

The `<?=$pageid;?>` is the PHP echo shortcut. The data-pageid attribute is used by commentia.js for the AJAX requests to the API.

Almost done! Now, just include 2 files in the head of the website:

```html
<head>
  <script src="/commentia-dir/assets/commentia.js"></script>
  <link href="/commentia-dir/assets/commentia-default-theme.css" rel="stylesheet">
  ...
</head>
```

Now, there should be a working instance of Commentia on any page, if the previous changes were done in a common template.

#### Displaying the comments section and login form

To display the comments section and login form, use the following code:

```html
<?=$commentia->displayComments();?>
<?=$commentia->displayAuthForm();?>
```

## Technical details

Internally, each comment has a UCID, a **U**nique **C**omment **ID**. These are referred to as `$ucid` in the code. Each new comment would increase the `$last_ucid` by 1, and the UCID of a new comment would merely be the `$last_ucid + 1`.

The reply path is used to determine under which parent a reply would go under. It is just all the UCIDs down the thread chain, e.g. if comment 2 is a reply of comment 1, and comment 1 is a reply of comment 0, the reply path of comment 2 would be 0-1-2. It is referred to by the variable `$reply_path`.

## Translatability

Both, translating the commenting front-end with an existing language and creating a new language is very easy. This is a list for language files currently included in the project:

  * American English/American English): `en-US.php`
  * German (Germany)/Deutsch (Deutschland): `de-DE.php`
  * French (France)/Français (France): `fr-FR.php`

### <a name='switch-language'>Switching the front-end language</a>

In order to switch the front-end language, all that has to be done is a modification of one line in the `/commentia-dir/app/data/config.php` file. The lexicon locale, `LEX_LOCALE`, is always the name of the language file _without_ the `.php` extension.

The name of the language file always adheres to the language tag according to the [RFC 5646](https://tools.ietf.org/html/rfc5646 'RFC 5646 standard') standard, with the extension `.php` appended to it.

E.g., for American English, the line would read

```php
define('LEX_LOCALE', 'en-US');
```

And for German it would be

```php
define('LEX_LOCALE', 'de-DE');
```

A full list of languages and their locales/culture codes can be found [here](http://download1.parallels.com/SiteBuilder/Windows/docs/3.2/en_US/sitebulder-3.2-win-sdk-localization-pack-creation-guide/30801.htm 'List of Culture Codes').

### Creating a new language

In order to create a new language, the `en-US.php` file has to be copied and renamed to the correct language for the target language, plus the `.php` extension in the same locale directory.

For example, if the target language is Chinese (China), the language code would be `zh-CN`, so the new file would be called `zh-CN.php` accordingly.

Open up the new language file and the `en-US.php` file side-by-side, and start translating. The format of each phrase is as follows: `define('PHRASE_IDENTIFIER', 'Localized phrase')`, where `PHRASE_IDENTIFIER` is an uppercase, underscore-separated string, clearly describing the meaning of a phrase, such as `COMMENT_INFO_COMMENT_BY`. Generally, the phrase identifier follows the pattern `CATEGORY_OBJECT`, e.g. `COMMENT_INFO` is the category and `COMMENT_BY` is the object. It is also why the phrases are grouped the way they are in the lexicon files. The localized phrase is just the translation.

To use the newly created language file, read the previous section on [switching the front-end language](#switch-language).
