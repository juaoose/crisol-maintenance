# El Crisol Maintenance

Plugin independiente de WordPress para mostrar la página amarilla de mantenimiento de Importaciones El Crisol. Incluye el logo transparente, teléfono, correo y dirección con enlace a Google Maps. Un único ajuste permite activar o desactivar la plantilla.

## Descargar e instalar

1. Abre [Actions](https://github.com/juaoose/crisol-maintenance/actions/workflows/build.yml) y selecciona una ejecución satisfactoria de **Test and build plugin** para el commit que quieras instalar.
2. Descarga el archivo de **Artifacts**. Si la descarga contiene otro ZIP, extrae y utiliza `el-crisol-mantenimiento.zip`; no subas el archivo exterior a WordPress.
3. En WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**, selecciona el ZIP e instala y activa **El Crisol - mantenimiento**. Para actualizar una versión anterior, acepta reemplazarla con la versión subida.
4. Desactiva cualquier otro plugin de mantenimiento que estuviera en uso.
5. En **Ajustes → Mantenimiento El Crisol**, marca **Mostrar la página de mantenimiento** y guarda.
6. Borra la caché del sitio si la hay y comprueba la página en una ventana de incógnito.

Para volver a mostrar el sitio normal, desmarca el ajuste y guarda. También puedes desactivar el plugin. La primera instalación deja el mantenimiento apagado. Los administradores con sesión iniciada pueden seguir viendo la web normal mientras está activo.

El plugin no reemplaza el login, la administración, cron ni los endpoints REST/AJAX. Su finalidad es mostrar una página temporal, no controlar el acceso a información confidencial. Necesita que WordPress pueda ejecutarse. Las cachés externas pueden requerir un vaciado manual.

## Desarrollo

El código instalable está en `el-crisol-mantenimiento/`. Los textos se encuentran en `template.php` y el diseño en `styles.css`.

Con PHP y Python 3 instalados:

```sh
php tests/maintenance.php
python3 scripts/build.py
```

El ZIP instalable se genera en `build/el-crisol-mantenimiento.zip`. El script usa una lista explícita de archivos, incluye la licencia, verifica su estructura y no empaqueta configuración local, pruebas ni credenciales.

El workflow se ejecuta con cada push, en pull requests y manualmente. Comprueba la sintaxis PHP, ejecuta 35 pruebas de comportamiento en PHP 7.4 y 8.3 y publica el ZIP cuando ambas versiones pasan. Las pruebas simulan el límite de APIs de WordPress; no equivalen a una instalación completa en cada versión de WordPress.

Mínimos declarados: WordPress 4.7 y PHP 5.6. La matriz de pruebas es PHP 7.4 y 8.3.

## Licencia

El código se distribuye bajo **GPL-2.0-or-later**; consulta [LICENSE](LICENSE). El nombre y el logo de El Crisol siguen siendo marcas de sus respectivos propietarios; la licencia no concede derechos sobre esas marcas.
