<?php
    session_start(); // 세션 시작

    // 로그인 여부 확인
    if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
        // 로그인하지 않았다면 로그인 페이지로 리다이렉트
        header("Location: ../users/login.php");
        exit;
    }

    // MySQLi 연결
    $conn = new mysqli('127.0.0.1', 'thesleepf', 'thesleepf');

    // 연결 확인
    if ($conn->connect_error) {
        die("연결 실패: " . $conn->connect_error);
    }
    
    // URL에서 선택된 DB 가져오기
    $db = isset($_GET['db']) ? $_GET['db'] : null; 
    $table = isset($_GET['table']) ? $_GET['table'] : null; 

    // 데이터베이스 목록 가져오기
    $sql = "SHOW DATABASES";
    $res = $conn->query($sql);

    // 테이블 목록 가져오기
    $table_list = [];
    if ($selected_db) {
        $conn->select_db($selected_db); // 선택된 DB로 변경
        $table_res = $conn->query("SHOW TABLES");
        while ($table_row = $table_res->fetch_array()) {
            $table_list[] = $table_row[0];
        }
    }

    
  
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Webbeaver</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <!-- Custom fonts for this template -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../css/button.css?v=1.0" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">


</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Webbeaver</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">


            
           
           

            <!-- Divider -->
            <hr class="sidebar-divider">


            <?php
            while ($row = $res->fetch_assoc()) {
                $db = $row['Database'];
                echo '<li class="nav-item">';
                    echo '<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#'.$db.'" aria-expanded="true" aria-controls="collapseUtilities">';
                        echo '<i class="fas fa-fw fa-wrench"></i>';
                        echo '<span>'.$db.'</span>';
                    echo '</a>';
                    echo '<div id="'.$db.'" class="collapse" aria-labelledby="headingUtilities"
                        data-parent="#accordionSidebar">';
                        echo '<div class="bg-white py-2 collapse-inner rounded">';
                            echo '<h6 class="collapse-header">Table list:</h6>';
                            
                            $sql2 = "SHOW TABLES FROM $db";
                            $res2 = $conn->query($sql2);

                            


                            while ($row2 = $res2->fetch_array()) {
                                
                                echo '<a id="table'.$row2[0].'" class="collapse-item" href="./table.php?db='.$db.'&table='.$row2[0].'&page=1">'.$row2[0].'</a>';
                            }
                            
                        echo '</div>';
                    echo '</div>';
                echo '</li>';

                
                echo '<hr class="sidebar-divider">';
            }
            ?>
         
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                

                    <!-- Sidebar Toggle (Topbar) -->
                    <form class="form-inline">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                    </form>

                   

                 

                        

                        

                      

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800"><?php echo $selected_table; ?></h1>
                    

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>

                                        </tr>
                                    </thead>
                                   
                                    <tbody>
                                        <tr>

                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                           
                                        </tr>
                                    </tfoot>
                                </table>
                                <?php
                                    
                                    $db = isset($_GET['db']) ? $_GET['db'] : null; 
                                    $table = isset($_GET['table']) ? $_GET['table'] : null; 

                                
                                    $conn->select_db($db); // 선택된 DB로 변경

                                    $sql = "SELECT * FROM $table";
                                    $query = mysqli_query($conn, $sql);
                                    $res = mysqli_num_rows($query);

                                    $total_pages = ceil($res / 10);

                                    $list_num = 10;
                                    $page_num = 5;

                                ?>

                                <div class="pagination">
                                    <a href="#" id="prevPage" onclick="changePageGroup(-1)" style="display: none;">«</a>
                                    <span id="pageNumbers"></span>
                                    <a href="#" id="nextPage" onclick="changePageGroup(1)" style="display: none;">»</a>
                                </div>

                                                       
                            </div>      
                        </div>
                        <hr>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="queryTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>쿼리 결과</th>
                                            <th>쿼리 작성</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div id="queryResult"></div>
                                                <div id="pagination"></div>
                                            </td>
                                            <td>
                                                <form id="queryForm">
                                                    <textarea id="queryInput" name="query" rows="4" cols="50" placeholder="쿼리문을 작성하세요"></textarea>
                                                    <br>
                                                    <button type="submit" class="btn btn-primary">쿼리 실행</button>
                                                </form>

                                            </td>
                                        </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <!-- /.container-fluid -->
                
            </div>
            <!-- End of Main Content -->

           

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

   

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/datatables-demo.js"></script>

    
    <script>

        let totalPages = <?php echo $total_pages; ?>;
        let currentPageGroup = 1;
        let pagesPerGroup = <?php echo $page_num; ?>;
        let currentPage = 1;

        function updatePagination() {
            let startPage = (currentPageGroup - 1) * pagesPerGroup + 1;
            let endPage = Math.min(startPage + pagesPerGroup - 1, totalPages);
            let pageNumbersHtml = '';

            for (let page = startPage; page <= endPage; page++) {
                pageNumbersHtml += `<a href="#" class="page-btn ${page === currentPage ? 'active' : ''}" 
                                    data-page="${page}" onclick="changePage(${page})">${page}</a> `;
            }

            document.getElementById("pageNumbers").innerHTML = pageNumbersHtml;


            // 첫 번째 페이지 그룹일 때 "«" 숨김
            document.getElementById("prevPage").style.display = (currentPageGroup > 1) ? "inline-block" : "none";

            // 마지막 페이지 그룹일 때 "»" 숨김
            document.getElementById("nextPage").style.display = (endPage < totalPages) ? "inline-block" : "none";
        }

        function changePageGroup(direction) {
            let newGroup = currentPageGroup + direction;

            if (newGroup < 1 || (newGroup - 1) * pagesPerGroup >= totalPages) {
                return; // 첫 번째나 마지막 그룹을 넘지 않도록 제한
            }

            currentPageGroup = newGroup;
            updatePagination();

            let firstPageInGroup = (currentPageGroup - 1) * pagesPerGroup + 1;
            changePage(firstPageInGroup);
        }

        updatePagination();

        function changePage(page) {
            currentPage = page;

            // 기존에 있던 모든 active 클래스 제거
            document.querySelectorAll('.page-btn').forEach(el => {
                el.classList.remove('active');
            });

             // 현재 페이지에 active 추가
            let activeBtn = document.querySelector(`.page-btn[data-page="${page}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                console.log(`Added active to: ${activeBtn.className}`);
            }

            // 테이블 데이터 불러오기
            load_table('<?php echo $db; ?>', '<?php echo $table; ?>', page);
        }

        

        $( document ).ready(function() {
            // URL에서 파라미터 가져오기
            let urlParams = new URLSearchParams(window.location.search);
            let db = urlParams.get("db");
            let table = urlParams.get("table");
            let page = urlParams.get("page");

            if (db) {
                let dbElement = $("#" + db); // 해당 DB에 대한 collapse 항목 찾기
                dbElement.addClass("show"); // Bootstrap collapse 펼치기
            }

            if (table) {
                // 해당하는 table의 링크 색 변경 (선택된 테이블에 스타일 적용)
                let tableElement = $('#table' + table);
                tableElement.css('color', 'red');  // 원하는 색깔로 변경
                tableElement.css('font-weight', 'bold');  // 선택된 테이블 강조 (선택적)
            }

            $(".h3").text(table);
            load_table(db, table, page);  
        });




        function load_table(db, table, page) {
            // URL 변경: 페이지를 클릭하면 URL의 page 파라미터를 업데이트
            let url = new URL(window.location.href);
            url.searchParams.set('db', db);
            url.searchParams.set('table', table);
            url.searchParams.set('page', page);
            history.pushState(null, '', url.toString()); // 페이지 새로 고침 없이 URL 업데이트

            $.ajax({
                url: "table_database.php",
                type: "GET",
                data: { db: db, table: table, page: page },
                success: function (response) {
                    $("#dataTable").html(response);
                    
                },
                error: function () {
                    $("#dataTable").html("<p>데이터를 불러오는 데 실패했습니다.</p>");
                }
            });

            // 페이지 링크 스타일 초기화
            $(".pagination a").css({
                "color": "black",  // 모든 페이지 링크 기본 색상
                "background-color": "white",  // 기본 배경색
                "border": "1px solid black",  // 기본 테두리 설정
                "padding": "10px 15px",  // 박스 크기 조정 (좌우 15px, 상하 10px)
                "margin": "0 5px",  // 페이지 버튼 간의 간격
                "text-align": "center",  // 텍스트 가운데 정렬
                "display": "inline-block",  // 인라인 블록으로 설정하여 크기 설정
                "border-radius": "5px"  // 모서리 둥글게 설정
            });

            // 선택된 페이지 스타일
            $(".pagination .active").css({
                "color": "white",  // 선택된 페이지 글자 색
                "background-color": "red",  // 선택된 페이지 배경색
                "border": "1px solid black",  // 테두리 설정
                "padding": "10px 15px",  // 박스 크기 조정 (좌우 15px, 상하 10px)
                "margin": "0 5px",  // 페이지 버튼 간의 간격
                "text-align": "center",  // 텍스트 가운데 정렬
                "display": "inline-block",  // 인라인 블록으로 설정하여 크기 설정
                "border-radius": "5px"  // 모서리 둥글게 설정
            });
        }

        // 페이지가 로드될 때 URL에 맞는 페이지에 스타일 적용
        $(document).ready(function() {
            let itemsPerPage = 10;  // 한 페이지에 표시할 행 개수
            let currentPage = 1;
            
            // URL에서 page 파라미터를 가져옴
            let urlParams = new URLSearchParams(window.location.search);
            let page = urlParams.get('page');

            if (page) {
                // 해당 페이지의 링크에 스타일을 적용
                $(".pagination a").css("color", "black");  // 모든 페이지 링크 초기화
                $("#page" + page).css("color", "red");  // 선택된 페이지 색상 변경
               
            }
        });


        $(document).ready(function() {
            // 페이지 새로고침 시, localStorage에 저장된 값 로드
            if(sessionStorage.getItem('query') && sessionStorage.getItem('queryResult')) {
                $('#queryInput').val(sessionStorage.getItem('query'));
                $('#queryResult').html(sessionStorage.getItem('queryResult'));
                setTimeout(() => paginateResults(), 10);
            }

            let query_currentPage = 1;
            let itemsPerPage = 10;  // 한 페이지에 표시할 항목 수
            let pageButtonsToShow = 5;  // 한 번에 보여줄 페이지 버튼 수 (5개씩 보이게)
            let currentPageGroup = 1;  // 현재 페이지 그룹 (1, 2, 3 등)

            function paginateResults() {
                let rows = $('#queryResult tbody tr');  // 모든 tr 태그 선택
                let totalRows = rows.length;  // 총 행 개수
                let totalPages = Math.ceil(totalRows / itemsPerPage);  // 총 페이지 수

                if (totalRows === 0) {
                    $('#pagination').html('');  // 결과가 없으면 페이지네이션 버튼 숨김
                    return;
                }

                rows.hide();  // 모든 tr 숨기기
                let start = (query_currentPage - 1) * itemsPerPage;
                let end = start + itemsPerPage;
                rows.slice(start, end).show();  // 해당 페이지의 tr만 표시

                queryUpdatePagination(totalPages);  // 페이지네이션 버튼 업데이트
            }

            function queryUpdatePagination(totalPages) {
                let paginationHtml = '';

                // << 이전 그룹 버튼 추가
                if (currentPageGroup > 1) {
                    paginationHtml += `<button class="query-page-btn" data-page="prev-group"><<</button> `;
                }

                // 현재 페이지를 기준으로 시작 페이지와 끝 페이지 계산
                let startPage = (currentPageGroup - 1) * pageButtonsToShow + 1;
                let endPage = Math.min(totalPages, currentPageGroup * pageButtonsToShow);

                // 페이지 번호 버튼 추가
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `<button class="query-page-btn ${i === query_currentPage ? 'active' : ''}" data-page="${i}">${i}</button> `;
                }

                // >> 다음 그룹 버튼 추가
                if (endPage < totalPages) {
                    paginationHtml += `<button class="query-page-btn" data-page="next-group">>></button>`;
                }

                $('#pagination').html(paginationHtml);  // 페이지네이션 버튼 출력
            }

            $(document).on('click', '.query-page-btn', function() {
                let page = $(this).attr('data-page');

                if (page === 'prev-group') {
                    // 이전 그룹으로 이동
                    currentPageGroup--;
                    query_currentPage = (currentPageGroup - 1) * pageButtonsToShow + 1;  // 첫 페이지로 이동
                } else if (page === 'next-group') {
                    // 다음 그룹으로 이동
                    currentPageGroup++;
                    query_currentPage = (currentPageGroup - 1) * pageButtonsToShow + 1;  // 첫 페이지로 이동
                } else {
                    // 페이지 번호 클릭
                    query_currentPage = parseInt(page);
                }

                $('.query-page-btn').removeClass('active');
                $(`[data-page="${query_currentPage}"]`).addClass('active');

                paginateResults();
            });

            // 폼 제출 이벤트
            $('#queryForm').on('submit', function(event) {
                event.preventDefault();  // 폼 제출 기본 동작 방지

                // 사용자 입력값 가져오기
                let urlParams = new URLSearchParams(window.location.search);
                let db = urlParams.get("db");
                let query = $('#queryInput').val();

                // AJAX 요청
                $.ajax({
                    url: 'query.php',  // 쿼리 처리하는 PHP 파일
                    type: 'POST',  // 데이터 전송 방식
                    data: { db: db, query: query },  // 전송할 데이터
                    success: function(response) {
                        // 쿼리 실행 결과를 받아서 화면에 표시
                        $('#queryResult').html(response);

                        setTimeout(() => paginateResults(), 10);

                        // localStorage에 쿼리와 결과 저장
                        sessionStorage.setItem('query', query);
                        sessionStorage.setItem('queryResult', response);
                    },
                    error: function() {
                        // 에러 발생 시 처리
                        $('#queryResult').html("<p>쿼리 실행에 실패했습니다.</p>");
                    }
                });
            });
        });



    </script>




</body>

</html>
