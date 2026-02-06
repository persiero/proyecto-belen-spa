<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Belen Degregori | @yield('titulo', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    {{-- FAVICON --}}
    <link rel="icon" type="image/png" href="{{ asset('adminlte/assets/img/Logo-belen.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('adminlte/assets/img/Logo-belen.png') }}">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/belen-custom.css') }}"/>
    
    @stack('css')
    @livewireStyles
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        
        @include('admin.partials.navbar')

        @include('admin.partials.sidebar')

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('titulo')</h3>
                        </div>
                        <div class="col-sm-6">
                            </div>
                    </div>
                </div>
            </div>

            <div class="app-content" style="padding-bottom: 250px;">
                <div class="container-fluid">
                    {{-- Lógica Híbrida: Soporta Livewire ($slot) y Blade Normal (@yield) --}}
                    @if(isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </div>
            </div>
        </main>

        @include('admin.partials.footer')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
    <script src="{{ asset('adminlte/js/adminlte.js') }}"></script>

    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>

    @stack('scripts')
    @livewireScripts
</body>
</html>