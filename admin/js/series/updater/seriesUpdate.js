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

        $(".add-season").on("click", function () {
            let season_name = "Season " + ($(".series-active").find("li").length + 1)
            let series_name = $(".series-name").val();
            let series_key = $(".key").val()
            let season = new FormData();
            season.append("season_name", season_name);
            season.append("series_key", series_key);
            season.append("series_name", series_name);
            $.ajax({
                url: "http://127.0.0.1:8090/api/v1/season/",
                type: "POST",
                data: season,
                processData: false,
                contentType: false
            })
                .done((msg)=>{
                    console.log(msg)
                    $(".series-active").html("")
                    $.each(msg, (key, seasonData)=>{
                        $(".series-active").append(`
                        <li class='el-upload-list__item is-ready'>
                            <input type="hidden" value="${seasonData.id}" >
                            <div style="padding:4px">
                                <p class="text-center text-danger">${seasonData.season_name}</p>
                                <b style="display:block">${seasonData.episodes.length > 1 ?
                                    seasonData.episodes.length+" Episodes": seasonData.episodes.length+" Episodes"}</b>
                            </div>
                            <hr>
                            <div class="text-center">
                                <a href="/admin/view/season/${seasonData.series_name}/${seasonData.short_url}" class="text-decoration-none m-1">
                                    <button type="button" class="btn btn-sm btn-danger btn-rounded btn-icon" title="Add Episode to $season[season_name]">
                                        <i class="mdi mdi-plus"></i>
                                    </button>     
                                </a>
                                <a href="http://localhost:3003/view/series/${seasonData.series_name}/${seasonData.short_url}" class="text-decoration-none" target="_blank">
                                    <button type="button" class="btn btn-sm btn-info btn-rounded btn-icon view-season" title="View this Season in Website">
                                        <i class="mdi mdi-open-in-new"></i>
                                    </button>
                                </a>
                                <a href="#delete$season[season_name]" class="text-decoration-none m-1 delete-season">
                                    <button type="button" class="btn btn-sm btn-danger btn-rounded btn-icon" title="Delete Season">
                                        <i class="mdi mdi-delete"></i>
                                    </button>     
                                </a>
                            </div>
                        </li>
                        `)
                    })
                    delSeason()
                })
                .fail((err)=>{
                    alert(err.responseText)
                })
        })

        /**
         * Delete Season from click
         * @return void
         */
        function delSeason(){
            $(".delete-season").on("click", function(){
                let id = $(this).parent().parent().find("input").val()
                $.ajax({
                    url: `http://127.0.0.1:8090/api/v1/season/delete/${id}`,
                    type: "DELETE"
                })
                .done((msg)=>{
                    console.log(msg)
                    $(".series-active").html("")
                    $.each(msg, (key, seasonData)=>{
                        $(".series-active").append(`
                            <li class='el-upload-list__item is-ready'>
                                
                                <input type="hidden" value="${seasonData.id}" >
                                <div style="padding:4px">
                                    <p class="text-center text-danger">${seasonData.season_name}</p>
                                    <b style="display:block">${seasonData.episodes.length > 1 ?
                                         seasonData.episodes.length+" Episodes": seasonData.episodes.length+" Episodes"}</b>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <a href="/admin/view/season/${seasonData.series_name}/${seasonData.short_url}" class="text-decoration-none m-1">
                                        <button type="button" class="btn btn-sm btn-danger btn-rounded btn-icon" title="Add Episode to $season[season_name]">
                                            <i class="mdi mdi-plus"></i>
                                        </button>     
                                    </a>
                                    <a href="http://localhost:3003/view/series/${seasonData.series_name}/${seasonData.short_url}" class="text-decoration-none" target="_blank">
                                        <button type="button" class="btn btn-sm btn-info btn-rounded btn-icon view-season" title="View this Season in Website">
                                            <i class="mdi mdi-open-in-new"></i>
                                        </button>
                                    </a>
                                    <a href="#delete$season[season_name]" class="text-decoration-none m-1 delete-season">
                                        <button type="button" class="btn btn-sm btn-danger btn-rounded btn-icon" title="Delete Season">
                                            <i class="mdi mdi-delete"></i>
                                        </button>     
                                    </a>
                                    
                                    
                                </div>
                                    
                            </li>
                        `)
                    })
                    delSeason()
                })
                .fail((err)=>{
                    alert(err.responseText)
                })
            })
        } 
        delSeason()
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
            let series_name = $('#series_title').val();
            let series_details = $('#about_series').val();
            let popular = $('.popular').prop("checked") === true ? 1 : 0
            let fields = [series_name, series_details]
            //check for empty fields
            for (let field = 0; field < fields.length; field++) {
                if (fields[field] == '') {
                    return alert("All fields are required")
                }
            }
            $(this).text("Updating...")

            let data = {
                series_details: series_details,
                popular: popular
            };
            $.ajax({
                url: `http://127.0.0.1:8090/api/v1/series/update/${_id}`,
                type: 'PUT',
                data: JSON.stringify(data),
                dataType: 'json',
                headers: { 'Content-Type': 'application/json' },
                crossDomain: true

            })
                .done(function (msg) {
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