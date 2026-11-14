<?php

namespace Laravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Repositories\ProductCacheRepository;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductCacheRepository $products) {}

    public function index(Request $request): JsonResponse
    {
        // Canonicalize params to avoid duplicate cache keys
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(10, (int) $request->query('per_page', 20)));

        $result = $this->products->paginate($page, $perPage);

        return response()->json([
            'source' => $result->source,
            'version' => $result->version,
            'data' => $result->data,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->products->find($id);

        if ($result->data === null) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['source' => $result->source, 'data' => $result->data]);
    }
}
