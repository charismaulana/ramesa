<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Ramesa' }} - Ramba Meal System Analytics</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Rajdhani:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF4500;
            --primary-light: #FF6B35;
            --secondary: #FF8C00;
            --accent: #FFD700;
            --dark-bg: #0a0505;
            --card-bg: rgba(255, 69, 0, 0.08);
            --card-border: rgba(255, 107, 53, 0.3);
            --text-primary: #FFFFFF;
            --text-secondary: #CCCCCC;
            --text-muted: #888888;
            --success: #00FF88;
            --error: #FF4444;
            --gradient-main: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(255, 69, 0, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(255, 140, 0, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(255, 215, 0, 0.05) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(10, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--gradient-main);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 0 20px rgba(255, 69, 0, 0.5);
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(255, 69, 0, 0.5);
            }

            50% {
                box-shadow: 0 0 35px rgba(255, 69, 0, 0.8);
            }
        }

        .logo-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 2px;
        }

        .logo-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            gap: 0.5rem;
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-primary);
            background: var(--card-bg);
            border-color: var(--card-border);
            box-shadow: 0 0 20px rgba(255, 69, 0, 0.2);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.2), rgba(255, 140, 0, 0.1));
            border-color: var(--primary-light);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        /* Dropdown */
        .nav-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.5rem;
            background: rgba(20, 10, 10, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 0.5rem;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--card-bg);
            color: var(--text-primary);
        }

        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--card-border);
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--gradient-main);
            color: var(--dark-bg);
            box-shadow: 0 4px 15px rgba(255, 69, 0, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 69, 0, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-primary);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover {
            background: var(--card-bg);
            border-color: var(--primary-light);
        }

        .btn-success {
            background: linear-gradient(135deg, #00AA55, #00FF88);
            color: var(--dark-bg);
        }

        .btn-danger {
            background: linear-gradient(135deg, #CC3333, #FF4444);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 20px rgba(255, 69, 0, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23FF6B35' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        select.form-control option {
            background: #1a0a0a;
            color: var(--text-primary);
            padding: 0.75rem;
        }

        select.form-control option:hover,
        select.form-control option:checked {
            background: #2a1515;
        }

        /* Date input calendar icon styling */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--card-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--card-border);
        }

        th {
            background: rgba(255, 69, 0, 0.1);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Sortable Headers */
        th a.sortable {
            color: var(--accent);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        th a.sortable:hover {
            color: var(--primary);
            text-shadow: 0 0 10px rgba(255, 69, 0, 0.5);
        }

        th a.sortable.active {
            color: var(--primary);
        }

        th a.sortable i {
            font-size: 0.7rem;
        }

        tr:hover {
            background: rgba(255, 69, 0, 0.05);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: rgba(0, 255, 136, 0.15);
            color: var(--success);
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        .badge-danger {
            background: rgba(255, 68, 68, 0.15);
            color: var(--error);
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .badge-primary {
            background: rgba(255, 69, 0, 0.15);
            color: var(--primary-light);
            border: 1px solid rgba(255, 69, 0, 0.3);
        }

        .badge-warning {
            background: rgba(255, 215, 0, 0.15);
            color: var(--accent);
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: var(--success);
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: rgba(255, 69, 0, 0.2);
            border-color: var(--primary-light);
            color: var(--text-primary);
        }

        .pagination .active {
            background: var(--gradient-main);
            color: var(--dark-bg);
            border-color: transparent;
        }

        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Grid */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.75rem;
        }

        .col {
            padding: 0.75rem;
        }

        .col-12 {
            width: 100%;
        }

        .col-6 {
            width: 50%;
        }

        .col-4 {
            width: 33.333%;
        }

        .col-3 {
            width: 25%;
        }

        @media (max-width: 768px) {

            .col-6,
            .col-4,
            .col-3 {
                width: 100%;
            }

            .navbar-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .main-content {
                padding: 1rem;
            }
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 1rem;
        }

        .mb-3 {
            margin-bottom: 1.5rem;
        }

        .mt-2 {
            margin-top: 1rem;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-1 {
            gap: 0.5rem;
        }

        .gap-2 {
            gap: 1rem;
        }

        /* Actions column */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* QR Code display */
        .qr-code {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            display: inline-block;
        }

        .qr-code img {
            display: block;
            max-width: 200px;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Filter bar */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-end;
        }

        .filter-bar .form-group {
            margin-bottom: 0;
            min-width: 150px;
        }

        /* Mobile menu toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .navbar {
                padding: 0.5rem 1rem;
            }

            .navbar-container {
                flex-wrap: wrap;
            }

            .logo-text {
                font-size: 1rem;
            }

            .logo-text span {
                display: none;
            }

            .logo-icon {
                width: 35px;
                height: 35px;
            }

            .nav-links {
                display: none;
                width: 100%;
                order: 3;
                margin-top: 0.5rem;
                max-height: 60vh;
                overflow-y: auto;
            }

            .nav-links.active {
                display: flex;
                flex-direction: column;
            }

            .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
            }

            .nav-dropdown .dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                margin-top: 0;
                margin-left: 1rem;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .dropdown-item {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            /* User dropdown on mobile */
            .navbar-container>.nav-dropdown {
                order: 2;
                margin-left: auto;
            }

            .navbar-container>.nav-dropdown .nav-link {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }

            .navbar-container>.nav-dropdown .dropdown-menu {
                position: absolute;
                right: 0;
                left: auto;
                top: 100%;
                background: rgba(20, 10, 10, 0.98);
                border: 1px solid var(--card-border);
                opacity: 0;
                visibility: hidden;
            }

            .navbar-container>.nav-dropdown:hover .dropdown-menu {
                opacity: 1;
                visibility: visible;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 0.4rem 0.75rem;
            }

            .logo-icon {
                width: 30px;
                height: 30px;
            }

            .logo-text {
                font-size: 0.9rem;
                letter-spacing: 1px;
            }

            .main-content {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('scan.index') }}" class="logo">
                <div class="logo-icon">
                    @if(file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo"
                            style="width: 35px; height: 35px; object-fit: contain;">
                    @else
                        <i class="bi bi-qr-code"></i>
                    @endif
                </div>
                <div>
                    <div class="logo-text">RAMESA</div>
                    <div class="logo-subtitle">Ramba Meal System Analytics</div>
                </div>
            </a>

            <button class="mobile-toggle" onclick="document.querySelector('.nav-links').classList.toggle('active')">
                <i class="bi bi-list"></i>
            </button>

            <ul class="nav-links">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                @if(auth()->user()->canAccessFullFeatures())
                    <li class="nav-dropdown">
                        <a href="#"
                            class="nav-link {{ request()->routeIs('scan.*') || request()->routeIs('bulk.*') ? 'active' : '' }}">
                            <i class="bi bi-qr-code-scan"></i>
                            Scan
                            <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('scan.index') }}" class="dropdown-item">
                                <i class="bi bi-camera"></i>
                                QR Scan Station
                            </a>
                            {{-- Hidden: Manual Entry
                            <a href="{{ route('scan.manual') }}" class="dropdown-item">
                                <i class="bi bi-pencil-square"></i>
                                Manual Entry
                            </a>
                            --}}
                            <a href="{{ route('bulk.index') }}" class="dropdown-item">
                                <i class="bi bi-list-check"></i>
                                Bulk Input
                            </a>
                        </div>
                    </li>
                    <li class="nav-dropdown">
                        <a href="#" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            Employee
                            <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('employees.index') }}" class="dropdown-item">
                                <i class="bi bi-list-ul"></i>
                                Employee List
                            </a>
                            <a href="{{ route('employees.create') }}" class="dropdown-item">
                                <i class="bi bi-person-plus"></i>
                                Add Employee
                            </a>
                        </div>
                    </li>
                @endif
                <li class="nav-dropdown">
                    <a href="#"
                        class="nav-link {{ request()->routeIs('historical.*') || request()->routeIs('report.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        Historical
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="{{ route('historical.index') }}" class="dropdown-item">
                            <i class="bi bi-table"></i>
                            View Records
                        </a>
                        @if(auth()->user()->canAccessFullFeatures())
                            <a href="{{ route('historical.exportForm') }}" class="dropdown-item">
                                <i class="bi bi-file-earmark-excel"></i>
                                Export Excel
                            </a>
                            {{-- Hidden: Print PDF
                            <a href="{{ route('report.form') }}" class="dropdown-item">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Print PDF
                            </a>
                            --}}
                        @endif
                    </div>
                </li>
                @if(auth()->user()->isSuperAdmin())
                    <li>
                        <a href="{{ route('admin.users') }}"
                            class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i>
                            Admin
                        </a>
                    </li>
                @endif
            </ul>
            <div class="nav-dropdown">
                <a href="#" class="nav-link">
                    <i class="bi bi-person-circle"></i>
                    {{ auth()->user()->name }}
                    <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                </a>
                <div class="dropdown-menu" style="right: 0; left: auto;">
                    <div class="dropdown-item"
                        style="pointer-events: none; color: var(--text-muted); font-size: 0.85rem;">
                        <i class="bi bi-envelope"></i>
                        {{ auth()->user()->email }}
                    </div>
                    <div class="dropdown-item"
                        style="pointer-events: none; border-bottom: 1px solid var(--card-border);">
                        <i class="bi bi-shield-check"></i>
                        {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item"
                            style="width: 100%; border: none; background: none; cursor: pointer; text-align: left;">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="bi bi-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="bi bi-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer
        style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.9rem; border-top: 1px solid var(--card-border); margin-top: 2rem;">
        <p style="margin: 0;">© GS Ramba 2025. All rights reserved.</p>
    </footer>

    @stack('scripts')
</body>

</html>