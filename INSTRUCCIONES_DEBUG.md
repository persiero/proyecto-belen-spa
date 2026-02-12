# 🔍 INSTRUCCIONES PARA DIAGNOSTICAR PROBLEMA DE CPE EN PRODUCCIÓN

## Problema Actual
El botón CPE no funciona en modo producción, pero sí funciona en modo beta.

## Pasos para Diagnosticar

### 1. HABILITAR DEBUG TEMPORALMENTE (En el PC del cliente)

Editar el archivo `.env` y cambiar:
```
APP_DEBUG=false
```
Por:
```
APP_DEBUG=true
```

**IMPORTANTE:** Después de diagnosticar, volver a poner `APP_DEBUG=false`

### 2. VERIFICAR LOGS

Los logs se guardan en: `storage/logs/laravel.log`

Buscar líneas que contengan:
- `Iniciando generación de comprobante`
- `Enviando comprobante a SUNAT`
- `Error al generar comprobante`
- `Error al emitir comprobante`

### 3. VERIFICAR CONFIGURACIÓN DE PRODUCCIÓN

Ir a: **Configuración del Sistema → Facturación SUNAT**

Verificar que:
- ✅ Modo: PRODUCCIÓN
- ✅ Usuario SOL: Debe ser RUC + Usuario (Ej: 20123456789MODDATOS)
- ✅ Clave SOL: Contraseña correcta
- ✅ Certificado Digital: Archivo .p12 cargado
- ✅ Contraseña del Certificado: Ingresada correctamente

### 4. ERRORES COMUNES EN PRODUCCIÓN

#### Error: "Certificado no válido"
**Solución:** 
- Verificar que el certificado .p12 sea el de PRODUCCIÓN (no el de pruebas)
- Verificar que la contraseña del certificado sea correcta

#### Error: "Usuario o clave incorrecta"
**Solución:**
- Verificar que el Usuario SOL sea: `RUC + USUARIO` (Ej: 20123456789MODDATOS)
- Verificar que la Clave SOL sea la correcta
- Probar ingresando a SOL Operaciones: https://www.sunat.gob.pe/operaciones/

#### Error: "El RUC no está autorizado"
**Solución:**
- Verificar que el RUC esté afiliado a Facturación Electrónica en SUNAT
- Ir a: https://www.sunat.gob.pe/ → Mis Trámites → Facturación Electrónica

#### Error: "Timeout" o "No se puede conectar"
**Solución:**
- Verificar conexión a internet
- Verificar que el firewall no bloquee la conexión a SUNAT
- Probar accediendo a: https://e-factura.sunat.gob.pe/

### 5. PROBAR MANUALMENTE

1. Ir a: **Tickets & Facturación**
2. Buscar una venta sin comprobante
3. Click en botón **CPE**
4. Observar el mensaje que aparece

### 6. VERIFICAR SERIES DE COMPROBANTES

Ir a la base de datos y verificar tabla `series_comprobantes`:

```sql
SELECT * FROM series_comprobantes WHERE activo = 1;
```

Debe tener:
- Serie B001 (Boletas) - Activa
- Serie F001 (Facturas) - Activa

### 7. VERIFICAR PERMISOS DE ARCHIVOS

En el servidor, verificar que la carpeta `storage/app/certificados/` tenga permisos de escritura:

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## Checklist de Verificación

- [ ] APP_DEBUG=true (temporal)
- [ ] Modo PRODUCCIÓN activado
- [ ] Certificado .p12 de PRODUCCIÓN cargado
- [ ] Contraseña del certificado correcta
- [ ] Usuario SOL correcto (RUC+USUARIO)
- [ ] Clave SOL correcta
- [ ] RUC afiliado a Facturación Electrónica
- [ ] Series B001 y F001 activas
- [ ] Conexión a internet funcionando
- [ ] Revisar logs en storage/logs/laravel.log

## Contacto de Soporte

Si después de seguir estos pasos el problema persiste, enviar:
1. Captura de pantalla del error
2. Últimas 50 líneas del archivo `storage/logs/laravel.log`
3. Captura de la configuración SUNAT (sin mostrar contraseñas)
