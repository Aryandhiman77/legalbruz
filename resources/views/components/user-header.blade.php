<!-- User Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}"
            style="font-size: 1.5rem; color: #1D3557;">
            <img src="{{ asset('logo.png') }}" alt="Legal Bruz (LLP) logo" class="navbar-logo">
        </a>

        <!-- Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar"
            aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        📊 Dashboard
                    </a>
                </li>

                <!-- My Documents -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.documents') ? 'active' : '' }}"
                        href="{{ route('user.documents') }}">
                        📄 My Documents
                    </a>
                </li>

                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        🔔 Notifications
                        @php
                            $unreadCount = \App\Services\NotificationService::getUnreadCount(Auth::id());
                        @endphp
                        @if ($unreadCount > 0)
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown-menu"
                        aria-labelledby="notificationsDropdown">
                        @php
                            $notifications = \App\Services\NotificationService::getRecentNotifications(Auth::id(), 100);
                        @endphp

                        @if ($notifications->count() > 0)
                            @foreach ($notifications as $notif)
                                @php
                                    $notificationData = $notif->data ?? [];
                                    $notificationUrl = $notificationData['action_url'] ?? null;

                                    if (!$notificationUrl && !empty($notificationData['application_id'])) {
                                        $notificationUrl = route(
                                            'trademark.status',
                                            $notificationData['application_id'],
                                        );
                                    }
                                @endphp
                                <li>
                                    <a class="dropdown-item notification-dropdown-item {{ $notif->isUnread() ? 'bg-light' : '' }}"
                                        href="{{ $notificationUrl ?: 'javascript:void(0)' }}"
                                        data-notification-url="{{ $notificationUrl }}"
                                        onclick="markNotificationRead(event, {{ $notif->id }}, this)">
                                        <div class="notification-row">
                                            <div class="notification-copy">
                                                <strong>{{ $notif->title }}</strong>
                                                <p class="text-muted mb-0">
                                                    {{ $notif->message }}
                                                </p>
                                            </div>
                                            @if ($notif->isUnread())
                                                <span class="badge bg-primary">New</span>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>
                            @endforeach
                        @else
                            <li class="text-center p-3">
                                <small class="text-muted">No notifications</small>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userProfileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        👤 {{ Auth::user()->name ?? 'User' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}">👤 Profile</a></li>
                        <li><a class="dropdown-item" href="#">⚙️ Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline;"
                                data-swal-confirm data-swal-title="Log out?"
                                data-swal-text="You will be signed out of your account." data-swal-icon="question"
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

<script>
    function markNotificationRead(event, notificationId, trigger = null) {
        event?.preventDefault();

        const notificationUrl = trigger?.dataset?.notificationUrl || trigger?.getAttribute('href');
        const shouldRedirect = notificationUrl && notificationUrl !== 'javascript:void(0)' && notificationUrl !== '#';

        window.LegalBruzButtonLoading?.set(trigger, 'Opening...');

        fetch(`{{ url('/api/notifications') }}/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).finally(() => {
            if (shouldRedirect) {
                window.location.href = notificationUrl;
                return;
            }

            location.reload();
        });
    }
</script>

<style>
    .navbar-nav .nav-link {
        color: #1D3557;
        transition: color 0.3s;
    }

    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
        color: #2A9D8F !important;
        font-weight: 600;
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

    .dropdown-menu {
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
    }

    .notification-dropdown-menu {
        width: min(420px, calc(100vw - 1.5rem));
        height: auto;
        max-height: min(72vh, 560px);
        overflow-y: scroll;
        overflow-x: hidden;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }

    .notification-dropdown-menu::-webkit-scrollbar {
        width: 8px;
    }

    .notification-dropdown-menu::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .notification-dropdown-menu::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .notification-dropdown-item {
        white-space: normal;
        overflow: hidden;
    }

    .notification-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.75rem;
        align-items: start;
    }

    .notification-copy {
        min-width: 0;
    }

    .notification-copy strong,
    .notification-copy p {
        display: block;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.25;
    }

    .notification-copy p {
        margin-top: 0.25rem;
        font-size: 0.78rem;
    }

    .notification-row .badge {
        justify-self: end;
        white-space: nowrap;
    }
</style>
