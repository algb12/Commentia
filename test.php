<!DOCTYPE html>

<?php
  // Disable these 4 lines if you don't want error-reporting
  error_reporting(E_ALL);
  ini_set('error_reporting', -1);
  ini_set('display_errors', 1);
  ini_set('html_errors', 1);

  // Path to Commentia with trailing slash
  $commentia_dir = 'Commentia/';

  // Page ID
  $pageid = 0;

  // Composer's autoload
  require $commentia_dir.'vendor/autoload.php';

  use Commentia\Controllers\CommentiaController;

  // Include and initiate Commentia with unique page ID and path to Commentia
  $commentia = new CommentiaController($pageid, $commentia_dir);
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Commentia - A lightweight, no DB comment system</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Let user set custom API endpoint path -->
  <script>
    window.commentia = window.commentia || {};
    window.commentia.APIURL = '<?=$commentia_dir?>api.php';
  </script>
  <!-- Only needed for demo -->
  <link href="<?=$commentia_dir?>assets/style.example.css" rel="stylesheet">
  <!-- Commentia AJAX script + CSS for comments section -->
  <script src="<?=$commentia_dir?>assets/commentia.js"></script>
  <link href="<?=$commentia_dir?>assets/commentia-default-theme.css" rel="stylesheet">
</head>

<body>
  <h3>Comments:</h3>
  <hr>
  <div class="commentia__comments-container" id="commentia__comments-container">
    <?=$commentia->displayComments(false);?>
  </div>
  <?=$commentia->displayAuthForm();?>
  <?=$commentia->displaySignUpForm();?>
  <button onclick='refreshComments()'>Refresh comments</button>
</body>
</html>
