# 📋 INSTRUCCIONES DE ACTUALIZACIÓN

## Pasos para actualizar el sistema

### 1. Hacer Git Pull
```bash
git pull origin main
```

### 2. Instalar dependencias (si hay nuevas)
```bash
composer install
```

### 3. Limpiar caché de Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. ⚠️ IMPORTANTE: Sincronizar correlativos (SOLO SI YA TIENES COMPROBANTES EMITIDOS)

Si ya has emitido comprobantes electrónicos antes de esta actualización, ejecuta:
```bash
php artisan comprobantes:sincronizar-correlativos
```

**¿Cuándo NO ejecutar este comando?**
- Si es la primera vez que usas el módulo de facturación electrónica
- Si tu tabla `comprobantes` está vacía
- Si todos tus correlativos en `series_comprobante` están en 0

**¿Cuándo SÍ ejecutar este comando?**
- Si ya has emitido boletas/facturas antes
- Si ves que los correlativos no coinciden con los últimos emitidos
- Después de restaurar un backup de la base de datos

### 5. ✅ Verificar configuración de SUNAT

Las credenciales de SUNAT se gestionan desde el sistema:
- **Menú:** SISTEMA → Configuración
- **Tablas BD:** `config_tributaria` y `config_negocio`
- **NO se configuran en el archivo .env**

Verifica que tengas:
- ✅ RUC del negocio
- ✅ Usuario SOL y Clave SOL
- ✅ Certificado digital (.pfx) subido
- ✅ Modo: "beta" para pruebas o "produccion" para real

---

## 🆕 Cambios en esta actualización

### ✅ Sistema de Roles y Permisos
- Implementado control de acceso por roles (Administrador, Cajero, Encargado)
- Restricciones en el sidebar según el rol del usuario
- Protección de rutas en el backend

### ✅ Correlativos de Comprobantes
- Los correlativos ahora se guardan en la tabla `series_comprobante`
- Sistema thread-safe para evitar duplicados
- Comando de sincronización disponible

### ✅ PDFs de Comprobantes Anulados
- Los comprobantes anulados ahora muestran un banner rojo "DOCUMENTO ANULADO"
- Las Notas de Crédito se muestran limpias (sin banner)
- Regeneración automática del PDF al anular

---

## 🔧 Solución de Problemas

### Problema: Los correlativos no se actualizan
**Solución:** Ejecuta `php artisan comprobantes:sincronizar-correlativos`

### Problema: Error al emitir comprobante
**Solución:** Verifica que la tabla `series_comprobante` tenga las series activas

### Problema: No veo algunos módulos en el sidebar
**Solución:** Verifica tu rol de usuario en la tabla `usuarios` → campo `id_rol`

---

## 📞 Soporte
Si tienes algún problema, contacta al desarrollador.
