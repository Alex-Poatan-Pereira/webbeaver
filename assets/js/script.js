// /assets/js/script.js
$(document).ready(function() {
    // 예시: DB 연결 정보와 테이블 목록
    var folders = [
        { name: 'DB1', files: ['table1', 'table2'] },
        { name: 'DB2', files: ['table3', 'table4'] }
    ];

    // 폴더 목록을 동적으로 생성
    folders.forEach(function(folder) {
        var folderElement = $('<li></li>').text(folder.name).addClass('folder');
        $('#folder-list').append(folderElement);

        // 각 폴더에 파일 목록 생성
        var fileList = $('<ul></ul>').addClass('file-list').hide();
        folder.files.forEach(function(file) {
            var fileElement = $('<li></li>').text(file).addClass('file');
            fileList.append(fileElement);
        });
        folderElement.append(fileList);

        // 폴더 클릭 시 파일 목록 표시
        folderElement.click(function() {
            fileList.toggle();
        });

        // 파일 클릭 시 해당 파일(테이블) 내용 표시
        fileList.on('click', '.file', function() {
            var selectedFile = $(this).text();
            $('#data-content').html('<h4>' + selectedFile + ' 데이터</h4>' + '<p>여기에 ' + selectedFile + ' 테이블 데이터가 표시됩니다.</p>');
        });
    });
});
