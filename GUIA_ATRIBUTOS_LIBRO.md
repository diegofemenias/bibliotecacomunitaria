# Guía de Atributos para Libros en una Biblioteca Comunitaria

## 📚 Atributos Básicos del Libro

### Información Identificadora
- **ISBN** (International Standard Book Number): Identificador único internacional
  - Tipo: String (puede ser ISBN-10 o ISBN-13)
  - Ejemplo: "978-84-376-0494-7"
  - Nota: No todos los libros tienen ISBN (especialmente libros antiguos o autoeditados)

- **Código de Barras Interno**: Código único asignado por la biblioteca
  - Tipo: String
  - Ejemplo: "BIB-2024-001234"
  - Nota: Útil para inventario y préstamos rápidos

- **ID Único**: Identificador primario en la base de datos
  - Tipo: Integer o UUID
  - Auto-generado

### Información Bibliográfica
- **Título**: Título completo del libro
  - Tipo: String (VARCHAR)
  - Requerido: Sí
  - Ejemplo: "Cien años de soledad"

- **Subtítulo**: Subtítulo si existe
  - Tipo: String (VARCHAR)
  - Requerido: No
  - Ejemplo: "Una novela de realismo mágico"

- **Autor(es)**: Nombre del autor o autores
  - Tipo: String o relación con tabla Autores
  - Requerido: Sí
  - Nota: Un libro puede tener múltiples autores
  - Ejemplo: "Gabriel García Márquez"

- **Editorial**: Casa editorial
  - Tipo: String o relación con tabla Editoriales
  - Ejemplo: "Editorial Sudamericana"

- **Año de Publicación**: Año en que se publicó el libro
  - Tipo: Integer (año)
  - Ejemplo: 1967

- **Edición**: Número de edición
  - Tipo: String
  - Ejemplo: "1ra edición", "2da edición revisada"

- **Lugar de Publicación**: Ciudad y país donde se publicó
  - Tipo: String
  - Ejemplo: "Buenos Aires, Argentina"

- **Número de Páginas**: Cantidad total de páginas
  - Tipo: Integer
  - Ejemplo: 471

- **Idioma**: Idioma en que está escrito el libro
  - Tipo: String o relación con tabla Idiomas
  - Ejemplo: "Español", "Inglés"

- **Formato**: Tipo de formato físico
  - Tipo: String o ENUM
  - Valores posibles: "Tapa dura", "Tapa blanda", "Rústica", "Espiral", etc.

- **Dimensiones**: Tamaño del libro (opcional)
  - Tipo: String
  - Ejemplo: "23 x 15 cm"

## 📖 Atributos de Categorización

- **Categoría/Temática**: Clasificación temática
  - Tipo: String o relación con tabla Categorías
  - Ejemplo: "Literatura", "Historia", "Ciencia", "Biografía"
  - Nota: Un libro puede tener múltiples categorías

- **Género**: Género literario o temático
  - Tipo: String
  - Ejemplo: "Novela", "Ensayo", "Poesía", "Infantil"

- **Clasificación Decimal Dewey (CDD)**: Sistema de clasificación estándar
  - Tipo: String
  - Ejemplo: "863.64" (Literatura colombiana)

- **Palabras Clave/Etiquetas**: Para búsqueda avanzada
  - Tipo: Array/String o relación con tabla Tags
  - Ejemplo: ["realismo mágico", "América Latina", "familia"]

## 📝 Atributos de Contenido

- **Sinopsis/Resumen**: Descripción breve del contenido
  - Tipo: Text (TEXT)
  - Ejemplo: "La historia de la familia Buendía..."

- **Índice**: Tabla de contenidos (opcional)
  - Tipo: Text
  - Requerido: No

- **Notas Adicionales**: Información extra sobre el libro
  - Tipo: Text
  - Ejemplo: "Incluye prólogo del autor", "Edición conmemorativa"

## 🏷️ Atributos de Gestión de la Biblioteca

### Estado y Disponibilidad
- **Estado Físico**: Condición del ejemplar
  - Tipo: ENUM o String
  - Valores: "Excelente", "Bueno", "Regular", "Malo", "Requiere reparación", "Perdido"
  - Requerido: Sí

- **Estado de Disponibilidad**: Si está disponible para préstamo
  - Tipo: ENUM
  - Valores: "Disponible", "Prestado", "Reservado", "En reparación", "No disponible"
  - Requerido: Sí

- **Ubicación Física**: Dónde se encuentra en la biblioteca
  - Tipo: String
  - Ejemplo: "Estante A-3, Fila 2", "Sala de lectura"
  - Nota: Útil para encontrar el libro físicamente

- **Número de Ejemplares**: Cantidad de copias del mismo libro
  - Tipo: Integer
  - Ejemplo: 3
  - Nota: Si tienes múltiples copias, cada una puede ser un registro separado

### Información de Adquisición
- **Fecha de Adquisición**: Cuándo se agregó a la biblioteca
  - Tipo: Date
  - Ejemplo: "2024-01-15"

- **Método de Adquisición**: Cómo se obtuvo el libro
  - Tipo: ENUM o String
  - Valores: "Compra", "Donación", "Intercambio", "Préstamo permanente"

- **Precio de Compra**: Si fue comprado, el precio pagado
  - Tipo: Decimal
  - Ejemplo: 25.50

- **Donante**: Si fue donado, quién lo donó
  - Tipo: String o relación con tabla Donantes
  - Ejemplo: "Juan Pérez"

### Información de Uso
- **Número de Préstamos**: Cuántas veces se ha prestado
  - Tipo: Integer
  - Valor inicial: 0
  - Se incrementa con cada préstamo

- **Última Fecha de Préstamo**: Cuándo fue prestado por última vez
  - Tipo: Date
  - Nullable: Sí

- **Fecha de Última Devolución**: Cuándo fue devuelto por última vez
  - Tipo: Date
  - Nullable: Sí

## 🖼️ Atributos Multimedia

- **URL de Portada**: Enlace a imagen de la portada
  - Tipo: String (URL)
  - Ejemplo: "/uploads/covers/cien-anos-soledad.jpg"

- **Imagen de Portada**: Archivo de imagen almacenado
  - Tipo: BLOB o ruta de archivo
  - Nota: Útil para mostrar en catálogo

## 👥 Atributos de Relación (Para Base de Datos Relacional)

### Relaciones Importantes a Considerar:

1. **Autores** (Relación Muchos a Muchos)
   - Un libro puede tener múltiples autores
   - Un autor puede tener múltiples libros
   - Tabla intermedia: `libro_autor`

2. **Categorías** (Relación Muchos a Muchos)
   - Un libro puede estar en múltiples categorías
   - Una categoría puede tener múltiples libros
   - Tabla intermedia: `libro_categoria`

3. **Préstamos** (Relación Uno a Muchos)
   - Un libro puede tener múltiples préstamos a lo largo del tiempo
   - Tabla: `prestamos` con referencia a `libro_id`

4. **Reservas** (Relación Uno a Muchos)
   - Un libro puede tener múltiples reservas
   - Tabla: `reservas` con referencia a `libro_id`

5. **Editoriales** (Relación Muchos a Uno)
   - Muchos libros pertenecen a una editorial
   - Tabla: `editoriales` con referencia desde `libros`

## 📊 Atributos Adicionales para Biblioteca Comunitaria

- **Recomendado para**: Grupos de edad o audiencia
  - Tipo: String
  - Ejemplo: "Adultos", "Jóvenes", "Niños 8-12 años"

- **Valoración/Calificación Promedio**: Si los usuarios pueden calificar
  - Tipo: Decimal
  - Ejemplo: 4.5 (de 5 estrellas)

- **Número de Reseñas**: Cantidad de reseñas de usuarios
  - Tipo: Integer

- **Destacado**: Si el libro está destacado en el catálogo
  - Tipo: Boolean
  - Ejemplo: true/false

- **Fecha de Registro en Sistema**: Cuándo se ingresó al sistema
  - Tipo: DateTime
  - Auto-generado

- **Última Actualización**: Cuándo se modificó por última vez
  - Tipo: DateTime
  - Auto-actualizado

## 🎯 Priorización de Atributos

### Atributos Esenciales (Mínimo Viable)
1. ID único
2. Título
3. Autor(es)
4. Estado de disponibilidad
5. Estado físico
6. Fecha de adquisición

### Atributos Importantes (Recomendados)
7. ISBN o código interno
8. Editorial
9. Año de publicación
10. Categoría
11. Número de páginas
12. Idioma
13. Ubicación física
14. Sinopsis

### Atributos Opcionales (Mejoras)
15. Clasificación Dewey
16. Imagen de portada
17. Palabras clave
18. Valoración promedio
19. Número de préstamos
20. Dimensiones y formato

## 💡 Recomendaciones para la Base de Datos

1. **Normalización**: Separa autores, editoriales y categorías en tablas propias
2. **Índices**: Crea índices en campos de búsqueda frecuente (título, autor, ISBN)
3. **Soft Delete**: Considera un campo `deleted_at` en lugar de borrar registros
4. **Auditoría**: Campos `created_at` y `updated_at` para rastrear cambios
5. **Versión de Ejemplar**: Si tienes múltiples copias, cada copia debe ser un registro separado con su propio estado

## 📋 Ejemplo de Estructura de Tabla SQL

```sql
-- Ejemplo básico (ajustar según necesidades)
CREATE TABLE libros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    codigo_interno VARCHAR(50) UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    subtitulo VARCHAR(255),
    editorial_id INT,
    anio_publicacion INT,
    edicion VARCHAR(50),
    lugar_publicacion VARCHAR(100),
    numero_paginas INT,
    idioma VARCHAR(50),
    formato VARCHAR(50),
    dimensiones VARCHAR(50),
    sinopsis TEXT,
    estado_fisico ENUM('Excelente', 'Bueno', 'Regular', 'Malo', 'Requiere reparación', 'Perdido') NOT NULL,
    estado_disponibilidad ENUM('Disponible', 'Prestado', 'Reservado', 'En reparación', 'No disponible') NOT NULL DEFAULT 'Disponible',
    ubicacion_fisica VARCHAR(100),
    fecha_adquisicion DATE,
    metodo_adquisicion VARCHAR(50),
    precio_compra DECIMAL(10,2),
    donante VARCHAR(100),
    numero_prestamos INT DEFAULT 0,
    ultima_fecha_prestamo DATE,
    ultima_fecha_devolucion DATE,
    url_portada VARCHAR(255),
    recomendado_para VARCHAR(50),
    valoracion_promedio DECIMAL(3,2),
    numero_resenas INT DEFAULT 0,
    destacado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (editorial_id) REFERENCES editoriales(id)
);
```

---

**Nota**: Esta guía es una referencia completa. Adapta los atributos según las necesidades específicas de tu biblioteca comunitaria. Puedes empezar con los atributos esenciales y agregar más conforme el sistema crezca.

