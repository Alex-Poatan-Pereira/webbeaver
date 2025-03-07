<!-- backend/login.php -->
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
</head>
<body>
    <h2>DB 관리자 로그인</h2>
    <form action="login_action.php" method="POST">
        <label for="username">사용자명:</label>
        <input type="text" name="username" id="username" required><br><br>
        
        <label for="password">비밀번호:</label>
        <input type="password" name="password" id="password" required><br><br>
        
        <input type="submit" value="로그인">
    </form>
</body>
</html>
