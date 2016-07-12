<?php

// Commentia API
// This should be the ONLY entry point directly from a website.
// Anything after the $_SESSION['member_is_logged_in'] check can only be executed ince authenticated.
// Author: Alexander Gilburg
// Last updated: 7th of July 2016

session_start();

// Require the controller
require_once("commentia.controller.php");

// Instantiate the controller
if ( isset($_GET['pageid']) ) {
  $commentia = new commentiaController($_GET['pageid']);
} else if ( isset($_POST['pageid']) ) {
  $commentia = new commentiaController($_POST['pageid']);
} else {
  $commentia = new commentiaController();
}

// Require member roles
require_once("members.roles.php");

$roles = new Roles();

if ( isset($_POST['action']) ) {
  $action = $_POST['action'];

  if ( ($action === 'loginMember')
  && isset($_POST['username'])
  && isset($_POST['password']) ) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $commentia->loginMember($username, $password);
  }

  if ( ($action === 'logoutMember') ) {
    $commentia->logoutMember();
  }
}

if ( isset($_GET['pageid'])
&& isset($_GET['action']) ) {
  $pageid = $_GET['pageid'];
  $action = $_GET['action'];

  if ($action === 'display') {
    $is_ajax_request = true;
    echo $commentia->displayComments($is_ajax_request);
  }
}


// This statement disabled functions bellow for non-lo
if ( !$_SESSION['member_is_logged_in'] ) {
  return 0;
}

if ( isset($_GET['pageid'])
&& isset($_GET['action']) ) {
  $pageid = $_GET['pageid'];
  $action = $_GET['action'];

  if ( ($action === 'getCommentMarkdown') && isset($_GET['ucid']) ) {
    $ucid = $_GET['ucid'];
    $reply_path = $_GET['reply_path'];
    echo $commentia->getCommentMarkdown($ucid, $reply_path);
  }
}

if ( isset($_POST['pageid'])
&& isset($_POST['action']) ) {
  $pageid = $_POST['pageid'];
  $action = $_POST['action'];

  if ( ( ($action === 'reply')
  || ($action === 'postNewComment')  )
  && isset($_POST['content'])) {
    $content = $_POST['content'];
    $username = $_SESSION['member_username'];
    $reply_path = $_POST['reply_path'];
    $commentia->createNewComment($content, $username, $reply_path);
  }

  if ( ($roles->memberHasUsername( $commentia->getCommentData($_POST['ucid'], $_POST['reply_path'], "creator_username") )
  || $roles->memberIsAdmin() )
  && ($action === 'edit')
  && isset($_POST['content'])
  && isset($_POST['ucid']) ) {
    $content = $_POST['content'];
    $ucid = $_POST['ucid'];
    $reply_path = $_POST['reply_path'];
    $commentia->editComment($ucid, $reply_path, $content);
  }

  if ( ($roles->memberHasUsername( $commentia->getCommentData($_POST['ucid'], $_POST['reply_path'], "creator_username") )
  || $roles->memberIsAdmin() )
  && ($action === 'delete')
  && isset($_POST['ucid']) ) {
    $ucid = $_POST['ucid'];
    $reply_path = $_POST['reply_path'];
    $commentia->deleteComment($ucid, $reply_path);
  }
}
