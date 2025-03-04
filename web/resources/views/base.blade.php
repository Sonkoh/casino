<html lang="es">

<head>
    <title>{{ env('APP_NAME') }} | {{ $title }}</title>
    <meta charset="utf-8">
    <!-- <meta name="description" content="
            The most advanced Bootstrap 5 Admin Theme with 40 unique prebuilt layouts on Themeforest trusted by 100,000 beginners and professionals. Multi-demo,
            Dark Mode, RTL support and complete React, Angular, Vue, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js, Node.js, Flask, Symfony &amp; Laravel versions.
            Grab your copy now and get life-time updates for free.
        "> -->
    <!-- <meta name="keywords" content="
            metronic, bootstrap, bootstrap 5, angular, VueJs, React, Asp.Net Core, Rails, Spring, Blazor, Django, Express.js,
            Node.js, Flask, Symfony &amp; Laravel starter kits, admin themes, web design, figma, web development, free templates,
            free admin themes, bootstrap theme, bootstrap template, bootstrap dashboard, bootstrap dak mode, bootstrap button,
            bootstrap datepicker, bootstrap timepicker, fullcalendar, datatables, flaticon
        ">  -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/branding/logo.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"> <!--end::Fonts-->
    <link href="/assets/plugins.bundle.css" rel="stylesheet" type="text/css">
    <link href="/assets/style.bundle.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <script>
        var defaultThemeMode = "light";
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }

        function toggleTheme() {
            mode = localStorage.getItem("data-bs-theme") == 'dark' ? 'light' : 'dark';
            localStorage.setItem("data-bs-theme", mode)
            document.documentElement.setAttribute("data-bs-theme", mode);
        }
    </script>
    @yield('head')
    <style>
        * {
            /* color: #2b223d; */
            -webkit-user-drag: none;
        }

        img {
            user-select: none;
        }

        .landing-header .menu .menu-link.active {
            color: #000;
        }
    </style>
    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body data-server="{{ $service['uuid'] ?? '' }}" data-csrf="{{ csrf_token() }}">
    <div class="d-flex flex-column flex-root">
        <div class="docs-page d-flex flex-row flex-column-fluid">
            <div id="kt_docs_aside" class="docs-aside" data-kt-drawer="true" data-kt-drawer-name="aside"
                data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
                data-kt-drawer-toggle="#kt_docs_aside_toggle">
                <div class="docs-aside-logo flex-column-auto h-75px px-7 d-flex align-items-center justify-content-center"
                    id="kt_docs_aside_logo">
                    <a href="/">
                        <img src="/branding/logo.png" class="h-50px">
                    </a>
                </div>
                <div class="docs-aside-menu flex-column-fluid mt-12">
                    <div class="hover-scroll-overlay-y mt-5 mb-5 mt-lg-0 mb-lg-5 mx-3" id="kt_docs_aside_menu_wrapper"
                        data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                        data-kt-scroll-height="auto"
                        data-kt-scroll-dependencies="#kt_docs_aside_logo, #kt_docs_aside_select, #kt_docs_aside_footer"
                        data-kt-scroll-wrappers="#kt_docs_aside_menu" data-kt-scroll-offset="10px"
                        style="height: 773px;">
                        <div class="menu menu-fit menu-column menu-title-gray-800 menu-arrow-gray-500 menu-state-primary fw-semibold px-5"
                            id="#kt_docs_aside_menu" data-kt-menu="true">
                            <div class="menu-item">
                                <h4 class="menu-content text-muted mb-0 fs-7 text-uppercase">Casino</h4>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link py-2" href="/"><span class="menu-title">Inicio</span></a>
                            </div>
                            @auth
                            <div class="menu-item">
                                <h4 class="menu-content text-muted mb-0 fs-7 text-uppercase">Mi Cuenta</h4>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link py-2" href="/auth/logout"><span class="menu-title">Cerrar
                                        Sesión</span></a>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            <div class="docs-wrapper d-flex flex-column flex-row-fluid" id="kt_docs_wrapper" @yield('wrapper')>
                @yield('loading')
                <div id="kt_docs_header" class="docs-header align-items-stretch mb-2 mb-lg-10">
                    <!--begin::Container-->
                    <div class="container">
                        <div class="d-flex align-items-stretch justify-content-between py-3 h-75px">
                            <!--begin::Aside toggle-->
                            <div class="d-flex align-items-center d-lg-none ms-n2 me-1" title="Show aside menu">
                                <div class="btn btn-icon btn-flex btn-active-color-primary" id="kt_docs_aside_toggle">
                                    <i class="ki-duotone ki-abstract-14 fs-2"><span class="path1"></span><span
                                            class="path2"></span></i>
                                </div>
                            </div>
                            <!--end::Aside toggle-->

                            <!--begin::Logo-->
                            <div class="d-flex d-lg-none align-items-center flex-grow-1 flex-lg-grow-0 me-3 me-lg-15">
                                <a href="/panel">
                                    <img src="/branding/logo.png" class="h-25px">
                                </a>
                            </div>
                            <!--end::Logo-->

                            <!--begin::Wrapper-->
                            <div class="d-flex align-items-center justify-content-between flex-lg-grow-1">
                                <!--begin::Header title-->
                                <div class="d-flex align-items-center" id="kt_docs_header_title">

                                    <!--begin::Page title-->
                                    <div class="docs-page-title d-flex flex-column flex-lg-row align-items-lg-center py-5 mb-lg-0"
                                        data-kt-swapper="true" data-kt-swapper-mode="prepend"
                                        data-kt-swapper-parent="{default: '#kt_docs_content_container', 'lg': '#kt_docs_header_title'}">
                                        <h1 class="d-flex align-items-center text-gray-900 my-1 fs-4"
                                            style="margin-right: .5rem;">{{ $title }}</h1>
                                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-6 my-1">
                                            @php
                                            $url = '/';
                                            @endphp
                                            @foreach (explode('/', $_SERVER['REQUEST_URI']) as $page)
                                            @if ($page == '')
                                            @continue
                                            @endif
                                            @php
                                            $url .= $page . '/';
                                            @endphp
                                            <li class="breadcrumb-item text-gray-600">
                                                /
                                            </li>
                                            <li class="breadcrumb-item text-gray-900">
                                                <a href="{{ $url }}"
                                                    class="text-muted">{{ ucfirst(explode('?', $page)[0]) }}</a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="flex-equal text-end">
                                        <div class="d-flex gap-4">
                                            @auth
                                            <div class="flex-equal text-end">
                                                <a href="/panel/account" class="btn btn-sm bg-secondary">
                                                    <div class="d-flex gap-2 text-gray-700">
                                                        <div class="d-flex"><i
                                                                class="ki-duotone ki-down m-auto fs-5"><span
                                                                    class="path1"></span><span
                                                                    class="path2"></span><span
                                                                    class="path3"></span></i></div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="d-flex align-items-center" id="kt_header_user_menu_toggle">
                                                <!--begin::Menu wrapper-->
                                                <div class="btn-secondary btn rounded w-35px h-35px p-0 d-flex align-items-center justify-content-center"
                                                    data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                                                    data-kt-menu-placement="bottom-end">
                                                    <i class="ki-duotone ki-user p-0">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>

                                                <!--begin::User account menu-->
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                                    data-kt-menu="true">
                                                    <!--begin::Menu item-->
                                                    <div class="menu-item px-3">
                                                        <div class="menu-content d-flex align-items-center px-3">
                                                            <!--begin::Avatar-->
                                                            <div class="h-50px w-50px me-5 bg-light rounded d-flex">
                                                                <i class="ki-duotone ki-user p-0 fs-2x m-auto">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </div>
                                                            <div class="d-flex flex-column">
                                                                <div
                                                                    class="fw-bold d-flex align-items-center fs-5 mb-n1 text-gray-700">
                                                                    {{ auth()->user()->firstname }}
                                                                    {{ auth()->user()->lastname }}
                                                                </div>
                                                                <p class="text-muted fs-6 m-0"
                                                                    style="font-weight: 100">
                                                                    {{ auth()->user()->email }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="separator my-2"></div>
                                                    <div class="menu-item px-5">
                                                        <a href="/panel/account"
                                                            class="menu-link px-5">
                                                            Mi Cuenta
                                                        </a>
                                                    </div>
                                                    <div class="menu-item px-5">
                                                        <a href="/panel/account?movements"
                                                            class="menu-link px-5">
                                                            Movimientos
                                                        </a>
                                                    </div>
                                                    <div class="menu-item px-5 my-1">
                                                        <div onclick="toggleTheme()"
                                                            class="menu-link px-5">
                                                            Alternar Modo Oscuro
                                                        </div>
                                                    </div>
                                                    <div class="separator my-2"></div>
                                                    <div class="menu-item px-5">
                                                        <a href="/auth/logout" class="menu-link px-5">
                                                            Cerrar Sesión
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                                <!--end::Toolbar-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <div class="border-gray-300 border-bottom border-bottom"></div>
                    </div>
                    <!--end::Container-->
                </div>
                <div class="docs-content d-flex flex-column flex-column-fluid">
                    <div class="{!! $content_container ?? 'container'!!}">
                        @yield('content')
                    </div>
                </div>
                <!--end::Content-->

                <!--begin::Footer-->
                <div class="py-4 d-flex flex-lg-column py-6" id="kt_footer">
                    <!--begin::Container-->
                    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <!--begin::Copyright-->
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-muted fw-semibold me-1">2025©</span>
                            <a href="/" target="_blank" class="text-gray-800 text-hover-primary">{{env("APP_NAME")}}</a>
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    @yield('modals')
    <script>
        var hostUrl = "/assets/";
    </script>

    <script src="/assets/plugins.bundle.js"></script>
    <script src="/assets/scripts.bundle.js"></script>
    @yield('scripts')
</body>

</html>