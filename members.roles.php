<?php

session_start();

class Roles {

  public function memberHasUsername($creator_username) {
    if ($creator_username === $_SESSION['member_username']) {
      return true;
    }
  }

  public function memberIsAdmin() {
    if ($_SESSION['member_role'] === "admin") {
      return true;
    }
  }

}
