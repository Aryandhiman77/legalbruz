@extends('layouts.admin-guest')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg border-0">
                    <!-- Header -->
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #1D3557 0%, #2A9D8F 100%); color: white; padding: 2rem;">
                        <div class="text-center">
                            <h3 class="mb-0">🔐 Admin Portal</h3>
                            <p class="mb-0 mt-2" style="font-size: 0.9rem; opacity: 0.9;">Trademark Application Management</p>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>❌ Login Failed!</strong>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form id="adminLoginForm" method="POST">
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-600">📧 Email Address</label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email') }}" placeholder="admin@trademark.com" required
                                    autofocus>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-600">🔑 Password</label>
                                <input type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Enter your password" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                            </div>

                            <!-- Login Button -->
                            <button type="submit" class="btn btn-lg w-100" id="submitBtn"
                                style="background: linear-gradient(135deg, #1D3557 0%, #2A9D8F 100%); color: white; font-weight: 600;">
                                🔓 Login to Admin Panel
                            </button>
                        </form>

                        <!--<hr class="my-4">-->

                        <!-- Info Box -->
                        <!--<div class="alert alert-info alert-dismissible fade show" role="alert">-->
                        <!--    <strong>ℹ️ Default Credentials:</strong><br>-->
                        <!--    📧 Email: <code>admin@trademark.com</code><br>-->
                        <!--    🔑 Password: <code>admin@123</code>-->
                        <!--    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>-->
                        <!--</div>-->
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-light text-center p-3">
                        <small class="text-muted">
                            🔒 This is a secure admin panel. Only authorized personnel may access.
                        </small>
                    </div>
                </div>

                <!-- Back to Home Link -->
                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="text-decoration-none" style="color: #1D3557;">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .fw-600 {
            font-weight: 600;
        }

        .form-control-lg {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }

        .form-control-lg:focus {
            border-color: #2A9D8F;
            box-shadow: 0 0 0 0.2rem rgba(42, 157, 143, 0.25);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 157, 143, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            const submitBtn = document.getElementById('submitBtn');

            window.LegalBruzButtonLoading?.set(submitBtn, 'Logging in...');

            try {
                const response = await fetch('{{ route('admin.login.post') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        remember: remember
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Login Successful!',
                        text: 'Welcome to Admin Panel',
                        timer: 1500,
                        showConfirmButton: false,
                        didClose: () => {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
                    // Show error alert
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Login Failed',
                        text: data.message,
                        confirmButtonColor: '#2A9D8F'
                    });
                    window.LegalBruzButtonLoading?.reset(submitBtn);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: '❌ Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#2A9D8F'
                });
                window.LegalBruzButtonLoading?.reset(submitBtn);
            }
        });
    </script>
@endsection
