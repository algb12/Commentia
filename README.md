# Commentia – Documentation

Commentia is a plugin which adds a comment functionality to any webpage, and does not require any database server, but uses a flat file, SQLite3 DB to store data, and only requires PHP, GD, the SQLite3 module installed/enabled on the server, and Composer for PHP dependency bootstrapping and autoloader generation (both, GD and SQLite3 module usually come preinstalled by default, Composer is easy to install).

Even though CMSs with commenting functionality already exist, some people prefer to have a static website without any CMS, and still want commenting functionality. This plugin caters for these kinds of people. Also, this plugin is fully self-contained, that is, comments and users don't get stored in the website database, if it runs on one.

The following is a short documentation for Commentia, serving as a quick getting started guide, and providing some background information on its technical workings:

## Dependencies

### Composer

The only real dependency (besides php-gd and php-sqlite3, but they're probably installed already anyways) is the Composer dependency manager – it will manage all needed dependencies all by itself.

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
    // Path to Commentia with trailing slash (root-relative)
    $commentia_dir = '/path/to/Commentia/';

    // Page ID
    $pageid = x;

    // Composer's autoload
    require $commentia_dir.'vendor/autoload.php';

    use Commentia\Controllers\CommentiaController;

    // Include and initiate Commentia with unique page ID and path to Commentia
    $commentia = new CommentiaController($pageid, $commentia_dir);
?>
```

Where `x` is the right side of the assignment for the `$pageid`, which can be any function/shortcode returning an ID for the page, such as:

- Wordpress's `get_the_ID()`
- Drupal's `$node->nid`
- MODX's `[[*id]]` template tag.

And `$commentia_dir` is the path to the directory Commentia is in.

Throughout other parts of this README, the term `commentia_dir` will be referred to exactly as that. It should be a root-relative and not absolute path, as the same path will be used for loading of resources such as comment area CSS and avatar images.

The above just describes some of the popular CMSs' ways of getting the page ID. For static blogs, `x` can also be any manually entered page ID. Make sure to replace `x` with the relevant way of getting the page ID.

It is irrelevant whether `$pageid` is a number or an alphanumeric string, as Commentia is just checking under the given page ID for comments to display.

The only thing that _does_ matter is that the page ID should be unique.

#### Modification of relevant tags

Now, just include 2 files in the head of the website, using the syntax which will locate the files in the `$commentia_dir` automatically. If, as supposed to, a root relative path is used, this code will automatically load all needed resources for Commentia. For this, modify the `<head>` tag as follows:

```html
    <script>
        window.commentia = window.commentia || {};
        window.commentia.APIURL = '<?=$commentia_dir?>api.php';
    </script>
    <!-- Commentia AJAX script + CSS for comments section -->
    <script src="<?=$commentia_dir?>assets/commentia.js"></script>
    <link href="<?=$commentia_dir?>assets/commentia-default-theme.css" rel="stylesheet">
```

Now, there should be a working instance of Commentia. Mind that for a folder-based structure, pages nested in folders may need `$commentia_dir` to be adjusted accordingly.

#### Displaying the comments section and login form

To display the comments section and login/signup form, use the following code:

```html
    <?=$commentia->displayComments();?>
    <?=$commentia->displayAuthForm();?>
```

## Technical details

Internally, each comment has a UCID, a **U**nique **C**omment **ID**. These are referred to as `$ucid` in the code. Each new comment would get a UCID of the last UCID + 1.

Manual changes can be made to the SQLite3 database by using an SQLite editor. A recommended editor is the [DB Browser for SQLite](http://sqlitebrowser.org).

## Translatability

**Note: If anyone has made good translations, it would be appreciated if they were shared with me. Just send me the file to my professional email: algb12.19@gmail.com. Thank you!**

Both, translating the commenting front-end with an existing language and creating a new language is very easy. This is a list for language files currently included in the project:

  * American English/American English): `en-US.php`
  * German (Germany)/Deutsch (Deutschland): `de-DE.php`
  * French (France)/Français (France): `fr-FR.php`

###<a name='switch-language'>Switching the front-end language</a>

In order to switch the front-end language, all that has to be done is a modification of one line in the `/commentia_dir/app/data/config.php` file. The lexicon locale, `COMMENTIA_LEX_LOCALE`, is always the name of the language file _without_ the `.php` extension.

The name of the language file always adheres to the language tag according to the [RFC 5646](https://tools.ietf.org/html/rfc5646 'RFC 5646 standard') standard, with the extension `.php` appended to it.

E.g., for American English, the line would read

```php
define('COMMENTIA_LEX_LOCALE', 'en-US');
```

And for German it would be

```php
define('COMMENTIA_LEX_LOCALE', 'de-DE');
```

A full list of languages and their locales/culture codes can be found [here](http://download1.parallels.com/SiteBuilder/Windows/docs/3.2/en_US/sitebulder-3.2-win-sdk-localization-pack-creation-guide/30801.htm 'List of Culture Codes').

### Creating a new language

In order to create a new language, the `en-US.php` file has to be copied and renamed to the correct language for the target language, plus the `.php` extension in the same locale directory.

For example, if the target language is Chinese (China), the language code would be `zh-CN`, so the new file would be called `zh-CN.php` accordingly.

Open up the new language file and the `en-US.php` file side-by-side, and start translating. The format of each phrase is as follows: `define('PHRASE_IDENTIFIER', 'Localized phrase')`, where `PHRASE_IDENTIFIER` is an uppercase, underscore-separated string, clearly describing the meaning of a phrase, such as `COMMENT_INFO_COMMENT_BY`. Generally, the phrase identifier follows the pattern `CATEGORY_OBJECT`, e.g. `COMMENT_INFO` is the category and `COMMENT_BY` is the object. It is also why the phrases are grouped the way they are in the lexicon files. The localized phrase is just the translation.

To use the newly created language file, read the previous section on [switching the front-end language](#switch-language).

## Readiness for production use

Even though the system was tested thoroughly to not contain any bugs (please do open up issues if any are found), it did not undergo formal tests (e.g. PHPunit).

Unit tests are planned for future releases of Commentia.
