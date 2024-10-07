import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

import './styles/bootstrap.rtl.min.css';
import './styles/dropify.min.css';
import './styles/app.css';
import './styles/select2.min.css';
import './styles/sweetalert2.min.css';


import './js/jquery-3.7.1.min.js';
import './js/select2.min.js';
import './js/bootstrap.min.js';
import './js/sweetalert2.all.min.js';

$(document).ready(function () {
    // new DataTable('#postTable');


    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    $(".copyBtn").on('click', function () {
        navigator.clipboard.writeText($(this).data('url'));
    })
    $("#select2tags").select2({
        width: 'resolve',
        dir: "rtl",
        tags: true,
        createTag: function (params) {
            return {
                id: params.term,
                text: params.term
            }
        },
        insertTag: function (data, tag) {
            // Insert the tag at the end of the results
            data.push(tag);
        }
    });
    $("#select2categories").select2({
        width: 'resolve',
        dir: "rtl",
        // tags: true,
    });
    // Bind an event
    $('#select2tags').on('select2:select', function (e) {
        e.preventDefault()

        if (e.params.data.element == undefined) {
            var studentSelect = $('#select2tags');
            let val = studentSelect.val().slice(0, -1)
            studentSelect.val(val).trigger('change')
            if (e.params.data.text == ""){
                return false;
            }
            $.ajax({
                type: 'POST',
                data: {
                    title: e.params.data.text,
                    description: ''
                },
                url: '/post/tag/save',
            }).then(function (data) {
                // create the option and append to Select2
                data = JSON.parse(data)
                var option = new Option(data.title, data.id, true, true);
                studentSelect.append(option).trigger('change');
            });
            return;
        }
    });


    $("body").on('click', '.video', function () {
        var theModal = $(this).data("target"),
            videoSRC = $(this).attr("data-video"),
            videoSRCauto = videoSRC + "";
        $(theModal + ' source').attr('src', videoSRCauto);
        let html = ''
        let fileName = $(this).data('file')
        let link = $(this).data('video')
        let meta = $(this).data('meta')
        if (meta !== "") {
            html = `<div class="h_iframe-aparat_embed_frame"><span style="display: block;padding-top: 57%"></span><iframe src="${meta}"  allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe></div>`
        } else if (link !== "") {
            html = `<video id="m_video" controls width="100%">
                        <source src="${link}" type="video/mp4">
                    </video>`
        } else if (fileName !== "") {
            html = `<video id="m_video" controls width="100%">
                        <source src="${fileName}" type="video/mp4">
                    </video>`
        }
        $('.video_wrapper').html(html)
        $('.modal').show()
        $(theModal + ' button.close').on('click', function (e) {
            e.preventDefault()
            $('.video_wrapper').html('')
            $('.modal').modal('hide')
        });
        $("#myModal").on("hidden.bs.modal", function (e) {
            e.preventDefault()
        });
    });

    function handleUploadWrapper(id){
        $("#image_upload_wrapper").hide()
        $("#video_upload_wrapper").hide()
        $("#image_link_wrapper").hide()
        $("#video_link_wrapper").hide()
        $("#aparat_wrapper").hide()
        // $("#inputMetaData").val('')
        // $("#inputFile").attr('disable',true)
        // $("#inputFile2").val('disabled',true)
        $(id).show()

    }
    if ($('#image_upload').is(':checked')) {
        handleUploadWrapper("#image_upload_wrapper")
    }
    if ($('#video_upload').is(':checked')) {
        handleUploadWrapper("#video_upload_wrapper")
    }
    if ($('#image_link').is(':checked')) {
        handleUploadWrapper("#image_link_wrapper")
    }
    if ($('#video_link').is(':checked')) {
        handleUploadWrapper("#video_link_wrapper")
    }
    if ($('#aparat').is(':checked')) {
        handleUploadWrapper("#aparat_wrapper")
    }

    // $('.post-image').on('click',function (){
    //     // let img = $(this).data('img')
    //     // let html = `<img  src="${img}" class="card-img-top post-image">`
    //     // $('.video_wrapper').html(html)
    //     // $('.modal').show()
    //     // $("#myModal").on("hidden.bs.modal", function (e) {
    //     //     e.preventDefault()
    //     // });
    // })


    $("#image_upload").on('click', function () {
        handleUploadWrapper("#image_upload_wrapper")
    })
    $("#video_upload").on('click', function () {
        handleUploadWrapper("#video_upload_wrapper")
    })
    $("#image_link").on('click', function () {
        handleUploadWrapper("#image_link_wrapper")
    })
    $("#video_link").on('click', function () {
        handleUploadWrapper("#video_link_wrapper")
    })
    $("#aparat").on('click', function () {
        handleUploadWrapper("#aparat_wrapper")
    })

    $("#deletePostImage").on('click', function () {
        $(this).parent().html(`<label for="inputFile" class="form-label">آپلود فایل</label>
                        <input type="file" name="file" id="inputFile" class="form-control">`)
    })
    $(".deletePostCoverImage").on('click', function () {
        $(this).parent().html(`<label for="inputCoverImage" class="form-label">آپلود تصویر کاور ویدیو</label>
                        <input type="file" name="${$("input[name='type']:checked").val()}_cover_image" id="inputCoverImage" class="form-control">`)
    })

    $('#deleteUserPhoto').on('click',function (){
        $(this).parent().html(`<label for="inputState" class="form-label">عکس پروفایل</label>
                    <input type="file" name="photo">`)
    })
    $("#uploadForm").on('submit', function (e) {
        e.preventDefault();
        let meta = $("#inputMetaData").val()
        if (meta !== "") {
            var doc = new DOMParser().parseFromString(meta, "text/html");
            let ele = doc.body.firstElementChild.firstElementChild.nextElementSibling.getAttribute('src')
            $("#inputMetaData").val(ele)
        }

        console.log(this)
        $.ajax({
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                if (($("#image_upload").is(':checked') || $('#video_upload').is(':checked') ) && $('#inputFile').val() != "") {
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = ((evt.loaded / evt.total) * 100);
                            $(".progress-bar").width(percentComplete + '%');
                            $(".progress-bar").html(percentComplete + '%');
                        }
                    }, false);
                }
                return xhr;

            },
            type: 'POST',
            url: '/post/save',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $(".progress-bar").width('0%');
                $('#uploadStatus').html('<img src="images/loading.gif"/>');
            },
            error: function () {
                $('#uploadStatus').html('<p style="color:#EA4335;">File upload failed, please try again.</p>');
            },
            success: function (resp) {
                resp = JSON.parse(resp)
                if (resp.code == 1) {
                    window.location.href = "/post/list/"+resp.post.gallery_id
                } else {
                    $('#notice_wrapper').html(`<li>${resp.message}</li>`)
                }
            }
        });
    });



    $('.deleteGallery').on('click',function (e){
        e.preventDefault()
        Swal.fire({
            title: "در صورت حذف گالری تمامی پست های مرتبط حذف خواهند شد",
            showCancelButton: true,
            confirmButtonText: "تایید",
            cancelButtonText: "لغو",

        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location.href = $(this).attr('href')
            } else if (result.isDenied) {

            }
        });
    })
    $('.deleteUser').on('click',function (e){
        e.preventDefault()
        Swal.fire({
            title: "آیا از حذف این کاربر مطمئن هستید؟",
            showCancelButton: true,
            confirmButtonText: "تایید",
            cancelButtonText: "لغو",

        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location.href = $(this).attr('href')
            } else if (result.isDenied) {

            }
        });
    })

    $('#multipleUpload').on('click',function (){

        if ($('#uploadFiles').css('display') == 'none'){
            $('#uploadFiles').show()
        }else{
            $('#uploadFiles').hide()
        }
    });

});


// import lightGallery from "lightgallery";

// import lgThumbnail from '/lightgallery/plugins/thumbnail';
// import lgZoom from '/lightgallery/plugins/zoom'
// import lgVideo from '/lightgallery/plugins/video'


// dynamicGallery.openGallery(0);
// $dynamicGallery.addEventListener("click", () => {
//
// });


// const inlineGallery = lightGallery($lgContainer, {
//     container: $lgContainer,
//     supportLegacyBrowser: true,
//     dynamic: true,
//     // Turn off hash plugin in case if you are using it
//     // as we don't want to change the url on slide change
//     hash: false,
//     // Do not allow users to close the gallery
//     closable: false,
//     // Add maximize icon to enlarge the gallery
//     showMaximizeIcon: true,
//     // Append caption inside the slide item
//     // to apply some animation for the captions (Optional)
//     appendSubHtmlTo: ".lg-item",
//     // Delay slide transition to complete captions animations
//     // before navigating to different slides (Optional)
//     // You can find caption animation demo on the captions demo page
//     slideDelay: 400,
//     plugins: [lgZoom, lgThumbnail, lgVideo],
//     dynamicEl: [
//         {
//             src:
//                 "http://localhost:8000/uploads/posts/5-65b5d6a4f1811.jpg",
//             responsive:
//                 "http://localhost:8000/uploads/posts/5-65b5d6a4f1811.jpg",
//             thumb:
//                 "http://localhost:8000/uploads/posts/5-65b5d6a4f1811.jpg",
//             subHtml: `<div class="lightGallery-captions">
//                     <h4>Photo by <a href="https://unsplash.com/@dann">Dan</a></h4>
//                     <p>Published on November 13, 2018</p>
//                 </div>`
//         },
//         // {
//         //     src:
//         //         "",
//         //     responsive:
//         //         "http://localhost:8000/uploads/posts/Screenshot-from-2024-01-26-17-27-08-65b5d0b82c57d.png",
//         //     thumb:
//         //         "http://localhost:8000/uploads/posts/Screenshot-from-2024-01-26-17-27-08-65b5d0b82c57d.png",
//         //     subHtml: `<div class="lightGallery-captions">
//         //             <h4>Photo by <a href="https://unsplash.com/@kylepyt">Kyle Peyton</a></h4>
//         //             <p>Published on September 14, 2016</p>
//         //         </div>`
//         // },
//         {
//             src: 'http://localhost:8000/uploads/posts/file-example-MP4-480-1-5MG-65b5ee3992c90.mp4',
//             poster: 'http://localhost:8000/uploads/posts/athletic-track-powerpoint-templates-65b5d0e72ece7.jpg',
//             thumb: 'http://localhost:8000/uploads/posts/athletic-track-powerpoint-templates-65b5d0e72ece7.jpg',
//             // html: '<video class="lg-video-object lg-html5" controls preload="none"><source src="http://localhost:8000/uploads/posts/file-example-MP4-480-1-5MG-65b5ee3992c90.mp4" type="video/mp4">Your browser does not support HTML5 video</video>'
//         }
//
//     ],
//
//     // Completely optional
//     // Adding as the codepen preview is usually smaller
//     thumbWidth: 60,
//     thumbHeight: "40px",
//     thumbMargin: 4
// });
//




console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
