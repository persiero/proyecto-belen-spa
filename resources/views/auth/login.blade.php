@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="text-center mb-4">
                 <img src="{{ asset('adminlte/assets/img/Logo-belen.png') }}" 
                      alt="Logo" 
                      style="height: 100px; filter: drop-shadow(0px 0px 5px rgba(255,255,255,0.1));">
                 
                 <h5 class="mt-3 text-uppercase fw-light" style="color: var(--belen-cream); letter-spacing: 3px;">
                     Acceso Administrativo
                 </h5>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden" 
                 style="background-color: #2c2c30; border-top: 4px solid var(--belen-cream) !important;">
                
                <div class="card-body p-5">
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Correo Electrónico</label>
                            
                            <div class="input-group rounded-3 overflow-hidden border border-secondary border-opacity-25">
                                <span class="input-group-text border-0" style="background-color: #212124; color: var(--belen-grey);">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input id="email" type="email" 
                                       class="form-control border-0 text-white shadow-none @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autofocus 
                                       placeholder="nombre@correo.com"
                                       style="background-color: #212124;">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Contraseña</label>
                            
                            <div class="input-group rounded-3 overflow-hidden border border-secondary border-opacity-25">
                                <span class="input-group-text border-0" style="background-color: #212124; color: var(--belen-grey);">
                                    <i class="bi bi-lock"></i>
                                </span>
                                
                                <input id="password" type="password" 
                                       class="form-control border-0 text-white shadow-none @error('password') is-invalid @enderror" 
                                       name="password" required 
                                       placeholder="••••••••"
                                       style="background-color: #212124;">
                                
                                {{-- BOTÓN PARA VER CONTRASEÑA --}}
                                <button class="btn border-0" type="button" id="togglePassword"
                                        style="background-color: #212124; color: var(--belen-grey); z-index: 10;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            @error('password')
                                <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                       style="background-color: #212124; border-color: #555; cursor: pointer;">
                                <label class="form-check-label text-secondary small" for="remember" style="cursor: pointer;">
                                    Recordarme
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a class="text-decoration-none small hover-effect" href="{{ route('password.request') }}" 
                                   style="color: var(--belen-grey); transition: 0.3s;">
                                    ¿Olvidaste tu clave?
                                </a>
                            @endif
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold text-dark rounded-3 shadow-sm" 
                                    style="letter-spacing: 1px;">
                                INICIAR SESIÓN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-5 small" style="color: var(--belen-cream); opacity: 0.6;">
                &copy; {{ date('Y') }} <span class="fw-bold">Belén</span>. Sistema Interno.
            </div>

        </div>
    </div>
</div>

<style>
    /* Efecto hover extra */
    .hover-effect:hover {
        color: var(--belen-cream) !important;
        text-decoration: underline !important;
    }
</style>

{{-- SCRIPT PARA EL OJITO --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const icon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function () {
            // Cambiar el tipo de input
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Cambiar el icono
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    });
</script>
@endsection
