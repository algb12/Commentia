<?php

/////////////////////////////////////////////////////
// Comments model                                  //
// This file defines the comments model,           //
// And the methods used to interact with them.     //
// Author: algb12.19@gmail.com                       //
/////////////////////////////////////////////////////

namespace Commentia\Models;

use Parsedown;
use Markdownify\Converter;
use Commentia\Lexicon\Lexicon;
use Commentia\Roles\Roles;
use Commentia\Metadata\Metadata;
use Commentia\DBHandler\DBHandler;

use DateTime;

class Comments
{
    public $comments = array();
    public $pageid;
    public $md_to_html;
    public $html_to_md;
    public $db;
    public $members;
    public $html_output;

    /**
     * Initiates MD <=> HTML converters, checks for DB file (if none, will create one), arranges and assigns content of DB file to array for PHP use.
     *
     * @param string $pageid ID of page on which comments should be displayed
     */
    public function __construct($pageid)
    {
        $this->pageid = $pageid;
        $this->db = new DBHandler(DB);
        $this->md_to_html = new Parsedown();
        $this->html_to_md = new Converter();

        $stmt = $this->db->prepare('SELECT * FROM comments WHERE pageid = :pageid');
        $stmt->bindValue(':pageid', $pageid);
        $res = $stmt->execute();

        while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
            $this->comments['ucid-'.$r['ucid']] = $r;
            $this->comments['ucid-'.$r['ucid']]['children'] = json_decode($this->comments['ucid-'.$r['ucid']]['children']);
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
            $this->html_output .= ('<div class="commentia-new_comment_area">'."\n".'<h4>'.TITLES_NEW_COMMENT.'</h4>'."\n".'<textarea id="comment-box" oninput="autoGrow(this);"></textarea>'."\n".'<button id="post-comment-button" onclick="postNewComment(this);">'.COMMENT_CONTROLS_PUBLISH.'</button>'."\n".'</div>'."\n");
        }

        return $this->html_output;
    }

    /**
     * Outputs the comment markup (including children).
     *
     * @param string $ucid    UCID of comment (unique comment ID)
     */
    public function renderCommentView($ucid) {
      global $lexicon;
      global $roles;

      $members = new Members();

      // TODO: implement SQLite3 comment display

      $comment_data = $this->comments['ucid-'.$ucid];
      $this->html_output .= ('<div class="commentia-comment"'.' data-ucid="'.$comment_data['ucid'].'">'."\n");
      $this->html_output .= ('<div class="commentia-comment_info">'."\n");
      $this->html_output .= ('<img src='.$members->getMemberData($comment_data['creator_username'], 'avatar_file').' class="commentia-member_avatar">'."\n");
      $this->html_output .= ('<p class="commentia-comment_by">'.COMMENT_INFO_COMMENT_BY.' '.($comment_data['creator_username']).', </p>'."\n");
      date_default_timezone_set(TIMEZONE);
      $this->html_output .= ('<p class="commentia-comment_timestamp">'.COMMENT_INFO_POSTED_AT.' '.date(DATETIME_LOCALIZED,strtotime($comment_data['timestamp'])).'</p>'."\n");
      date_default_timezone_set('UTC');
      $this->html_output .= ('</div>'."\n");
      $this->html_output .= ('<div class="commentia-comment_content">'.$comment_data['content'].'</div>'."\n");
      $this->html_output .= ('<div class="commentia-edit_area"></div>'."\n");

      if (!$comment_data['is_deleted']) {
          if ($_SESSION['member_is_logged_in']) {
              $this->html_output .= ('<p class="commentia-comment_controls">
                             <a href="javascript:void(0)" onclick="showReplyArea(this)">'.COMMENT_CONTROLS_REPLY.'</a>');
              if ($roles->memberHasUsername($comment_data['creator_username']) || $roles->memberIsAdmin()) {
                  $this->html_output .= ('<a href="javascript:void(0)" onclick="showEditArea(this)">'.COMMENT_CONTROLS_EDIT.'</a>');
                  $this->html_output .= ('<a href="javascript:void(0)" onclick="deleteComment(this)">'.COMMENT_CONTROLS_DELETE.'</a>');
              }
              $this->html_output .= ('</p>'."\n");
          }
      }

      $this->html_output .= ('<div class="commentia-reply_area"></div>'."\n");

      if ($comment_data['children']) {
          foreach($comment_data['children'] as $child) {
              if (isset($this->comments['ucid-'.$child])) {
                  $this->renderCommentView($child);
                  unset($this->comments['ucid-'.$child]);
              }
          }
      }

      $this->html_output .= ('</div>'."\n");

    }

    /**
     * Creates a new comment/reply.
     *
     * @param string $content    Text/content of the comment to be created
     * @param int    $childof    The UCID of the parent comment if reply
     */
    public function createNewComment($content, $childof)
    {
        $res = $this->db->query('SELECT MAX(ucid) FROM comments');
        $last_ucid = $res->fetchArray(SQLITE3_ASSOC)['MAX(ucid)'];

        if (is_numeric($last_ucid)) {
            $ucid = $last_ucid + 1;
        } else {
            $ucid = 0;
        }

        $content = $this->md_to_html->text(htmlspecialchars(urldecode($content)));
        $timestamp = date(DateTime::ISO8601);
        $creator_username = $_SESSION['member_username'];
        $is_deleted = 0;
        $children = '';
        $pageid = $this->pageid;

        $stmt = $this->db->prepare('INSERT INTO comments (ucid, content, timestamp, creator_username, is_deleted, children, pageid) VALUES (
                                    :ucid, :content, :timestamp, :creator_username, :is_deleted, :children, :pageid);');

        $stmt->bindValue(':ucid', $ucid);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':timestamp', $timestamp);
        $stmt->bindValue(':creator_username', $creator_username);
        $stmt->bindValue(':is_deleted', $is_deleted);
        $stmt->bindValue(':children', $children);
        $stmt->bindValue(':pageid', $pageid);

        $stmt->execute();

        if (isset($childof)) {
            $stmt = $this->db->prepare('SELECT children FROM comments WHERE ucid = :childof');
            $stmt->bindValue(':childof', $childof);
            $res = $stmt->execute();

            $children = json_decode($res->fetchArray(SQLITE3_ASSOC)['children']);
            $children[] = $ucid;
            $children_new = json_encode($children);

            $stmt = $this->db->prepare('UPDATE comments SET children = :children_new WHERE ucid = :childof');
            $stmt->bindValue(':children_new', $children_new);
            $stmt->bindValue(':childof', $childof);
            $stmt->execute();
        }
    }

    /**
     * Updates comment content with supplied value.
     *
     * @param int    $ucid       Unique comment ID
     * @param string $content    New content to be put into comment text
     */
    public function editComment($ucid, $content)
    {
        $content = $this->md_to_html->text(htmlspecialchars(urldecode($content)));
        $timestamp = date(DateTime::ISO8601);
        $stmt = $this->db->prepare('UPDATE comments SET content = :content, timestamp = :timestamp WHERE ucid = :ucid');
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':timestamp', $timestamp);
        $stmt->bindValue(':ucid', $ucid);
        $stmt->execute();
    }

    /**
     * Sets is_deleted flag of comment to true, overwrites content with the string '[[deleted]]'.
     *
     * @param int    $ucid       Unique comment ID
     */
    public function deleteComment($ucid)
    {
        $content = $this->md_to_html->text(htmlspecialchars(urldecode('_[[deleted]]_')));
        $timestamp = date(DateTime::ISO8601);
        $stmt = $this->db->prepare('UPDATE comments SET content = :content, timestamp = :timestamp, is_deleted = 1 WHERE ucid = :ucid');
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':timestamp', $timestamp);
        $stmt->bindValue(':ucid', $ucid);
        $stmt->execute();
    }

    /**
     * Used by the frontend to get the comment's Markdown for editing.
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
}
