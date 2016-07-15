<?php

# Commentia controller
# This file routes the functions to the relevant classes/controls the program flow.
# It contains a blueprint of every publically accessible function.
# Author: Alexander Gilburg
# Last updated: 15th of July 2016

namespace Commentia\Controllers;

require_once __DIR__.'/../../../vendor/autoload.php';

require_once __DIR__.'/../../data/config.php';

use Commentia\Models\Comments;
use Commentia\Models\Members;
use Commentia\Lexicon\Lexicon;

Lexicon::load(LEX_LOCALE);

class CommentiaController
{
    public $members;
    public $comments;
    public $params = array();

    public function __construct($pageid)
    {
        if (isset($pageid)) {
            $this->comments = new Comments(JSON_FILE_COMMENTS, $pageid);
        }
        $this->members = new Members(JSON_FILE_MEMBERS);

        $this->params = array();
        foreach ($_GET as $key => $value) {
            $this->params[$key] = $value;
        }

        session_start();
    }

    public function displayComments($is_ajax_request)
    {
        return $this->comments->displayComments($is_ajax_request);
    }

    public function createNewComment($content, $reply_path)
    {
        return $this->comments->createNewComment($content, $reply_path);
    }

    public function editComment($ucid, $reply_path, $content)
    {
        return $this->comments->editComment($ucid, $reply_path, $content);
    }

    public function deleteComment($ucid, $reply_path)
    {
        return $this->comments->deleteComment($ucid, $reply_path);
    }

    public function getCommentMarkdown($ucid, $reply_path)
    {
        return $this->comments->getCommentMarkdown($ucid, $reply_path);
    }

    public function getCommentData($ucid, $reply_path, $entry)
    {
        return $this->comments->getCommentData($ucid, $reply_path, $entry);
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

    public function displayAuthForm()
    {
        return $this->members->displayAuthForm();
    }
}
