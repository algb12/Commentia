<!DOCTYPE html>

<?php
error_reporting(-1);
  // Include and initiate Commentia with unique page-id
  include 'CommentiaController.php';
  $pageid = 0;
  $commentia = new CommentiaController($pageid);
?>

<html lang="en" data-pageid="<?=$pageid;?>">
<head>
  <meta charset="UTF-8">
  <title>Commentia - A lightweight, no DB comment system</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Commentia AJAX-script + CSS for comments section -->
  <script src="commentia.js"></script>
  <link href="commentia-default-theme.css" rel="stylesheet">
</head>

<body>
  <h3>Comments:</h3>
  <hr>
  <div>
    <?=$commentia->displayComments();?>
    <?=$commentia->displayAuthForm();?>
  </div>
  <!-- Debug (will be removed) -->
  <button type="button" name="refreshComments" onclick="refreshComments()">Refresh comments</button>
</body>
</html>
