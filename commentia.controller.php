<?php

// Commentia controller
// This file routes the functions to the relevant classes/controls the program flow.
// It contains a blueprint of every publically accessible function.
// Author: Alexander Gilburg
// Last updated: 7th of July 2016

// Load config file
require_once("commentia.config.php");

// Members model
require_once("members.model.php");

// Comments model
require_once("comments.model.php");

class commentiaController {

  public $members;
  public $comments;
  public $params = array();

  public function __construct($pageid) {
    if ( isset($pageid) ) {
      $this->comments = new Comments(JSON_FILE_COMMENTS, $pageid);
    }
    $this->members = new Members(JSON_FILE_MEMBERS);

    $this->params = array();
    foreach ($_GET as $key=>$value) {
      $this->params[$key] = $value;
    }
  }

  public function displayComments($is_ajax_request) {
    return $this->comments->displayComments($is_ajax_request);
  }

  public function createNewComment($content, $username, $creator_uuid, $reply_path) {
    return $this->comments->createNewComment($content, $username, $creator_uuid, $reply_path);
  }

  public function editComment($ucid, $reply_path, $content) {
    return $this->comments->editComment($ucid, $reply_path, $content);
  }

  public function deleteComment($ucid, $reply_path) {
    return $this->comments->deleteComment($ucid, $reply_path);
  }

  public function getCommentMarkdown($ucid, $reply_path) {
    return $this->comments->getCommentMarkdown($ucid, $reply_path);
  }

  public function getCommentData($ucid, $reply_path, $entry) {
    return $this->comments->getCommentData($ucid, $reply_path, $entry);
  }

  public function getMemberData($username, $entry) {
    return $this->members->getMemberData($username, $entry);
  }

  public function loginMember($username, $password) {
    return $this->members->loginMember($username, $password);
  }

  public function logoutMember() {
    return $this->members->logoutMember();
  }

  public function displayAuthForm() {
    return $this->members->displayAuthForm();
  }
}
