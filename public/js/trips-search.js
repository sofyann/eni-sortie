$('#btn_rechercher').on('click', search);

function search() {
    console.log('test');
    var search = $('#choixMagasin option:selected').attr('id');
    $.ajax({
        method: "GET",
        url: ajaxUrl,
        data: {id: search},
        success: function (data) {
            console.log(data);
            $('#divResultSearch').html(data);
        }
    });
}