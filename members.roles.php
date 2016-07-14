<?php

session_start();

class Roles {

  public function memberHasUsername($creator_username) {
    return $creator_username === $_SESSION['member_username'];
  }

  public function memberIsAdmin() {
    return $_SESSION['member_role'] === 'admin';
  }
}
