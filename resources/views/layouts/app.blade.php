<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00e5ff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="IIMS HR">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
</head>
<body>
@php
    $unreadNotifs     = auth()->user()->unreadNotifications()->latest()->take(5)->get();
    $isAdmin          = auth()->user()->isAdmin();
    $isManager        = auth()->user()->isManager();
    $isHR             = auth()->user()->isHR();
    $isAdminOrManager = auth()->user()->isAdminOrManager();
    $isAdminOrHR      = auth()->user()->isAdminOrHR();
    $managerDeptId    = ($isManager && auth()->user()->employee) ? auth()->user()->employee->department_id : null;
    $sidebarLeaves    = $isAdmin || $isHR
        ? \App\Models\LeaveRequest::where('status','pending')->count()
        : ($isManager && $managerDeptId
            ? \App\Models\LeaveRequest::where('status','pending')
                ->whereHas('employee', fn($q) => $q->where('department_id', $managerDeptId))->count()
            : 0);
    $sidebarOT        = $isAdmin || $isHR
        ? \App\Models\OvertimeRecord::where('status','pending')->count()
        : ($isManager && $managerDeptId
            ? \App\Models\OvertimeRecord::where('status','pending')
                ->whereHas('employee', fn($q) => $q->where('department_id', $managerDeptId))->count()
            : 0);
    $sidebarProfileUpdates = $isAdmin || $isHR
        ? \App\Models\ProfileUpdateRequest::where('status','pending')->count()
        : 0;
@endphp
    <div class="app-layout">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">P</div>
                <div class="sidebar-brand">IIMS <span>Payroll</span></div>
                <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Collapse sidebar" onclick="toggleSidebar()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">Main</div>
                <a href="{{ route('dashboard') }}" data-label="Dashboard" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>

                @if($isAdminOrHR)
                    <div class="nav-section">HR Management</div>
                    <a href="{{ route('employees.index') }}" data-label="Employees" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Employees</span>
                    </a>
                    <a href="{{ route('departments.index') }}" data-label="Departments" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>Departments</span>
                    </a>
                @endif

                <div class="nav-section">Operations</div>
                <a href="{{ route('attendance.index') }}" data-label="Attendance" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/></svg>
                    <span>Attendance</span>
                </a>
                <a href="{{ route('leaves.index') }}" data-label="Leave" class="nav-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
                    <span>Leave</span>
                    @if($sidebarLeaves > 0)
                        <span class="nav-badge">{{ $sidebarLeaves > 99 ? '99+' : $sidebarLeaves }}</span>
                    @endif
                </a>

                @if($isAdminOrManager || $isHR)
                    {{-- Overtime — visible to admin, manager, and HR --}}
                    <a href="{{ route('overtime.index') }}" data-label="Overtime" class="nav-link {{ request()->routeIs('overtime.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Overtime</span>
                        @if($sidebarOT > 0)
                            <span class="nav-badge nav-badge-purple">{{ $sidebarOT > 99 ? '99+' : $sidebarOT }}</span>
                        @endif
                    </a>
                @endif

                @if($isAdmin)
                    <a href="{{ route('payroll.index') }}" data-label="Payroll" class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span>Payroll</span>
                    </a>
                    <a href="{{ route('tax.index') }}" data-label="Tax &amp; Deductions" class="nav-link {{ request()->routeIs('tax.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span>Tax &amp; Deductions</span>
                    </a>
                    <a href="{{ route('bonuses.index') }}" data-label="Bonuses" class="nav-link {{ request()->routeIs('bonuses.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>Bonuses</span>
                    </a>
                    <a href="{{ route('audit.index') }}" data-label="Audit Log" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Audit Log</span>
                    </a>
                    <a href="{{ route('queue.monitor') }}" data-label="Queue Monitor" class="nav-link {{ request()->routeIs('queue.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>Queue Monitor</span>
                    </a>
                    <a href="{{ route('settings.index') }}" data-label="Settings" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        <span>Settings</span>
                    </a>
                @endif

                @if($isAdminOrHR)
                    <a href="{{ route('performance.index') }}" data-label="Performance" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>Performance</span>
                    </a>
                    <a href="{{ route('holidays.index') }}" data-label="Holidays" class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/></svg>
                        <span>Holidays</span>
                    </a>
                    <a href="{{ route('reports.index') }}" data-label="Reports" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="12" y2="17"/></svg>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('profile-updates.index') }}" data-label="Profile Updates" class="nav-link {{ request()->routeIs('profile-updates.index') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Profile Updates</span>
                        @if($sidebarProfileUpdates > 0)
                            <span class="nav-badge nav-badge-orange">{{ $sidebarProfileUpdates > 99 ? '99+' : $sidebarProfileUpdates }}</span>
                        @endif
                    </a>
                @endif

                @if($isManager)
                    {{-- Manager-only: team performance view --}}
                    <a href="{{ route('performance.index') }}" data-label="Team Reviews" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>Team Reviews</span>
                    </a>
                @endif

                <a href="{{ route('payslips.index') }}" data-label="My Payslips" class="nav-link {{ request()->routeIs('payslips.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>My Payslips</span>
                </a>
                @if(!auth()->user()->isAdmin())
                    <a href="{{ route('performance.index') }}" data-label="My Reviews" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>My Reviews</span>
                    </a>
                @endif
                @if(auth()->user()->isEmployee())
                    <a href="{{ route('profile-updates.create') }}" data-label="Update Profile" class="nav-link {{ request()->routeIs('profile-updates.create') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Update Profile</span>
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->role }}</div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="main-content">
            {{-- Top Bar --}}
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                        &#9776;
                    </button>
                    <div>
                        <h2 class="topbar-title">@yield('page-title', 'Dashboard')</h2>
                        @hasSection('breadcrumbs')
                            <nav class="breadcrumb" aria-label="Breadcrumb">
                                <a href="{{ route('dashboard') }}">Home</a>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                @yield('breadcrumbs')
                            </nav>
                        @endif
                    </div>
                </div>
                <div class="topbar-right">
                    {{-- Dark / Light Mode Toggle --}}
                    <button class="topbar-icon-btn theme-toggle-btn" id="themeToggle" onclick="toggleTheme()" title="Toggle light/dark mode">
                        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg class="icon-sun"  width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>

                    {{-- Notification Bell Dropdown --}}
                    @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                    <div class="notif-dropdown" id="notifDropdown">
                        <button class="topbar-icon-btn" onclick="toggleNotifDropdown(event)" title="Notifications" aria-expanded="false" aria-haspopup="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            @if($unreadCount > 0)
                                <span class="notif-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>

                        <div class="notif-panel" id="notifPanel" role="menu">
                            <div class="notif-panel-header">
                                <span class="notif-panel-title">Notifications</span>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST" style="margin:0">
                                        @csrf
                                        <button type="submit" class="notif-mark-all">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            @forelse($unreadNotifs as $notif)
                                @php
                                    $nUrl = $notif->data['url'] ?? route('notifications.index');
                                    $nIcon = $notif->data['icon'] ?? 'info';
                                @endphp
                                <a href="{{ route('notifications.read', $notif->id) }}?redirect={{ urlencode($nUrl) }}"
                                   class="notif-item" role="menuitem">
                                    <div class="notif-item-icon notif-icon-{{ $nIcon }}">
                                        @if($nIcon === 'success')
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        @elseif($nIcon === 'danger')
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        @elseif($nIcon === 'warning')
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        @else
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        @endif
                                    </div>
                                    <div class="notif-item-body">
                                        <div class="notif-item-title">{{ $notif->data['title'] ?? 'Notification' }}</div>
                                        <div class="notif-item-msg">{{ \Illuminate\Support\Str::limit($notif->data['message'] ?? '', 65) }}</div>
                                        <div class="notif-item-time">{{ $notif->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @empty
                                <div class="notif-empty">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                    <p>All caught up!</p>
                                </div>
                            @endforelse

                            <a href="{{ route('notifications.index') }}" class="notif-view-all">View all notifications</a>
                        </div>
                    </div>

                    {{-- Profile Dropdown --}}
                    <div class="profile-dropdown" id="profileDropdown">
                        <button class="profile-trigger" onclick="toggleProfileDropdown(event)" aria-expanded="false" aria-haspopup="true">
                            <div class="profile-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <div class="profile-trigger-info">
                                <span class="profile-trigger-name">{{ auth()->user()->name }}</span>
                                <span class="profile-trigger-role">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                            <svg class="profile-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>

                        <div class="profile-menu" id="profileMenu" role="menu">
                            <div class="profile-menu-header">
                                <div class="profile-menu-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                <div>
                                    <div class="profile-menu-name">{{ auth()->user()->name }}</div>
                                    <div class="profile-menu-email">{{ auth()->user()->email }}</div>
                                    @php
                                        $prevLogin = session('previous_login_at')
                                            ? \Carbon\Carbon::parse(session('previous_login_at'))
                                            : auth()->user()->last_login_at;
                                    @endphp
                                    @if($prevLogin)
                                        <div class="profile-menu-last-login" title="{{ $prevLogin->format('d M Y, g:i A') }}">
                                            Last login: {{ $prevLogin->diffForHumans() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="profile-menu-divider"></div>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('settings.index') }}" class="profile-menu-item" role="menuitem">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                    Settings
                                </a>
                            @endif
                            <div class="profile-menu-divider"></div>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0">
                                @csrf
                                <button type="submit" class="profile-menu-item profile-menu-logout" role="menuitem">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Body --}}
            <main class="page-content">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul style="margin: 0.5rem 0 0 1rem; font-size: 0.82rem">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // ── Sidebar mobile overlay close ─────────────────────
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.menu-toggle');
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')
                && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
            // Close dropdowns when clicking outside
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && !dropdown.contains(e.target)) closeProfileDropdown();
            const notifDd = document.getElementById('notifDropdown');
            if (notifDd && !notifDd.contains(e.target)) closeNotifDropdown();
        });

        // ── Collapsible Sidebar ──────────────────────────────
        (function () {
            const SIDEBAR_KEY = 'iims_sidebar_collapsed';
            const sidebar = document.getElementById('sidebar');
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            const mainContent = document.querySelector('.main-content');

            function applyCollapsed(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('sidebar-collapsed-ml');
                    if (collapseBtn) collapseBtn.classList.add('rotated');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('sidebar-collapsed-ml');
                    if (collapseBtn) collapseBtn.classList.remove('rotated');
                }
            }

            // Restore saved state
            try {
                const saved = localStorage.getItem(SIDEBAR_KEY);
                if (saved === '1' && window.innerWidth > 768) applyCollapsed(true);
            } catch(e) {}

            window.toggleSidebar = function () {
                if (window.innerWidth <= 768) return; // on mobile, use the open/close toggle instead
                const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                applyCollapsed(!isCollapsed);
                try { localStorage.setItem(SIDEBAR_KEY, isCollapsed ? '0' : '1'); } catch(e) {}
            };
        })();

        // ── Notification Dropdown ────────────────────────────
        function toggleNotifDropdown(e) {
            e.stopPropagation();
            const panel = document.getElementById('notifPanel');
            const btn   = e.currentTarget;
            const isOpen = panel.classList.contains('open');
            // Close profile dropdown if open
            closeProfileDropdown();
            if (isOpen) {
                panel.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            } else {
                panel.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        function closeNotifDropdown() {
            const panel = document.getElementById('notifPanel');
            const btn   = document.querySelector('#notifDropdown .topbar-icon-btn');
            if (panel) panel.classList.remove('open');
            if (btn)   btn.setAttribute('aria-expanded', 'false');
        }

        // ── Dark / Light Mode Toggle (Feature 17) ────────────
        (function () {
            const THEME_KEY = 'iims_theme';
            function applyTheme(theme) {
                const isDark = theme !== 'light';
                document.body.classList.toggle('light-mode', !isDark);
                const moon = document.querySelector('.icon-moon');
                const sun  = document.querySelector('.icon-sun');
                if (moon) moon.style.display = isDark  ? '' : 'none';
                if (sun)  sun.style.display  = !isDark ? '' : 'none';
            }
            try {
                const saved = localStorage.getItem(THEME_KEY);
                applyTheme(saved || 'dark');
            } catch(e) {}

            window.toggleTheme = function () {
                const isLight = document.body.classList.contains('light-mode');
                const next = isLight ? 'dark' : 'light';
                applyTheme(next);
                try { localStorage.setItem(THEME_KEY, next); } catch(e) {}
            };
        })();

        // ── Confirmation Modal (Feature 14) ──────────────────
        (function () {
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('form[data-confirm]');
                if (!form) return;
                e.preventDefault();
                const msg = form.dataset.confirm || 'Are you sure?';
                document.getElementById('confirmModalMsg').textContent = msg;
                document.getElementById('confirmModal').style.display = 'flex';
                document.getElementById('confirmModalOk').onclick = function () {
                    closeConfirmModal();
                    form.removeAttribute('data-confirm');
                    form.submit();
                };
            });
        })();

        window.closeConfirmModal = function () {
            document.getElementById('confirmModal').style.display = 'none';
        };

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeConfirmModal();
        });

        // ── Table Enhancements (Feature 15) ──────────────────
        (function () {
            document.querySelectorAll('.data-table-wrapper').forEach(function (wrapper) {
                const table = wrapper.querySelector('table');
                if (!table) return;

                // ── Build toolbar ──────────────────────────
                const toolbar = document.createElement('div');
                toolbar.className = 'dt-toolbar';
                toolbar.innerHTML = `
                    <input type="search" class="dt-search form-control" placeholder="Search..." style="max-width:260px">
                    <div class="dt-page-size">
                        Show
                        <select class="dt-per-page form-control" style="width:auto;display:inline-block;padding:0.3rem 1.5rem 0.3rem 0.5rem">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        entries
                    </div>
                `;
                wrapper.insertBefore(toolbar, wrapper.firstChild);

                const searchInput = toolbar.querySelector('.dt-search');
                const perPageSel  = toolbar.querySelector('.dt-per-page');
                const tbody = table.querySelector('tbody');
                const headers = table.querySelectorAll('thead th');
                let rows = Array.from(tbody ? tbody.querySelectorAll('tr') : []);
                let sortCol = -1, sortAsc = true;
                let perPage = 10, currentPage = 1;

                // Sort indicators on headers
                headers.forEach(function (th, i) {
                    th.style.cursor = 'pointer';
                    th.title = 'Click to sort';
                    th.addEventListener('click', function () {
                        if (sortCol === i) { sortAsc = !sortAsc; }
                        else { sortCol = i; sortAsc = true; }
                        headers.forEach(h => h.classList.remove('dt-sort-asc','dt-sort-desc'));
                        th.classList.add(sortAsc ? 'dt-sort-asc' : 'dt-sort-desc');
                        renderRows();
                    });
                });

                function getFilteredRows() {
                    const q = searchInput.value.toLowerCase().trim();
                    return rows.filter(function (row) {
                        if (!q) return true;
                        return row.textContent.toLowerCase().includes(q);
                    });
                }

                function getSortedRows(filtered) {
                    if (sortCol < 0) return filtered;
                    return [...filtered].sort(function (a, b) {
                        const aText = (a.cells[sortCol] ? a.cells[sortCol].textContent : '').trim();
                        const bText = (b.cells[sortCol] ? b.cells[sortCol].textContent : '').trim();
                        const aNum = parseFloat(aText.replace(/,/g,''));
                        const bNum = parseFloat(bText.replace(/,/g,''));
                        let cmp;
                        if (!isNaN(aNum) && !isNaN(bNum)) cmp = aNum - bNum;
                        else cmp = aText.localeCompare(bText);
                        return sortAsc ? cmp : -cmp;
                    });
                }

                function renderRows() {
                    const filtered = getFilteredRows();
                    const sorted   = getSortedRows(filtered);
                    const total    = sorted.length;
                    const pages    = Math.max(1, Math.ceil(total / perPage));
                    currentPage    = Math.min(currentPage, pages);
                    const start    = (currentPage - 1) * perPage;
                    const visible  = sorted.slice(start, start + perPage);

                    rows.forEach(r => r.style.display = 'none');
                    visible.forEach(r => r.style.display = '');

                    // Pagination info + controls
                    let pager = wrapper.querySelector('.dt-pager');
                    if (!pager) {
                        pager = document.createElement('div');
                        pager.className = 'dt-pager';
                        wrapper.appendChild(pager);
                    }
                    let pHtml = `<span class="dt-info">Showing ${total === 0 ? 0 : start+1}–${Math.min(start+perPage, total)} of ${total}</span><div class="dt-pages">`;
                    pHtml += `<button class="dt-btn" ${currentPage===1?'disabled':''} onclick="this.closest('.data-table-wrapper')._dt.goPage(${currentPage-1})">&#8249;</button>`;
                    const maxBtn = 5;
                    let s = Math.max(1, currentPage - Math.floor(maxBtn/2));
                    let e = Math.min(pages, s + maxBtn - 1);
                    if (e - s < maxBtn - 1) s = Math.max(1, e - maxBtn + 1);
                    for (let p = s; p <= e; p++) {
                        pHtml += `<button class="dt-btn${p===currentPage?' active':''}" onclick="this.closest('.data-table-wrapper')._dt.goPage(${p})">${p}</button>`;
                    }
                    pHtml += `<button class="dt-btn" ${currentPage===pages?'disabled':''} onclick="this.closest('.data-table-wrapper')._dt.goPage(${currentPage+1})">&#8250;</button>`;
                    pHtml += '</div>';
                    pager.innerHTML = pHtml;
                }

                wrapper._dt = {
                    goPage: function(p) { currentPage = p; renderRows(); }
                };

                searchInput.addEventListener('input', function() { currentPage = 1; renderRows(); });
                perPageSel.addEventListener('change', function() {
                    perPage = parseInt(this.value);
                    currentPage = 1;
                    renderRows();
                });

                renderRows();
            });
        })();

        // ── Profile Dropdown ─────────────────────────────────
        function toggleProfileDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('profileMenu');
            const btn = e.currentTarget;
            const isOpen = menu.classList.contains('open');
            if (isOpen) {
                closeProfileDropdown();
            } else {
                menu.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        function closeProfileDropdown() {
            const menu = document.getElementById('profileMenu');
            const btn = document.querySelector('.profile-trigger');
            if (menu) menu.classList.remove('open');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')

    {{-- Confirmation Modal (Feature 14) --}}
    <div id="confirmModal" class="confirm-modal-overlay" style="display:none" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h3 class="confirm-modal-title" id="confirmModalTitle">Are you sure?</h3>
            <p class="confirm-modal-msg" id="confirmModalMsg">This action cannot be undone.</p>
            <div class="confirm-modal-actions">
                <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button class="btn btn-danger" id="confirmModalOk">Confirm</button>
            </div>
        </div>
    </div>

    {{-- PWA Install Banner --}}
    <div id="pwa-install-banner" style="display:none; position:fixed; bottom:1rem; left:1rem; right:1rem; background:var(--bg-card); border:1px solid var(--border-accent); border-radius:var(--radius); padding:1rem 1.25rem; z-index:9999; box-shadow:var(--shadow); align-items:center; gap:1rem;">
        <svg width="32" height="32" viewBox="0 0 192 192" style="flex-shrink:0;"><rect width="192" height="192" rx="32" fill="#0f1923"/><text x="96" y="130" font-family="system-ui,sans-serif" font-size="110" font-weight="700" fill="#00e5ff" text-anchor="middle">P</text></svg>
        <div style="flex:1">
            <div style="font-weight:600;color:var(--text-primary);">Install IIMS HR</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);">Add to home screen for quick access</div>
        </div>
        <button id="pwa-install-btn" class="btn btn-primary btn-sm">Install</button>
        <button id="pwa-dismiss-btn" class="btn btn-secondary btn-sm">✕</button>
    </div>

    <script>
        // ── Service Worker registration ──────────────────────────
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(console.error);
            });
        }

        // ── PWA Install Banner ───────────────────────────────────
        (function () {
            const STORAGE_KEY = 'iims_pwa_install_state';
            // Possible values: 'installed' | 'dismissed_permanent' | null

            const banner      = document.getElementById('pwa-install-banner');
            const installBtn  = document.getElementById('pwa-install-btn');
            const dismissBtn  = document.getElementById('pwa-dismiss-btn');
            let   deferredPrompt = null;

            // Helper: read stored state
            function getState() {
                try { return localStorage.getItem(STORAGE_KEY); } catch(e) { return null; }
            }

            // Helper: write stored state
            function setState(value) {
                try { localStorage.setItem(STORAGE_KEY, value); } catch(e) {}
            }

            // Helper: is app already running in standalone (installed) mode?
            function isRunningInstalled() {
                return window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true; // iOS Safari
            }

            // If already running installed OR user previously dismissed, never show again
            if (isRunningInstalled() || getState() !== null) return;

            // Capture the install prompt — browser only fires this when NOT installed
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                if (banner) banner.style.display = 'flex';
            });

            // User clicked "Install"
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    if (banner) banner.style.display = 'none';
                    // If accepted, mark installed so banner never returns
                    if (outcome === 'accepted') setState('installed');
                });
            }

            // User clicked "✕" dismiss — remember permanently
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => {
                    if (banner) banner.style.display = 'none';
                    setState('dismissed_permanent');
                    deferredPrompt = null;
                });
            }

            // Browser fires this when the app is successfully installed from outside the banner
            window.addEventListener('appinstalled', () => {
                if (banner) banner.style.display = 'none';
                setState('installed');
                deferredPrompt = null;
            });
        })();
    </script>

    {{-- ── HR Chatbot Widget ──────────────────────────────────────── --}}
    <style>
        #chatbot-fab {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 10000;
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--border-accent, #00e5ff); color: #0f1923;
            border: none; cursor: pointer; box-shadow: 0 4px 18px #00e5ff55;
            display: flex; align-items: center; justify-content: center;
            transition: transform .2s, box-shadow .2s;
        }
        #chatbot-fab:hover { transform: scale(1.1); box-shadow: 0 6px 24px #00e5ff88; }
        #chatbot-panel {
            position: fixed; bottom: 4.5rem; right: 1.5rem; z-index: 10000;
            width: 340px; max-height: 480px;
            background: var(--bg-card, #12202e); border: 1px solid var(--border-accent, #00e5ff44);
            border-radius: 12px; box-shadow: 0 8px 40px #00000088;
            display: flex; flex-direction: column; overflow: hidden;
        }
        #chatbot-panel.hidden { display: none; }
        #chatbot-header {
            padding: .75rem 1rem; background: var(--bg-sidebar, #0f1923);
            border-bottom: 1px solid var(--border-color, #1e2d3d);
            display: flex; align-items: center; justify-content: space-between;
        }
        #chatbot-header .cb-title { font-weight: 700; font-size: .9rem; color: var(--border-accent, #00e5ff); }
        #chatbot-header .cb-close { background: none; border: none; cursor: pointer; color: var(--text-secondary, #8899aa); font-size: 1.1rem; line-height: 1; padding: 0; }
        #chatbot-messages {
            flex: 1; overflow-y: auto; padding: .75rem; display: flex;
            flex-direction: column; gap: .5rem; min-height: 0;
        }
        .cb-msg { max-width: 86%; padding: .5rem .75rem; border-radius: 10px; font-size: .82rem; line-height: 1.45; word-break: break-word; }
        .cb-msg.user { align-self: flex-end; background: var(--border-accent, #00e5ff)22; color: var(--text-primary, #e0eaff); border: 1px solid var(--border-accent, #00e5ff)44; }
        .cb-msg.bot  { align-self: flex-start; background: var(--bg-sidebar, #0f1923); color: var(--text-secondary, #a8b8cc); border: 1px solid var(--border-color, #1e2d3d); }
        .cb-msg.typing { color: var(--text-muted, #667788); font-style: italic; }
        #chatbot-form { display: flex; border-top: 1px solid var(--border-color, #1e2d3d); }
        #chatbot-input {
            flex: 1; background: transparent; border: none; outline: none;
            padding: .65rem .8rem; font-size: .82rem; color: var(--text-primary, #e0eaff);
            font-family: inherit;
        }
        #chatbot-input::placeholder { color: var(--text-muted, #667788); }
        #chatbot-send {
            border: none; background: none; cursor: pointer;
            padding: 0 .85rem; color: var(--border-accent, #00e5ff);
            display: flex; align-items: center;
        }
        #chatbot-send:disabled { opacity: .4; cursor: default; }
    </style>

    <button id="chatbot-fab" aria-label="Open HR Assistant" title="HR Assistant">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </button>

    <div id="chatbot-panel" class="hidden" role="dialog" aria-label="HR Assistant">
        <div id="chatbot-header">
            <span class="cb-title">HR Assistant</span>
            <button class="cb-close" id="chatbot-close" aria-label="Close">✕</button>
        </div>
        <div id="chatbot-messages">
            <div class="cb-msg bot">Hi {{ auth()->user()->name }}! I can answer questions about your payroll, attendance, leave, and performance. How can I help?</div>
        </div>
        <form id="chatbot-form" autocomplete="off">
            <input id="chatbot-input" type="text" placeholder="Ask a question…" maxlength="1000" required />
            <button id="chatbot-send" type="submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </div>

    <script>
    (function () {
        const fab     = document.getElementById('chatbot-fab');
        const panel   = document.getElementById('chatbot-panel');
        const closeBtn= document.getElementById('chatbot-close');
        const form    = document.getElementById('chatbot-form');
        const input   = document.getElementById('chatbot-input');
        const send    = document.getElementById('chatbot-send');
        const msgs    = document.getElementById('chatbot-messages');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        let history = [];

        fab.addEventListener('click', () => { panel.classList.toggle('hidden'); if (!panel.classList.contains('hidden')) input.focus(); });
        closeBtn.addEventListener('click', () => panel.classList.add('hidden'));

        function appendMsg(role, text) {
            const div = document.createElement('div');
            div.className = 'cb-msg ' + (role === 'user' ? 'user' : 'bot');
            div.textContent = text;
            msgs.appendChild(div);
            msgs.scrollTop = msgs.scrollHeight;
            return div;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            send.disabled = true;

            appendMsg('user', text);
            history.push({ role: 'user', content: text });

            const typing = appendMsg('bot', '…');
            typing.classList.add('typing');

            try {
                const res = await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ message: text, history: history.slice(-10) })
                });
                const data = await res.json();
                const reply = data.reply ?? 'Sorry, I could not get a response.';
                typing.textContent = reply;
                typing.classList.remove('typing');
                history.push({ role: 'assistant', content: reply });
                if (history.length > 20) history = history.slice(-20);
            } catch (_) {
                typing.textContent = 'Connection error. Please try again.';
                typing.classList.remove('typing');
            }

            send.disabled = false;
            input.focus();
        });
    })();
    </script>
</body>
</html>
