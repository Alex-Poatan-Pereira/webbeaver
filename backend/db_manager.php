<?php
session_start();

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
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : null;
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

if (isset($_GET['table']) && isset($_GET['db'])) {
    $selected_table = $_GET['table'];
    $table_data_sql = "SELECT * FROM `$selected_table`";
    
    // 쿼리 정렬 추가
    if ($sort_column) {
        $table_data_sql .= " ORDER BY `$sort_column` $sort_order";
    }
    
    $table_data_result = $conn->query($table_data_sql);
}

// 쿼리 실행 후 데이터 수정
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['data'])) {
    // 수정된 데이터가 있다면
    $updated_data = $_POST['data'];  // 수정된 데이터

    foreach ($updated_data as $row_id => $columns) {
        foreach ($columns as $column => $value) {
            $value = $conn->real_escape_string($value); // 값 escaping

            // 업데이트 쿼리 작성
            $update_sql = "UPDATE `$selected_table` SET `$column` = '$value' WHERE id = '$row_id'";

            if (!$conn->query($update_sql)) {
                echo "쿼리 오류: " . $conn->error;
            }
        }
    }

    // 업데이트 후 페이지 새로고침
    header("Location: db_manager.php?db=$selected_db&table=$selected_table");
    exit();
}


// 페이지 로딩 시 메시지 표시
if (isset($_SESSION['message'])) {
    echo "<div class='message'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);  // 메시지 출력 후 삭제
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
            max-width: 100%;
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
                <form method="POST">
                    <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($selected_table); ?>" />
                    <button type="submit" id="saveChangesButton" class="save-button">변경사항 저장</button>
                    <div class="table-data">
                        <table>
                            <thead>
                                <tr>
                                    <?php while ($field_info = $table_data_result->fetch_field()): ?>
                                        <th>
                                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&sort_column=<?php echo urlencode($field_info->name); ?>&sort_order=<?php echo $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                                                <?php echo htmlspecialchars($field_info->name); ?>
                                            </a>
                                        </th>
                                    <?php endwhile; ?>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <?php while ($row = $table_data_result->fetch_assoc()): ?>
                                    <tr>
                                        <?php foreach ($row as $column => $value): ?>
                                            <td contenteditable="true" name="data[<?php echo $row['id']; ?>][<?php echo $column; ?>]" data-id="<?php echo $row['id']; ?>">
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
                <textarea name="query" placeholder="SQL 쿼리를 입력하세요"><?php echo isset($_POST['query']) ? htmlspecialchars($_POST['query']) : ''; ?></textarea><br>
                <button type="submit">쿼리 실행</button>
            </form>

            <div class="query-result">
                <?php
                if ($query_result) {
                    if (is_string($query_result)) {
                        echo $query_result;
                    } else {
                        echo "<form method='POST' id='queryResultForm'>";
                        echo "<table><tr>";
                        $fields = $query_result->fetch_fields();
                        foreach ($fields as $field) {
                            echo "<th>{$field->name}</th>";
                        }
                        echo "</tr>";

                        $row_id = 1; // 각 행에 대한 고유 ID (예시로 사용)

                        while ($row = $query_result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $col => $val) {
                                echo "<td contenteditable='true' name='data[$row_id][$col]' data-id='$row_id'>" . htmlspecialchars($val) . "</td>";
                            }
                            echo "</tr>";
                            $row_id++;
                        }
                        echo "</table>";
                        echo "<button type='submit' id='saveQueryResultButton' class='save-button'>변경사항 저장</button>";
                        echo "</form>";
                    }
                }
                ?>
            </div>

        </div>
    </div>

    <script>
        function toggleTables(db) {
        // 테이블 목록을 항상 열어두기 위해 style을 변경하지 않음
        const tableList = document.getElementById('tables-' + db);
        tableList.style.display = 'block';  // 항상 테이블 목록을 보이게 설정

        // 모든 DB 항목에서 active 클래스를 제거하고, 클릭된 DB에만 active 클래스를 추가
        const dbItems = document.querySelectorAll('.db-item');
        dbItems.forEach(item => {
            item.classList.remove('active');
        });

        // 클릭된 DB 항목에만 active 클래스를 추가
        const selectedDbItem = document.getElementById('db-' + db);
        if (selectedDbItem) {
            selectedDbItem.classList.add('active');
        }
    }

    // 페이지가 로드될 때 URL에 db 파라미터가 있으면 해당 DB의 테이블 목록을 열어줌
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const db = urlParams.get('db');
        if (db) {
            toggleTables(db);
        }
    });



        function loadTableData(db, table) {
            window.location.href = '?db=' + db + '&table=' + table;
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
            let changesMade = false;  // 변경사항이 있는지 확인하는 변수

            // 모든 수정 가능한 셀에 이벤트 리스너 추가
            document.querySelectorAll("td[contenteditable=true]").forEach(td => {
                td.addEventListener("input", function () {
                    let id = td.getAttribute("data-id");
                    let name = td.getAttribute("name");
                    let value = td.innerText.trim();

                    let hiddenInput = document.querySelector(`input[name="${name}"]`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = name;
                        td.closest("form").appendChild(hiddenInput);
                    }
                    hiddenInput.value = value;

                    // 변경사항이 발생했음을 기록
                    changesMade = true;
                });
            });

            const saveButton = document.getElementById("saveQueryResultButton");

            if (saveButton) {
                saveButton.addEventListener("click", function (event) {
                    event.preventDefault();  // 기본 폼 제출을 방지

                    // 변경사항이 있는 경우에만 알림창을 띄움
                    if (changesMade) {
                        const confirmation = confirm("변경사항을 저장하시겠습니까?");
                        if (confirmation) {
                            // 사용자가 확인을 누르면 폼 제출
                            document.querySelector("form").submit();
                        }
                    } else {
                        // 변경사항이 없으면 바로 폼 제출
                        document.querySelector("form").submit();
                    }
                });
            }
        });


    </script>
</body>
</html>
