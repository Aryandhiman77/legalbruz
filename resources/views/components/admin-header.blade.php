<!-- Admin Header -->
<nav class="navbar navbar-expand-lg navbar-dark"
    style="background: linear-gradient(135deg, #1D3557 0%, #2A9D8F 100%); padding: 1rem 0;">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}"
            style="font-size: 1.5rem;">
            <!-- <div style="margin-bottom:40px;background-color:#fff; border-radius:25px;width:fit-content; margin-left:auto;margin-right:auto;padding:5px;"> -->
            <div style="background-color:#fff;padding:5px; border-radius:8px;">
                <img src="{{ asset('logo.png') }}" alt="Legal Bruz (LLP) logo" class="navbar-logo">
            </div>
            <!-- </div> -->
            <span style="color:#fff!important;">Admin</span>
        </a>

        <!-- Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"
            aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        📊 Dashboard
                    </a>
                </li>

                <!-- Pending Applications -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.applications') ? 'active' : '' }}"
                        href="{{ route('admin.applications') }}">
                        📋 Applications
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.stuck-trademark.*') ? 'active' : '' }}"
                        href="{{ route('admin.stuck-trademark.index') }}">
                        🧾 Recovery Cases
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.trademark-opposition.*') ? 'active' : '' }}"
                        href="{{ route('admin.trademark-opposition.index') }}">
                        🛡️ Opposition Cases
                    </a>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminProfileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        👤 {{ Auth::guard('admin')->user()?->name ?? 'Admin' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfileDropdown">
                        <li><a class="dropdown-item" href="#">⚙️ Settings</a></li>
                        <li><a class="dropdown-item" href="#">📊 Reports</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;"
                                data-swal-confirm data-swal-title="Log out?"
                                data-swal-text="You will be signed out of the admin panel." data-swal-icon="question"
                                data-swal-confirm-text="Yes, log out">
                                @csrf
                                <button class="dropdown-item" type="submit">🚪 Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
        transition: color 0.3s;
    }

    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
        color: #fff !important;
        font-weight: 600;
    }

    .dropdown-menu {
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Responsive Logo Styling */
    .navbar-logo {
        height: 56px;
        width: auto;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .navbar-logo {
            height: 48px;
        }
    }

    @media (max-width: 480px) {
        .navbar-logo {
            height: 40px;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
