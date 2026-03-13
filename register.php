<?php
/**
 * Registration page.
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
    <title>Register - Student Budget Tracker</title>
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
            <p class="text-sm text-muted-foreground mt-1">Create a new account</p>
        </div>

        <div class="card">
            <form id="register-form" class="space-y-4">
                <div>
                    <label for="full_name" class="label">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="input" required autofocus maxlength="100">
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input type="text" name="username" id="username" class="input" required maxlength="50"
                           pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only">
                </div>
                <div>
                    <label for="password" class="label">Password</label>
                    <input type="password" name="password" id="password" class="input" required minlength="6">
                </div>
                <div>
                    <label for="confirm_password" class="label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="input" required minlength="6">
                </div>
                <div id="register-error" class="text-red-400 text-sm hidden"></div>
                <button type="submit" class="btn btn-primary w-full">Create Account</button>
            </form>
            <p class="text-sm text-muted-foreground text-center mt-4">
                Already have an account? <a href="login.php" class="text-blue-400 hover:underline">Sign in</a>
            </p>
        </div>
    </div>

    <script>
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errEl = document.getElementById('register-error');
        errEl.classList.add('hidden');

        const password = e.target.password.value;
        const confirm = e.target.confirm_password.value;
        if (password !== confirm) {
            errEl.textContent = 'Passwords do not match.';
            errEl.classList.remove('hidden');
            return;
        }

        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Creating account...';

        try {
            const res = await fetch('api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    full_name: e.target.full_name.value.trim(),
                    username: e.target.username.value.trim(),
                    password: password,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                errEl.textContent = data.error || 'Registration failed.';
                errEl.classList.remove('hidden');
                return;
            }
            window.location.href = 'index.php';
        } catch {
            errEl.textContent = 'Network error. Please try again.';
            errEl.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Create Account';
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
