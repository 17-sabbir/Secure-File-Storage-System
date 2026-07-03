<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure File Storage System — @yield('title', 'Home')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/cryptologo.png?v={{ time() }}" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #1D3353;
            --primary-light: #264573;
            --accent: #3B82F6;
            --bg: #0f1923;
            --card: #1a2a3a;
            --card2: #1e3044;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #2d4a6b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        /* Light Mode Theme */
        :root[data-theme="light"] {
            --primary: #f8fafc;
            --primary-light: #e2e8f0;
            --accent: #3B82F6;
            --bg: #ffffff;
            --card: #f1f5f9;
            --card2: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --border: #cbd5e1;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
        }

        :root[data-theme="light"] .nav-brand {
            color: #1e293b;
        }

        :root[data-theme="light"] .nav-brand i {
            color: #3B82F6;
        }

        :root[data-theme="light"] nav {
            background: #f8fafc;
            border-bottom-color: #3B82F6;
        }

        :root[data-theme="light"] .btn-outline {
            color: #1e293b;
            border-color: #cbd5e1;
        }

        :root[data-theme="light"] .btn-outline:hover {
            background: #e2e8f0;
            color: #3B82F6;
        }

        /* Dark Mode Theme (default) */
        :root[data-theme="dark"] {
            --primary: #1D3353;
            --primary-light: #264573;
            --accent: #3B82F6;
            --bg: #0f1923;
            --card: #1a2a3a;
            --card2: #1e3044;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --border: #2d4a6b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Light Mode Background */
        :root[data-theme="light"] body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 25%, #f1f5f9 50%, #e0f2fe 75%, #f0f9ff 100%) fixed;
            background-size: 400% 400%;
            animation: gradientShiftLight 15s ease infinite;
        }

        /* Dark Mode Background (default) */
        :root[data-theme="dark"] body {
            background: linear-gradient(135deg, #0a1e2e 0%, #16334a 25%, #1a2a3a 50%, #16334a 75%, #0a1e2e 100%) fixed;
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShiftLight {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Navbar */
        nav {
            background: var(--primary);
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent);
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 20px rgba(0,0,0,0.4);
            transition: all 0.2s ease;
        }
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #fff;
            display: flex; align-items: center; gap: 10px;
            transition: all 0.3s ease;
        }
        .nav-brand i { color: var(--accent); transition: all 0.3s ease; }
        .nav-right { display: flex; align-items: center; gap: 16px; }
        .nav-email { color: var(--muted); font-size: 0.9rem; transition: all 0.3s ease; }

        /* Theme Toggle Button */
        .theme-toggle-btn {
            transition: all 0.3s ease;
        }

        .theme-toggle-btn i {
            transition: transform 0.3s ease;
        }

        .theme-toggle-btn:hover i {
            transform: rotate(20deg);
        }

        /* Light mode icon */
        :root[data-theme="light"] .theme-toggle-btn i::before {
            content: '\f185';
        }

        /* Dark mode icon */
        :root[data-theme="dark"] .theme-toggle-btn i::before {
            content: '\f186';
        }

        /* Buttons */
        .btn {
            padding: 9px 20px; border-radius: 8px; border: none;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; display: inline-flex; align-items: center; gap: 7px;
            text-decoration: none;
        }
        .btn-primary { background: var(--accent); color: #fff; transition: all 0.2s ease; }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
        .btn-danger  { background: var(--danger); color: #fff; transition: all 0.2s ease; }
        .btn-danger:hover  { background: #dc2626; }
        .btn-outline { background: transparent; color: var(--text); border: 1px solid var(--border); transition: all 0.2s ease; }
        .btn-outline:hover { background: var(--card2); }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }

        /* Cards */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            transition: all 0.2s ease;
        }
        .card-title {
            font-size: 1.15rem; font-weight: 700;
            margin-bottom: 20px; color: var(--accent);
            display: flex; align-items: center; gap: 8px;
            transition: all 0.2s ease;
        }

        /* Form elements */
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 6px; font-weight: 600; transition: all 0.2s ease; }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"], select {
            width: 100%; padding: 14px 16px;
            background: rgba(255,255,255,0.95); border: 1px solid rgba(45, 74, 107, 0.12);
            border-radius: 10px; color: #071029; font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.15s, background 0.2s ease, color 0.2s ease;
            box-shadow: 0 6px 18px rgba(2,6,23,0.06) inset;
        }

        :root[data-theme="light"] input[type="text"],
        :root[data-theme="light"] input[type="email"],
        :root[data-theme="light"] input[type="password"],
        :root[data-theme="light"] select {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        :root[data-theme="dark"] input[type="text"],
        :root[data-theme="dark"] input[type="email"],
        :root[data-theme="dark"] input[type="password"],
        :root[data-theme="dark"] select {
            background: rgba(255,255,255,0.95);
            border-color: rgba(45, 74, 107, 0.12);
            color: #071029;
        }

        input:focus, select:focus { outline: none; border-color: var(--accent); }
        .key-input { font-family: 'Courier New', monospace; letter-spacing: 2px; }
        .key-counter {
            text-align: right; font-size: 0.78rem;
            margin-top: 4px; color: var(--muted);
            transition: all 0.2s ease;
        }
        .key-counter.ok  { color: var(--success); }
        .key-counter.bad { color: var(--danger);  }

        /* Alerts */
        .alert {
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
            font-size: 0.9rem;
        }
        .alert-success { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.3); color: var(--success); }
        .alert-danger  { background: rgba(239,68,68,.15);  border: 1px solid rgba(239,68,68,.3);  color: var(--danger); }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; padding: 12px 14px;
            background: var(--primary); font-size: 0.82rem;
            text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted);
        }
        tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        tbody tr:hover { background: var(--card2); }
        tbody td { padding: 12px 14px; font-size: 0.9rem; }

        /* Badges */
        .badge {
            padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;
        }
        .badge-aes { background: rgba(59,130,246,.2); color: #60a5fa; }
        .badge-des { background: rgba(245,158,11,.2); color: #fbbf24; }
        .badge-enc { background: rgba(34,197,94,.15); color: #4ade80; }
        .badge-dec { background: rgba(239,68,68,.15);  color: #f87171; }

        /* Layout helpers */
        .container { max-width: 1100px; margin: 0 auto; padding: 32px 20px; }
        .grid-2    { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        @media(max-width:700px) { .grid-2 { grid-template-columns: 1fr; } }
        .mt-24 { margin-top: 24px; }
        .text-center { text-align: center; }
        .text-muted { color: var(--muted); font-size: 0.85rem; }

        /* Dashboard Main Layout */
        .dashboard-main {
            display: flex;
            gap: 20px;
            min-height: calc(100vh - 80px);
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 20px;
        }

        @media(max-width: 1024px) {
            .dashboard-main {
                flex-direction: column;
                gap: 24px;
            }
        }

        /* Sidebar Form */
        .sidebar-form {
            width: 420px;
            flex-shrink: 0;
        }

        @media(max-width: 1024px) {
            .sidebar-form {
                width: 100%;
            }
        }

        .sidebar-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        @media(max-width: 1024px) {
            .sidebar-card {
                position: relative;
                top: 0;
            }
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-title i {
            font-size: 1.3rem;
        }

        /* Operation Buttons */
        .op-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }

        .op-btn-main {
            flex: 1;
            padding: 10px 12px;
            background: var(--card2);
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .op-btn-main:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .op-btn-main-active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 0;
        }

        @media(max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(26, 42, 58, 0.8) 0%, rgba(30, 48, 68, 0.8) 100%);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        /* File Drop Zone for Sidebar */
        .file-drop-zone-sidebar {
            border: 2px dashed var(--border);
            border-radius: 8px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(59, 130, 246, 0.02);
        }

        .file-drop-zone-sidebar:hover {
            border-color: var(--accent);
            background: rgba(59, 130, 246, 0.08);
        }

        .file-drop-zone-sidebar i {
            font-size: 2rem;
            color: var(--accent);
            display: block;
            margin-bottom: 8px;
        }

        .drop-text-sidebar {
            color: var(--text);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .drop-hint-sidebar {
            color: var(--muted);
            font-size: 0.8rem;
        }

        .browse-file-link {
            display: block;
            text-align: center;
            color: var(--accent);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .browse-file-link:hover {
            text-decoration: underline;
            color: #60a5fa;
        }

        /* Table Card */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Auth page */
        .auth-wrap {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
        }
        .auth-card {
            /* Subtler, more transparent glass panel */
            background: linear-gradient(180deg, rgba(14,24,35,0.45), rgba(20,34,50,0.50));
            border: 1px solid rgba(59, 130, 246, 0.55);
            border-radius: 18px;
            padding: 48px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 30px 80px rgba(2,6,23,0.55), inset 0 1px 0 rgba(255,255,255,0.02);
            backdrop-filter: blur(14px) saturate(120%);
            -webkit-backdrop-filter: blur(14px) saturate(120%);
            transform: translateY(8px);
            opacity: 0;
            animation: authCardIn 600ms ease-out forwards;
            position: relative;
        }
        @keyframes authCardIn {
            to { transform: translateY(0); opacity: 1; }
        }
        .auth-card:hover {
            box-shadow:
                0 24px 70px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(59, 130, 246, 0.12) inset,
                0 0 60px rgba(59, 130, 246, 0.22);
        }
        .auth-logo {
            text-align: center; margin-bottom: 30px;
        }
        .auth-logo i { font-size: 3rem; color: var(--accent); }
        .auth-logo h1 { font-size: 1.6rem; font-weight: 800; margin-top: 10px; letter-spacing: 1px; }
        .auth-logo p  { color: var(--muted); font-size: 0.85rem; margin-top: 4px; }

        /* Delete form inline */
        .delete-form { display: inline; }

        /* Operation Toggle Styling */
        #operationToggle {
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--card2) 0%, var(--card) 100%);
            color: var(--text);
            border: 2px solid var(--accent);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        #operationToggle:hover {
            background: linear-gradient(135deg, var(--accent) 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        #operationToggle:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .crypto-form-section {
            transition: all 0.3s ease;
        }

        /* Filter Button Styling */
        .filter-btn {
            padding: 8px 14px;
            background: var(--card2);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .filter-btn:hover {
            background: var(--card);
            border-color: var(--accent);
            color: var(--accent);
        }

        .filter-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(26, 42, 58, 0.6) 0%, rgba(30, 48, 68, 0.6) 100%);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 500;
        }

        /* Dashboard Grid Layout */
        .dashboard-grid {
            display: none;
        }

        .form-column, .table-column {
            display: none;
        }

        .form-card, .table-card {
            padding: 24px !important;
        }

        .form-header {
            display: none;
        }

        /* File Input Styling */
        .file-input-wrapper {
            position: relative;
            margin-top: 8px;
        }

        .file-input {
            display: none;
        }

        .file-drop-zone {
            display: none;
        }

        .drop-text {
            display: none;
        }

        .drop-hint {
            display: none;
        }

        .btn-large {
            width: 100% !important;
            padding: 12px 16px !important;
            font-size: 0.95rem !important;
            justify-content: center !important;
        }

        /* Table Header */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border);
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
        }

        .file-count {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .table-wrapper {
            flex: 1;
            overflow-y: auto;
        }

        .files-table {
            width: 100%;
            border-collapse: collapse;
        }

        .files-table thead th {
            background: rgba(59, 130, 246, 0.08);
            padding: 12px 14px;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            border-bottom: 2px solid var(--border);
        }

        .files-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: all 0.15s ease;
        }

        .files-table tbody tr:hover {
            background: var(--card2);
        }

        .files-table tbody td {
            padding: 14px;
            font-size: 0.9rem;
        }

        .file-name-cell {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .file-type-icon {
            display: inline-flex;
            align-items: center;
        }

        /* Success and Error Messages for Encryption/Decryption */
        .success-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            margin-top: 10px;
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 6px;
            color: var(--success);
            font-size: 0.85rem;
            font-weight: 600;
            animation: slideDown 0.3s ease;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            margin-top: 10px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            color: var(--danger);
            font-size: 0.85rem;
            font-weight: 600;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Responsive Design */
        @media(max-width: 768px) {
            nav {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 12px;
            }

            .nav-brand {
                font-size: 1.2rem;
            }

            .nav-brand i {
                display: none;
            }

            .nav-right {
                gap: 8px;
                width: 100%;
                justify-content: flex-end;
            }

            .dashboard-main {
                padding: 16px 12px;
                gap: 16px;
            }

            .sidebar-form {
                width: 100%;
            }

            .sidebar-card {
                position: relative;
                top: 0;
                padding: 16px;
            }

            .sidebar-title {
                font-size: 1rem;
                margin-bottom: 16px;
            }

            .sidebar-title i {
                font-size: 1.1rem;
            }

            .op-buttons {
                margin-bottom: 16px;
                gap: 6px;
            }

            .op-btn-main {
                font-size: 0.8rem;
                padding: 8px 10px;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 16px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }

            .table-card {
                padding: 16px;
                margin-bottom: 20px;
            }

            .table-header {
                flex-direction: column;
                gap: 12px;
                margin-bottom: 16px;
            }

            .table-header > div {
                width: 100%;
            }

            #typeFilterButtons {
                width: 100% !important;
            }

            .filter-btn {
                flex: 1;
                min-width: 0;
                justify-content: center;
                padding: 8px 10px;
                font-size: 0.75rem;
            }

            .files-table {
                font-size: 0.8rem;
            }

            .files-table thead th {
                padding: 10px 8px;
                font-size: 0.7rem;
            }

            .files-table tbody td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }

            .file-drop-zone-sidebar {
                padding: 16px 12px;
            }

            .file-drop-zone-sidebar i {
                font-size: 1.5rem;
                margin-bottom: 6px;
            }

            .drop-text-sidebar {
                font-size: 0.85rem;
                margin-bottom: 3px;
            }

            .drop-hint-sidebar {
                font-size: 0.75rem;
            }

            .browse-file-link {
                font-size: 0.8rem;
                margin-top: 8px;
            }

            .btn {
                padding: 8px 16px;
                font-size: 0.85rem;
                gap: 5px;
            }

            .btn-large {
                padding: 10px 14px !important;
                font-size: 0.9rem !important;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 0.75rem;
            }

            input[type="text"], input[type="email"], input[type="password"], select {
                padding: 10px 12px;
                font-size: 0.95rem;
            }

            label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .key-counter {
                font-size: 0.7rem;
                margin-top: 3px;
            }

            .key-input {
                letter-spacing: 1px;
            }

            .table-title {
                font-size: 1rem;
            }

            .file-count {
                font-size: 0.8rem;
                padding: 2px 8px;
            }

            .badge {
                padding: 2px 8px;
                font-size: 0.7rem;
            }
        }

        @media(max-width: 480px) {
            nav {
                padding: 10px 12px;
            }

            .nav-brand {
                font-size: 1rem;
            }

            .nav-email {
                display: none;
            }

            .dashboard-main {
                padding: 12px 10px;
                gap: 12px;
            }

            .sidebar-card {
                padding: 12px;
            }

            .sidebar-title {
                font-size: 0.95rem;
            }

            .sidebar-title i {
                font-size: 1rem;
            }

            .op-btn-main {
                font-size: 0.75rem;
                padding: 7px 8px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-card {
                padding: 12px;
                flex-direction: row;
                gap: 12px;
            }

            .stat-value {
                font-size: 1.3rem;
                margin-bottom: 0;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .table-card {
                padding: 12px;
            }

            .filter-btn {
                padding: 7px 8px;
                font-size: 0.7rem;
            }

            .files-table {
                font-size: 0.7rem;
            }

            .files-table thead th {
                padding: 8px 6px;
                font-size: 0.65rem;
            }

            .files-table tbody td {
                padding: 8px 6px;
                font-size: 0.7rem;
            }

            .file-drop-zone-sidebar {
                padding: 12px 10px;
            }

            .file-drop-zone-sidebar i {
                font-size: 1.2rem;
            }

            .drop-text-sidebar {
                font-size: 0.8rem;
            }

            .drop-hint-sidebar {
                font-size: 0.7rem;
            }

            .btn {
                padding: 7px 12px;
                font-size: 0.8rem;
            }

            .btn-large {
                padding: 9px 12px !important;
                font-size: 0.85rem !important;
            }

            .btn-sm {
                padding: 5px 8px;
                font-size: 0.7rem;
            }

            input[type="text"], input[type="email"], input[type="password"], select {
                padding: 8px 10px;
                font-size: 0.9rem;
            }

            label {
                font-size: 0.75rem;
            }

            .key-counter {
                font-size: 0.65rem;
            }

            .success-message,
            .error-message {
                font-size: 0.8rem;
                padding: 8px 10px;
                gap: 6px;
            }

            .success-message i,
            .error-message i {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

@if(request()->routeIs('dashboard') || request()->routeIs('encrypt') || request()->routeIs('decrypt') || request()->routeIs('download') || request()->routeIs('file.delete'))
<nav>
    <div class="nav-brand">
          <img src="{{ asset('cryptologo.png') }}" alt="Secure File Storage" style="max-width: 40px; margin-bottom: 3px;">
        Secure File Storage System
    </div>
    <div class="nav-right">
        <span class="nav-email"><i class="fas fa-user"></i> {{ session('user_email') }}</span>
        <button id="themeToggleBtn" class="btn btn-outline btn-sm theme-toggle-btn" title="Toggle Dark/Light Mode">
            <i class="fas fa-moon"></i>
        </button>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button class="btn btn-outline btn-sm" type="submit">
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</nav>
@endif

@yield('content')

<script>
// Theme Toggle Functionality
const initTheme = () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
};

const updateThemeIcon = (theme) => {
    const themeBtn = document.getElementById('themeToggleBtn');
    if (!themeBtn) return;
    
    const icon = themeBtn.querySelector('i');
    if (theme === 'light') {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
};

const toggleTheme = () => {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
};

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }
});

// Also init immediately if DOM is already loaded
if (document.readyState !== 'loading') {
    initTheme();
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }
}

// Key character counter (AES: 5-20, DES: 3-20)
const updateKeyUI = (input) => {
    const form = input.closest('form');
    const algoSelect = form?.querySelector('select[name="algorithm"]');
    const group = input.closest('.form-group');
    const counter = group?.querySelector('.key-counter');
    const label = group?.querySelector('label');
    if (!counter || !label) return;

    const algo = algoSelect?.value || 'AES';
    const minLength = algo === 'DES' ? 3 : 5;
    const maxLength = 20;
    if (input.value.length > maxLength) {
        input.value = input.value.slice(0, maxLength);
    }

    const requiredSpan = label.querySelector('span');
    label.textContent = minLength + '-' + maxLength + ' Character Secret Key ';
    if (requiredSpan) {
        label.appendChild(requiredSpan);
    } else {
        const span = document.createElement('span');
        span.style.color = 'var(--danger)';
        span.textContent = '*';
        label.appendChild(span);
    }

    input.setAttribute('maxlength', String(maxLength));
    input.setAttribute('placeholder', 'Enter a ' + minLength + '-' + maxLength + ' character key');

    const n = input.value.length;
    counter.textContent = n + ' / ' + minLength + '-' + maxLength + ' characters';
    counter.className = 'key-counter ' + (n >= minLength && n <= maxLength ? 'ok' : 'bad');
};

document.querySelectorAll('.key-input').forEach(input => {
    const form = input.closest('form');
    const algoSelect = form?.querySelector('select[name="algorithm"]');

    updateKeyUI(input);
    input.addEventListener('input', () => updateKeyUI(input));
    if (algoSelect) {
        algoSelect.addEventListener('change', () => updateKeyUI(input));
    }
});
</script>
</body>
</html>