<?php
/**
 * Login page.
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Budget Tracker</title>
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
                    fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'] },
                    colors: {
                        border: 'hsl(var(--border))',
                        input: 'hsl(var(--input))',
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                        card: { DEFAULT: 'hsl(var(--card))', foreground: 'hsl(var(--card-foreground))' },
                        muted: { DEFAULT: 'hsl(var(--muted))', foreground: 'hsl(var(--muted-foreground))' },
                    },
                },
            },
        }
    </script>
    <link rel="stylesheet" href="assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-background text-foreground font-sans min-h-screen antialiased flex items-center justify-center">

    <div class="w-full max-w-sm px-4">
        <div class="text-center mb-8">
            <div class="flex justify-center mb-3">
                <button onclick="toggleTheme()" class="theme-toggle" title="Toggle theme">
                    <svg class="w-5 h-5 icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg class="w-5 h-5 icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>
            <span class="text-4xl">💰</span>
            <h1 class="text-2xl font-bold tracking-tight mt-2">BudgetTracker</h1>
            <p class="text-sm text-muted-foreground mt-1">Sign in to your account</p>
        </div>

        <div class="card">
            <form id="login-form" class="space-y-4">
                <div>
                    <label for="username" class="label">Username</label>
                    <input type="text" name="username" id="username" class="input" required autofocus>
                </div>
                <div>
                    <label for="password" class="label">Password</label>
                    <input type="password" name="password" id="password" class="input" required>
                </div>
                <div id="login-error" class="text-red-400 text-sm hidden"></div>
                <button type="submit" class="btn btn-primary w-full">Sign In</button>
            </form>
            <p class="text-sm text-muted-foreground text-center mt-4">
                Don't have an account? <a href="register.php" class="text-blue-400 hover:underline">Register</a>
            </p>
        </div>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errEl = document.getElementById('login-error');
        errEl.classList.add('hidden');
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Signing in...';

        try {
            const res = await fetch('api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: e.target.username.value.trim(),
                    password: e.target.password.value,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                errEl.textContent = data.error || 'Login failed.';
                errEl.classList.remove('hidden');
                return;
            }
            window.location.href = 'index.php';
        } catch {
            errEl.textContent = 'Network error. Please try again.';
            errEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Sign In';
        }
    });

    function toggleTheme() {
        document.documentElement.classList.add('theme-transition');
        document.documentElement.classList.toggle('dark');
        var isDark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        setTimeout(function() { document.documentElement.classList.remove('theme-transition'); }, 300);
    }
    </script>
</body>
</html>
