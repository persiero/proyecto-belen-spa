# 🚀 Instrucciones para Configurar en Producción (PC Cliente)

## Problema Identificado
El certificado no se muestra como cargado aunque se suba correctamente.

## Solución - Ejecutar estos comandos en orden:

### 1. Crear carpeta de certificados con permisos
```bash
mkdir storage\app\certificados
```

### 2. Limpiar cache de Laravel y Livewire
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan optimize:clear
```

### 3. Dar permisos a la carpeta storage (Windows)

**Opción A - Desde CMD como Administrador:**
```bash
icacls storage /grant Everyone:(OI)(CI)F /T
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T
```

**Opción B - Desde el Explorador de Windows (MÁS FÁCIL):**
1. Clic derecho en la carpeta `storage` → Propiedades
2. Pestaña "Seguridad" → Botón "Editar"
3. Botón "Agregar" → Escribir "Todos" → Aceptar
4. Seleccionar "Todos" → Marcar "Control total" → Aplicar
5. Repetir lo mismo con la carpeta `bootstrap\cache`

**Opción C - Comando simple (ejecutar como Administrador):**
```bash
attrib -r storage\* /s /d
attrib -r bootstrap\cache\* /s /d
```

### 4. Verificar que existe el registro en la base de datos
```bash
php artisan tinker
```
Luego ejecutar:
```php
\App\Models\ConfigTributaria::first()
```

Si no existe, crear uno:
```php
\App\Models\ConfigTributaria::create(['igv_porcentaje' => 18, 'emision_automatica_cpe' => 0, 'modo' => 'beta']);
```

### 5. Reiniciar el servidor
```bash
php artisan serve
```

## Verificación Final
1. Ir a la página de Configuración
2. Subir el certificado
3. Hacer clic en "Actualizar Configuración SUNAT"
4. Debería aparecer el mensaje verde: "Configuración SUNAT actualizada correctamente"
5. El mensaje amarillo debe cambiar a verde mostrando el nombre del certificado

## Si persiste el problema
Verificar que el archivo se guardó físicamente:
```bash
dir storage\app\certificados
```

Debe aparecer un archivo con nombre: `certificado_[timestamp].[extension]`
