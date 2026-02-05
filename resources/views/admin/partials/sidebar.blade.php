<aside class="app-sidebar shadow" data-bs-theme="dark">
    
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-link text-decoration-none">
            <img src="{{ asset('adminlte/assets/img/Logo-belen.png') }}" 
                 alt="Belen Spa Logo"
                 class="brand-image" 
                 style="opacity: 1; max-height: 40px;">
            <span class="brand-text fw-light">Belén System</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation">
                
                {{-- =========================================================
                     BLOQUE 1: OPERACIÓN DIARIA
                     ROLES: Admin, Encargado, Cajero
                     ========================================================= --}}
                
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                {{-- BOTÓN POS (ACCESO RÁPIDO VENTA) --}}
                <li class="nav-item my-2 px-2">
                    <a href="{{ route('admin.pos') }}" class="nav-link nav-link-pos {{ request()->routeIs('admin.pos') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cart4"></i>
                        <p>PUNTO DE VENTA</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.turnos') }}" class="nav-link {{ request()->routeIs('admin.turnos') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-calendar-week"></i>
                        <p>Agenda / Turnos</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.caja') }}" class="nav-link {{ request()->routeIs('admin.caja') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>Caja Chica</p>
                    </a>
                </li>

                {{-- =========================================================
                     BLOQUE 2: GESTIÓN & LOGÍSTICA
                     ROLES: Admin, Encargado (Cajero NO debería ver esto)
                     ========================================================= --}}
                
                <li class="nav-header mt-2">GESTIÓN ADMINISTRATIVA</li>

                {{-- GRUPO: CATÁLOGOS (Datos Maestros) --}}
                <li class="nav-item {{ request()->routeIs('admin.clientes*', 'admin.estilistas*', 'admin.servicios*', 'admin.productos*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.clientes*', 'admin.estilistas*', 'admin.servicios*', 'admin.productos*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-collection"></i>
                        <p>
                            Catálogos
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.clientes') }}" class="nav-link {{ request()->routeIs('admin.clientes') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-people"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.estilistas') }}" class="nav-link {{ request()->routeIs('admin.estilistas') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person-hearts"></i>
                                <p>Estilistas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.servicios') }}" class="nav-link {{ request()->routeIs('admin.servicios') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-scissors"></i>
                                <p>Servicios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.productos') }}" class="nav-link {{ request()->routeIs('admin.productos') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-tags"></i>
                                <p>Productos</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- GRUPO: INVENTARIO & COMPRAS --}}
                <li class="nav-item {{ request()->routeIs('admin.inventario*', 'admin.compras*', 'admin.proveedores*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.inventario*', 'admin.compras*', 'admin.proveedores*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>
                            Inventario & Compras
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.inventario') }}" class="nav-link {{ request()->routeIs('admin.inventario') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-clipboard-data"></i>
                                <p>Control de Stock</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.compras') }}" class="nav-link {{ request()->routeIs('admin.compras') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-bag-plus"></i>
                                <p>Compras</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.proveedores') }}" class="nav-link {{ request()->routeIs('admin.proveedores') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-truck"></i>
                                <p>Proveedores</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- =========================================================
                     BLOQUE 3: INTELIGENCIA DE NEGOCIO (EL REQUERIMIENTO NUEVO)
                     ROLES: Admin, Encargado (Cajero NO)
                     Aquí van las métricas solicitadas
                     ========================================================= --}}
                
                <li class="nav-header mt-2">REPORTES & ANÁLISIS</li>

                <li class="nav-item {{ request()->routeIs('admin.ventas*', 'admin.reportes*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.ventas*', 'admin.reportes*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>
                            Reportes
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.reportes.analitica') }}" class="nav-link {{ request()->routeIs('admin.reportes.analitica') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-pie-chart-fill"></i>
                                <p>Resúmen General</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.ventas.historial') }}" class="nav-link {{ request()->routeIs('admin.ventas.historial') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Historial de Ventas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reportes.comisiones') }}" class="nav-link {{ request()->routeIs('admin.reportes.comisiones') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-currency-exchange"></i>
                                <p>Comisiones</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- =========================================================
                     BLOQUE 4: SISTEMA
                     ROLES: Solo Admin
                     ========================================================= --}}
                
                <li class="nav-header mt-2">SISTEMA</li>

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