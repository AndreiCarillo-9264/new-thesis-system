<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CPC Nexboard - Sign In</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <!-- Left: Visual / Branding Side -->
        <div class="login-hero">
            <div class="hero-overlay"></div>
            <img 
                src="{{ asset('assets/images/company-background.jpg') }}" 
                alt="Manufacturing background" 
                class="hero-image"
            />

            <div class="hero-text">
                <h2 class="hero-title">CPC Nexboard</h2>
                <p class="hero-subtitle">Manufacturing Coordination System</p>
            </div>
        </div>

        <!-- Right: Login Form Side -->
        <div class="login-form-side">
            <div class="login-card">
                <div class="card-header">
                    <img 
                        src="{{ asset('assets/images/system-logo.webp') }}" 
                        alt="CPC Nexboard Logo" 
                        class="logo"
                    />
                    <h1 class="system-title">CPC Nexboard</h1>
                    <p class="page-subtitle">Sign In</p>
                </div>

                <!-- Messages & Errors -->
                <div class="messages-container">
                    <x-auth-session-status class="status-message" :status="session('status')" />

                    @if (session('error'))
                        <div class="alert alert-error">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

                    <div class="form-group">
                        <label for="email">Username or Email</label>
                        <input 
                            id="email"
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Username or email address"
                            required 
                            autofocus
                            autocomplete="username webauthn"
                        />
                    </div>

                    <div class="form-group password-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input 
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                required 
                                autocomplete="current-password"
                            />
                            <button 
                                type="button" 
                                class="password-toggle"
                                onclick="togglePassword()"
                            >
                                <span id="eye-icon">Show</span>
                            </button>
                        </div>
                    </div>

                    <div class="remember-me-wrapper">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        Sign In
                    </button>
                </form>

                <div class="card-footer">
                    <p class="copyright">© {{ date('Y') }} Manufacturing Coordination System</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = 'Show';
            }
        }
    </script>
</body>
</html>