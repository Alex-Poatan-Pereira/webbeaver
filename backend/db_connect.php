<?php
session_start();

// 로그인 상태 확인
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');  // 로그인되지 않으면 로그인 페이지로 리디렉션
    exit();
}

// DB 연결 정보
$host = 'localhost';
$username = 'root';
$password = 'thesleepf';
$database = 'webbeaver';  // 실제 데이터베이스 이름

// DB 연결
$conn = new mysqli($host, $username, $password, $database);

// 연결 오류 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 폴더 및 파일 목록 가져오기
$sql = "SELECT folder_name, file_name, file_path FROM file_structure";
$result = $conn->query($sql);

// 폴더별 파일 목록 저장
$folders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $folders[$row['folder_name']][] = [
            'file_name' => $row['file_name'],
            'file_path' => $row['file_path']
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB 관리자</title>
</head>
<body>
    <h1>DB 관리자</h1>
    <p>로그인 되었습니다.</p>
    <h2>파일 목록</h2>

    <?php foreach ($folders as $folder_name => $files): ?>
        <h3><?php echo htmlspecialchars($folder_name); ?> 폴더</h3>
        <ul>
            <?php foreach ($files as $file): ?>
                <li><a href="<?php echo htmlspecialchars($file['file_path']); ?>"><?php echo htmlspecialchars($file['file_name']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</body>
</html>
