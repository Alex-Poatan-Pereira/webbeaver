<?php
session_start();

ini_set('display_errors', 0);  // 화면에 PHP 오류 출력 안 함
ini_set('log_errors', 1);       // 오류를 서버 로그에 기록
error_reporting(E_ALL);         // 모든 오류 로깅

// 로그인 상태 확인
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');  // 로그인되지 않으면 로그인 페이지로 리디렉션
    exit();
}

// DB 연결 정보 (MariaDB 연결)
$host = 'localhost';
$username = 'root';  // MariaDB의 사용자명
$password = 'thesleepf';  // MariaDB의 비밀번호
$database = 'webbeaver';  // 기본 DB 설정

// MariaDB 연결
$conn = new mysqli($host, $username, $password, $database);

// 연결 오류 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 데이터베이스 목록 가져오기
$sql = "SHOW DATABASES";
$db_result = $conn->query($sql);

// 선택된 DB 테이블 목록 가져오기
$table_result = null;
if (isset($_GET['db'])) {
    $selected_db = $_GET['db'];
    $conn->select_db($selected_db);
    $table_sql = "SHOW TABLES";
    $table_result = $conn->query($table_sql);
} else {
    $selected_db = null;
}

// 선택된 테이블의 데이터 가져오기
$table_data_result = null;
if (isset($_GET['table']) && isset($_GET['db'])) {
    $selected_table = $_GET['table'];
    $table_data_sql = "SELECT * FROM $selected_table"; // 모든 데이터 가져오기
    $table_data_result = $conn->query($table_data_sql);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // JSON 데이터 읽기
    $inputData = json_decode(file_get_contents("php://input"), true);

    // 데이터를 올바르게 파싱했는지 확인
    if ($inputData === null) {
        echo json_encode(["success" => false, "error" => "잘못된 JSON 형식입니다."]);
        exit();
    }

    // 확인: 파싱된 데이터가 제대로 들어왔는지 로그로 확인
    error_log(json_encode($inputData)); // 여기서 로그 확인

    if (isset($inputData['table_name']) && isset($inputData['changedData'])) {
        $table_name = $inputData['table_name'];  // 테이블 이름
        $changedData = $inputData['changedData'];  // 수정된 데이터

        if (empty($changedData)) {
            echo json_encode(["success" => false, "error" => "변경된 데이터가 없습니다."]);
            exit();
        }

        // 데이터베이스에서 데이터 업데이트
        foreach ($changedData as $data) {
            $id = $data['id'];  // 행의 ID
            $column = $data['column'];  // 수정된 컬럼
            $value = $data['value'];  // 수정된 값

            // SQL 쿼리로 데이터 업데이트
            $sql = "UPDATE $table_name SET $column = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            // 값 바인딩 확인
            if ($stmt === false) {
                echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
                exit();
            }

            // 파라미터 바인딩 (예: 문자열 값과 정수값 바인딩)
            $stmt->bind_param("si", $value, $id);

            if (!$stmt->execute()) {
                echo json_encode(["success" => false, "error" => $conn->error]);
                exit();
            }
        }

        // 변경 사항 반영 후에 다시 조회해서 확인하기
        $select_sql = "SELECT * FROM $table_name";
        $select_result = $conn->query($select_sql);
        $rows = [];
        while ($row = $select_result->fetch_assoc()) {
            $rows[] = $row;
        }

        echo json_encode(["success" => true, "data" => $rows]);
    } else {
        echo json_encode(["success" => false, "error" => "필요한 데이터가 없습니다."]);
    }
}




// 쿼리 실행 처리
$query_result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query'])) {
    $query = trim($_POST['query']);  // 쿼리에서 불필요한 공백 제거

    if (!empty($query)) {  // 쿼리가 비어 있지 않은 경우에만 실행
        if ($query_result = $conn->query($query)) {
            if ($query_result->num_rows == 0) {
                $query_result = '결과가 없습니다.';
            }
        } else {
            $query_result = "쿼리 실행 오류: " . $conn->error;
        }
    } else {
        $query_result = "쿼리가 비어 있습니다. 쿼리를 입력하세요.";
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB 관리</title>
    <style>
        /* 전체 스타일 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        #container {
            display: flex;
            margin: 10px;
        }
        #db-list {
            width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            max-height: 80vh;
            overflow-y: auto;
        }
        .db-item {
            padding: 10px;
            cursor: pointer;
            background-color: #f1f1f1;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .db-item:hover {
            background-color: #ddd;
        }
        .db-item.active {
            background-color: #4CAF50;
            color: white;
        }
        .tables-list {
            padding-left: 20px;
            display: none;
            margin-top: 10px;
        }
        .table-item {
            background-color: #e9e9e9;
            padding: 10px;
            border-radius: 3px;
            margin: 3px 0;
            cursor: pointer;
        }
        .table-item:hover {
            background-color: #d4d4d4;
        }
        .table-item.selected {
            background-color: #00aaff;
            color: white;
        }
        #table-data {
            margin-left: 30px;
            padding: 10px;
            flex-grow: 1;
            max-width: 50%;
            height: 80vh;
            overflow-y: auto;  /* 세로 스크롤 */
            overflow-x: visible; /* 가로 스크롤 영역 밖으로 뺀다 */
        }

        

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            word-wrap: break-word;
        }

        .table-data table {
            width: 100%;  /* 테이블의 폭을 100%로 설정 */
            min-width: 100%;  /* 최소 폭을 100%로 설정 */
            white-space: nowrap;  /* 셀 내용이 한 줄로 표시되도록 */
        }

        /* 검색 버튼 스타일 */
        #search {
            margin-bottom: 20px;
            padding: 5px;
            width: 200px;
        }
        /* 변경사항 저장 버튼 */
        .save-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .save-button:hover {
            background-color: #45a049;
        }
        /* 쿼리 섹션 스타일 */
        #query-section {
            display: flex;
            flex-direction: column;
            gap: 0px;
        }
        #query-section textarea {
            width: 95%;
            height: 400px;  /* 고정 높이 */
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow-y: auto;  /* 내용이 길어지면 세로 스크롤 추가 */
            resize: none;
        }

        #query-section button {
            margin-top: 0;  /* 버튼과 쿼리 입력창 사이의 간격 없애기 */
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        #query-section button:hover {
            background-color: #45a049;
        }

        .query-result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            white-space: pre-wrap;
            max-height: 300px;  /* 결과 영역의 최대 높이 */
            overflow-y: auto;   /* 세로 스크롤 추가 */
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="db-list">
            <h3>데이터베이스 목록</h3>
            <?php if ($db_result && $db_result->num_rows > 0): ?>
                <?php while ($row = $db_result->fetch_assoc()): ?>
                    <div id="db-<?php echo urlencode($row['Database']); ?>" class="db-item" onclick="toggleTables('<?php echo urlencode($row['Database']); ?>')">
                        <?php echo htmlspecialchars($row['Database']); ?>
                    </div>
                    <?php if (isset($_GET['db']) && $_GET['db'] === $row['Database'] && $table_result && $table_result->num_rows > 0): ?>
                        <div id="tables-<?php echo urlencode($row['Database']); ?>" class="tables-list">
                            <?php while ($table_row = $table_result->fetch_assoc()): ?>
                                <div id="table-<?php echo urlencode($table_row['Tables_in_' . $row['Database']]); ?>" class="table-item" onclick="loadTableData('<?php echo urlencode($row['Database']); ?>', '<?php echo urlencode($table_row['Tables_in_' . $row['Database']]); ?>')">
                                    <?php echo htmlspecialchars($table_row['Tables_in_' . $row['Database']]); ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p>데이터베이스가 없습니다.</p>
            <?php endif; ?>
        </div>

        <div id="table-data">
            <?php if (isset($table_data_result) && $table_data_result->num_rows > 0): ?>
                <h3>테이블: <?php echo htmlspecialchars($selected_table); ?></h3>
                <input type="text" id="search" onkeyup="searchTable()" placeholder="검색..." />
                <form id="dataForm">
                    <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selected_table); ?>" />
                    <button type="button" id="saveChangesButton" class="save-button">변경사항 저장</button>
                    <div class="table-data">
                        <table>
                            <thead>
                                <tr>
                                    <?php while ($field_info = $table_data_result->fetch_field()): ?>
                                        <th><?php echo htmlspecialchars($field_info->name); ?></th>
                                    <?php endwhile; ?>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <?php while ($row = $table_data_result->fetch_assoc()): ?>
                                    <tr>
                                        <?php foreach ($row as $column => $value): ?>
                                            <td contenteditable="true" name="data[<?php echo $row['id']; ?>][<?php echo $column; ?>]" data-id="<?php echo $row['id']; ?>" data-column="<?php echo $column; ?>">
                                                <?php echo htmlspecialchars($value); ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php else: ?>
                <p>테이블 데이터가 없습니다.</p>
            <?php endif; ?>
        </div>

        <div id="query-section">
            <form method="POST">
                <textarea name="query" placeholder="SQL 쿼리를 입력하세요"></textarea><br>
                <button type="submit">쿼리 실행</button>
            </form>

            <div class="query-result">
                <?php
                if ($query_result) {
                    if (is_string($query_result)) {
                        echo $query_result;
                    } else {
                        echo "<table><tr>";
                        $fields = $query_result->fetch_fields();
                        foreach ($fields as $field) {
                            echo "<th>{$field->name}</th>";
                        }
                        echo "</tr>";

                         while ($row = $query_result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $col => $val) {
                if (isset($row['id'])) {
                    echo "<td contenteditable='true' data-column='$col' data-id='{$row['id']}'>" . htmlspecialchars($val) . "</td>";
                } else {
                    echo "<td contenteditable='true' data-column='$col'>" . htmlspecialchars($val) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}
                echo "<button onclick='saveQueryResults()' class='save-button' style='margin-top: 10px;'>변경사항 저장</button>";
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 페이지 로딩 시 URL에서 DB와 Table 파라미터 가져오기
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDb = urlParams.get('db');
            const selectedTable = urlParams.get('table');

            // URL에 있는 DB 값에 맞는 DB 목록에 'active' 클래스 추가
            if (selectedDb) {
                const selectedDbItem = document.getElementById('db-' + selectedDb);
                if (selectedDbItem) {
                    selectedDbItem.classList.add('active');
                }

                // 해당 DB의 테이블 목록 보이기
                const tableList = document.getElementById('tables-' + selectedDb);
                if (tableList) {
                    tableList.style.display = 'block';  // 선택된 DB의 테이블 목록이 계속 보이도록 설정
                }
            }

            // URL에 있는 테이블 값에 맞는 테이블 선택 표시
            if (selectedTable) {
                const selectedTableItem = document.getElementById('table-' + selectedTable);
                if (selectedTableItem) {
                    selectedTableItem.classList.add('active');
                }
            }
        });

        function toggleTables(db) {
            console.log("toggleTables called for: " + db);

            // 선택된 데이터베이스를 localStorage에 저장 (새로고침해도 유지)
            localStorage.setItem("selectedDB", db);
            localStorage.removeItem("selectedTable"); // DB 변경 시 기존 테이블 선택 해제

            // 모든 데이터베이스 리스트에서 'active' 제거
            document.querySelectorAll('.db-item').forEach(item => item.classList.remove('active'));

            // 선택한 데이터베이스에 'active' 클래스 추가
            const selectedDbItem = document.getElementById('db-' + db);
            if (selectedDbItem) {
                selectedDbItem.classList.add('active');
            }

            
            // 선택한 데이터베이스의 테이블 목록 표시
            const tableList = document.getElementById('tables-' + db);
            if (tableList) {
                tableList.style.display = 'block'; // 선택된 DB의 테이블 목록은 계속 보이도록 유지
            }

            // 선택한 데이터베이스가 URL에 반영되도록 변경
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('db', db); // 선택한 DB를 URL에 추가
            
            // 페이지를 새로고침하지 않고 URL을 변경하는 방법
            window.history.pushState({}, '', `${window.location.pathname}?${urlParams.toString()}`);
        }


        function loadTableData(db, table) {
            console.log(`Loading table: ${table} from DB: ${db}`);

            // 선택한 테이블을 localStorage에 저장 (새로고침해도 유지)
            localStorage.setItem("selectedTable", table);

            // URL 업데이트 (DB + Table 선택 시)
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('db', db);
            urlParams.set('table', table);
            window.location.search = urlParams.toString();
        }

        
        
        function searchTable() {
            const input = document.getElementById('search');
            const filter = input.value.toUpperCase();
            const rows = document.getElementById('table-body').getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].innerText.toUpperCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? "" : "none";
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            let changesMade = false;
            let modifiedData = [];

            document.querySelectorAll('td[contenteditable="true"]').forEach(cell => {
                cell.addEventListener('focus', function () {
                    this.dataset.originalValue = this.textContent.trim();
                });

                cell.addEventListener('input', function () {
                    const newValue = this.textContent.trim();
                    const originalValue = this.dataset.originalValue;
                    const rowId = this.dataset.id;
                    const columnName = this.dataset.column;

                    if (!rowId || !columnName) return;

                    // 변경 사항이 있을 경우만 저장
                    if (newValue !== originalValue) {
                        changesMade = true;

                        // 기존 데이터가 있으면 업데이트
                        const existingIndex = modifiedData.findIndex(item => item.id === rowId && item.column === columnName);
                        if (existingIndex > -1) {
                            modifiedData[existingIndex].value = newValue;
                        } else {
                            modifiedData.push({ id: rowId, column: columnName, value: newValue });
                        }
                    }
                });
            });

            document.getElementById("saveChangesButton")?.addEventListener("click", function (event) {
                event.preventDefault();
                if (!changesMade) {
                    alert("변경된 내용이 없습니다.");
                    return;
                }

                if (confirm("변경사항을 저장하시겠습니까?")) {
                    updateDatabase(modifiedData);
                }
            });

            function updateDatabase(modifiedData) {
                const tableName = getCurrentTable();
                if (!tableName) {
                    alert("테이블 이름을 찾을 수 없습니다.");
                    return;
                }

                const data = {
                    table_name: tableName,
                    changedData: modifiedData
                };

                fetch("db_manager.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert("변경사항이 MariaDB에 반영되었습니다.");
                        location.reload(); // 저장 후 새로고침
                    } else {
                        alert("변경사항 반영 실패: " + (result.error || "알 수 없는 오류"));
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("서버에 문제가 발생했습니다.");
                });
            }
        });






    </script>
</body>
</html>