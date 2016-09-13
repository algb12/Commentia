<?php

//////////////////////////////////////////////////////////////
// Member roles                                             //
// This class has methods which return the role of a member //
// Author: algb12.19@gmail.com                                //
//////////////////////////////////////////////////////////////


namespace Commentia\Roles;

class Roles
{
    /**
     * Checks if currently logged in member has specified username and returns result.
     *
     * @param string $creator_username The username of the comment creator
     *
     * @return bool Whether it is the requested member ot not
     */
    public function memberHasUsername($creator_username)
    {
        return $creator_username === $_SESSION['member_username'];
    }

    /**
     * Checks if currently logged in member is admin and returns result.
     *
     * @return bool Whether member is admin or not
     */
    public function memberIsAdmin()
    {
        return $_SESSION['member_role'] === 'admin';
    }
}
