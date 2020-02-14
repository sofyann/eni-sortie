$('#btn_rechercher').on('click', search);

function search() {
    console.log('test');
    var search = $('#choixMagasin option:selected').attr('id');
    $param = $('input[name=radioSearch]:checked').val();
    console.log($param);
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