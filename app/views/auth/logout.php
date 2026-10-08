<?php
session_start();
session_destroy();
header("Location: /Self-Management/index.php");
exit();
