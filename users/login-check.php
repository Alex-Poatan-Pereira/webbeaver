<?php
// 에러 검증용 코드입니다.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // 세션을 시작합니다.


$dbHost = '127.0.0.1'; // 데이터베이스 호스트
$dbUser = 'thesleepf'; // 데이터베이스 사용자 이름
$dbPass = 'thesleepf'; // 데이터베이스 비밀번호
$dbName = 'webbeaver'; // 데이터베이스 이름

// 데이터베이스 연결
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// 연결 오류 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// POST 데이터 검증 및 정제
if (!isset($_POST['user_id']) || !isset($_POST['user_pw'])) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>alert('아이디 또는 비밀번호가 빠졌거나 잘못된 접근입니다.');";
    echo "window.location.replace('login.php');</script>";
    exit;
}

$user_id = $_POST['user_id'];
$user_pw = $_POST['user_pw'];

// 준비된 쿼리 사용 (SQL 인젝션 방어)
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $user_id); // s는 string 타입
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($user_pw, $row['password'])) {
        // 인증 성공, 세션 변수 설정
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['loggedIn'] = true;

        // 페이지 리다이렉션
        header("Location: ../databases/table.php");
        exit;
    } else {
        // 인증 실패
        header("Content-Type: text/html; charset=UTF-8");
        echo "<script>alert('아이디 또는 비밀번호가 잘못되었습니다.');";
        echo "window.location.replace('login.php');</script>";
        exit;
    }
} else {
    // 사용자가 존재하지 않음
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>alert('아이디 또는 비밀번호가 잘못되었습니다.');";
    echo "window.location.replace('login.php');</script>";
    exit;
}


?>
