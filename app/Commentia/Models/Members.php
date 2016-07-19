<?php

////////////////////////////////////////////////////
// Members model                                  //
// This file contains the members-related methods //
// Author: Alexander Gilburg                      //
////////////////////////////////////////////////////


namespace Commentia\Models;

use DateTime;

class Members
{
    public $members_json = JSON_FILE_MEMBERS;
    public $members = array();

    /**
     * Checks if JSON_FILE_MEMBERS defined in the config file exists. If not, it creates a new JSON file
     * Also does some security stuff for log in.
     */
    public function __construct()
    {
        if (empty($this->members_json)) {
            exit('Error: No members JSOn file set.');
        }

        if (!file_exists($this->members_json)) {
            file_put_contents($this->members_json, '');
        }

        $this->members = json_decode(file_get_contents($this->members_json), true);

        if (!isset($_SESSION['member_username'])) {
            $_SESSION['member_is_logged_in'] = false;
        }

        if (!isset($_SESSION['login_error_msg'])) {
            $_SESSION['login_error_msg'] = '';
        }
    }

    /**
     * Generates a thumbnail for the member avatar (used on signup) and returns its path.
     *
     * @param string $src    The path to the full image
     * @param int    $dest_w Desired width of the thumbnail
     * @param int    $dest_h Desired height of the thumbnail
     *
     * @return string The path to the newly generated thumbnail
     */
    private function generateAvatarThumbnail($src, $dest_w, $dest_h)
    {
        if (!file_exists($src)) {
            throw new InvalidArgumentException('File "'.$src.'" not found.');
        }
        switch (strtolower(pathinfo($src, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                $unoriented_source_image = imagecreatefromjpeg($src);
                $source_image = imagerotate($unoriented_source_image, array_values([0, 0, 0, 180, 0, 0, -90, 0, 90])[@exif_read_data($src)['Orientation'] ?: 0], 0);
            break;

            case 'png':
                $source_image = imagecreatefrompng($src);
            break;

            case 'gif':
                $source_image = imagecreatefromgif($src);
            break;

            default:
                throw new InvalidArgumentException('File "'.$src.'" is not valid jpg, png or gif image.');
            break;
        }

        $orig_w = imagesx($source_image);
        $orig_h = imagesy($source_image);

        $w_ratio = ($dest_w / $orig_w);
        $h_ratio = ($dest_h / $orig_h);

        if ($orig_w > $orig_h) {
            $crop_w = round($orig_w * $h_ratio);
            $crop_h = $dest_h;
        } elseif ($orig_w < $orig_h) {
            $crop_h = round($orig_h * $w_ratio);
            $crop_w = $dest_w;
        } else {
            $crop_w = $dest_w;
            $crop_h = $dest_h;
        }

        $dest_image = imagecreatetruecolor($dest_w, $dest_h);
        imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $crop_w, $crop_h, $orig_w, $orig_h);

        $image_file = (AVATAR_DIR.uniqid('avatar_', true).'.jpg');

        switch (strtolower(pathinfo($src, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($dest_image, $image_file);
            break;

            case 'png':
                imagepng($dest_image, $image_file);
            break;

            case 'gif':
                imagegif($dest_image, $image_file);
            break;

            default:
                throw new InvalidArgumentException('File "'.$src.'" is not valid jpg, png or gif image.');
            break;
        }

        unlink($src);

        return $image_file;
    }

    /**
     * Writes new account data to JSON.
     *
     * @param string $username    The username used to log in
     * @param string $password    The password to be hashed and used for log in
     * @param string $email       The email of the Members
     * @param string $role        The role of the new member (e.g. member, admin, guest etc.)
     * @param string $avatar_file The path to the member's avatar image thumbnail
     */
    private function createNewMember($username, $password, $email, $role, $avatar_file)
    {
        $this->members['members'][$username] = array();
        $this->members['members'][$username]['username'] = $username;
        $this->members['members'][$username]['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $this->members['members'][$username]['email'] = $email;
        $this->members['members'][$username]['avatar_file'] = $this->generateAvatarThumbnail($avatar_file, 150, 150);
        $this->members['members'][$username]['is_banned'] = false;
        $this->members['members'][$username]['role'] = $role;
        $this->members['members'][$username]['member_since'] = date(DateTime::ISO8601);
        $this->members['last_modified'] = date(DateTime::ISO8601);
        $this->updateMembers($this->members_json);
    }

    /**
     * Performs checking and validation prior to creating member account.
     *
     * @param string $username         The username used to log in
     * @param string $password         The password used to log in
     * @param string $retyped_password Compared to the password to prevent mistyping
     * @param string $email            The email of the new Members
     * @param array  $avatar_file      An array containing information on the image uploaded for avatar generation
     */
    public function signUpMember($username, $password, $retyped_password, $email, $avatar_file)
    {
        $error_encountered = false;

        if (empty($username) || $username === '') {
            $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_MISSING_USERNAME."<br>\n";
            $error_encountered = true;
        } elseif (isset($this->members['members'][$username])) {
            $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_USERNAME_TAKEN."<br>\n";
            $error_encountered = true;
        }

        if (empty($password) || $password === '') {
            $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_MISSING_PASSWORD."<br>\n";
            $error_encountered = true;
        } elseif ($password !== $retyped_password) {
            $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_PASSWORD_MISMATCH."<br>\n";
            $error_encountered = true;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_INVALID_EMAIL."<br>\n";
            $error_encountered = true;
        }

        if (!empty($_FILES['avatar_img']['tmp_name'])) {
            $uploads_dir = AVATAR_DIR;
            $upload_img_tmp = $_FILES['avatar_img']['tmp_name'];
            $is_image = getimagesize($_FILES['avatar_img']['tmp_name']) ? true : false;
            $filesize = $_FILES['avatar_img']['size'];

            if (($_FILES['avatar_img']['error'] == UPLOAD_ERR_OK) && $is_image && ($filesize <= 2097152)) {
                $name = $_FILES['avatar_img']['name'];
                $tmp_img_store_name = "$uploads_dir".uniqid()."_$name";
                move_uploaded_file($upload_img_tmp, $tmp_img_store_name);
                $avatar_file = $tmp_img_store_name;
            } else {
                $_SESSION['sign_up_error_msg'] .= ERROR_SIGN_UP_AVATAR_UPLOAD."<br>\n";
                $error_encountered = true;
            }
        } else {
            $avatar_file = 'app/data/avatar_placeholder.jpg';
        }

        if (!$error_encountered) {
            $role = 'member';

            $this->createNewMember($username, $password, $email, $role, $avatar_file);

            $_SESSION['sign_up_error_msg'] .= NOTICE_SIGN_UP_SUCCESS."<br>\n";
        }
        header('Location:'.$_SESSION['log_in_page']);
    }

    /**
     * Deletes existing members.
     *
     * @param string $username The username used to log in
     */
    public function deleteMember($username)
    {
        unset($this->members['members'][$username]);
        $this->members['last_modified'] = date(DateTime::ISO8601);
        $this->updateMembers($this->members_json);
    }

    /**
     * Retrieves an entry for an existing member and returns it.
     *
     * @param string $username The username used for log in
     * @param string $entry    The entry to be returned (e.g. avatar_file, is_banned etc.)
     *
     * @return [type] [description]
     */
    public function getMemberData($username, $entry)
    {
        return $this->members['members'][$username][$entry];
    }

    /**
     * Sets a certain entry in the member data of an existing member to a specified value.
     *
     * @param string $username The username used for log in
     * @param string $entry    The entry to be set (e.g. member_since, role, is_banned)
     * @param [type] $data     [description]
     */
    public function setMemberData($username, $entry, $data)
    {
        $this->members['members'][$username][$entry] = $data;
        $this->updateMembers($this->members_json);
    }

    /**
     * Updates the members JSON files.
     *
     * @param string $members_json Path to the members JSON file
     */
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

    /**
     * Logs member into account.
     *
     * @param string $username The username used for login
     * @param string $password The password used for login
     */
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
                $_SESSION['member_is_logged_in'] = false;
                $_SESSION['login_error_msg'] = 'LOGIN_AUTH_FAIL';
                session_regenerate_id();
                header('Location:'.$_SESSION['log_in_page']);
            }
        }
    }

    /**
     * Logs currently logged in member out of their account.
     */
    public function logoutMember()
    {
        $_SESSION['member_is_logged_in'] = false;
        unset($_SESSION['member_username']);
        unset($_SESSION['member_role']);
        $_SESSION['login_error_msg'] = 'LOGOUT';
        session_regenerate_id();
        header('Location:'.$_SESSION['log_in_page']);
    }

    /**
     * Generates and returns the HTML to display the auth form.
     *
     * @return string HTML markup of the auth form
     */
    public function displayAuthForm()
    {
        $html = '<h3>'.TITLES_AUTH_FORM.'</h3>';
        if ($_SESSION['member_is_logged_in']) {
            $html .= '
            <form class="commentia-logout-form" action="api.php" method="POST">
                <input type="hidden" name="action" value="logoutMember">
                <input type="submit" name="log-out" value="'.AUTH_FORM_BUTTONS_LOG_OUT.'">
            </form>
            <p>Logged in as '.$_SESSION['member_username'].' with role '.$_SESSION['member_role'].'</p>';
        } else {
            $html .= '
            <form class="commentia-login-form" action="api.php" method="POST">
                <table>
                    <tbody>
                        <tr>
                            <td>'.AUTH_FORM_LABELS_USERNAME.'</td>
                            <td><input type="text" name="username"></td>
                        </tr>
                        <tr>
                            <td>'.AUTH_FORM_LABELS_PASSWORD.'</td>
                            <td><input type="password" name="password"></td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="loginMember">
                <input type="submit" name="log-in" value="'.AUTH_FORM_BUTTONS_LOG_IN.'">
            </form>';
        }
        $html .= '<p>'.$_SESSION['login_error_msg'].'</p>';
        $_SESSION['login_error_msg'] = '';

        $isSecure = false;

        $this->setLoginPage();

        return $html;
    }

    /**
     * Generates and returns the HTML to display the sign up form.
     *
     * @return string HTML markup of the sign up form
     */
    public function displaySignUpForm()
    {
        $sign_up_error_msg = isset($_SESSION['sign_up_error_msg']) ? $_SESSION['sign_up_error_msg'] : '';
        $html = '<h3>'.TITLES_SIGN_UP_FORM.'</h3>';
        $html .= '
        <form class="commentia-signup-form" action="api.php" method="POST" enctype="multipart/form-data">
            <table>
                <tbody>
                    <tr>
                        <td>'.SIGN_UP_FORM_LABELS_USERNAME.'</td>
                        <td><input type="text" name="username"></td>
                    </tr>
                    <tr>
                        <td>'.SIGN_UP_FORM_LABELS_PASSWORD.'</td>
                        <td><input type="password" name="password"></td>
                    </tr>
                    <tr>
                        <td>'.SIGN_UP_FORM_LABELS_RETYPE_PASSWORD.'</td>
                        <td><input type="password" name="retyped_password"></td>
                    </tr>
                    <tr>
                        <td>'.SIGN_UP_FORM_LABELS_EMAIL.'</td>
                        <td><input type="text" name="email"></td>
                    </tr>
                    <tr>
                        <td>'.SIGN_UP_FORM_LABELS_AVATAR.'</td>
                        <td><input type="file" name="avatar_img"></td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="action" value="signUpMember">
            <input type="submit" name="log-in" value="'.SIGN_UP_FORM_BUTTONS_SIGN_UP.'">
        </form>
        <p>'.$sign_up_error_msg.'</p>';

        $_SESSION['sign_up_error_msg'] = '';

        $this->setLoginPage();

        return $html;
    }

    /**
     * When called, sets login page to a session variable (because $_SERVER['REQUEST_URI'] is very unreliable).
     */
    private function setLoginPage()
    {
        $isSecure = false;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }

        $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';

        $_SESSION['log_in_page'] = $REQUEST_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    }
}
