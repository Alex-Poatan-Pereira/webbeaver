$(document).ready(function() {
    // 데이터베이스 연결 폼 제출
    $('#dbConnectForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: 'backend/db_connect.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // 연결 성공 시
                if (response.includes("연결 성공")) {
                    $('#connectionStatus').text("연결 성공!");
                    // 이후 페이지를 전환하거나 다른 작업을 할 수 있습니다.
                } else {
                    $('#connectionStatus').text("연결 실패: " + response);
                }
            },
            error: function() {
                $('#connectionStatus').text('서버와의 통신에 실패했습니다.');
            }
        });
    });
});
