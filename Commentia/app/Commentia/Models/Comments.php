<?php

/////////////////////////////////////////////////////
// Comments model                                  //
// This file defines the comments model,           //
// And the methods used to interact with them.     //
// Author: Alexander Gilburg                       //
/////////////////////////////////////////////////////

namespace Commentia\Models;

use Parsedown;
use Markdownify\Converter;
use Commentia\Lexicon\Lexicon;
use Commentia\Roles\Roles;
use Commentia\Metadata\Metadata;

use DateTime;

class Comments
{
    public $comments_json = JSON_FILE_COMMENTS;
    public $comments = array();
    public $comments_global = array();
    public $pageid;
    public $md_to_html;
    public $html_to_md;
    public $members;
    public $metadata_json = JSON_FILE_METADATA;
    public $metadata = array();

    /**
     * Initiates MD <=> HTML converters, checks for JSON file (if none, will create one), assigns content of JSON file to array for PHP use.
     *
     * @param string $pageid ID of page on which comments should be displayed
     */
    public function __construct($pageid)
    {
        $this->md_to_html = new Parsedown();

        $this->html_to_md = new Converter();

        $this->metadata = new Metadata();

        if (empty($this->comments_json)) {
            exit('Error: No comments JSON file set.');
        }

        if (!file_exists($this->comments_json)) {
            file_put_contents($this->comments_json, '');
        }

        $this->comments_global = json_decode(file_get_contents($this->comments_json), true);
        $this->pageid = $pageid;
        $this->comments = &$this->comments_global['comments'];

        // Traverse array, remove comments from other pages
        foreach ($this->comments as $comment_key => &$comment) {
            if ($comment['pageid'] !== $this->pageid) {
                unset($this->comments[$comment_key]);
            }
        }
    }

    /**
     * Returns the comments as HTML markup.
     *
     * @param bool $is_ajax_request If set to true, only inner comments without container will be outputted for use with JS's innerHTML
     *
     * @return string Comments HTML markup
     */
    public function displayComments()
    {
        global $html;

        global $lexicon;
        $lexicon = new Lexicon(LEX_LOCALE);

        global $roles;
        $roles = new Roles();

        /**
         * Iterates through each comment recursively, and appends it to the var holding the HTML markup.
         *
         * @param array $comment An array containing all the comment data
         */

        foreach ($this->comments as $comment) {
            if (isset($this->comments['ucid-'.$comment['ucid']])) {
                $this->renderCommentView($comment['ucid']);
                unset($this->comments['ucid-'.$comment['ucid']]);
            }
        }

        if ($_SESSION['member_is_logged_in']) {
            $html .= ('<div class="commentia-new_comment_area">'."\n".'<h4>'.TITLES_NEW_COMMENT.'</h4>'."\n".'<textarea id="comment-box" oninput="autoGrow(this);"></textarea>'."\n".'<button id="post-comment-button" onclick="postNewComment(this);">'.COMMENT_CONTROLS_PUBLISH.'</button>'."\n".'</div>'."\n");
        }

        return $html;
    }

    /**
     * Outputs the comment markup (including children).
     *
     * @param string $ucid    UCID of comment (unique comment ID)
     */
    public function renderCommentView($ucid) {

      global $html;
      global $lexicon;
      global $roles;

      $members = new Members(JSON_FILE_MEMBERS);

      $comment_data = $this->comments['ucid-'.$ucid];
      $html .= ('<div class="commentia-comment"'.' data-ucid="'.$comment_data['ucid'].'">'."\n");
      $html .= ('<div class="commentia-comment_info">'."\n");
      $html .= ('<img src='.$members->getMemberData($comment_data['creator_username'], 'avatar_file').' class="commentia-member_avatar">'."\n");
      $html .= ('<p class="commentia-comment_by">'.COMMENT_INFO_COMMENT_BY.' '.($comment_data['creator_username']).', </p>'."\n");
      date_default_timezone_set(TIMEZONE);
      $html .= ('<p class="commentia-comment_timestamp">'.COMMENT_INFO_POSTED_AT.' '.date(DATETIME_LOCALIZED,strtotime($comment_data['timestamp'])).'</p>'."\n");
      date_default_timezone_set('UTC');
      $html .= ('</div>'."\n");
      $html .= ('<div class="commentia-comment_content">'.$comment_data['content'].'</div>'."\n");
      $html .= ('<div class="commentia-edit_area"></div>'."\n");

      if (!$comment_data['is_deleted']) {
          if ($_SESSION['member_is_logged_in']) {
              $html .= ('<p class="commentia-comment_controls">
                             <a href="javascript:void(0)" onclick="showReplyArea(this)">'.COMMENT_CONTROLS_REPLY.'</a>');
              if ($roles->memberHasUsername($comment_data['creator_username']) || $roles->memberIsAdmin()) {
                  $html .= ('<a href="javascript:void(0)" onclick="showEditArea(this)">'.COMMENT_CONTROLS_EDIT.'</a>');
                  $html .= ('<a href="javascript:void(0)" onclick="deleteComment(this)">'.COMMENT_CONTROLS_DELETE.'</a>');
              }
              $html .= ('</p>'."\n");
          }
      }

      $html .= ('<div class="commentia-reply_area"></div>'."\n");

      if ($comment_data['children']) {
          foreach($comment_data['children'] as $child) {
              if (isset($this->comments['ucid-'.$child])) {
                  $this->renderCommentView($child);
                  unset($this->comments['ucid-'.$child]);
              }
          }
      }

      $html .= ('</div>'."\n");

    }

    /**
     * Creates a new comment/reply.
     *
     * @param string $content    Text/content of the comment to be created
     * @param int    $childof    The UCID of the parent comment if reply
     */
    public function createNewComment($content, $childof)
    {
        if ($this->metadata->getMetadata('last_ucid') !== '') {
            $ucid = $this->metadata->getMetadata('last_ucid') + 1;
        } else {
            $ucid = 0;
        }

        $comment_post_path = &$this->comments;

        $comment_post_path["ucid-$ucid"] = array();
        $comment_post_path["ucid-$ucid"]['ucid'] = $ucid;
        $comment_post_path["ucid-$ucid"]['content'] = $this->md_to_html->text(htmlspecialchars(urldecode("$content")));
        $comment_post_path["ucid-$ucid"]['timestamp'] = date(DateTime::ISO8601);
        $comment_post_path["ucid-$ucid"]['creator_username'] = $_SESSION['member_username'];
        $comment_post_path["ucid-$ucid"]['is_deleted'] = false;
        $comment_post_path["ucid-$ucid"]['children'] = array();
        $comment_post_path["ucid-$ucid"]['pageid'] = $this->pageid;

        if ($childof) {
            $comment_post_path["ucid-$childof"]['children'][] = $ucid;
        }

        $this->metadata->setMetadata('last_ucid', $ucid);
        $this->metadata->setMetadata('last_modified_comments', date(DateTime::ISO8601));

        $this->updateComments($this->comments_json);
    }

    /**
     * Updates comment content with supplied value.
     *
     * @param int    $ucid       Unique comment ID
     * @param string $content    New content to be put into comment text
     */
    public function editComment($ucid, $content)
    {
        $comment_post_path = &$this->comments["ucid-$ucid"];

        $comment_post_path['content'] = $this->md_to_html->text(urldecode($content));
        $comment_post_path['timestamp'] = date(DateTime::ISO8601);
        $this->metadata->setMetadata('last_modified_comments', date(DateTime::ISO8601));

        $this->updateComments($this->comments_json);
    }

    /**
     * Sets is_deleted flag of comment to true, overwrites content with the string '[[deleted]]'.
     *
     * @param int    $ucid       Unique comment ID
     */
    public function deleteComment($ucid)
    {
        $comment_post_path = &$this->comments["ucid-$ucid"];

        $comment_post_path['content'] = $this->md_to_html->text(urldecode('_[[deleted]]_'));
        $comment_post_path['timestamp'] = date(DateTime::ISO8601);
        $comment_post_path['is_deleted'] = true;
        $this->metadata->setMetadata('last_modified_comments', date(DateTime::ISO8601));

        $this->updateComments($this->comments_json);
    }

    /**
     * Used by the frontend to get the comment's markdown for editing.
     *
     * @param int    $ucid       Unique comment ID
     */
    public function getCommentMarkdown($ucid)
    {
        $comment_post_path = &$this->comments["ucid-$ucid"];

        $comment_md = $this->html_to_md->parseString($comment_post_path['content']);

        return $comment_md;
    }

    /**
     * Returns a specified entry for a comment.
     *
     * @param int    $ucid       Unique comment ID
     * @param string $entry      The entry that should be retrieved (e.g. creator_username, content, is_deleted etc.)
     *
     * @return mixed Returns the wanted entry
     */
    public function getCommentData($ucid, $entry)
    {
        $comment_post_path = &$this->comments["ucid-$ucid"];

        return $comment_post_path[$entry];
    }

    /**
     * Updates the comments JSON with the current comments array.
     *
     * @param string $comments_json The path to the comments JSON file
     */
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
