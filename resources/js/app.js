// ✅ 1. Import jQuery and expose it globally
import $ from 'jquery';
window.$ = window.jQuery = $;

// ✅ 2. Import Select2 JS, but also manually attach it to jQuery
import select2 from 'select2/dist/js/select2.full';
select2($); // 👈 this binds Select2 to your jQuery instance

// ✅ 3. Other dependencies
import 'bootstrap';
import 'admin-lte/dist/js/adminlte.min.js';

// ✅ 4. Styles
import 'bootstrap/dist/css/bootstrap.min.css';
import 'admin-lte/dist/css/adminlte.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'select2/dist/css/select2.min.css';
import '@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css';
import '../css/custom.css'; 

// ✅ 5. Alpine.js (if needed)
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ✅ 6. Init Select2
$(function () {
  console.log('jQuery:', $.fn.jquery);
  console.log('Select2:', $.fn.select2); // ✅ should NOT be undefined now

  $('.select2bs4').select2({
    theme: 'bootstrap4',
    width: 'resolve',
    placeholder: 'Select an option',
    allowClear: true,
  });
});
