<!DOCTYPE html>
<?php session_start(); ?>
<html>
        <head>
                <meta charset="utf-8" />
                <title>PHP Session Login</title>
        </head>
        <body>
                <div class="container">
                        <h1>Webbeaver</h1>
                <?php
                        if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) { ?>
                <form method="post" action="login-check.php">
                        <p>아이디: <input type="text" name="user_id"/> </p>
                        <p>비밀번호: <input type="password" name="user_pw"/> </p>
                        <a href="register.php">[회원가입]</a></p>
                        <p><input type="submit" value="로그인"/> </p>
                </form>
                <?php } else {
                        $user_id = $_SESSION['user_id'];
                        $user_name = $_SESSION['user_name'];
                        echo "<a href=\"../index.php\">[돌아가기]</a> ";
                        echo "<a href=\"logout.php\">[로그아웃]</a></p>";
                } ?>
        </body>
</html>
