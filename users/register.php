<?php
$username = $password = $password_confirm = $nickname = "";
$wu = $wp = 0; // 변수 초기화

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $password_confirm = $_POST['password_confirm'];
  $nickname = $_POST['nickname']; // 닉네임을 POST 데이터에서 가져옵니다.

  if ($username != "") {
    $jb_conn = mysqli_connect('127.0.0.1', 'thesleepf', 'thesleepf', 'webbeaver');
    if (!$jb_conn) {
      die("Connection failed: " . mysqli_connect_error());
    }

    // SQL 인젝션 방지
    $username = mysqli_real_escape_string($jb_conn, $username); // SQL 인젝션 방지 처리
    $nickname = mysqli_real_escape_string($jb_conn, $nickname); // 닉네임에 대해서도 SQL 인젝션 방지 처리

    $jb_sql = "SELECT username FROM users WHERE username = '$username';";
    $jb_result = mysqli_query($jb_conn, $jb_sql);

    if ($jb_result && mysqli_fetch_array($jb_result)) {
      $wu = 1; // 사용자 이름이 이미 존재합니다.
    } elseif ($password !== $password_confirm) {
      $wp = 1; // 비밀번호가 일치하지 않습니다.
    } else {
      $encrypted_password = password_hash($password, PASSWORD_DEFAULT);
      $jb_sql_add_user = "INSERT INTO users (username, password, nickname) VALUES ('$username', '$encrypted_password', '$nickname');";
      if (!mysqli_query($jb_conn, $jb_sql_add_user)) {
        die("Error: " . $jb_sql_add_user . "<br>" . mysqli_error($jb_conn));
      }
      header('Location: register-check.php');
      exit;
    }
    mysqli_close($jb_conn);
  }
}
?>
<!doctype html>
<html lang="ko">
  <head>
    <meta charset="utf-8">
    <title>회원 가입</title>
  </head>
  <body>
    <h1>회원 가입</h1>
    <form action="register.php" method="POST">
      <p><input type="text" name="username" placeholder="사용자 ID" required></p>
      <p><input type="password" name="password" placeholder="비밀번호" required></p>
      <p><input type="password" name="password_confirm" placeholder="비밀번호 확인" required></p>
      <p><input type="text" name="nickname" placeholder="닉네임" required></p> 
      <p><input type="submit" value="회원 가입"></p>
      <?php
        if ($wu === 1) {
          echo "<p>사용자 ID가 중복되었습니다.</p>";
        }
        if ($wp === 1) {
          echo "<p>비밀번호가 일치하지 않습니다.</p>";
        }
      ?>
    </form>
  </body>
</html>
