# Evidencia de trabajo — Celeste

Rama de trabajo: `dev/celeste-auth`

## Commit: `7be6ee9`

- Objetivo: centralizar controles de sesión y autorización.
- Autor: Celeste `<nohemiruiz705@gmail.com>`
- Fecha: 2026-08-20
- Mensaje: `refactor(auth): centralizar controles de sesión`
- Archivos: `includes/security.php`, `login.php`, `logout.php`, `Administrador/panelAdmin.php`
- Cambio: 55 líneas agregadas; 7 eliminadas.
- Problema encontrado: cada entrada configuraba sesiones de forma distinta y el panel admin repetía autorización.
- Solución: cookies `HttpOnly`/`SameSite`, inicio seguro de sesión y `require_role()` centralizado.
- Prueba ejecutada: `php -l` en los cuatro archivos.
- Resultado: PASS — sin errores de sintaxis.

## Commit: `806b4e9`

- Objetivo: centralizar protección CSRF en administración de usuarios.
- Autor: Celeste `<nohemiruiz705@gmail.com>`
- Fecha: 2026-08-20
- Mensaje: `security(auth): centralizar protección csrf`
- Archivos: `Administrador/usuarios.php`, `Administrador/usuario_form.php`
- Cambio: 10 líneas agregadas; 18 eliminadas.
- Problema encontrado: tokens generados y comparados de forma duplicada en dos endpoints.
- Solución: namespaces CSRF independientes mediante `csrf_token()` y `csrf_valid()`.
- Prueba ejecutada: `php -l` en ambos archivos.
- Resultado: PASS — sin errores de sintaxis.

## Commit: `286d4f6`

- Objetivo: evitar abuso del reenvío de códigos MFA.
- Autor: Celeste `<nohemiruiz705@gmail.com>`
- Fecha: 2026-08-20
- Mensaje: `fix(auth): aplicar límite de reenvío MFA`
- Archivos: `reenviar_codigo.php`, `verificar_mfa.php`
- Cambio: 14 líneas agregadas; 11 eliminadas.
- Problema encontrado: el cooldown de 30 segundos estaba después de `exit` y nunca se ejecutaba.
- Solución: mover la consulta y el bloqueo antes de enviar el OTP, además de usar sesión segura.
- Prueba ejecutada: `php -l verificar_mfa.php` y `php -l reenviar_codigo.php`.
- Resultado: PASS — sin errores de sintaxis.

## Resumen

- Rama: `dev/celeste-auth`
- Autor Git: Celeste `<nohemiruiz705@gmail.com>`
- Área: autenticación, usuarios y seguridad.
- Commits: `7be6ee9`, `806b4e9`, `286d4f6`.
- Archivos tocados: helper de seguridad, login/logout, MFA y administración de usuarios.
- Problemas solucionados: sesiones débiles, autorización duplicada, CSRF duplicado y rate limit MFA inoperante.
- Pruebas: lint PHP PASS en todos los archivos modificados.
- Pendientes: pruebas funcionales con base de datos y pruebas de navegador para flujos MFA/CSRF.
