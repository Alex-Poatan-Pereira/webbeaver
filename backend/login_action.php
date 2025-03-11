<?php
session_start();

// 하드코딩된 사용자명과 해시화된 비밀번호 (예시: 비밀번호 'thesleepf'를 hash한 값)
$valid_username = 'thesleepf';  
$hashed_password = '$2y$12$xSulMJbB0iVEtr314xzahO3wfg9hialq7mE6Oo1DTHIy5QZR9wMoi';  // 실제 비밀번호 'thesleepf'에 해당하는 해시값

// 로그인 폼에서 입력된 값 받기
$username = $_POST['username'];
$password = $_POST['password'];

// 비밀번호 입력값이 비어있는지 체크
if (empty($password)) {
    echo "비밀번호를 입력해주세요.";
    exit();
}

// 사용자명과 비밀번호 비교
if ($username === $valid_username && password_verify($password, $hashed_password)) {
    // 로그인 성공 시 세션 저장
    $_SESSION['logged_in'] = true;
    session_regenerate_id(true);  // 세션 ID 재생성 (세션 하이재킹 방지)
    header('Location: ../frontend/db_manager.html');  // 로그인 후 DB 관리 페이지로 리디렉션
    exit();
} else {
    // 로그인 실패 시 에러 메시지 출력
    echo "로그인 실패: 사용자명 또는 비밀번호가 틀립니다.";
}
?>
