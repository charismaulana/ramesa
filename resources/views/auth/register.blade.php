<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RAMESA</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #FF4500;
            --secondary: #FF8C00;
            --accent: #FFD700;
            --bg-dark: #0a0505;
            --card-bg: rgba(20, 10, 10, 0.9);
            --card-border: rgba(255, 69, 0, 0.2);
            --text-primary: #ffffff;
            --text-secondary: #f5f5f5;
            --text-muted: #888;
            --success: #00FF88;
            --error: #FF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(255, 69, 0, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(255, 140, 0, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(255, 215, 0, 0.05) 0%, transparent 70%);
            z-index: -1;
        }

        .register-container {
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(20px);
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(255, 69, 0, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23FF6B35' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        select.form-control option {
            background: #1a0a0a;
            color: var(--text-primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 69, 0, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(255, 68, 68, 0.15);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: var(--error);
        }

        .alert-info {
            background: rgba(255, 215, 0, 0.15);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: var(--accent);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .role-options {
            display: flex;
            gap: 1rem;
        }

        .role-option {
            flex: 1;
            cursor: pointer;
        }

        .role-option input {
            display: none;
        }

        .role-card {
            padding: 1rem;
            border: 1px solid var(--card-border);
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .role-option input:checked+.role-card {
            border-color: var(--primary);
            background: rgba(255, 69, 0, 0.1);
        }

        .role-card:hover {
            border-color: var(--primary);
        }

        .role-card i {
            font-size: 1.5rem;
            color: var(--accent);
            display: block;
            margin-bottom: 0.5rem;
        }

        .role-card span {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="logo">
            <h1>RAMESA</h1>
            <p>Ramba Meal System Analytics</p>
        </div>

        <div class="card">
            <h2 class="card-title">REGISTER</h2>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Akun baru memerlukan persetujuan admin sebelum dapat digunakan.
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name"
                        value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email"
                        value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                        placeholder="Confirm password" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="tim_catering" {{ old('role') == 'tim_catering' ? 'checked' : '' }} required>
                            <div class="role-card">
                                <i class="bi bi-cup-hot"></i>
                                <span>Tim Catering</span>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="employee" {{ old('role', 'employee') == 'employee' ? 'checked' : '' }}>
                            <div class="role-card">
                                <i class="bi bi-person"></i>
                                <span>Employee</span>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i>
                    Register
                </button>
            </form>

            <div class="login-link">
                Sudah punya akun? <a href="{{ route('login') }}">Login disini</a>
            </div>
        </div>

        <div class="copyright">
            © GS Ramba 2025. All rights reserved.
        </div>
    </div>
</body>

</html>