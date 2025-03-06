<?php
if($login_successful) {
    $_SESSION['user_id'] = $user['user_id'];
    header('Location: index.php');
    exit();
}
?>