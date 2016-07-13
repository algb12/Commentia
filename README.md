# Definition for MVC structure of Commentia#

Commentia is a plug-in which adds a comment functionality to any webpage, and does not require any database. It is flat file, uses JSON files to store data, and only requires PHP, GD and the JSON module for PHP (both, GD and php-json usually come preinstalled by default).

You may wonder, what is the point of a comment plugin, if CMSes with commenting functionality already exist? Some people prefer to have a static website without any CMS, and still want commenting functionality. This plugin caters for these kinds of people.

The following content describes the planned MVC structure of Commentia, to ensure extendability and adaptability for future changes and implementations of the plugin, and the easy and hassle-free integration into websites:

## Installation

_NOTE:_ To be updated for the autoloader

### Unpacking onto website

Simply unpack Commentia onto the root-directory, or into a separate folder (such as `/var/www/example.org/public_html/commentia`).

### Modification of website code

#### Initialization

Then, modify your template to have the following code at the top of the page:

```php
<?php
  // Include and initiate Commentia with unique page-id
  require_once('/path/to/commentia.controller.php');
  $pageid = $x;
  $commentia = new commentiaController($pageid);
?>
```

Where `$x` is the right side of the assignment for the `$pageid`, which can be any function/shortcode returning an ID for the page, such as:

- Wordpress's `get_the_ID()`
- Drupal's `$node->nid`
- MODX's `[[*id]]` template tag.

The above just describes some of the popular CMS's ways of getting the page ID. For static blogs, `$x` can also be a manually entered page ID. Make sure to replace `$x` with the relevant way of getting the page ID.

It is irrelevant whether `$pageid` is a number or an alphanumeric string, as Commentia is just checking under the given page ID for comments to display.

The only thing that _does_ matter is that the page ID should be unique.

#### Modification of relevant tags

Then, modify the HTML tag to read:

```html
<html data-pageid="<?=$pageid;?>">
```

The `<?=$pageid;?>` is the PHP echo shortcut. The data-pageid attribute is used by commentia.js for the AJAX requests to the API.

Almost done! Now, just include 2 files in the head of the website:

```html
<head>
  <script src="/path/to/commentia.js"></script>
  <link href="/path/to/commentia-default-theme.css" rel="stylesheet">
  ...
</head>
```

Now, there should be a working instance of Commentia on any page, if the previous changes were done in a common template.

#### Displaying the comments section and login form

To display the comments section and login form, use the following code:

```php
<?=$commentia->displayComments();?>
<?=$commentia->displayAuthForm();?>
```

## Technical details

Internally, each comment has a UCID, a **U**nique **C**omment **ID**. These are referred to as `$ucid` in the code. Each new comment would increase the `$last_ucid` by 1, and the UCID of a new comment would merely be the `$last_ucid + 1`.

The reply path is used to determine under which parent a reply would go under. It is just all the UCIDs down the thread chain, e.g. if comment 2 is a reply of comment 1, and comment 1 is a reply of comment 0, the reply path of comment 2 would be 0-1-2. It is referred to by the variable `$reply_path`.
