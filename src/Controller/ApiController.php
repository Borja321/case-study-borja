<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Categoria;
use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{

    /**
     * @Route("/categorias", methods={"GET"})
     */
    #[Route('/categorias', name: 'listarCategorias', methods: 'GET')]
    public function listarCategorias(EntityManagerInterface $em): JsonResponse
    {

        $categorias = $em->getRepository(Categoria::class)->findAll();

        $data = [];

        foreach ($categorias as $categoria) {

            $data[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
                'descripcion' => $categoria->getDescripcion(),
            ];

        }

        return $data !== [] ? $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => $data],Response::HTTP_OK) : $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'No existen categorías.']);

    }


    /**
     * @Route("/categoria", methods={"POST"})
     */
    #[Route('/categoria', name: 'crearCategoria', methods: 'POST')]
    public function crearCategoria(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (
            !isset(
                $data['nombre'],
                $data['descripcion']
            )
            || !is_string($data['nombre'])
            || !is_string($data['descripcion'])
        ){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'Debe proporcionar en el cuerpo, el nombre y la descripción como string.'], Response::HTTP_BAD_REQUEST);

        }

        $categoria_original = $em->getRepository(Categoria::class)->findBy(['nombre' => $data['nombre']]);

        if ($categoria_original) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_CONFLICT,'Response' => 'Categoría ya existente.'], Response::HTTP_CONFLICT);

        }

        $categoria = new Categoria();

        $categoria->setNombre($data['nombre']);
        $categoria->setDescripcion($data['descripcion']);

        $em->persist($categoria);
        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_CREATED,'Response' => 'Categoría creado correctamente.'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/categoria/{id}", methods={"PUT"})
     */
    #[Route('/categoria/{id}', name: 'actualizarCategoria', methods: 'PUT')]
    public function actualizarCategoria($id, Request $request, EntityManagerInterface $em): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (
            !isset(
                $data['nombre'],
                $data['descripcion'],
            )
            || !filter_var($id, FILTER_VALIDATE_INT)
            || !is_string($data['nombre'])
            || !is_string($data['descripcion'])
        ){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'Debe proporcionar la id como int en la url del endpoint, y en el cuerpo, el nombre y la descripción como string.'], Response::HTTP_BAD_REQUEST);

        }

        $categoria = $em->getRepository(Categoria::class)->find($id);

        if (!$categoria) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Categoría no encontrado.'], Response::HTTP_NOT_FOUND);

        }

        $categoria_original = $em->getRepository(Categoria::class)->findBy(['nombre' => $data['nombre']]);

        if ($categoria_original && $categoria_original[0]->getId() !== $categoria->getId()) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_CONFLICT,'Response' => 'Categoría ya existente.'], Response::HTTP_CONFLICT);

        }

        if (isset($data['nombre'])) {

            $categoria->setNombre($data['nombre']);

        }

        if (isset($data['descripcion'])) {

            $categoria->setDescripcion($data['descripcion']);

        }

        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => 'Categoría actualizado correctamente.'], Response::HTTP_OK);
    }

    /**
     * @Route("/categoria/{id}", methods={"DELETE"})
     */
    #[Route('/categoria/{id}', name: 'eliminarCategoria', methods: 'DELETE')]
    public function eliminarCategoria($id, EntityManagerInterface $em): JsonResponse
    {

        if (!filter_var($id, FILTER_VALIDATE_INT)){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'El identificador que se ha pasado no es un número.'], Response::HTTP_BAD_REQUEST);

        }

        $categoria = $em->getRepository(Categoria::class)->find($id);

        if (!$categoria) {

            return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Categoría no encontrado.'], Response::HTTP_NOT_FOUND);

        }

        $em->remove($categoria);
        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => 'Categoría eliminado correctamente.'], Response::HTTP_OK);
    }

    /**
     * @Route("/productos", methods={"GET"})
     */
    #[Route('/productos', name: 'listarProductos', methods: 'GET')]
    public function listarProductos(EntityManagerInterface $em): JsonResponse
    {

        $productos = $em->getRepository(Producto::class)->findAll();

        $data = [];

        foreach ($productos as $producto) {

            $categoria = $em->getRepository(Categoria::class)->find($producto->getCategoriaId());

            $data[] = [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'descripcion' => $producto->getDescripcion(),
                'precio' => $producto->getPrecio(),
                'categoria_id' => $producto->getCategoriaId(),
                'categoria_nombre' => $categoria->getNombre(),
            ];

        }

        return $data !== [] ? $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => $data], Response::HTTP_OK) : new JsonResponse(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'No existen productos.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/producto", methods={"POST"})
     */
    #[Route('/producto', name: 'crearProducto', methods: 'POST')]
    public function crearProducto(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (
            !isset(
                $data['nombre'],
                $data['descripcion'],
                $data['precio'],
                $data['categoria_id']
            )
            || !is_string($data['nombre'])
            || !is_string($data['descripcion'])
            || !(is_int($data['precio']) || is_double($data['precio']))
            || !is_int($data['categoria_id'])
        ){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'Debe proporcionar en el cuerpo, el nombre y la descripción como string, el precio como int o double y la categoria_id como int.'], Response::HTTP_BAD_REQUEST);

        }

        $categoria = $em->getRepository(Categoria::class)->find($data['categoria_id']);

        if (!$categoria) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Categoría no encontrada.'], Response::HTTP_NOT_FOUND);

        }

        $producto_original = $em->getRepository(Producto::class)->findBy(['nombre' => $data['nombre']]);

        if ($producto_original) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_CONFLICT,'Response' => 'Producto ya existe.'], Response::HTTP_CONFLICT);

        }

        $producto = new Producto();

        $producto->setNombre($data['nombre']);
        $producto->setDescripcion($data['descripcion']);
        $producto->setPrecio($data['precio']);
        $producto->setCategoriaId($data['categoria_id']);

        $em->persist($producto);
        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_CREATED,'Response' => 'Producto creado correctamente.'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/producto/{id}", methods={"PUT"})
     */
    #[Route('/producto/{id}', name: 'actualizarProducto', methods: 'PUT')]
    public function actualizarProducto($id, Request $request, EntityManagerInterface $em): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if (
            !isset(
                $data['nombre'],
                $data['descripcion'],
                $data['precio'],
                $data['categoria_id']
            )
            || !filter_var($id, FILTER_VALIDATE_INT)
            || !is_string($data['nombre'])
            || !is_string($data['descripcion'])
            || !(is_int($data['precio']) || is_double($data['precio']))
            || !is_int($data['categoria_id'])
        ){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'Debe proporcionar la id como int en la url del endpoint, y en el cuerpo, el nombre y la descripción como string, el precio como int o double y la categoria_id como int.'], Response::HTTP_BAD_REQUEST);

        }

        $producto = $em->getRepository(Producto::class)->find($id);

        if (!$producto) {

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Producto no encontrado.'], Response::HTTP_NOT_FOUND);

        }

        $producto_original = $em->getRepository(Producto::class)->findBy(['nombre' => $data['nombre']]);

        if ($producto_original && $producto_original[0]->getId() !== $producto->getId()) {
            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_CONFLICT,'Response' => 'Producto ya existe.'], Response::HTTP_CONFLICT);
        }

        $producto->setNombre($data['nombre']);
        $producto->setDescripcion($data['descripcion']);
        $producto->setPrecio($data['precio']);

        $categoria = $em->getRepository(Categoria::class)->find($data['categoria_id']);

        if (!$categoria) {
            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Categoría no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $producto->setCategoriaId($data['categoria_id']);

        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => 'Producto actualizado correctamente.'], Response::HTTP_OK);

    }

    /**
     * @Route("/producto/{id}", methods={"DELETE"})
     */
    #[Route('/producto/{id}', name: 'eliminarProducto', methods: 'DELETE')]
    public function eliminarProducto($id, EntityManagerInterface $em): JsonResponse
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)){

            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_BAD_REQUEST,'Response' => 'El identificador que se ha pasado no es un número.'], Response::HTTP_BAD_REQUEST);

        }

        $producto = $em->getRepository(Producto::class)->find($id);

        if (!$producto) {
            return $this->json(['Status' => 'Error', 'Code' => Response::HTTP_NOT_FOUND,'Response' => 'Producto no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($producto);
        $em->flush();

        return $this->json(['Status' => 'Success', 'Code' => Response::HTTP_OK,'Response' => 'Producto eliminado correctamente.'], Response::HTTP_OK);
    }
}
