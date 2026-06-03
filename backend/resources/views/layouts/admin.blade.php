<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'MSW') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root {
                --sidebar-width: 272px;
                --sidebar-collapsed: 76px;
                --bg: #f5f6f7;
                --panel: #ffffff;
                --ink: #1f2933;
                --muted: #697386;
                --line: #e1e5ea;
                --line-soft: #eef1f4;
                --brand: #c2410c;
                --nav: #1f2933;
                --nav-muted: #c5ccd6;
                --success: #047857;
                --warning: #b45309;
                --info: #0369a1;
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                min-width: 320px;
                background: var(--bg);
                color: var(--ink);
                font-family: "Instrument Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }

            body.sidebar-collapsed {
                --sidebar-width: var(--sidebar-collapsed);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            button {
                font: inherit;
            }

            .admin-shell {
                min-height: 100vh;
            }

            .admin-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 30;
                width: var(--sidebar-width);
                overflow: hidden;
                background: var(--nav);
                color: #fff;
                border-right: 1px solid rgba(255, 255, 255, .08);
                transition: width .18s ease, transform .18s ease;
            }

            .brand {
                height: 76px;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 0 22px;
                border-bottom: 1px solid rgba(255, 255, 255, .1);
                overflow: hidden;
            }

            .brand-mark {
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                display: grid;
                place-items: center;
                border-radius: 6px;
                background: var(--brand);
                color: #fff;
                font-size: 17px;
                font-weight: 700;
            }

            .brand-copy {
                min-width: 0;
                transition: opacity .14s ease;
            }

            .brand-title,
            .brand-subtitle {
                display: block;
                white-space: nowrap;
            }

            .brand-title {
                font-size: 13px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .brand-subtitle {
                margin-top: 2px;
                color: var(--nav-muted);
                font-size: 12px;
            }

            .admin-nav {
                display: grid;
                gap: 4px;
                padding: 18px 12px;
            }

            .admin-nav a {
                min-height: 42px;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 12px;
                overflow: hidden;
                border-radius: 6px;
                color: var(--nav-muted);
                font-weight: 650;
            }

            .admin-nav a:hover,
            .admin-nav a.is-active {
                background: #fff;
                color: var(--ink);
            }

            .nav-icon {
                width: 22px;
                height: 22px;
                flex: 0 0 22px;
                display: grid;
                place-items: center;
                border-radius: 6px;
                color: inherit;
            }

            .nav-icon svg {
                width: 19px;
                height: 19px;
                stroke: currentColor;
                stroke-width: 1.9;
                stroke-linecap: round;
                stroke-linejoin: round;
                fill: none;
            }

            .nav-label {
                white-space: nowrap;
                transition: opacity .14s ease;
            }

            .admin-main {
                min-width: 0;
                margin-left: var(--sidebar-width);
                transition: margin-left .18s ease;
            }

            .topbar {
                position: sticky;
                top: 0;
                z-index: 20;
                min-height: 76px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 14px 32px;
                background: rgba(255, 255, 255, .97);
                border-bottom: 1px solid var(--line);
            }

            .topbar-left {
                min-width: 0;
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .sidebar-toggle {
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                display: inline-grid;
                place-items: center;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #fff;
                cursor: pointer;
            }

            .hamburger {
                width: 18px;
                display: grid;
                gap: 4px;
            }

            .hamburger span {
                height: 2px;
                border-radius: 99px;
                background: #344054;
            }

            .eyebrow {
                margin: 0;
                color: var(--brand);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .page-title {
                margin: 2px 0 0;
                font-size: 22px;
                line-height: 1.2;
            }

            .topbar-actions,
            .toolbar-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn {
                min-height: 40px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #fff;
                color: #344054;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
            }

            .btn-primary {
                border-color: var(--brand);
                background: var(--brand);
                color: #fff;
            }

            .btn-dark {
                border-color: var(--nav);
                background: var(--nav);
                color: #fff;
            }

            .content {
                padding: 28px 32px 40px;
            }

            .metrics-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
            }

            .metric-card,
            .panel {
                background: var(--panel);
                border: 1px solid var(--line);
                border-radius: 8px;
                box-shadow: 0 1px 1px rgba(16, 24, 40, .03);
            }

            .metric-card {
                padding: 18px;
            }

            .label {
                margin: 0;
                color: var(--muted);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .metric-row {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 16px;
                margin-top: 20px;
            }

            .metric-value {
                font-size: 36px;
                line-height: 1;
                font-weight: 700;
            }

            .metric-note {
                max-width: 128px;
                color: var(--muted);
                font-size: 12px;
                font-weight: 650;
                text-align: right;
            }

            .dashboard-grid {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
                gap: 20px;
                margin-top: 22px;
            }

            .panel-header,
            .table-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                padding: 18px 20px;
                border-bottom: 1px solid var(--line);
            }

            .panel-title {
                margin: 2px 0 0;
                font-size: 18px;
                line-height: 1.25;
            }

            .flow-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 12px;
                padding: 18px;
            }

            .flow-card {
                min-height: 146px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding: 14px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #fbfcfd;
            }

            .flow-bar {
                height: 4px;
                border-radius: 99px;
                background: var(--brand);
            }

            .tone-blue .flow-bar { background: #0284c7; }
            .tone-amber .flow-bar { background: #d97706; }
            .tone-red .flow-bar { background: #c2410c; }
            .tone-violet .flow-bar { background: #6d5bd0; }
            .tone-green .flow-bar { background: #059669; }

            .flow-name {
                min-height: 42px;
                margin: 16px 0 10px;
                font-weight: 700;
            }

            .flow-count {
                margin: 0;
                font-size: 30px;
                line-height: 1;
                font-weight: 700;
            }

            .flow-unit {
                margin: 4px 0 0;
                color: var(--muted);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .activity-list {
                display: grid;
                gap: 16px;
                padding: 20px;
            }

            .activity-item {
                display: grid;
                grid-template-columns: 12px minmax(0, 1fr);
                gap: 12px;
                color: #475467;
            }

            .activity-dot {
                width: 8px;
                height: 8px;
                margin-top: 7px;
                border-radius: 99px;
                background: var(--brand);
            }

            .table-panel {
                margin-top: 22px;
            }

            .table-wrap {
                width: 100%;
                overflow-x: auto;
            }

            .admin-table {
                width: 100%;
                min-width: 760px;
                border-collapse: collapse;
                text-align: left;
            }

            .admin-table th {
                background: #f8fafc;
                color: var(--muted);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .admin-table th,
            .admin-table td {
                padding: 15px 20px;
                border-bottom: 1px solid var(--line-soft);
            }

            .admin-table tbody tr:hover {
                background: #fbfcfd;
            }

            .cell-strong {
                font-weight: 700;
            }

            .cell-muted {
                color: var(--muted);
            }

            .text-right {
                text-align: right;
            }

            .link-button {
                border: 0;
                background: transparent;
                color: var(--brand);
                padding: 0;
                font-weight: 700;
                cursor: pointer;
            }

            .status-pill {
                display: inline-flex;
                align-items: center;
                min-height: 26px;
                border-radius: 99px;
                padding: 4px 10px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .status-pending {
                background: #fff7ed;
                color: var(--warning);
            }

            .status-confirmed {
                background: #ecfdf3;
                color: var(--success);
            }

            .status-waiting {
                background: #eff6ff;
                color: var(--info);
            }

            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 25;
                display: none;
                background: rgba(15, 23, 42, .34);
            }

            body.sidebar-collapsed .brand {
                justify-content: center;
                padding: 0 14px;
            }

            body.sidebar-collapsed .brand-copy,
            body.sidebar-collapsed .nav-label {
                width: 0;
                opacity: 0;
                pointer-events: none;
            }

            body.sidebar-collapsed .admin-nav {
                padding: 18px 10px;
            }

            body.sidebar-collapsed .admin-nav a {
                justify-content: center;
                padding: 10px;
            }

            @media (max-width: 1180px) {
                .metrics-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .dashboard-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 900px) {
                .admin-sidebar {
                    width: 272px;
                    transform: translateX(-100%);
                }

                .admin-main {
                    margin-left: 0;
                }

                body.sidebar-open .admin-sidebar {
                    transform: translateX(0);
                }

                body.sidebar-open .sidebar-backdrop {
                    display: block;
                }

                body.sidebar-collapsed .admin-sidebar {
                    width: 272px;
                }

                body.sidebar-collapsed .brand {
                    justify-content: flex-start;
                    padding: 0 22px;
                }

                body.sidebar-collapsed .brand-copy,
                body.sidebar-collapsed .nav-label {
                    width: auto;
                    opacity: 1;
                    pointer-events: auto;
                }

                body.sidebar-collapsed .admin-nav {
                    padding: 18px 12px;
                }

                body.sidebar-collapsed .admin-nav a {
                    justify-content: flex-start;
                    padding: 10px 12px;
                }

                .topbar {
                    padding: 16px 20px;
                }

                .content {
                    padding: 22px 20px 32px;
                }

                .flow-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .topbar,
                .panel-header,
                .table-toolbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .topbar-left {
                    width: 100%;
                }

                .topbar-actions,
                .toolbar-actions {
                    width: 100%;
                }

                .btn {
                    flex: 1;
                }

                .metrics-grid,
                .flow-grid {
                    grid-template-columns: 1fr;
                }

                .metric-row {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .metric-note {
                    max-width: none;
                    text-align: left;
                }
            }
        </style>
    </head>
    <body>
        <div class="admin-shell">
            <aside class="admin-sidebar" id="adminSidebar">
                <a class="brand" href="{{ route('admin.dashboard') }}">
                    <span class="brand-mark">M</span>
                    <span class="brand-copy">
                        <span class="brand-title">MSW Admin</span>
                        <span class="brand-subtitle">Motor service ops</span>
                    </span>
                </a>

                <nav class="admin-nav" aria-label="Admin navigation">
                    <a class="is-active" href="{{ route('admin.dashboard') }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 13h7V4H4v9Z"></path><path d="M13 20h7V4h-7v16Z"></path><path d="M4 20h7v-5H4v5Z"></path></svg>
                        </span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M8 3v4"></path><path d="M16 3v4"></path><path d="M4 9h16"></path><path d="M5 5h14a1 1 0 0 1 1 1v15H4V6a1 1 0 0 1 1-1Z"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path></svg>
                        </span>
                        <span class="nav-label">Bookings</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M14.7 6.3a4 4 0 0 0-5.6 5.6l-5.4 5.4a2 2 0 0 0 2.8 2.8l5.4-5.4a4 4 0 0 0 5.6-5.6l-2.8 2.8-2.8-2.8 2.8-2.8Z"></path></svg>
                        </span>
                        <span class="nav-label">Work Orders</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                        </span>
                        <span class="nav-label">Customers</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                        </span>
                        <span class="nav-label">Inventory</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M3 3v18h18"></path><path d="m7 15 4-4 3 3 5-7"></path></svg>
                        </span>
                        <span class="nav-label">Reports</span>
                    </a>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.55-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.55V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.55 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.55 1Z"></path></svg>
                        </span>
                        <span class="nav-label">Settings</span>
                    </a>
                </nav>
            </aside>

            <button class="sidebar-backdrop" type="button" aria-label="Close sidebar" data-sidebar-close></button>

            <div class="admin-main">
                <header class="topbar">
                    <div class="topbar-left">
                        <button class="sidebar-toggle" type="button" aria-label="Toggle sidebar" aria-controls="adminSidebar" aria-expanded="true" data-sidebar-toggle>
                            <span class="hamburger" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                        <div>
                            <p class="eyebrow">@yield('eyebrow', 'Admin')</p>
                            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="topbar-actions">
                        <button class="btn" type="button">Export</button>
                        <button class="btn btn-primary" type="button">New Booking</button>
                    </div>
                </header>

                <main class="content">
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            (() => {
                const body = document.body;
                const toggle = document.querySelector('[data-sidebar-toggle]');
                const close = document.querySelector('[data-sidebar-close]');
                const storageKey = 'msw-admin-sidebar-collapsed';
                const isMobile = () => window.matchMedia('(max-width: 900px)').matches;

                if (localStorage.getItem(storageKey) === 'true' && !isMobile()) {
                    body.classList.add('sidebar-collapsed');
                    toggle?.setAttribute('aria-expanded', 'false');
                }

                toggle?.addEventListener('click', () => {
                    if (isMobile()) {
                        body.classList.toggle('sidebar-open');
                        toggle.setAttribute('aria-expanded', body.classList.contains('sidebar-open') ? 'true' : 'false');
                        return;
                    }

                    body.classList.toggle('sidebar-collapsed');
                    const collapsed = body.classList.contains('sidebar-collapsed');
                    localStorage.setItem(storageKey, collapsed ? 'true' : 'false');
                    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                });

                close?.addEventListener('click', () => {
                    body.classList.remove('sidebar-open');
                    toggle?.setAttribute('aria-expanded', 'false');
                });
            })();
        </script>
    </body>
</html>
