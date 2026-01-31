<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= $title ?? 'Master Admin' ?> - Master Admin</title>

    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        blueMain: "#2563EB",
                        greenMain: "#10B981",
                        yellowMain: "#F59E0B",
                        purpleMain: "#8B5CF6",
                        pinkMain: "#EC4899",
                        indigoMain: "#6366F1",
                        sidebarBg: "#1a2c57ff"
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif; }
        :root{
            --color-primary:#5B6BD5;
            --color-primary-hover:#4F5FCC;
            --color-secondary:#6B6F8D;
            --color-heading:#2F3045;
            --color-page-bg:#F0F1F6;
            --color-white:#FFFFFF;
            --color-border:#E3E5ED;
            --color-sidebar-bg:#0F172A;
            --color-active-menu-bg:#E9ECFF;
        }
        body{background-color:var(--color-page-bg);color:var(--color-heading)}
        .bg-white{background-color:var(--color-white)!important}
        .border-gray-200,.border-gray-100,.border-gray-300{border-color:var(--color-border)!important}
        .text-gray-900,.text-gray-800{color:var(--color-heading)!important}
        .text-gray-700,.text-gray-600{color:var(--color-secondary)!important}
        .bg-blue-600{background-color:var(--color-primary)!important}
        .hover\:bg-blue-700:hover{background-color:var(--color-primary-hover)!important}
        .bg-purple-700\/30,.bg-purple-700,.text-purple-300,.text-purple-700{color:var(--color-primary)!important;background-color:var(--color-active-menu-bg)!important}
        .sidebar-gradient{background: linear-gradient(180deg, #334155 0%, #1e293b 100%)}
        .sidebar-item{color:#e2e8f0}
        .sidebar-item:hover{background:rgba(255,255,255,0.1); color:#ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.1)}
        .sidebar-item-active{background:rgb(79 70 229 / var(--tw-bg-opacity, 1)); color:#ffffff; box-shadow: 0 6px 16px rgba(139,92,246,0.35)}
        .sidebar-icon{background: rgba(255,255,255,0.1); color:#e2e8f0}
        .sidebar-item:hover .sidebar-icon{background: rgba(255,255,255,0.2); color:#ffffff}
        [class*="bg-gradient"]{background-image:none!important}
        .collapsed .menu-label{display:none}
        .collapsed nav{padding-left:0;padding-right:0}
        .collapsed .sidebar-item{justify-content:center;padding-left:0;padding-right:0}
        .collapsed .sidebar-icon{margin:0}
        .submenu-container{margin-left:2.25rem;padding-left:.75rem;border-left:1px solid rgba(255,255,255,0.2)}
        .submenu-item{color:#e2e8f0}
        .submenu-item:hover{background:rgba(255,255,255,0.08); color:#ffffff}
        .sidebar-item, .submenu-item{background-color:rgba(17,24,39,0.0)}
        .sidebar-gradient{background: linear-gradient(180deg,#0F172A 0%, #0B1224 40%, #0F172A 100%)}
    </style>
</head>

<body class="bg-gray-100 overflow-x-hidden" x-data="{ sidebarOpen: true }">

<div class="min-h-screen">

o    <!-- SIDEBAR -->
    <aside class="fixed top-0 left-0 sidebar-gradient shadow-md h-screen z-30 flex flex-col rounded-tr-2xl rounded-br-2xl" x-bind:class="sidebarOpen ? 'w-64' : 'w-16 collapsed'">

        <div class="px-6 py-6 text-2xl font-normal text-white">
            Master Admin
        </div>
      <button
  x-on:click="sidebarOpen = !sidebarOpen"
  class="absolute top-4 -right-3 w-6 h-6 rounded-full bg-indigoMain text-white
         flex items-center justify-center shadow"
>

  <!-- Close icon (when sidebar is open) -->
  <svg x-show="sidebarOpen" x-transition class="w-4 h-4" fill="none" stroke="currentColor"
       stroke-width="2" viewBox="0 0 24 24"
       stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 6h18M3 12h18M3 18h18" />
  </svg>

  <!-- Open icon (when sidebar is closed) -->
  <svg x-show="!sidebarOpen" x-transition class="w-4 h-4" fill="none" stroke="currentColor"
       stroke-width="2" viewBox="0 0 24 24"
       stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 6h18M3 12h18M3 18h18" />
  </svg>

</button>


        <?php 
            $current = $_SERVER['REQUEST_URI'];
            function mActive($path,$current){
                return str_starts_with($current,$path) ? "sidebar-item-active" : "";
            }
        ?>

        <nav class="px-3 py-2 space-y-1 text-[15px] font-medium flex-1 overflow-y-auto">

            <!-- DASHBOARD -->
            <a href="/master/dashboard"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item <?= mActive('/master/dashboard',$current) ?>">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </span>
                <span class="menu-label">Dashboard</span>
            </a>

            <!-- ROLES -->
            <a href="/master/roles"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item <?= mActive('/master/roles',$current) ?>">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l9 4v6c0 7-9 10-9 10S3 19 3 12V6l9-4z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                    </svg>
                </span>
                <span class="menu-label">Roles</span>
            </a>

            <!-- PERMISSIONS -->
            <a href="/master/permissions"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item <?= mActive('/master/permissions',$current) ?>">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c.828 0 1.5.672 1.5 1.5S12.828 14 12 14s-1.5-.672-1.5-1.5S11.172 11 12 11z"></path>
                        <rect x="4" y="10" width="16" height="10" rx="2" ry="2" stroke-width="2"></rect>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10V7a4 4 0 018 0v3"></path>
                    </svg>
                </span>
                <span class="menu-label">Permissions</span>
            </a>

            <!-- SALES (Dropdown) -->
            <?php $salesActive = str_starts_with($current,'/master/sales') || str_starts_with($current,'/sales/manager') || str_starts_with($current,'/sales/executive'); ?>
            <div x-data="{ open: <?= $salesActive ? 'true' : 'false' ?> }" class="space-y-1">
                <button @click="open=!open"
                        class="w-full flex items-center justify-between px-4 py-2 rounded-md transition sidebar-item <?= $salesActive ? 'sidebar-item-active' : '' ?>">
                    <span class="flex items-center gap-3">
                        <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                            </svg>
                        </span>
                        <span class="menu-label">Sales</span>
                    </span>
                    <svg x-show="open" class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 15l6-6 6 6"/></svg>
                    <svg x-show="!open" class="h-4 w-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
                </button>
                <div x-show="open" x-transition class="submenu-container space-y-1">
                    <a href="/master/sales"
                       class="flex items-center gap-3 px-3 py-2 rounded-md transition submenu-item <?= mActive('/master/sales',$current) ?>">
                        <span class="w-7 h-7 flex items-center justify-center rounded-md sidebar-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                        </span>
                        <span class="menu-label">Overview</span>
                    </a>
                    <a href="/sales/manager/dashboard"
                       class="flex items-center gap-3 px-3 py-2 rounded-md transition submenu-item <?= mActive('/sales/manager',$current) ?>">
                        <span class="w-7 h-7 flex items-center justify-center rounded-md sidebar-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20v-2a4 4 0 00-3-3.87M7 20v-2a4 4 0 013-3.87M12 7a4 4 0 110 8 4 4 0 010-8z"></path>
                            </svg>
                        </span>
                        <span class="menu-label">Sales Manager Panel</span>
                    </a>
                    <a href="/sales/executive/dashboard"
                       class="flex items-center gap-3 px-3 py-2 rounded-md transition submenu-item <?= mActive('/sales/executive',$current) ?>">
                        <span class="w-7 h-7 flex items-center justify-center rounded-md sidebar-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20v-2a5 5 0 015-5h4a5 5 0 015 5v2"></path>
                                <circle cx="12" cy="7" r="4" stroke-width="2"></circle>
                            </svg>
                        </span>
                        <span class="menu-label">Sales Executive Panel</span>
                    </a>
                    <a href="/master/sales/leads"
                       class="flex items-center gap-3 px-3 py-2 rounded-md transition submenu-item <?= mActive('/master/sales/leads',$current) ?>">
                        <span class="w-7 h-7 flex items-center justify-center rounded-md sidebar-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 2h10v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                        </span>
                        <span class="menu-label">Leads</span>
                    </a>
                </div>
            </div>

            <!-- VERIFICATIONS -->
            <a href="/master/verifications"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M5 7h14M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                    </svg>
                </span>
                <span class="menu-label">Verifications</span>
            </a>

            <!-- EMPLOYERS -->
            <a href="/master/employers"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16M9 8h2M13 8h2M9 12h2M13 12h2M9 16h2M13 16h2"></path>
                    </svg>
                </span>
                <span class="menu-label">Employers</span>
            </a>

            <!-- CANDIDATES -->
            <a href="/master/candidates"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20v-2a5 5 0 015-5h4a5 5 0 015 5v2"></path>
                        <circle cx="12" cy="7" r="4" stroke-width="2"></circle>
                    </svg>
                </span>
                <span class="menu-label">Candidates</span>
            </a>

            <!-- PAYMENTS -->
            <a href="/master/payments"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2" ry="2" stroke-width="2"></rect>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 9h20"></path>
                    </svg>
                </span>
                <span class="menu-label">Payments</span>
            </a>

            <!-- SUBSCRIPTIONS -->
            <a href="/master/subscriptions"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h10a2 2 0 012 2v14l-3-2-3 2-3-2-3 2V5a2 2 0 012-2z"></path>
                    </svg>
                </span>
                <span class="menu-label">Subscriptions</span>
            </a>

            <!-- REPORTS -->
            <a href="/master/reports"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M7 10h3v9H7zM14 7h3v12h-3z"></path>
                    </svg>
                </span>
                <span class="menu-label">Reports</span>
            </a>

            <!-- SETTINGS -->
            <a href="/master/settings"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings h-5 w-5 flex-shrink-0 transition-transform group-hover:scale-110 text-slate-400 group-hover:text-white"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </span>
                <span class="menu-label">Settings</span>
            </a>

            <!-- SUPPORT -->
            <a href="/master/support"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-help h-5 w-5 flex-shrink-0 transition-transform group-hover:scale-110 text-slate-400 group-hover:text-white"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>
                </span>
                <span class="menu-label">Support</span>
            </a>

            <!-- LOGS -->
            <a href="/master/logs"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4h9a3 3 0 013 3v12H7a3 3 0 01-3-3V7a3 3 0 013-3z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8H8M16 12H8M13 16H8"></path>
                    </svg>
                </span>
                <span class="menu-label">Logs</span>
            </a>

            <!-- SYSTEM MONITOR -->
            <a href="/master/system/monitor"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item <?= mActive('/master/system/monitor',$current) ?>">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v5l4 2"></path>
                    </svg>
                </span>
                <span class="menu-label">System Monitor</span>
            </a>

            <!-- CRON -->
            <a href="/master/system/cron"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="13" r="7" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13l4-2M9 4h6"></path>
                    </svg>
                </span>
                <span class="menu-label">System Cron</span>
            </a>

            <!-- API KEYS -->
            <a href="/master/system/api"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="7" cy="12" r="3" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 12h10M17 12v3"></path>
                    </svg>
                </span>
                <span class="menu-label">API Keys</span>
            </a>

            <!-- IP WHITELIST -->
            <a href="/master/system/ip-whitelist"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M12 3c3 4 3 14 0 18M6 6c2 1.5 4 1.5 12 0M6 18c2-1.5 4-1.5 12 0"></path>
                    </svg>
                </span>
                <span class="menu-label">IP Whitelist</span>
            </a>

            <!-- PANEL BUILDER -->
            <a href="/master/system/panel-builder"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10a2 2 0 114 0 2 2 0 11-4 0zm6 0h4v4h-4v-4zM7 14h4v4H7v-4z"></path>
                    </svg>
                </span>
                <span class="menu-label">Panel Builder</span>
            </a>

            <a href="/support-exec/tickets"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8h18v8H3zM7 8v-2M17 8v-2M7 16v2M17 16v2"></path>
                    </svg>
                </span>
                <span class="menu-label">Support Tickets</span>
            </a>

            <a href="/finance/payments"
               class="flex items-center gap-3 px-4 py-2 rounded-md transition sidebar-item">
                <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-5 9 5v10H3V10zM7 10v10M12 10v10M17 10v10"></path>
                    </svg>
                </span>
                <span class="menu-label">Finance Payments</span>
            </a>

        </nav>
        <a href="/admin/logout" class="flex items-center gap-3 px-4 py-3 border-t border-white/10 sidebar-item">
            <span class="w-8 h-8 flex items-center justify-center rounded-md sidebar-icon">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3h8a2 2 0 012 2v14a2 2 0 01-2 2h-8M7 12l-4-4m0 0l4-4m-4 4h11"></path>
                </svg>
            </span>
            <span class="menu-label">Logout</span>
        </a>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="min-h-screen flex flex-col" x-bind:style="`margin-left: ${sidebarOpen ? '16rem' : '4rem'}`">

        <header class="sticky top-0 bg-white border-b border-gray-200 shadow-sm z-20">
            <div class="flex items-center h-14 px-6">
                <div class="hidden md:block flex-1 max-w-xl">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="2"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20l-3-3"></path></svg>
                        </span>
                        <input type="text" placeholder="Search anything..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigoMain">
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-3 mx-auto" x-data="{reportDate:'<?= htmlspecialchars(date('Y-m-d')) ?>'}">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3M16 7V3M3 9h18M5 12h14M5 16h14M5 20h14"></path></svg>
                        </span>
                        <input x-model="reportDate" type="date" class="pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigoMain bg-white" />
                    </div>
                    <a :href="'/master/reports?date='+reportDate" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm flex items-center gap-2 shadow hover:bg-indigo-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 13l3 3 7-7"></path></svg>
                        Generate Report
                    </a>
                </div>
                <div class="ml-auto flex items-center gap-4">
                    <button class="relative w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a2 2 0 002-2H10a2 2 0 002 2zm6-6V9a6 6 0 10-12 0v7l-2 2h16l-2-2z"></path></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full"></span>
                    </button>
                    <?php $name = $_SESSION['admin_name'] ?? 'Admin User'; $role = $_SESSION['admin_role'] ?? 'Super Admin'; ?>
                    <div class="relative" x-data="{menuOpen:false}">
                        <button @click="menuOpen=!menuOpen" class="flex items-center gap-3 px-2 py-1 rounded-md bg-slate-100">
                            <span class="w-8 h-8 rounded-full bg-indigoMain/20 flex items-center justify-center">
                                <svg class="h-5 w-5 text-indigoMain" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20v-2a5 5 0 015-5h4a5 5 0 015 5v2"></path><circle cx="12" cy="7" r="4" stroke-width="2"></circle></svg>
                            </span>
                            <span class="text-left">
                                <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($name) ?></div>
                                <div class="text-xs text-gray-600"><?= htmlspecialchars($role) ?></div>
                            </span>
                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div x-show="menuOpen" x-transition @click.outside="menuOpen=false" class="absolute right-0 top-12 w-56 bg-white rounded-md shadow border border-slate-200">
                            <div class="px-3 py-2 text-sm font-semibold text-gray-800">My Account</div>
                            <a href="/master/settings" class="block px-3 py-2 text-sm hover:bg-slate-50">Profile Settings</a>
                            <a href="/master/settings?tab=prefs" class="block px-3 py-2 text-sm hover:bg-slate-50">Preferences</a>
                            <a href="/master/support" class="block px-3 py-2 text-sm hover:bg-slate-50">Help & Support</a>
                            <div class="border-t border-slate-200"></div>
                            <a href="/admin/logout" class="block px-3 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <?= $content ?? '' ?>
        </main>

    </div>
</div>

</body>
</html>
