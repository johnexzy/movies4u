(function ($) {
    'use strict';
    $(function () {

        let _key = $(".key").val();
        let _id = $(".id").val()
        $('.openfile').on("click", function () {
            $(this).parent().find('.file-upload-default').trigger('click')
        })

        $('.image-upload').on('change', function (e) {
            $(".image-upload-progress").show()
            let imagefiles = new FormData();
            $.each(e.target.files, (key, image) => {
                imagefiles.append(`images[${key}]`, image)
            })
            $.ajax({
                xhr: function () {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (ext) {
                        if (ext.lengthComputable) {
                            let perCentComplete = ((ext.loaded / ext.total) * 100).toFixed();
                            $(".image-bar").width(perCentComplete + '%');
                            $(".image-bar").html(perCentComplete + '% (' +
                                (ext.loaded / (1024 * 1024)).toFixed(2) + 'mb of ' + (ext.total / (1024 * 1024)).toFixed(2) + 'mb)');
                        }
                    }, false)
                    return xhr;
                },
                url: `http://127.0.0.1:8090/api/v1/images/${_key}`,
                type: 'POST',
                data: imagefiles,
                processData: false,
                contentType: false,
                responseType: 'application/json'

            })
                .done((msg) => {
                    let images = JSON.parse(msg);
                    $(".image-list").html("")
                    $.each(images, function (key, image) {

                        $(".image-list").append(`
                                <li tabindex='0' class='el-upload-list__item is-ready'>
                                <img src='/${image}' alt='' class='el-upload-list__item-thumbnail'>
                                </li>
                            `);
                    });
                    $(".del-thumbnail").show();
                })
                .fail((err) => {
                    console.log(err.responseText)
                    alert("unexpected error occured")
                })

            // alert($(this).val())

        });

        $(".del-thumbnail").on("click", function () {
            let perm = confirm("Confirm to Erase this images");
            if (!perm) {
                return false
            }
            $.ajax({
                url: `http://127.0.0.1:8090/api/v1/images/delete/${_key}`,
                type: "DELETE"
            })
                .done((msg) => {
                    $(".image-list").html("");
                    $(this).hide();
                    console.log(msg)
                })
                .fail((err) => {
                    console.log(err.responseText);
                    alert("Unexpected error occured")
                })
        })
        $('#handleSubmit').on('click', function () {
            let video_name = $('#video_title').val();
            let video_details = $('#about_video').val();
            let category = $('input[name=Category]:checked').val();
            let popular = $('.popular').prop("checked") === true ? 1 : 0
            let fields = [video_name, video_details, category]
            //check for empty fields
            for (let field = 0; field < fields.length; field++) {
                if (fields[field] == '') {
                    return alert("All fields are required")
                }
            }
            $(this).text("Updating...")

            let data = {
                video_name: video_name,
                video_details: video_details,
                category: category,
                popular: popular
            };
            console.log(data)
            $.ajax({
                url: `http://127.0.0.1:8090/api/v1/videos/${_id}`,
                type: 'PUT',
                data: JSON.stringify(data),
                dataType: 'json',
                headers: { 'Content-Type': 'application/json' },
                crossDomain: true

            })
                .done(function (msg) {
                    $(".status-msg").text("Updated Successfully")
                    $(".status-msg").show()
                    //reset All State to default
                    console.log(msg)
                    $('#handleSubmit').html(`<i class="mdi mdi-content-save-all btn-icon-prepend"></i>
                    Save All`)
                    $('#handleSubmit').addClass("btn-primary")
                    $('body,html').animate({
                        scrollTop: -1,
                        // opacity: 0
                    }, 1000);
                    $(".status-msg").slideUp(3000)
                })
                .fail(function (err) {
                    alert("Sorry, Something went wrong \nif problem persist contact developer")
                    $('#handleSubmit').html(`<i class="mdi mdi-reload btn-icon-prepend"></i>                                                    
                    Retry`)
                    $('#handleSubmit').addClass("btn-danger")
                    console.log(err)

                })
        })

    });
})(jQuery);