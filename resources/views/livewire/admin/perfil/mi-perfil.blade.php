<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-purple shadow-sm">
            <div class="card-header">
                <h3 class="card-title fw-bold">Configuración de Perfil</h3>
            </div>
            <div class="card-body">

                @if (session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form wire:submit.prevent="actualizar">

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                {{-- Lógica de Previsualización --}}
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="rounded-circle shadow" width="150" height="150" style="object-fit: cover;">
                                @elseif ($foto_actual)
                                    <img src="{{ asset('storage/' . $foto_actual) }}" class="rounded-circle shadow" width="150" height="150" style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow" style="width: 150px; height: 150px; font-size: 3rem;">
                                        {{ substr($nombre, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <label class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-camera"></i> Cambiar Foto
                                    <input type="file" wire:model="foto" class="d-none" accept="image/*">
                                </label>
                            </div>
                            @error('foto') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" wire:model="nombre" class="form-control">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" wire:model="email" class="form-control">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label text-muted"><i class="bi bi-lock"></i> Cambiar Contraseña (Opcional)</label>
                        <input type="password" wire:model="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                        @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>