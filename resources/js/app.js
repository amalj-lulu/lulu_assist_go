import './bootstrap';              // Laravel’s default bootstrap file

import Alpine from 'alpinejs';     // Alpine.js for reactive components

// CSS Imports
import 'bootstrap/dist/css/bootstrap.min.css';
import 'admin-lte/dist/css/adminlte.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// JS Imports
import 'jquery';
import 'bootstrap';
import 'admin-lte/dist/js/adminlte.min.js';

window.Alpine = Alpine;
Alpine.start();
