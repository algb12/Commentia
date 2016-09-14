<?php

////////////////////////////////////////////////////
// Members model                                  //
// This file contains the members model,          //
// And the methods used to interact with them.    //
// Author: algb12.19@gmail.com                      //
////////////////////////////////////////////////////

namespace Commentia\Models;

use Commentia\Metadata\Metadata;
use Commentia\DBHandler\DBHandler;
use DateTime;

class Members
{
    public $db;
    public $members = array();

    /**
     * Initializes DB, checks if user is logged in
     */
    public function __construct()
    {
        $this->db = new DBHandler(DB);

        if (!isset($_SESSION['__COMMENTIA__']['member_username'])) {
            $_SESSION['__COMMENTIA__']['member_is_logged_in'] = false;
        }

        if (!isset($_SESSION['__COMMENTIA__']['login_error_msg'])) {
            $_SESSION['__COMMENTIA__']['login_error_msg'] = '';
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

        $image_file_no_ext = (AVATAR_DIR.uniqid('avatar_', true));

        switch (strtolower(pathinfo($src, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                $image_file = $image_file_no_ext.'.jpg';
                imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $crop_w, $crop_h, $orig_w, $orig_h);
                imagejpeg($dest_image, $image_file);
            break;

            case 'png':
                $image_file = $image_file_no_ext.'.png';
                imagealphablending($dest_image, false);
                imagesavealpha($dest_image, true);
                imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $crop_w, $crop_h, $orig_w, $orig_h);
                imagepng($dest_image, $image_file);
            break;

            case 'gif':
                $image_file = $image_file_no_ext.'.gif';
                imagealphablending($dest_image, false);
                $trans_index = imagecolortransparent($source_image);
                if ($trans_index >= 0) {
                    $trans_index = imagecolortransparent($source_image);
                    $trans_col = imagecolorsforindex($source_image, $trans_index);
                    $trans_index = imagecolorallocatealpha(
                        $dest_image,
                        $trans_col['red'],
                        $trans_col['green'],
                        $trans_col['blue'],
                        127
                    );
                    imagefill($dest_image, 0, 0, $trans_index);
                }

                imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $crop_w, $crop_h, $orig_w, $orig_h);

                if ($trans_index >= 0) {
                    imagecolortransparent($dest_image, $trans_index);
                    for ($y = 0; $y < $crop_h; ++$y) {
                        for ($x = 0; $x < $crop_w; ++$x) {
                            if (((imagecolorat($dest_image, $x, $y) >> 24) & 0x7F) >= 100) {
                                imagesetpixel(
                                    $dest_image,
                                    $x,
                                    $y,
                                    $trans_index
                                );
                            }
                        }
                    }
                }

                imagetruecolortopalette($dest_image, true, 255);
                imagegif($dest_image, $image_file);
            break;
            default:
                throw new InvalidArgumentException('File "'.$src.'" is not valid jpg, png or gif image.');
            break;
        }

        if ($src !== 'app/data/avatars/placeholder/avatar_placeholder.jpg') {
            unlink($src);
        }

        return basename($image_file);
    }

    /**
     * Writes new account data to DB.
     *
     * @param string $username    The username used to log in
     * @param string $password    The password to be hashed and used for log in
     * @param string $email       The email of the Members
     * @param string $role        The role of the new member (e.g. member, admin, guest etc.)
     * @param string $avatar_file The path to the member's avatar image thumbnail
     */
    private function createNewMember($username, $password, $email, $role, $avatar_file)
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $avatar_file = $this->generateAvatarThumbnail($avatar_file, 150, 150);
        $is_banned = false;
        $member_since = date(DateTime::ISO8601);

        $stmt = $this->db->prepare('INSERT INTO members (username, password_hash, email, avatar_file, is_banned, role, member_since) VALUES (
                                    :username, :password_hash, :email, :avatar_file, :is_banned, :role, :member_since);');

        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password_hash', $password_hash);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':avatar_file', $avatar_file);
        $stmt->bindValue(':is_banned', $is_banned);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':member_since', $member_since);

        $stmt->execute();

        return true;
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
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_MISSING_USERNAME."<br>\n";
            $error_encountered = true;
        } elseif ($this->getMemberData($username, 'username')) {
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_USERNAME_TAKEN."<br>\n";
            $error_encountered = true;
        }

        if (empty($password) || $password === '') {
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_MISSING_PASSWORD."<br>\n";
            $error_encountered = true;
        } elseif ($password !== $retyped_password) {
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_PASSWORD_MISMATCH."<br>\n";
            $error_encountered = true;
        }

        if (strlen($password) < MIN_PASSWORD_LEN) {
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_PASSWORD_INSECURE."<br>\n";
            $error_encountered = true;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_INVALID_EMAIL."<br>\n";
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
                $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= ERROR_SIGN_UP_AVATAR_UPLOAD."<br>\n";
                $error_encountered = true;
            }
        } else {
            $avatar_file = 'app/data/avatars/placeholder/avatar_placeholder.jpg';
        }

        if (!$error_encountered) {
            $role = 'member';

            $member_created = $this->createNewMember($username, $password, $email, $role, $avatar_file);

            if ($member_created) {
                $_SESSION['__COMMENTIA__']['sign_up_error_msg'] .= NOTICE_SIGN_UP_SUCCESS."<br>\n";
            }
        }
        header('Location:'.$_SESSION['__COMMENTIA__']['log_in_page']);
    }

    /**
     * Deletes existing members.
     *
     * @param string $username The username used to log in
     */
    public function deleteMember($username)
    {
        $stmt = $this->db->prepare('DELETE FROM members WHERE username = :username');
        $stmt->bindValue(':username', $username);
        $stmt->execute();
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
        $stmt = $this->db->prepare('SELECT * FROM members WHERE username = :username');
        $stmt->bindValue(':username', $username);
        $res = $stmt->execute();

        $entry_data = $res->fetchArray(SQLITE3_ASSOC)[$entry];

        if ($entry === 'avatar_file') {
            $result = ABS_PATH_PREFIX.AVATAR_DIR.$entry_data;
        } else {
            $result = $entry_data;
        }

        return $result;
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
        $stmt = $this->db->prepare('UPDATE members SET :entry = :data WHERE username = :username');
        $stmt->bindValue(':entry', $entry);
        $stmt->bindValue(':data', $data);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
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
                $_SESSION['__COMMENTIA__']['member_is_logged_in'] = true;
                $_SESSION['__COMMENTIA__']['member_username'] = $username;
                $_SESSION['__COMMENTIA__']['member_role'] = $this->getMemberData($username, 'role');
                // TODO: Login success message localization
                $_SESSION['__COMMENTIA__']['login_error_msg'] = 'LOGIN_AUTH_SUCCESS';
                session_regenerate_id();
                header('Location:'.$_SESSION['__COMMENTIA__']['log_in_page']);
            } else {
                $_SESSION['__COMMENTIA__']['member_is_logged_in'] = false;
                // TODO: Login fail message localization
                $_SESSION['__COMMENTIA__']['login_error_msg'] = 'LOGIN_AUTH_FAIL';
                session_regenerate_id();
                header('Location:'.$_SESSION['__COMMENTIA__']['log_in_page']);
            }
        }
    }

    /**
     * Logs currently logged in member out of their account.
     */
    public function logoutMember()
    {
        $_SESSION['__COMMENTIA__']['member_is_logged_in'] = false;
        unset($_SESSION['__COMMENTIA__']['member_username']);
        unset($_SESSION['__COMMENTIA__']['member_role']);
        $_SESSION['__COMMENTIA__']['login_error_msg'] = 'LOGOUT';
        session_regenerate_id();
        header('Location:'.$_SESSION['__COMMENTIA__']['log_in_page']);
    }

    /**
     * Generates and returns the HTML to display the auth form.
     *
     * @return string HTML markup of the auth form
     */
    public function displayAuthForm()
    {
        $html = '<h3>'.TITLES_AUTH_FORM.'</h3>';
        if ($_SESSION['__COMMENTIA__']['member_is_logged_in']) {
            $html .= '
            <form class="commentia-logout_form" action="'.ABS_PATH_PREFIX.'api.php" method="POST">
                <input type="hidden" name="action" value="logoutMember">
                <input type="submit" name="log-out" value="'.AUTH_FORM_BUTTONS_LOG_OUT.'">
            </form>
            <p>Logged in as '.$_SESSION['__COMMENTIA__']['member_username'].' with role '.$_SESSION['__COMMENTIA__']['member_role'].'</p>';
        } else {
            $html .= '
            <form class="commentia-login_form" action="'.ABS_PATH_PREFIX.'api.php" method="POST">
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
        $html .= '<p>'.$_SESSION['__COMMENTIA__']['login_error_msg'].'</p>';
        $_SESSION['__COMMENTIA__']['login_error_msg'] = '';

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
        $sign_up_error_msg = isset($_SESSION['__COMMENTIA__']['sign_up_error_msg']) ? $_SESSION['__COMMENTIA__']['sign_up_error_msg'] : '';
        $html = '<h3>'.TITLES_SIGN_UP_FORM.'</h3>';
        $html .= '
        <form class="commentia-signup_form" action="'.ABS_PATH_PREFIX.'api.php" method="POST" enctype="multipart/form-data">
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
                    </tr>
                </tbody>
            </table>
            <input type="file" name="avatar_img">
            <input type="hidden" name="action" value="signUpMember"><br><br>
            <input type="submit" name="sign-up" value="'.SIGN_UP_FORM_BUTTONS_SIGN_UP.'">
        </form>
        <p>'.$sign_up_error_msg.'</p>';

        $_SESSION['__COMMENTIA__']['sign_up_error_msg'] = '';

        $this->setLoginPage();

        return $html;
    }

    /**
     * When called, sets login page to a session variable (because $_SERVER['REQUEST_URI'] is very unreliable).
     */
    public function setLoginPage()
    {
        $isSecure = false;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }

        $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';

        $_SESSION['__COMMENTIA__']['log_in_page'] = $REQUEST_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    }
}
