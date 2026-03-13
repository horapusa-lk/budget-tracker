<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Budget Tracker</title>
    <!-- Theme init: apply before render to prevent flash -->
    <script>
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark') { document.documentElement.classList.add('dark'); }
        })();
    </script>
    <script src="assets/vendor/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        border: 'hsl(var(--border))',
                        input: 'hsl(var(--input))',
                        ring: 'hsl(var(--ring))',
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                        card: {
                            DEFAULT: 'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))',
                        },
                        muted: {
                            DEFAULT: 'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))',
                        },
                        accent: {
                            DEFAULT: 'hsl(var(--accent))',
                            foreground: 'hsl(var(--accent-foreground))',
                        },
                        destructive: {
                            DEFAULT: 'hsl(var(--destructive))',
                            foreground: 'hsl(0 0% 98%)',
                        },
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-background text-foreground font-sans min-h-screen antialiased">

    <!-- Navigation -->
    <nav class="border-b border-border sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <span class="text-xl">💰</span>
                    <h1 class="text-lg font-semibold tracking-tight">BudgetTracker</h1>
                </div>

                <!-- Desktop nav -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="index.php"
                       class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                              <?= $currentPage === 'index.php'
                                  ? 'bg-accent text-foreground'
                                  : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        Dashboard
                    </a>
                    <a href="add-transaction.php"
                       class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                              <?= $currentPage === 'add-transaction.php'
                                  ? 'bg-accent text-foreground'
                                  : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        + Add Expense
                    </a>
                    <a href="history.php"
                       class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                              <?= $currentPage === 'history.php'
                                  ? 'bg-accent text-foreground'
                                  : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        History
                    </a>
                    <a href="settings.php"
                       class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                              <?= $currentPage === 'settings.php'
                                  ? 'bg-accent text-foreground'
                                  : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                        Settings
                    </a>
                    <div class="ml-3 pl-3 border-l border-border flex items-center gap-2">
                        <!-- Theme toggle -->
                        <button onclick="toggleTheme()" class="theme-toggle" title="Toggle theme">
                            <svg class="w-4 h-4 icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <svg class="w-4 h-4 icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </button>
                        <span class="text-sm text-muted-foreground"><?= htmlspecialchars(getCurrentUserName()) ?></span>
                        <a href="logout.php"
                           class="px-2 py-1 rounded-md text-xs font-medium text-red-400 hover:text-red-300 hover:bg-accent transition-colors">
                            Logout
                        </a>
                    </div>
                </div>

                <!-- Mobile hamburger button -->
                <div class="flex items-center gap-1 md:hidden">
                    <button onclick="toggleTheme()" class="theme-toggle" title="Toggle theme">
                        <svg class="w-5 h-5 icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg class="w-5 h-5 icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                    <button id="mobile-menu-btn" class="p-2 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent transition-colors" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile menu dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-border bg-background/95 backdrop-blur">
            <div class="max-w-5xl mx-auto px-4 py-3 space-y-1">
                <a href="index.php"
                   class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
                          <?= $currentPage === 'index.php'
                              ? 'bg-accent text-foreground'
                              : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                    Dashboard
                </a>
                <a href="add-transaction.php"
                   class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
                          <?= $currentPage === 'add-transaction.php'
                              ? 'bg-accent text-foreground'
                              : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                    + Add Expense
                </a>
                <a href="history.php"
                   class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
                          <?= $currentPage === 'history.php'
                              ? 'bg-accent text-foreground'
                              : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                    History
                </a>
                <a href="settings.php"
                   class="block px-3 py-2 rounded-md text-sm font-medium transition-colors
                          <?= $currentPage === 'settings.php'
                              ? 'bg-accent text-foreground'
                              : 'text-muted-foreground hover:text-foreground hover:bg-accent' ?>">
                    Settings
                </a>
                <div class="pt-2 mt-2 border-t border-border flex items-center justify-between">
                    <span class="text-sm text-muted-foreground"><?= htmlspecialchars(getCurrentUserName()) ?></span>
                    <a href="logout.php"
                       class="px-2 py-1 rounded-md text-xs font-medium text-red-400 hover:text-red-300 hover:bg-accent transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
