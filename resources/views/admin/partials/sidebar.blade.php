<aside class="app-sidebar shadow" data-bs-theme="dark">
    
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-decoration-none">
            <img src="{{ asset('adminlte/assets/img/Logo-belen.png') }}" 
                 alt="Belen Spa Logo"
                 class="brand-image" 
                 style="opacity: 1; max-height: 40px;">
            
            <span class="brand-text fw-light">BELÉN SYSTEM</span>
        </a>
        </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                aria-label="Main navigation" data-accordion="false">
                
                <li class="nav-header text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Gestión</li>

                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.turnos') }}" class="nav-link {{ request()->routeIs('admin.turnos') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-calendar-check"></i>
                        <p>Turnos / Citas</p>
                    </a>
                </li>
                
                {{-- BOTÓN POS DESTACADO --}}
                <li class="nav-item my-2">
                    <a href="{{ route('admin.pos') }}" class="nav-link" 
                       style="background-color: var(--belen-cream) !important; color: var(--belen-dark) !important; font-weight: bold;">
                        <i class="nav-icon bi bi-cart4 text-dark"></i>
                        <p>PUNTO DE VENTA</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Administración</li>

                <li class="nav-item">
                    <a href="{{ route('admin.caja') }}" class="nav-link {{ request()->routeIs('admin.caja') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>Caja Chica</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.productos') }}" class="nav-link {{ request()->routeIs('admin.productos') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Productos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.inventario') }}" class="nav-link {{ request()->routeIs('admin.inventario') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-clipboard-data"></i>
                        <p>Kardex</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.compras') }}" class="nav-link {{ request()->routeIs('admin.compras') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bag-plus"></i>
                        <p>Compras</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Personal</li>

                <li class="nav-item">
                    <a href="{{ route('admin.estilistas') }}" class="nav-link {{ request()->routeIs('admin.estilistas') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-hearts"></i>
                        <p>Estilistas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.clientes') }}" class="nav-link {{ request()->routeIs('admin.clientes') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>Clientes</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.servicios') }}" class="nav-link {{ request()->routeIs('admin.servicios') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-scissors"></i>
                        <p>Servicios</p>
                    </a>
                </li>

                <li class="nav-header text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Sistema</li>

                <li class="nav-item">
                    <a href="{{ route('admin.ventas.historial') }}" class="nav-link {{ request()->routeIs('admin.ventas.historial') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-receipt"></i>
                        <p>Historial Ventas</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.configuracion') }}" class="nav-link {{ request()->routeIs('admin.configuracion') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Configuración</p>
                    </a>
                </li>
            </ul>
            </nav>
    </div>
</aside>