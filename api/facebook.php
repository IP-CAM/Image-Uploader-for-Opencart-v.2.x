<?php

require 'redirect.php';
$redirect = new Redirect($_GET['code'], 'facebook');
$redirect->getAccessFacebook();

?>
