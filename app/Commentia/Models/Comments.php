<?php

# Comments model
# This file contains the comments-related methods
# Author: Alexander Gilburg
# Last updated: 15th of July 2016

namespace Commentia\Models;

use Parsedown;
use Markdownify\Converter;
use Commentia\Lexicon\Lexicon;
use Commentia\Roles\Roles;
use DateTime;

class Comments
{
    public $comments_json = 'app/data/comments.json';
    public $comments = array();
    public $comments_global = array();
    public $pageid;
    public $md_to_html;
    public $html_to_md;

    public function __construct($comments_json, $pageid)
    {
        $this->md_to_html = new Parsedown();

        $this->html_to_md = new Converter();

        if (!empty($comments_json)) {
            $this->comments_json = $comments_json;
        }
        if (!file_exists($this->comments_json)) {
            file_put_contents($this->comments_json, '');
        }
        $this->comments_global = json_decode(file_get_contents($this->comments_json), true);
        $this->pageid = $pageid;
        $this->comments = &$this->comments_global["pageid-$this->pageid"];

        date_default_timezone_set('UTC');
    }

    public function displayComments($is_ajax_request)
    {
        global $html;

        global $lexicon;
        $lexicon = new Lexicon(LEX_LOCALE);

        global $roles;
        $roles = new Roles();

        if (!$is_ajax_request) {
            $html .= ('<div class="commentia-comments_container" id="comments_pageid-'.$this->pageid.'">'."\n");
        }

        function iterateCommentData($comment)
        {
            global $html;
            global $lexicon;
            global $roles;
            foreach ($comment as $comment_data) {
                $html .= ('<div class="commentia-comment"'.'data-ucid="'.$comment_data['ucid'].'"'.'data-reply-path="'.$comment_data['reply_path'].'">'."\n");
                $html .= ('<p class="commentia-comment_by">'.$lexicon->getPhrase('comment_info', 'comment_by').' '.($comment_data['creator_username']).', </p>'."\n");
                $datetime = DateTime::createFromFormat(DateTime::ISO8601, $comment_data['timestamp']);
                $html .= ('<p class="commentia-comment_timestamp">'.$lexicon->getPhrase('comment_info', 'posted_at').' '.date_format($datetime, 'Y-m-d H:i:s').'</p>'."\n");
                $html .= ('<div class="commentia-comment_content">'.$comment_data['content'].'</div>'."\n");
                $html .= ('<div class="commentia-edit_area"></div>'."\n");

                if (!$comment_data['is_deleted']) {
                    if ($_SESSION['member_is_logged_in']) {
                        $html .= ('<p class="commentia-comment_controls">
            <a href="javascript:void(0)" onclick="showReplyArea(this)">'.$lexicon->getPhrase('comment_controls', 'reply').'</a>');
                        if ($roles->memberHasUsername($comment_data['creator_username']) || $roles->memberIsAdmin()) {
                            $html .= ('<a href="javascript:void(0)" onclick="showEditArea(this)">'.$lexicon->getPhrase('comment_controls', 'edit').'</a>');
                            $html .= ('<a href="javascript:void(0)" onclick="deleteComment(this)">'.$lexicon->getPhrase('comment_controls', 'delete').'</a>');
                        }
                        $html .= ('</p>'."\n");
                    }
                }

                $html .= ('<div class="commentia-reply_area"></div>'."\n");
                if (isset($comment_data['replies'])) {
                    iterateCommentData($comment_data['replies']);
                }
                $html .= ('</div>'."\n");
            }
        }

        foreach ($this->comments as $comment) {
            iterateCommentData($comment);
        }

        if ($_SESSION['member_is_logged_in']) {
            $html .= ('<div class="commentia-new_comment_area">'."\n".'<h4>'.$lexicon->getPhrase('titles', 'new_comment').'</h4>'."\n".'<textarea id="comment-box" oninput="autoGrow(this);"></textarea>'."\n".'<button id="post-comment-button" onclick="postNewComment(this);">'.$lexicon->getPhrase('comment_controls', 'publish').'</button>'."\n".'</div>'."\n");
        }

        if (!$is_ajax_request) {
            $html .= ('</div>'."\n");
        }

        return $html;
    }

    public function createNewComment($content, $reply_path)
    {
        if ($this->comments_global['last_ucid'] !== '') {
            $ucid = $this->comments_global['last_ucid'] + 1;
        } else {
            $ucid = 0;
        }

        $comment_post_path = &$this->comments['comments'];

        if (isset($reply_path) && ($reply_path !== '')) {
            $ucid_nodes = explode('-', $reply_path);
            foreach ($ucid_nodes as $ucid_node) {
                $comment_post_path = &$comment_post_path["ucid-$ucid_node"];
                if (!isset($comment_post_path['replies'])) {
                    $comment_post_path['replies'] = array();
                }
                $comment_post_path = &$comment_post_path['replies'];
            }
        }

        $comment_post_path["ucid-$ucid"] = array();
        $comment_post_path["ucid-$ucid"]['ucid'] = $ucid;
        $comment_post_path["ucid-$ucid"]['content'] = $this->md_to_html->text(htmlspecialchars(urldecode("$content")));
        $comment_post_path["ucid-$ucid"]['timestamp'] = date(DateTime::ISO8601);
        $comment_post_path["ucid-$ucid"]['creator_username'] = $_SESSION['member_username'];
        $comment_post_path["ucid-$ucid"]['is_deleted'] = false;
        $comment_post_path["ucid-$ucid"]['reply_path'] = (isset($reply_path) && !empty($reply_path) ? $reply_path."-$ucid" : "$ucid");

        $this->comments_global['last_ucid'] = $ucid;
        $this->comments_global['last_modified'] = date(DateTime::ISO8601);

        $this->updateComments($this->comments_json);
    }

    public function editComment($ucid, $reply_path, $content)
    {
        $comment_post_path = &$this->gotoComment($ucid, $reply_path);

        $comment_post_path['content'] = $this->md_to_html->text(urldecode($content));
        $comment_post_path['timestamp'] = date(DateTime::ISO8601);
        $this->comments_global['last_modified'] = date(DateTime::ISO8601);

        $this->updateComments($this->comments_json);
    }

    public function deleteComment($ucid, $reply_path)
    {
        $comment_post_path = &$this->gotoComment($ucid, $reply_path);

        $comment_post_path['content'] = $this->md_to_html->text(urldecode('_[[deleted]]_'));
        $comment_post_path['timestamp'] = date(DateTime::ISO8601);
        $comment_post_path['is_deleted'] = true;
        $this->comments_global['last_modified'] = date(DateTime::ISO8601);

        $this->updateComments($this->comments_json);
    }

    public function getCommentMarkdown($ucid, $reply_path)
    {
        $comment_post_path = &$this->gotoComment($ucid, $reply_path);

        $comment_md = $this->html_to_md->parseString($comment_post_path['content']);

        return $comment_md;
    }

    public function getCommentData($ucid, $reply_path, $entry)
    {
        $comment_post_path = &$this->gotoComment($ucid, $reply_path);

        return $comment_post_path[$entry];
    }

    private function &gotoComment($ucid, $reply_path)
    {
        $comment_post_path = &$this->comments['comments'];

        if (isset($reply_path) && ($reply_path !== '')) {
            $ucid_nodes = explode('-', $reply_path);
            foreach ($ucid_nodes as $ucid_node) {
                $comment_post_path = &$comment_post_path["ucid-$ucid_node"];
                if ($ucid_node !== $ucid) {
                    $comment_post_path = &$comment_post_path['replies'];
                }
            }
        }

        return $comment_post_path;
    }

    private function updateComments($comments_json)
    {
        if (!is_writable(dirname($comments_json))) {
            exit('Error: Directory not writable.');
        }

        $fp = fopen($comments_json, 'w+');
        flock($fp, LOCK_EX);
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, json_encode($this->comments_global));
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
