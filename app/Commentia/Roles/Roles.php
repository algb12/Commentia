<?php

// Member roles
// This class has methods which return the role of a member
// Author: Alexander Gilburg
// Last updated: 15th of July 2016

namespace Commentia\Roles;

class Roles
{
    public function memberHasUsername($creator_username)
    {
        return $creator_username === $_SESSION['member_username'];
    }

    public function memberIsAdmin()
    {
        return $_SESSION['member_role'] === 'admin';
    }
}
