import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './vendor/bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

import $ from 'jquery';
import 'select2';
import 'select2/dist/css/select2.min.css';

$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Sélectionner un ou plusieurs membres",
        allowClear: true,
        width: '100%'
    });
});