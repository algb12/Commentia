<?php

# Members model
# This file contains the members-related methods
# Author: Alexander Gilburg
# Last updated: 14th of July 2016

namespace Commentia\Models;

use Commentia\Lexicon\Lexicon;

# # Auth function compatibility library under PHP v5.5.0
# require_once 'vendor/password_compat/password.php';

class Members
{
    public $members_json = 'app/data/members.json';
    public $members = array();

    public function __construct($members_json)
    {
        if (!empty($members_json)) {
            $this->members_json = $members_json;
        }
        if (!file_exists($this->members_json)) {
            file_put_contents($this->members_json, '');
        }
        $this->members = json_decode(file_get_contents($this->members_json), true);
    }

    private function generateAvatarThumbnail($avatar_file, $avatar_width, $avatar_height)
    {
        if (!getimagesize($avatar_file)) {
            exit('File "'.$avatar_file.'" not found.');
        }
        switch (strtolower(pathinfo($avatar_file, PATHINFO_EXTENSION))) {
      case 'jpeg':
      case 'jpg':
        $image = imagecreatefromjpeg($avatar_file);
      break;

      case 'png':
        $image = imagecreatefrompng($avatar_file);
      break;

      case 'gif':
        $image = imagecreatefromgif($avatar_file);
      break;

      default:
        exit('File "'.$avatar_file.'" is not valid jpg, png or gif image.');
      break;
    }

        $filename = 'avatars/'.uniqid('avatar_', true).'.jpg';

        $width = imagesx($image);
        $height = imagesy($image);

        $original_aspect = $width / $height;
        $avatar_aspect = $avatar_width / $avatar_height;

        if ($original_aspect >= $avatar_aspect) {
            $new_height = $avatar_height;
            $new_width = $width / ($height / $avatar_height);
        } else {
            $new_width = $avatar_width;
            $new_height = $height / ($width / $avatar_width);
        }

        $avatar = imagecreatetruecolor($avatar_width, $avatar_height);

        imagecopyresampled(
      $avatar,
      $image,
      0 - ($new_width - $avatar_width) / 2, # Center image horizontally
      0 - ($new_height - $avatar_height) / 2, # Center image vertically
      0, 0,
      $new_width, $new_height,
      $width, $height
    );

        imagejpeg($avatar, $filename, 80);

        return $filename;
    }

    public function createNewMember($username, $password, $email, $member_type, $avatar_file)
    {
        $this->members['members'][$username] = array();
        $this->members['members'][$username]['username'] = $username;
        $this->members['members'][$username]['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $this->members['members'][$username]['email'] = $email;
        $this->members['members'][$username]['avatar_file'] = $this->generateAvatarThumbnail($avatar_file, 150, 150);
        $this->members['members'][$username]['is_banned'] = false;
        $this->members['members'][$username]['role'] = $member_type;
        $this->members['members'][$username]['member_since'] = date(DateTime::ISO8601);
        $this->members['last_modified'] = date(DateTime::ISO8601);
        $this->updateMembers($this->members_json);
    }

    public function deleteMember($username)
    {
        unset($this->members['members'][$username]);
        $this->members['last_modified'] = date(DateTime::ISO8601);
        $this->updateMembers($this->members_json);
    }

    public function getMemberData($username, $entry)
    {
        return $this->members['members'][$username][$entry];
    }

    public function setMemberData($username, $entry, $data)
    {
        $this->members['members'][$username][$entry] = $data;
        $this->updateMembers($this->members_json);
    }

    private function updateMembers($members_json)
    {
        if (!is_writable(dirname($members_json))) {
            exit('Error: Directory not writable.');
        }

        $fp = fopen($members_json, 'w+');
        flock($fp, LOCK_EX);
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, json_encode($this->members));
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public function loginMember($username, $password)
    {
        if (isset($username) && isset($password)) {
            if (password_verify($password, $this->getMemberData($username, 'password_hash'))) {
                $_SESSION['member_is_logged_in'] = true;
                $_SESSION['member_username'] = $username;
                $_SESSION['member_role'] = $this->getMemberData($username, 'role');
                $_SESSION['login_error_msg'] = 'LOGIN_AUTH_SUCCESS';
                session_regenerate_id();
                header('Location:'.$_SESSION['log_in_page']);
            } else {
                $_SESSION['login_error_msg'] = 'LOGIN_AUTH_FAIL';
                session_regenerate_id();
                header('Location:'.$_SESSION['log_in_page']);
            }
        }
    }

    public function logoutMember()
    {
        $_SESSION['member_is_logged_in'] = false;
        unset($_SESSION['member_username']);
        unset($_SESSION['member_role']);
        $_SESSION['login_error_msg'] = 'LOGOUT';
        session_regenerate_id();
        header('Location:'.$_SESSION['log_in_page']);
    }

    public function displayAuthForm()
    {
        $lexicon = new Lexicon('en_US');

        if ($_SESSION['member_is_logged_in']) {
            $html = '<form class="commentia-logout-form" action="api.php" method="POST">
        <input type="hidden" name="action" value="logoutMember">
        <input type="submit" name="log-out" value="Log out">
      </form>';
            $html .= '<p>Logged in as '.$_SESSION['member_username'].' with role '.$_SESSION['member_role'].'</p>';
        } else {
            $html = '<form class="commentia-login-form" action="api.php" method="POST">
        <table>
          <tbody>
            <tr>
              <td>Username:</td>
              <td><input type="text" name="username"></td>
            </tr>
            <tr>
              <td>Password:</td>
              <td><input type="password" name="password"></td>
            </tr>
          </tbody>
        </table>
        <input type="hidden" name="action" value="loginMember">
        <input type="submit" name="log-in" value="Log in">
      </form>';
        }
        $html .= '<p>'.$_SESSION['login_error_msg'].'</p>';
        $_SESSION['login_error_msg'] = '';

        if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https:#';
        } else {
            $protocol = 'http:#';
        }

        $_SESSION['log_in_page'] = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

        return $html;
    }
}
