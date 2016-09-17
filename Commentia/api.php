<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Commentia API                                                                                      //
// This should be the ONLY entry point directly from a website.                                       //
// Anything after the $_SESSION['__COMMENTIA__']['member_is_logged_in'] check can only be executed ince authenticated. //
// Author: algb12.19@gmail.com                                                                          //
////////////////////////////////////////////////////////////////////////////////////////////////////////


session_start();

$_SESSION['__COMMENTIA__']['member_is_logged_in'] = true;

require_once 'vendor/autoload.php';

// Require the controller
use Commentia\Controllers\CommentiaController;

// Instantiate the controller
if (isset($_SESSION['__COMMENTIA__']['pageid'])) {
    $commentia = new CommentiaController($_SESSION['__COMMENTIA__']['pageid'], $_SESSION['__COMMENTIA__']['abs_path_prefix']);
    $pageid = $_SESSION['__COMMENTIA__']['pageid'];
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

if (isset($_SESSION['__COMMENTIA__']['pageid'])
&& isset($_GET['action'])) {
    $pageid = $_SESSION['__COMMENTIA__']['pageid'];
    $action = $_GET['action'];

    if ($action === 'display') {
        echo $commentia->displayComments();
    }

    if ($action === 'getPhrase'
    && $_SESSION['__COMMENTIA__']['member_is_logged_in']) {
        if (isset($_GET['phrase'])) {
            echo $commentia->getPhrase($_GET['phrase']);
        }
    }

    if (($action === 'getCommentMarkdown')
    && isset($_GET['ucid'])
    && $_SESSION['__COMMENTIA__']['member_is_logged_in']) {
        $ucid = $_GET['ucid'];
        echo $commentia->getCommentMarkdown($ucid);
    }
}

if (isset($_SESSION['__COMMENTIA__']['pageid'])
&& isset($_POST['action'])
&& $_SESSION['__COMMENTIA__']['member_is_logged_in']) {
    $pageid = $_SESSION['__COMMENTIA__']['pageid'];
    $action = $_POST['action'];

    if ((($action === 'reply')
    || ($action === 'postNewComment'))
    && isset($_POST['content'])) {
        $content = $_POST['content'];
        $childof = $_POST['childof'];

        $commentia->createNewComment($content, $childof);
    }

    if (($roles->memberHasUsername($commentia->getCommentData($_POST['ucid'], 'creator_username'))
    || $roles->memberIsAdmin())
    && ($action === 'edit')
    && isset($_POST['content'])
    && isset($_POST['ucid'])) {
        $content = $_POST['content'];
        $ucid = $_POST['ucid'];
        $commentia->editComment($ucid, $content);
    }

    if (($roles->memberIsLoggedIn())
    && ($action === 'updateRating')
    && isset($_POST['direction'])
    && isset($_POST['ucid'])) {
        $direction = $_POST['direction'];
        $ucid = $_POST['ucid'];
        $commentia->updateRating($ucid, $direction);
    }

    if (($roles->memberHasUsername($commentia->getCommentData($_POST['ucid'], 'creator_username'))
    || $roles->memberIsAdmin())
    && ($action === 'delete')
    && isset($_POST['ucid'])) {
        $ucid = $_POST['ucid'];
        $commentia->deleteComment($ucid);
    }
}
