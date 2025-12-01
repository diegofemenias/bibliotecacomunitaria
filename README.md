# Sistema de Gestión de Biblioteca Comunitaria

Sistema web desarrollado en PHP 8 y MySQL para la gestión de una biblioteca comunitaria.

## Características

### Usuario Público (Sin Login)
- Búsqueda de libros por título, autor, ISBN o código interno
- Visualización de ejemplares disponibles y totales
- Reserva de libros ingresando solo el número de cédula
- Las reservas reducen automáticamente los ejemplares disponibles mostrados
- Interfaz moderna y responsive

### Usuario Administrador
- Autenticación simple con password hardcodeada
- Dashboard con estadísticas y lista de libros con acciones rápidas
- CRUD completo para todas las entidades:
  - **Libros**: Con dropdowns dependientes (editorial, idioma) y relaciones múltiples (autores, categorías, tags)
  - **Ejemplares**: Gestión de copias físicas de libros (múltiples ejemplares por libro)
  - **Autores**: Gestión completa de información de autores
  - **Editoriales**: Gestión de editoriales
  - **Categorías**: Sistema de categorías con categorías padre
  - **Idiomas**: Gestión de idiomas
  - **Usuarios**: Gestión de usuarios de la biblioteca
  - **Préstamos**: Gestión de préstamos con cálculo de vencimientos e historial completo
  - **Reservas**: Visualización y gestión de reservas (al completar una reserva se crea automáticamente un préstamo)
  - **Tags**: Etiquetas para clasificación
  - **Reseñas**: Aprobación y gestión de reseñas de usuarios
- Búsqueda avanzada de libros por código, título o autor
- Acciones rápidas: Prestar, Reservar, Devolver desde el dashboard y lista de libros

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7 o superior (o MariaDB)
- Servidor web (Apache/Nginx) o MAMP/XAMPP/WAMP
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - mbstring

## Instalación

### 1. Configurar Base de Datos

Ejecuta el script SQL para crear la base de datos y todas las tablas:

```bash
mysql -u root -p < migracion_completa.sql
```

O importa el archivo `migracion_completa.sql` desde phpMyAdmin o tu cliente MySQL preferido.

Este script incluye:
- Estructura completa de la base de datos
- Datos de muestra (5 usuarios, 20 libros, 5 categorías, 5 autores, 3 editoriales)

### 2. Configurar Conexión a Base de Datos

Edita el archivo `config/database.php` y ajusta las credenciales según tu configuración:

```php
private $host = 'localhost';
private $dbname = 'biblio';
private $username = 'root';  // Cambiar según tu configuración
private $password = 'root';  // Cambiar según tu configuración
```

### 3. Configurar Credenciales de Administrador

Edita el archivo `config/config.php` para cambiar las credenciales del administrador:

```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Cambiar en producción
```

### 4. Configurar URL Base (Opcional)

Si tu proyecto no está en la raíz del servidor, ajusta la URL en `config/config.php`:

```php
define('APP_URL', 'http://localhost:8888/biblio');
```

### 5. Permisos de Archivos

Asegúrate de que el servidor web tenga permisos de lectura en todos los archivos.

## Estructura del Proyecto

```
biblio/
├── config/
│   ├── config.php          # Configuración general
│   └── database.php        # Conexión a base de datos
├── models/
│   ├── Model.php           # Modelo base
│   ├── LibroModel.php      # Modelo de libros
│   ├── EjemplarModel.php   # Modelo de ejemplares (copias físicas)
│   ├── ReservaModel.php    # Modelo de reservas
│   └── UsuarioModel.php    # Modelo de usuarios
├── admin/
│   ├── index.php           # Dashboard con estadísticas y lista de libros
│   ├── login.php           # Login de administrador
│   ├── libros.php          # CRUD de libros
│   ├── ejemplares.php      # CRUD de ejemplares
│   ├── autores.php         # CRUD de autores
│   ├── editoriales.php     # CRUD de editoriales
│   ├── categorias.php      # CRUD de categorías
│   ├── idiomas.php         # CRUD de idiomas
│   ├── usuarios.php        # CRUD de usuarios
│   ├── prestamos.php       # CRUD de préstamos (activos e histórico)
│   ├── reservas.php        # Gestión de reservas
│   ├── tags.php            # CRUD de tags
│   ├── resenas.php         # Gestión de reseñas
│   ├── ajax_create.php     # Endpoint AJAX para crear entidades
│   ├── ajax_ejemplares.php # Endpoint AJAX para cargar ejemplares
│   └── includes/
│       ├── header.php      # Header con sidebar
│       ├── sidebar.php     # Estilos del sidebar
│       └── footer.php      # Footer
├── index.php               # Página pública de búsqueda
├── login.php               # Login de administrador (raíz)
├── logout.php              # Cerrar sesión
├── reservar.php            # Procesar reservas
├── migracion_completa.sql  # Script completo de base de datos con datos de muestra
└── README.md               # Este archivo
```

## Uso

### Acceso Público

1. Abre `http://localhost:8888/biblio/` en tu navegador
2. Busca libros por título, autor, ISBN o código
3. Haz clic en "Reservar" en un libro disponible
4. Ingresa tu número de cédula
5. Confirma la reserva

### Acceso Administrador

1. Ve a `http://localhost:8888/biblio/admin/login.php` o `http://localhost:8888/biblio/login.php`
2. Ingresa las credenciales:
   - Usuario: `admin`
   - Contraseña: `admin123` (o la que hayas configurado)
3. Accede al panel de administración

### Gestión de Libros

Al crear o editar un libro:
- Selecciona la **Editorial** del dropdown (dependiente)
- Selecciona el **Idioma** del dropdown (dependiente)
- Selecciona **múltiples Autores** (mantén Ctrl/Cmd presionado)
- Selecciona **múltiples Categorías** (mantén Ctrl/Cmd presionado)
- Selecciona **múltiples Tags** (mantén Ctrl/Cmd presionado)
- Puedes crear nuevas editoriales, autores, categorías y tags desde popups sin salir del formulario

### Sistema de Ejemplares

- Cada libro puede tener múltiples ejemplares (copias físicas)
- Los ejemplares tienen su propio estado de disponibilidad
- Al prestar un libro, se selecciona un ejemplar específico
- Al devolver un préstamo, el ejemplar vuelve automáticamente a estar disponible
- Las reservas reducen los ejemplares disponibles mostrados en la búsqueda pública

### Gestión de Reservas

- Al cambiar una reserva de "Pendiente" a "Completada", se crea automáticamente un préstamo
- El sistema asigna automáticamente un ejemplar disponible al préstamo

## Características Técnicas

- **Arquitectura**: MVC básico
- **Base de Datos**: MySQL con relaciones normalizadas
- **Seguridad**: 
  - Sanitización de entradas
  - Prepared statements (PDO)
  - Soft delete (no elimina físicamente)
- **Interfaz**: Bootstrap 5
- **Iconos**: Bootstrap Icons

## Notas Importantes

1. **Password del Administrador**: Cambia la contraseña hardcodeada en producción
2. **Base de Datos**: Asegúrate de ejecutar el script `migracion_completa.sql` antes de usar el sistema
3. **Permisos**: El sistema crea usuarios automáticamente cuando se hace una reserva si no existen
4. **Reservas**: Las reservas tienen una validez de 7 días por defecto (configurable en `config/config.php`)
5. **Ejemplares**: Cada libro puede tener múltiples ejemplares físicos. Los préstamos se realizan sobre ejemplares específicos
6. **Reservas a Préstamos**: Al completar una reserva, se crea automáticamente un préstamo con un ejemplar disponible

## Solución de Problemas

### Error de conexión a la base de datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que MySQL esté corriendo
- Verifica que la base de datos existe

### No se muestran los libros
- Verifica que hayas ejecutado el script SQL
- Revisa que los datos iniciales se hayan insertado correctamente

### Error al crear libro
- Verifica que existan editoriales, idiomas, autores y categorías creadas previamente
- Revisa los logs de PHP para más detalles

## Desarrollo Futuro

Posibles mejoras:
- Sistema de autenticación más robusto
- Subida de imágenes de portadas
- Reportes y estadísticas
- Notificaciones por email
- API REST
- Sistema de multas automático

## Licencia

Este proyecto es de código abierto y está disponible para uso comunitario.


