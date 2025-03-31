<?php

$db = isset($_POST['db']) ? $_POST['db'] : null;
$conn = new mysqli('127.0.0.1', 'thesleepf', 'thesleepf', $db);
// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 사용자로부터 전송된 쿼리 받기
if (isset($_POST['query'])) {
    $query = $_POST['query'];

    // 쿼리 실행
    if ($result = $conn->query($query)) {
        
        echo "<table border='1'><thead><tr>";

        // 결과 테이블 헤더 출력
        while ($field = $result->fetch_field()) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr></thead><tbody>";

        // 결과 출력
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";

        $result->free(); // 결과 메모리 해제
    } else {
        echo "";
    }
} else {
    echo "";
}

$conn->close();
?>
