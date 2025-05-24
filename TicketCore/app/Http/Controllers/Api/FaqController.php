<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{

    /**
     * Get paginated faqs.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginatedFaqs(Request $request)
    {
        // Obtener parámetros del cuerpo de la solicitud
        $data = $request->json()->all();

        $perPage = $data['pageSize'];
        $page = $data['page'];
        $searchTerm = $data['searchTerm'] ?? '';
        $statusIds = array_filter($data['statusids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $sortBy = 'order';
        $sortDirection = 'asc';
        // Iniciar la consulta
        $query = Faq::with('category');

        // Filtrar por statusids si está presente 
        if (!empty($statusIds)) {
            $query->whereIn('is_published', $statusIds);
        }

        // Aplicar filtro de búsqueda si existe
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', "%{$searchTerm}%")
                    ->orWhere('summary', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar orden
        $query->orderBy($sortBy, $sortDirection);
        // Condicion deleted == 0
        $query->where('deleted', 0);

        // Obtener resultados paginados
        $faqs = $query->paginate($perPage, ['*'], 'page', $page);

        // Mapear faqs para incluir el nombre de la categoria
        $faqsData = $faqs->getCollection()->map(function ($faq) {
            return [
                'id' => $faq->id,
                'question' => $faq->question,
                'summary' => $faq->summary,
                'category' => $faq->category->name ?? null,
                'order' => $faq->order,
                'is_published' => $faq->is_published
            ];
        })->values();

        return response()->json([
            'status' => 'ok',
            'faqs' => $faqsData,
            'totalCount' => $faqs->total(),
            'page' => $faqs->currentPage(),
            'pageSize' => $faqs->perPage()
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function createFaq(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'question' => 'required',
            'summary' => '',
            'category_id' => 'required',
            'is_published' => 'required',
            'order' => 'required',
        ]);
        // Creamos un nuevo departamento
        $faq = Faq::create([
            'question' => $data['question'],
            'summary' => $data['summary'],
            'category_id' => $data['category_id'],
            'is_published' => $data['is_published'],
            'order' => $data['order'],
        ]);

        return response()->json(
            [
                'faq' => $faq,
                'status' => 'ok'
            ]
        );
    }

    /*
    * Method to obtain a faq by id
    */
    public function getFaqById(Request $request)
    {
        $faq = Faq::find($request->id);
        return response()->json(
            [
                'faq' => $faq,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateFaqById(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'question' => 'required',
            'summary' => '',
            'category_id' => 'required',
            'is_published' => 'required',
            'order' => 'required',
        ]);
        // Actualizar departamento
        $faq = Faq::find($request->id);
        $faq->update([
            'question' => $data['question'],
            'summary' => $data['summary'],
            'category_id' => $data['category_id'],
            'is_published' => $data['is_published'],
            'order' => $data['order'],
        ]);
        return response()->json(
            [
                'faq' => $faq,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteFaqById(Request $request)
    {
        $ids = $request->ids;

        // Convertir a array si viene como string separado por comas
        if (is_string($ids)) {
            $ids = explode(',', $ids);
            $ids = array_map('trim', $ids);
        }
        // En lugar de eliminar pasamos el deleted a 1 para marcarlo como borrado
        faq::whereIn('id', $ids)->update(['deleted' => 1]);

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
