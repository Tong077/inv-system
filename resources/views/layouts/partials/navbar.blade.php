<nav class="app-header navbar navbar-expand bg-body" aria-label="Primary navigation">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a class="nav-link" href="{{ route('dashboard') }}">Overview</a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <span class="nav-link workspace-label">
                    <span class="workspace-indicator" aria-hidden="true"></span>
                    Inventory workspace
                </span>
            </li>
        </ul>
    </div>
</nav>
