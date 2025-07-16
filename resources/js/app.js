// ✅ 1. Import jQuery and expose it globally
import $ from 'jquery';
window.$ = window.jQuery = $;

// ✅ 2. Import Bootstrap 5
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// ✅ 3. Import Select2
import select2 from 'select2/dist/js/select2.full';
select2($);

// ✅ 4. Import DataTables
import DataTable from 'datatables.net-bs5/js/dataTables.bootstrap5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
window.DataTable = DataTable;

// ✅ 5. Import CSS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'select2/dist/css/select2.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/custom.css';

// ✅ 6. Setup CSRF token globally
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': csrfToken,
    },
  });
}

// ✅ 7. DOM Ready
$(function () {
  console.log('✅ jQuery version:', $.fn.jquery);
  console.log('✅ Bootstrap version:', bootstrap?.Modal?.VERSION);

  // ✅ Init Select2 globally
  $('.select2').select2({
    width: 'resolve',
    placeholder: 'Select an option',
    allowClear: true,
  });

  // ✅ Init DataTables
  $('table.datatable').each(function () {
    new DataTable(this);
  });

  // ✅ AJAX modal open handler
  $(document).on('click', '.ajax-modal-btn', function (e) {
    e.preventDefault();

    const url = $(this).data('url');
    const modalEl = document.getElementById('globalModal');
    if (!modalEl) return;

    const modalBody = modalEl.querySelector('.modal-body');
    if (!modalBody) return;

    modalBody.innerHTML = `
      <div class="d-flex justify-content-center align-items-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>`;

    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();

    $.get(url)
      .done((response) => {
        modalBody.innerHTML = response;

        $(modalBody).find('.select2').select2({
          width: '100%',
          dropdownParent: $('#globalModal'),
          allowClear: true,
          placeholder: 'Select an option',
        });
      })
      .fail(() => {
        modalBody.innerHTML = '<div class="alert alert-danger m-3">Failed to load content.</div>';
      });
  });

  // ✅ AJAX form submit
  $(document).on('submit', 'form.ajax-form', function (e) {
    e.preventDefault();

    const $form = $(this);
    const action = $form.attr('action');
    const method = 'POST';
    const formData = new FormData(this);
    const $errorBox = $form.find('.form-errors');

    // Clear previous errors
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.text-danger').remove();
    if ($errorBox.length) {
      $errorBox.addClass('d-none').find('ul').html('');
    }

    $.ajax({
      url: action,
      type: method,
      data: formData,
      contentType: false,
      processData: false,
      success: function () {
        const modal = document.getElementById('globalModal');
        bootstrap.Modal.getInstance(modal)?.hide();
        window.location.reload();
      },
      error: function (xhr) {
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors || {};

          if ($errorBox.length) {
            $errorBox.removeClass('d-none');
            const ul = $errorBox.find('ul').html('');
            $.each(errors, function (field, messages) {
              ul.append(`<li>${messages[0]}</li>`);
            });
          }

          $.each(errors, function (field, messages) {
            const baseField = field.replace(/\.\d+$/, '');
            const selector = `[name="${baseField}"], [name="${baseField}[]"]`;
            const $input = $form.find(selector);
            const message = messages[0];

            $input.addClass('is-invalid');

            const $customError = $form.find(`#error-${baseField}`);
            if ($customError.length) {
              $customError.text(message).show();
            } else if ($input.hasClass('select2-hidden-accessible')) {
              const $select2 = $input.next('.select2');
              if ($select2.next('.text-danger').length === 0) {
                $select2.after(`<div class="text-danger small">${message}</div>`);
              }
            } else {
              if ($input.next('.text-danger').length === 0) {
                $input.after(`<small class="text-danger">${message}</small>`);
              }
            }
          });

          const firstError = $form.find('.is-invalid').first();
          if (firstError.length) {
            $('html, body').animate({
              scrollTop: firstError.offset().top - 100,
            }, 600);
          }

        } else if (xhr.status === 419) {
          alert('Session expired. Please refresh the page and try again.');
        } else {
          alert('Unexpected error occurred.');
        }
      },
    });
  });

  // ✅ Modal close cleanup
  $('#globalModal').on('hidden.bs.modal', function () {
    const modal = $(this);
    modal.find('form').trigger('reset');
    modal.find('.text-danger, .alert').addClass('d-none').empty();
    modal.find('.is-invalid').removeClass('is-invalid');
    modal.find('.select2').val(null).trigger('change');

    if (modal.data('clear-on-close')) {
      modal.find('.modal-body').html('');
    }
  });
});
