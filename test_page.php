<!DOCTYPE html>

<?php

  // Disable these 4 lines if you don't want error-reporting
  error_reporting(E_STRICT);
  ini_set('error_reporting', -1);
  ini_set('display_errors', 1);
  ini_set('html_errors', 1);

  require 'vendor/autoload.php';

  use Commentia\Controllers\CommentiaController;

  // Include and initiate Commentia with unique page-id
  $pageid = 0;
  $commentia = new CommentiaController($pageid);
?>

<html lang="en" data-pageid="<?=$pageid;?>">
<head>
  <meta charset="UTF-8">
  <title>Commentia - A lightweight, no DB comment system</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Commentia AJAX-script + CSS for comments section -->
  <script src="assets/commentia.js"></script>
  <link href="assets/commentia-default-theme.css" rel="stylesheet">
</head>

<body>
  <h3>Comments:</h3>
  <hr>
  <div>
    <?=$commentia->displayComments(false);?>
    <?=$commentia->displayAuthForm();?>
  </div>
  <!-- Debug (will be removed) -->
  <button type="button" name="refreshComments" onclick="refreshComments()">Refresh comments</button>
</body>
</html>
