# Nombre de la Aplicación

Breve descripción de lo que hace la aplicación y su propósito.

---

## Índice

1. [Introducción](#introducción)
2. [Endpoints](#endpoints)

---

## Introducción

Para tener la BBDD y las tablas creadas, habrá que ejecutar el siguiente comando:
```bash
    php bin/console doctrine:database:create && php bin/console doctrine:migrations:migrate
```
PD: hay un bug que no he podido solucionar por tiempo, y es el tratamiento de acentos y caracteres especial.
Si se intenta guardar alguna string con acentos o caracteres especiales, dará un 500.

## Endpoints

Ya teniendo la BBDD y las tablas creadas, comenzamos con los distintos endpoints que existen en la aplicación:

### 1. Lista de categorías
Endpoint para listar las diferentes categorías.
```bash
    curl -X GET http://localhost:8000/categorias
```
Posibles resultados:
1. Listado de las categorías.
2. Mensaje de error que dice que no existe categorías.
### 2. Creación de categorías
Endpoint para crear una nueva categoría.
```bash
    curl -X POST http://localhost:8000/categoria -H "Content-Type: application/json" -d '{"nombre":"Comida","descripcion":"Etiqueta que permite categorizar productos alimenticios."}'
```
Posibles resultados:
1. Crea la categoría.
2. Mensaje de error que dice que ya existe una categoría con el mismo nombre.
3. Mensaje de error que obliga a pasar los parámetros correctamente.
### 3. Actualización de categorías
Endpoint para actualizar las diferentes categorías.
```bash
    curl -X PUT http://localhost:8000/categoria/1 -H "Content-Type: application/json" -d '{"nombre":"Alimentos","descripcion":"Etiqueta que permite categorizar productos alimenticios, y que ademas ha sido modificada."}'
```
Posibles resultados:
1. Actualice la categoría sin mayor problema.
2. No encuentre la categoría.
3. Que ya existe una categoría con el mismo nombre y no pueda actualizar la categoría.
4. Obligue a pasar los parámetros correctamente.
### 4. Eliminación de categorías
Endpoint para eliminar las diferentes categorías.
```bash
    curl -X DELETE http://localhost:8000/categoria/1
```
Posibles resultados:
1. Elimine la categoría sin mayor problema.
2. No encuentre la categoría.
3. Obligue a pasar los parámetros correctamente.

### 5. Lista de productos
Endpoint para listar los diferentes productos.
```bash
    curl -X GET http://localhost:8000/productos
```
Posibles resultados:
1. Listado de los productos.
2. Mensaje de error que dice que no existe el producto.
### 6. Creación de productos
Endpoint para crear un nuevo producto.
```bash
    curl -X POST http://localhost:8000/producto -H "Content-Type: application/json" -d '{"nombre":"Tarta de queso","descripcion":"Un postre suave y cremoso que se deshace en la boca.","precio":5,"categoria_id":1}'
```
Posibles resultados:
1. Crea el producto.
2. Mensaje de error que dice que ya existe un producto con el mismo nombre.
3. La categoría que se intenta asociar no existe.
4. Mensaje de error que obliga a pasar los parámetros correctamente.
### 7. Actualización de productos
Endpoint para actualizar los diferentes productos.
```bash
    curl -X PUT http://localhost:8000/producto/1 -H "Content-Type: application/json" -d '{"nombre":"Tarta de chocolate","descripcion":"Un postre suave y cremoso que se deshace en la boca. Esta tarta es el preferido de todos los que lo han provado.","precio":10.0,"categoria_id":1}'
```
Posibles resultados:
1. Actualice el producto sin mayor problema.
2. No encuentre el producto.
3. Que ya existe un producto con el mismo nombre y no pueda actualizar el producto.
4. La categoría que se intenta asociar no existe.
5. Mensaje de error que obliga a pasar los parámetros correctamente.
### 8. Eliminación de productos
Endpoint para eliminar los diferentes productos.
```bash
    curl -X DELETE http://localhost:8000/producto/1
```
Posibles resultados:
1. Elimine el producto sin mayor problema.
2. No encuentre el producto.
3. Mensaje de error que obliga a pasar los parámetros correctamente.