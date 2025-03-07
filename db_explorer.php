<?php
session_start();
if (!isset($_SESSION['conn'])) {
    header("Location: index.html"); // 연결되지 않으면 다시 연결 페이지로
    exit();
}

$conn = $_SESSION['conn'];
$dbname = $_SESSION['dbname'];
$tables = $_SESSION['tables'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Explorer</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>DB Explorer</h1>
        <p>데이터베이스: <?php echo $dbname; ?></p>
    </header>

    <section id="dbTables">
        <h2>테이블 목록</h2>
        <ul>
            <?php foreach ($tables as $table): ?>
                <li><?php echo $table; ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section id="querySection">
        <h2>쿼리 실행</h2>
        <form id="queryForm">
            <textarea id="query" name="query" rows="5" placeholder="SQL 쿼리 입력" required></textarea>
            <button type="submit">실행</button>
        </form>
        <div id="queryResults"></div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
