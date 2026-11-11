<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="menu-btn" id="menuBtn" aria-label="Menu"><x-icon name="menu" :size="24" /></button>
        <h1>@yield('title', 'Dashboard')</h1>
    </div>
    <div class="topbar-right">
        <span>Halo, <strong>{{ auth()->user()->name }}</strong></span>
        <span class="badge badge-purple">{{ auth()->user()->role_label }}</span>
    </div>
</header>
