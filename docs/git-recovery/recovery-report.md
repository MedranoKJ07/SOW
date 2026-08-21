# Informe final de recuperación de Git

Fecha: 2026-08-20

## 1. Motivo de la reinicialización

La carpeta `.git` original no existía en la raíz del proyecto. Se preservó primero el estado completo en `C:/Users/kmuri/Downloads/Optica-preservation-20260820` y después se inicializó un repositorio nuevo directamente en `C:/Users/kmuri/Downloads/download/htdocs/sow/Optica`.

## 2. Evidencia encontrada

Se revisaron la raíz del proyecto, su carpeta superior, `download.zip`, los SQL adyacentes, los diagramas `.mwb`/`.bak` y las ubicaciones de trabajo habituales. El ZIP contiene los mismos 917 archivos del estado actual bajo `htdocs/sow/Optica`; la comparación por SHA-256 encontró cero agregados, eliminados o modificados.

## 3. Historial recuperable

No se encontró historial Git, bundle, parche, diff ni snapshot anterior con diferencias verificables. Las fechas disponibles corresponden a copias/extracciones y no permiten reconstruir fechas originales de commits.

## 4. Commit reconstruido

Se creó un único baseline neutral:

| Commit | Autor | Mensaje | Alcance |
| --- | --- | --- | --- |
| `e6ce2dc` | Git Reconstruction `<reconstruction@local.invalid>` | `chore(project): restaurar código fuente después de pérdida del historial Git` | Estado actual recuperado, documentación de recuperación y configuración segura de ejemplo |

No se dividió artificialmente el árbol por módulos porque no existe evidencia de diferencias entre etapas.

## 5. Autoría

No fue posible verificar qué cambios pertenecían a Kerlint, Celeste, Eduardo o Cristopher. Por ello no se realizaron commits con sus identidades ni se configuró Git globalmente. La identidad usada es local al repositorio y neutral.

## 6. Información no recuperada

No se recuperaron commits originales, fechas históricas, ramas originales, mensajes originales ni atribución individual verificable.

## 7. Estado actual

El baseline conserva el código fuente, recursos, `composer.json`, `composer.lock`, documentación de recuperación y `.env.example`. `vendor/`, configuraciones locales, archivos de correo con credenciales, logs, SQL locales y backups están excluidos por `.gitignore`; no se eliminaron del respaldo ni del entorno de trabajo.

Se detectaron credenciales SMTP incrustadas en archivos locales ignorados. Deben revocarse/rotarse si fueron reales y migrarse a variables de entorno antes de publicar el repositorio.

Validaciones realizadas:

- PHP 8.2: lint sin errores en 73 archivos PHP rastreados.
- Composer: `composer.json` válido; advertencia no bloqueante por licencia no declarada.
- No se ejecutó `git push`.

## 8. Ramas de trabajo

Desde `main` se crearán las ramas de desarrollo neutrales indicadas en la misión:

- `dev/kerlint-database`
- `dev/celeste-auth`
- `dev/eduardo-clinical`
- `dev/cristopher-admin`

Estas ramas son para desarrollo posterior y no representan ramas históricas recuperadas.
