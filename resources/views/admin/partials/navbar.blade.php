<nav class="app-header navbar navbar-expand bg-white">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="{{ route('admin.dashboard') }}" class="nav-link fw-bold">Inicio</a></li>
        </ul>

        <ul class="navbar-nav ms-auto">
            
            <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="bi bi-search"></i>
                </a>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    
                    {{-- FOTO PEQUEÑA (Barra) --}}
                    @if(Auth::user()->foto_perfil)
                        <img src="{{ asset('storage/' . Auth::user()->foto_perfil) }}"
                            class="user-image rounded-circle shadow-sm" 
                            alt="User Image"
                            style="object-fit: cover;">
                    @else
                        {{-- Inicial si no hay foto --}}
                        <div class="user-image rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                             style="width: 30px; height: 30px; object-fit: cover;">
                            {{ substr(Auth::user()->nombre, 0, 1) }}
                        </div>
                    @endif

                    <span class="d-none d-md-inline fw-bold ms-1">{{ Auth::user()->nombre }}</span>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end border-2 shadow">
                    
                    <li class="user-header" style="background-color: var(--belen-dark); color: var(--belen-cream);">
                        
                        {{-- FOTO GRANDE (Desplegable) --}}
                        @if(Auth::user()->foto_perfil)
                            <img src="{{ asset('storage/' . Auth::user()->foto_perfil) }}"
                                class="rounded-circle shadow" 
                                alt="User Image" 
                                style="border: 3px solid var(--belen-cream); width: 90px; height: 90px; object-fit: cover;" />
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-2 shadow" 
                                 style="width: 90px; height: 90px; font-size: 2.5rem; border: 3px solid var(--belen-cream);">
                                {{ substr(Auth::user()->nombre, 0, 1) }}
                            </div>
                        @endif

                        <p>
                            {{ Auth::user()->nombre }}
                            <small>Administrador</small>
                        </p>
                    </li>

                    <li class="user-footer">
                        {{-- Botón Perfil --}}
                        <a href="{{ route('admin.perfil') }}" class="btn btn-light btn-flat">
                            <i class="bi bi-person-gear"></i> Perfil
                        </a>
                        
                        {{-- Botón Cerrar Sesión (Con lógica POST) --}}
                        <a href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="btn btn-light btn-flat float-end text-danger">
                            <i class="bi bi-power"></i> Cerrar Sesión
                        </a>

                        {{-- Formulario oculto necesario para Laravel --}}
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>