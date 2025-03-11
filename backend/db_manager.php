<?php
session_start();

ini_set('display_errors', 0);  // 화면에 PHP 오류 출력 안 함
ini_set('log_errors', 1);       // 오류를 서버 로그에 기록
error_reporting(E_ALL);         // 모든 오류 로깅

// 로그인 상태 확인
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');  // 로그인되지 않으면 로그인 페이지로 리디렉션
    exit();
}

// DB 연결 정보 (MariaDB 연결)
$host = 'localhost';
$username = 'root';  // MariaDB의 사용자명
$password = 'thesleepf';  // MariaDB의 비밀번호
$database = 'webbeaver';  // 기본 DB 설정

// DB 연결 함수
function getDBConnection($database = null) {
    global $host, $username, $password;

    // MariaDB 연결
    $conn = new mysqli($host, $username, $password, $database);

    // 연결 오류 확인
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// 데이터베이스 목록 가져오기
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['db']) && !isset($_GET['table'])) {
    $conn = getDBConnection();
    $db_result = $conn->query("SHOW DATABASES");

    if ($db_result) {
        $databases = [];
        while ($row = $db_result->fetch_assoc()) {
            $databases[] = $row['Database'];
        }
        echo json_encode(["databases" => $databases]);
    } else {
        echo json_encode(["error" => "데이터베이스 목록을 가져오는 데 실패했습니다."]);
    }
    exit();
}

// 선택된 DB 테이블 목록 가져오기
if (isset($_GET['db']) && !isset($_GET['table'])) {
    $selected_db = $_GET['db'];
    $conn = getDBConnection($selected_db); // 선택된 DB로 연결
    $table_sql = "SHOW TABLES";
    $table_result = $conn->query($table_sql);

    if ($table_result) {
        $tables = [];
        while ($row = $table_result->fetch_assoc()) {
            $tables[] = $row['Tables_in_' . $selected_db];
        }
        echo json_encode(["tables" => $tables]);
    } else {
        echo json_encode(["error" => "테이블 목록을 가져오는 데 실패했습니다."]);
    }
    exit();
}

// 선택된 테이블에서 데이터 가져오기
if (isset($_GET['db']) && isset($_GET['table'])) {
    $selected_db = $_GET['db'];
    $selected_table = $_GET['table'];

    $conn = getDBConnection($selected_db); // 선택된 DB로 연결
    $table_data_sql = "SELECT * FROM `$selected_table`"; // 테이블의 모든 데이터 가져오기
    $table_data_result = $conn->query($table_data_sql);

    if ($table_data_result) {
        $rows = [];
        while ($row = $table_data_result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(["data" => $rows]);
    } else {
        echo json_encode(["error" => "테이블 데이터를 가져오는 데 실패했습니다."]);
    }
    exit();
}

$conn->close();
?>
