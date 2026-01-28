<!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <img
              src="{{ asset('adminlte/assets/img/AdminLTELogo.png') }}"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <span class="brand-text fw-light">Belen SPA</span>
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>Dashboard</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.servicios') }}" class="nav-link {{ request()->routeIs('admin.servicios') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-scissors"></i>
                    <p>Servicios</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.estilistas') }}" class="nav-link {{ request()->routeIs('admin.estilistas') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-hearts"></i>
                    <p>Estilistas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.clientes') }}" class="nav-link {{ request()->routeIs('admin.clientes') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-people-fill"></i>
                    <p>Clientes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.productos') }}" class="nav-link {{ request()->routeIs('admin.productos') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-box-seam"></i>
                    <p>Productos / Insumos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.turnos') }}" class="nav-link {{ request()->routeIs('admin.turnos') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar-check"></i>
                    <p>Recepción / Turnos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.pos') }}" class="nav-link bg-success">
                    <i class="nav-icon bi bi-cart-check"></i>
                    <p>Punto de Venta (POS)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.caja') }}" class="nav-link {{ request()->routeIs('admin.caja') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-wallet2"></i>
                    <p>Control de Caja</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.compras') }}" class="nav-link">
                    <i class="nav-icon bi bi-bag-plus"></i>
                    <p>Compras (Entradas)</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.proveedores') }}" class="nav-link">
                    <i class="nav-icon bi bi-truck"></i>
                    <p>Proveedores</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.inventario') }}" class="nav-link">
                    <i class="nav-icon bi bi-clipboard-data"></i>
                    <p>Kardex / Movimientos</p>
                </a>
              </li><li class="nav-item">
                <a href="{{ route('admin.ventas.historial') }}" class="nav-link">
                    <i class="nav-icon bi bi-receipt"></i>
                    <p>Historial de Ventas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.configuracion') }}" class="nav-link">
                    <i class="nav-icon bi bi-gear"></i>
                    <p>Configuración</p>
                </a>
              </li>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->