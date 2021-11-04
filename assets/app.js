/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

import 'dropzone/dist/dropzone.css';
import Dropzone from 'dropzone';

Dropzone.autoDiscover = false;
document.addEventListener('DOMContentLoaded', () => {
  let myDropzone = new Dropzone('.vich-file', {
    autoProcessQueue: false,
    chunking: true,
    maxFileSize: 102400,
    chunkSize: 256000,
    parallelChunkUploads: true,
    parallelUploads: 1,
    forceChunking: true,
    retryChunks: true,
    retryChunksLimit: 3,
    method: "post",
    url: "handle-file",

    init: function() {
        document.getElementById('file_upload_submit').addEventListener("click", function(e) {
            e.preventDefault();
            myDropzone
        })
    }
});
// je mag het woordt 'this' niet gebruiken als variabel. 
//in JS is dit een verwijzing binnen het bestand. Verdander = this naar new Dropzone
// ook mis je een url waar je naartoe kan uploaden
//ik raad aan om een console.log toe te voegen om te checken of er uberhaupt iets wordt gestuurd
// voeg ook een console.log toe om te checken of er success is.
//kijk even goed naar mijn github repo en bekijk wat jij mist wat ik wel heb
//probeer deze javascript in een javascript file te zetten, app.js is een prime plek.
})