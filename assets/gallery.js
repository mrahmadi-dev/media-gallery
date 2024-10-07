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


import './js/jquery-3.7.1.min.js';
import './js/select2.min.js';
import './js/bootstrap.min.js';

import lightGallery from "https://cdn.skypack.dev/lightgallery@2.4.0";
import lgZoom from "https://cdn.skypack.dev/lightgallery@2.4.0/plugins/zoom";
import lgThumbnail from "https://cdn.skypack.dev/lightgallery@2.4.0/plugins/thumbnail";
import lgVideo from "https://cdn.skypack.dev/lightgallery@2.4.0/plugins/video";


const $lgContainer = document.getElementById("inline-gallery-container");
let elements = [];
if (typeof posts !== 'undefined'){
    posts.forEach(item => {
        if(item.fileType === 'IMAGE'){
            elements.push({
                src: item.fileNamePath,
                responsive: item.fileNamePath,
                thumb: item.fileNamePath,
            })
        }else{
            if (item.metadata !== "") {
                elements.push({
                    poster: item.coverImagePath,
                    thumb: item.coverImagePath,
                    iframe: true,
                    src: item.metadata
                })
            }else{
                elements.push({
                    video: {"source": [{"src":item.fileNamePath, "type":"video/mp4"}], "attributes": {"preload": false, "controls": true}},
                    // src: "http://localhost:8000/uploads/posts/file-example-MP4-480-1-5MG-65b5ee3992c90.mp4",
                    poster: item.coverImagePath,
                    thumb: item.coverImagePath,

                })
            }
        }
    })
}

const inlineGallery = lightGallery($lgContainer, {
    dynamic: true,
    plugins: [lgZoom, lgVideo, lgThumbnail],
    dynamicEl : elements,
    container: $lgContainer,
    closable: false,
});

setTimeout(() => {
    inlineGallery.openGallery();
}, 200);

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
