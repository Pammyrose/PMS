<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .login-container {
            max-width: 420px;
            width: 100%;
        }
        .card {
            border: 0.3px solid gray;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .card-header {
            background: #1e40af;
            color: white;
            border-bottom: none;
            padding: 1rem 1.5rem;
            text-align: center;
        }
        .img{
            height: 100px;
        }
        .btn-primary {
            background: #1e40af;
            border: 1px;
            border-color: black;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body class="d-flex align-items-center bg-light h-100">

<div class="container login-container mx-auto px-3">

    <div class="card">
        <div class="card-header">
            <img src="denr_logo.png" alt="Logo" class="img">
            <p class="mt-2 mb-0 opacity-75">DENR-CAR Performance Monitoring System</p>
        </div>

        <div class="card-body p-4 p-md-5">

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="/login" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label fw-medium">Email address</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="far fa-envelope"></i></span>
                        <input 
                            id="email" 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="email" 
                            autofocus 
                            placeholder="name@example.com"
                        >
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input 
                            id="password" 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            name="password" 
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none small">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Sign In
                </button>
            </form>

            @if (Route::has('register'))
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="text-primary fw-medium text-decoration-none">
                            Create one
                        </a>
                    </p>
                </div>
            @endif

        </div>
    </div>

    <div class="text-center mt-4 text-muted small">
        © {{ date('Y') }} {{ config('app.name', 'Laravel') }} • All rights reserved
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>