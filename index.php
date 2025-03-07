<!-- index.php -->
<?php
session_start(); // 세션 시작

// 로그인된 사용자 정보 확인
if (isset($_SESSION['user_id'])) {
    echo "환영합니다, " . $_SESSION['username'] . "님!";
    // 추가적인 로그인 후 콘텐츠 표시
    echo "<br><a href='logout.php'>로그아웃</a>";
} else {
    echo "로그인되지 않았습니다.";
    // 로그인 페이지로 리디렉션
    header("Location: backend/login.php");
    exit();
}
?>
