<?php

error_reporting(E_STRICT);
ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Commentia API
// This should be the ONLY entry point directly from a website.
// Anything after the $_SESSION['member_is_logged_in'] check can only be executed ince authenticated.
// Author: Alexander Gilburg
// Last updated: 15th of July 2016

require_once 'vendor/autoload.php';

// Require the controller
use Commentia\Controllers\CommentiaController;

// Instantiate the controller
if (isset($_GET['pageid'])) {
    $commentia = new CommentiaController($_GET['pageid']);
    $pageid = $_GET['pageid'];
} elseif (isset($_POST['pageid'])) {
    $commentia = new CommentiaController($_POST['pageid']);
    $pageid = $_POST['pageid'];
} else {
    $commentia = new CommentiaController();
}

file_put_contents("pageid.txt", $pageid);

// Require member roles
use Commentia\Roles\Roles;

$roles = new Roles();

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if (($action === 'loginMember')
  && isset($_POST['username'])
  && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $commentia->loginMember($username, $password);
    }

    if (($action === 'logoutMember')) {
        $commentia->logoutMember();
    }
}

if (isset($_GET['pageid'])
&& isset($_GET['action'])) {
    $pageid = $_GET['pageid'];
    $action = $_GET['action'];

    if ($action === 'display') {
        echo $commentia->displayComments();
    }
}

// This statement disabled functions bellow for non-lo
if (!$_SESSION['member_is_logged_in']) {
    return 0;
}

if (isset($_GET['pageid'])
&& isset($_GET['action'])) {
    $pageid = $_GET['pageid'];
    $action = $_GET['action'];

    if (($action === 'getCommentMarkdown') && isset($_GET['ucid'])) {
        $ucid = $_GET['ucid'];
        $reply_path = $_GET['reply_path'];
        echo $commentia->getCommentMarkdown($ucid, $reply_path);
    }
}

if (isset($_POST['pageid'])
&& isset($_POST['action'])) {
    $pageid = $_POST['pageid'];
    $action = $_POST['action'];

    if ((($action === 'reply')
  || ($action === 'postNewComment'))
  && isset($_POST['content'])) {
        $content = $_POST['content'];
        $reply_path = $_POST['reply_path'];

        $commentia->createNewComment($content, $reply_path);
    }

    if (($roles->memberHasUsername($commentia->getCommentData($_POST['ucid'], $_POST['reply_path'], 'creator_username'))
  || $roles->memberIsAdmin())
  && ($action === 'edit')
  && isset($_POST['content'])
  && isset($_POST['ucid'])) {
        $content = $_POST['content'];
        $ucid = $_POST['ucid'];
        $reply_path = $_POST['reply_path'];
        $commentia->editComment($ucid, $reply_path, $content);
    }

    if (($roles->memberHasUsername($commentia->getCommentData($_POST['ucid'], $_POST['reply_path'], 'creator_username'))
  || $roles->memberIsAdmin())
  && ($action === 'delete')
  && isset($_POST['ucid'])) {
        $ucid = $_POST['ucid'];
        $reply_path = $_POST['reply_path'];
        $commentia->deleteComment($ucid, $reply_path);
    }
}
