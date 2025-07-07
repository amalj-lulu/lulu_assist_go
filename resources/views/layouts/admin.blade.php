@php use Illuminate\Support\Facades\Auth; @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - Lulu Assist Go</title>
    @vite(['resources/js/app.js']) {{-- Ensure this includes jQuery, Bootstrap, Select2 --}}
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- TOP NAVBAR --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="fas fa-user-circle mr-1"></i> {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        {{-- SIDEBAR --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light">Lulu Assist Go</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('plant-user.index') }}"
                                class="nav-link {{ request()->routeIs('plant-user.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Plant Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customers.index') }}"
                                class="nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                        <li class="nav-item has-treeview {{ request()->is('master/plants*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('master/plants*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Master <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('plants.index') }}"
                                        class="nav-link {{ request()->is('master/plants') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Plant Management</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <h1 class="m-0">@yield('page-title')</h1>
                </div>
            </div>
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <footer class="main-footer text-sm">
            <strong>&copy; {{ date('Y') }} Lulu Assist Go</strong> — All rights reserved.
        </footer>
    </div>

    {{-- GLOBAL MODAL --}}
    <div class="modal fade" id="globalAjaxModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="globalAjaxModalTitle">Loading...</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="globalAjaxModalContent">
                    <div class="modal-body text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function loadModalViaAjax(url) {
                if (!url) return;

                $('#globalAjaxModalTitle').text('Loading...');
                $('#globalAjaxModalContent').html(`
                    <div class="modal-body text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    </div>
                `);

                $('#globalAjaxModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#globalAjaxModalContent').html(response);

                        const title = $('#globalAjaxModalContent').find('[data-modal-title]').data(
                            'modal-title');
                        if (title) {
                            $('#globalAjaxModalTitle').text(title);
                        }

                        $('#globalAjaxModalContent').find('.select2').select2({
                            dropdownParent: $('#globalAjaxModal')
                        });
                    },
                    error: function(xhr) {
                        let fallback =
                            `<div class="modal-body text-danger">Failed to load content.</div>`;
                        let fallbackTitle = 'Error';

                        if (xhr.status === 422 || xhr.status === 200) {
                            $('#globalAjaxModalContent').html(xhr.responseText);
                            const title = $('#globalAjaxModalContent').find('[data-modal-title]').data(
                                'modal-title');
                            if (title) {
                                $('#globalAjaxModalTitle').text(title);
                            }

                            $('#globalAjaxModalContent').find('.select2').select2({
                                dropdownParent: $('#globalAjaxModal')
                            });
                        } else {
                            $('#globalAjaxModalContent').html(fallback);
                            $('#globalAjaxModalTitle').text(fallbackTitle);
                        }
                    }
                });
            }

            // Open modal when clicking button/link
            $(document).on('click', '.ajax-modal-btn', function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                if (url) loadModalViaAjax(url);
            });

            // Clear modal content on close
            $('#globalAjaxModal').on('hidden.bs.modal', function() {
                $('#globalAjaxModalContent').html('');
                $('#globalAjaxModalTitle').text('');
            });

            // Reopen modal after redirect (on validation error)
            @if (session('show_modal') && session('modal_url'))
                loadModalViaAjax(@json(session('modal_url')));
            @endif
        });

        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    </script>

</body>

</html>
@php
    $errors = session()->get('errors');
    if ($errors) {
        dd($errors->all()); // Print all error messages
    }
@endphp
