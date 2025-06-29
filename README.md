
```
loteria
├─ assets
│  ├─ css
│  │  ├─ styles.css
│  │  └─ temas-efectos.css
│  ├─ images
│  └─ js
│     └─ scripts.js
├─ config
│  ├─ constants.php
│  └─ database.php
├─ controllers
│  ├─ AdminCoontroller.php
│  ├─ AuthController.php
│  └─ SorteoController.php
├─ includes
│  ├─ functions.php
│  └─ session.php
├─ index-original.php
├─ index.php
├─ models
│  ├─ balota.php
│  ├─ database.php
│  ├─ empleado.php
│  ├─ sorteo.php
│  └─ usuario.php
├─ public
│  └─ index.php
└─ views
   ├─ admin
   │  ├─ dashboard.php
   │  ├─ sorteos.php
   │  └─ usuarios.php
   ├─ auth
   │  └─ login.php
   ├─ layout
   │  ├─ footer.php
   │  └─ header.php
   └─ sorteo
      ├─ dashboard.php
      ├─ elegir_balota.php
      └─ listado.php

```



// ==================================================================
// DOCUMENTACIÓN DE USO
// ==================================================================

/*
GUÍA DE INSTALACIÓN Y USO

1. REQUISITOS DEL SISTEMA:
   - PHP 7.4 o superior
   - MySQL 5.7 o superior
   - Extensiones: PDO, PDO_MySQL, Session, JSON
   - Apache/Nginx con mod_rewrite

2. INSTALACIÓN:

   a) Subir archivos al servidor
   b) Configurar base de datos en config/database.php
   c) Ejecutar: php scripts/cli.php install
   d) Configurar permisos de directorios
   e) Configurar .htaccess

3. CONFIGURACIÓN INICIAL:

   a) Acceder con usuario admin (12345678 / admin123)
   b) Crear sorteo desde panel administrativo
   c) Inscribir empleados al sorteo
   d) Los empleados pueden comenzar a jugar

4. FUNCIONALIDADES PRINCIPALES:

   PARA CONCURSANTES:
   - Login con número de documento
   - Ver dashboard personal
   - Elegir balotas (manual o aleatoria)
   - Ver listado general de participantes

   PARA MODERADORES:
   - Gestionar sorteos (crear, pausar, cerrar)
   - Inscribir/remover participantes
   - Ver estadísticas en tiempo real
   - Generar reportes

5. MANTENIMIENTO:

   - Backups automáticos diarios
   - Limpieza de logs antiguos
   - Monitoreo de sesiones
   - Auditoría de operaciones

6. SEGURIDAD:

   - Prepared statements para SQL
   - Validación de datos de entrada
   - Control de sesiones seguras
   - Logs de auditoría
   - Rate limiting para prevenir ataques

7. API ENDPOINTS:

   - GET /api/ping - Verificar estado del sistema
   - GET /api/verificar-balota?numero=X - Verificar disponibilidad
   - GET /api/estadisticas - Obtener estadísticas (admin)

8. PERSONALIZACIÓN:

   - Modificar CSS en assets/css/
   - Agregar JavaScript en assets/js/
   - Personalizar vistas en views/
   - Extender modelos en models/

9. TROUBLESHOOTING:

   - Revisar logs en logs/sistema.log
   - Verificar permisos de archivos
   - Comprobar configuración de base de datos
   - Verificar extensiones PHP

10. SOPORTE:

    Para soporte técnico, revisar:
    - Logs del sistema
    - Documentación en línea
    - Contactar al desarrollador

*/

```
sorteo_system
├─ api
│  └─ endpoints.php
├─ assets
│  ├─ css
│  │  ├─ styles.css
│  │  └─ temas-efectos.css
│  ├─ images
│  └─ js
│     └─ scripts.js
├─ config
│  ├─ constants.php
│  └─ database.php
├─ controllers
│  ├─ AdminCoontroller.php
│  ├─ AuthController.php
│  └─ SorteoController.php
├─ cron
│  └─ tareas.php
├─ includes
│  ├─ functions.php
│  ├─ logger.php
│  ├─ session.php
│  └─ validator.php
├─ index-original.php
├─ index.php
├─ models
│  ├─ balota.php
│  ├─ database.php
│  ├─ empleado.php
│  ├─ sorteo.php
│  └─ usuario.php
├─ public
│  └─ index.php
├─ README.md
├─ scripts
│  ├─ backup.php
│  ├─ install.php
│  └─ migration.php
├─ utils
│  └─ reportes.php
└─ views
   ├─ admin
   │  ├─ dashboard.php
   │  ├─ sorteos.php
   │  └─ usuarios.php
   ├─ auth
   │  └─ login.php
   ├─ layout
   │  ├─ footer.php
   │  └─ header.php
   └─ sorteo
      ├─ dashboard.php
      ├─ elegir_balota.php
      └─ listado.php

```