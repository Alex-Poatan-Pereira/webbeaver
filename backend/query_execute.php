<?php
session_start();
if (!isset($_SESSION['conn'])) {
    echo "연결이 필요합니다.";
    exit();
}

$conn = $_SESSION['conn'];
$query = $_POST['query'];

$result = $conn->query($query);

if ($result) {
    // SELECT 쿼리의 경우 결과를 테이블 형식으로 출력
    if ($result->num_rows > 0) {
        // CSV 파일로 다운로드 기능
        if (isset($_POST['download']) && $_POST['download'] == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="query_result.csv"');
            $output = fopen('php://output', 'w');

            // 컬럼명 출력
            $fields = $result->fetch_fields();
            $header = [];
            foreach ($fields as $field) {
                $header[] = $field->name;
            }
            fputcsv($output, $header);

            // 데이터 출력
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit();
        }

        // HTML 테이블 형식으로 출력
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%;'>";
        echo "<tr>";
        
        // 컬럼명 출력
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";

        // 데이터 출력
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $column) {
                echo "<td>" . htmlspecialchars($column) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        // CSV 다운로드 버튼
        echo "<form method='POST'>
                <input type='hidden' name='query' value='" . htmlspecialchars($query) . "'>
                <input type='hidden' name='download' value='csv'>
                <button type='submit'>CSV로 다운로드</button>
              </form>";
    } else {
        echo "결과가 없습니다.";
    }
} else {
    echo "쿼리 실행 실패: " . $conn->error;
}
?>
