<?php
// GET 요청으로 받은 데이터 가져오기
$db = isset($_GET['db']) ? $_GET['db'] : null;
$table = isset($_GET['table']) ? $_GET['table'] : null;
$page = isset($_GET['page']) ? $_GET['page'] : null;

// 데이터베이스 연결
$conn = new mysqli('127.0.0.1', 'thesleepf', 'thesleepf', $db);

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

$page_num = ($page-1)*10;

// 테이블 데이터 조회
$sql = "SELECT * FROM $table limit $page_num, 10";
$table_res = $conn->query($sql);





// 결과가 있는지 확인
if ($table_res && $table_res->num_rows > 0) {
    // 컬럼 이름 저장
    
    $fields = [];
    while ($field = $table_res->fetch_field()) {
        $fields[] = $field->name;  // 컬럼 이름을 배열에 저장
    }

    echo "<thead><tr>";
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field) . "</th>";
    }
    echo "</tr></thead>";


    echo "<tbody>";
    // 데이터 출력
    while ($row = $table_res->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</tbody>";
} else {
    echo "<p>데이터가 없습니다.</p>";
}


// 데이터베이스 연결 종료
$conn->close();
?>
