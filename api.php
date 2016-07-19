<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Commentia API                                                                                      //
// This should be the ONLY entry point directly from a website.                                       //
// Anything after the $_SESSION['member_is_logged_in'] check can only be executed ince authenticated. //
// Author: Alexander Gilburg                                                                          //
////////////////////////////////////////////////////////////////////////////////////////////////////////


session_start();

$_SESSION['member_is_logged_in'] = true;

require_once 'vendor/autoload.php';

// Require the controller
use Commentia\Controllers\CommentiaController;

// Instantiate the controller
if (isset($_SESSION['pageid'])) {
    $commentia = new CommentiaController($_SESSION['pageid']);
    $pageid = $_SESSION['pageid'];
} elseif (isset($_SESSION['pageid'])) {
    $commentia = new CommentiaController($_SESSION['pageid']);
    $pageid = $_SESSION['pageid'];
} else {
    $commentia = new CommentiaController();
}

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

    if ($action === 'signUpMember') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $retyped_password = $_POST['retyped_password'];
        $email = $_POST['email'];
        $avatar_file = $_POST['avatar_file'];
        $commentia->signUpMember($username, $password, $retyped_password, $email, $avatar_file);
    }
}

if (isset($_SESSION['pageid'])
&& isset($_GET['action'])) {
    $pageid = $_SESSION['pageid'];
    $action = $_GET['action'];

    if ($action === 'display') {
        $is_ajax_request = true;
        echo $commentia->displayComments($is_ajax_request);
    }

    if ($action === 'getPhrase'
    && $_SESSION['member_is_logged_in']) {
        if (isset($_GET['phrase'])) {
            echo $commentia->getPhrase($_GET['phrase']);
        }
    }

    if (($action === 'getCommentMarkdown')
    && isset($_GET['ucid'])
    && $_SESSION['member_is_logged_in']) {
        $ucid = $_GET['ucid'];
        $reply_path = $_GET['reply_path'];
        echo $commentia->getCommentMarkdown($ucid, $reply_path);
    }
}

if (isset($_SESSION['pageid'])
&& isset($_POST['action'])
&& $_SESSION['member_is_logged_in']) {
    $pageid = $_SESSION['pageid'];
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
