<?php
session_start();
session_destroy();
header('Location: ../get_started.php');
exit;
