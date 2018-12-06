<?php

require 'redirect.php';
$redirect = new Redirect($_GET['code'], 'instagram');
$redirect->getAccessInstagram();

?>
