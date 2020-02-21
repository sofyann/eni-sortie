$('#btn_rechercher').on('click', search);

function search() {
    $param = $('input[name=radioSearch]:checked').val();
    $.ajax({
        method: "GET",
        url: ajaxUrl,
        data: {'search': $param},
        success: function (data) {
            console.log(data);
            $('#divResultSearch').html(data);
        }
    });
}