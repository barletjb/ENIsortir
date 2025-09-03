import './bootstrap.js';

import './vendor/bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

import './styles/navbar.css';
import './styles/boutons.css';
import './styles/usersList.css';

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