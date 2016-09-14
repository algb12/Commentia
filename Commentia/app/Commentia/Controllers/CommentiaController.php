<?php

///////////////////////////////////////////////////////////////////////////////////////
// Commentia controller                                                              //
// This file routes the functions to the relevant classes/controls the program flow. //
// It contains a blueprint of every publically accessible function.                  //
// Author: algb12.19@gmail.com                                                         //
///////////////////////////////////////////////////////////////////////////////////////

namespace Commentia\Controllers;

require_once __DIR__.'/../../../vendor/autoload.php';

use Commentia\Models\Comments;
use Commentia\Models\Members;
use Commentia\Lexicon\Lexicon;
use Commentia\Lexicon\Metadata;

class CommentiaController
{
    public $members;
    public $comments;
    public $params = array();

    /**
     * Initiates a new controller instance for the relevant pageid.
     *
     * @param string $pageid The page ID for the comments (see README.md)
     */
    public function __construct($pageid, $abs_path_prefix = '')
    {
        session_start();

        date_default_timezone_set('UTC');

        require_once __DIR__.'/../../data/config.php';

        Lexicon::load(COMMENTIA_LEX_LOCALE);

        define('ABS_PATH_PREFIX', $abs_path_prefix);
        $_SESSION['__COMMENTIA__']['abs_path_prefix'] = ABS_PATH_PREFIX;

        $real_pageid = (isset($_SESSION['__COMMENTIA__']['pageid']) ? $_SESSION['__COMMENTIA__']['pageid'] : $pageid);

        if (isset($real_pageid)) {
            $this->comments = new Comments($real_pageid);
        } else {
            exit('Error: Page ID not set');
        }

        $this->members = new Members();

        $this->params = array();
        foreach ($_GET as $key => $value) {
            $this->params[$key] = $value;
        }

        $_SESSION['__COMMENTIA__']['pageid'] = $pageid;
    }

    public function displayComments($is_ajax_request)
    {
        return $this->comments->displayComments($is_ajax_request);
    }

    public function createNewComment($content, $childof)
    {
        return $this->comments->createNewComment($content, $childof);
    }

    public function editComment($ucid, $content)
    {
        return $this->comments->editComment($ucid, $content);
    }

    public function deleteComment($ucid)
    {
        return $this->comments->deleteComment($ucid);
    }

    public function getCommentMarkdown($ucid)
    {
        return $this->comments->getCommentMarkdown($ucid);
    }

    public function getCommentData($ucid, $entry)
    {
        return $this->comments->getCommentData($ucid, $entry);
    }

    public function getMemberData($username, $entry)
    {
        return $this->members->getMemberData($username, $entry);
    }

    public function loginMember($username, $password)
    {
        return $this->members->loginMember($username, $password);
    }

    public function logoutMember()
    {
        return $this->members->logoutMember();
    }

    public function signUpMember($username, $password, $retyped_password, $email, $avatar_file)
    {
        return $this->members->signUpMember($username, $password, $retyped_password, $email, $avatar_file);
    }

    public function displayAuthForm()
    {
        return $this->members->displayAuthForm();
    }

    public function displaySignUpForm()
    {
        return $this->members->displaySignUpForm();
    }

    public function getPhrase($phrase)
    {
        return Lexicon::getPhrase($phrase);
    }
}
