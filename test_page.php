<!DOCTYPE html>

<?php
error_reporting(-1);
  // Include and initiate Commentia with unique page-id
  include 'commentia.controller.php';
  $pageid = 0;
  $commentia = new commentiaController($pageid);
?>

<html lang="en" data-pageid="<?php echo $pageid; // Pass page-id as data-attribute to html-tag ?>">
<head>
  <meta charset="UTF-8">
  <title>Commentia - A lightweight, no DB comment system</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Commentia AJAX-script + CSS for comments section -->
  <script src="commentia.js" charset="utf-8"></script>
  <link rel="stylesheet" href="commentia-default-theme.css" media="screen" charset="utf-8">
</head>

<body>
  <h3>Comments:</h3>
  <hr>
  <div>
    <?php
      // Display the comments html element
      echo $commentia->displayComments();

      // Display authentication form
      echo $commentia->displayAuthForm();
    ?>
  </div>
  <!-- Debug (will be removed) -->
  <button type="button" name="refreshComments" onclick="refreshComments()">Refresh comments</button>
</body>
</html>
