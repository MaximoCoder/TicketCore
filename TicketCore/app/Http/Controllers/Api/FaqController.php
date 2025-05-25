<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqStep;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    /** 
     *  METHODS FOR STEPS 
     * **/

    // Get paginated steps.
    public function getPaginatedSteps(Request $request)
    {
        // Obtener parámetros del cuerpo de la solicitud
        $data = $request->json()->all();

        $perPage = $data['pageSize'];
        $page = $data['page'];
        $sortBy = 'step_order';
        $sortDirection = 'asc';

        $query = FaqStep::query();
        // donde deleted == 0
        $query->where('deleted', 0);
        $steps = $query->orderBy($sortBy, $sortDirection)->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'ok',
            'steps' => $steps->items(),
            'totalCount' => $steps->total(),
            'page' => $steps->currentPage(),
            'pageSize' => $steps->perPage()
        ]);
    }


    // Store a newly created resource in storage.
    public function createStepFaq(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'faq_id' => 'required', // faq_id
            'title' => 'required',
            'content' => '',
            'image_path' => 'required',
            'step_order' => 'required',
        ]);
        // Creamos un nuevo departamento
        $stepFaq = FaqStep::create([
            'faq_id' => $data['faq_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => '',
            'step_order' => $data['step_order'],
        ]);

        // Procesar la imagen en base64
        if (Str::startsWith($data['image_path'], 'data:image')) {
            preg_match("/^data:image\/(.*?);base64,/", $data['image_path'], $matches);

            $extension = $matches[1]; // por ejemplo: jpeg, png
            $base64Data = str_replace($matches[0], '', $data['image_path']);
            $base64Data = str_replace(' ', '+', $base64Data);

            // Decodificar y guardar
            $imageData = base64_decode($base64Data);
            $filename = uniqid() . '.' . $extension;
            $path = 'faq_steps/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            // Actualizar el path en la base de datos
            $stepFaq->image_path = $path;
            $stepFaq->save();
        }

        return response()->json(
            [
                'stepFaq' => $stepFaq,
                'status' => 'ok'
            ]
        );
    }

    // Update the specified resource in storage.
    public function updateStepFaqById(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'id' => 'required',
            'faq_id' => 'required', // faq_id
            'title' => 'required',
            'content' => '',
            'image_path' => 'required',
            'step_order' => 'required',
        ]);

        // Encontrar el paso FAQ a actualizar
        $stepFaq = FaqStep::find($request->id);

        // Procesar la imagen en base64 si es una nueva imagen
        $imagePath = $data['image_path'];
        if (Str::startsWith($data['image_path'], 'data:image')) {
            preg_match("/^data:image\/(.*?);base64,/", $data['image_path'], $matches);

            $extension = $matches[1]; // por ejemplo: jpeg, png
            $base64Data = str_replace($matches[0], '', $data['image_path']);
            $base64Data = str_replace(' ', '+', $base64Data);

            // Decodificar y guardar
            $imageData = base64_decode($base64Data);
            $filename = uniqid() . '.' . $extension;
            $path = 'faq_steps/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            // Eliminar la imagen anterior si existe
            if ($stepFaq->image_path && Storage::disk('public')->exists($stepFaq->image_path)) {
                Storage::disk('public')->delete($stepFaq->image_path);
            }

            $imagePath = $path;
        }

        // Actualizar faq step
        $stepFaq->update([
            'faq_id' => $data['faq_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'image_path' => $imagePath,
            'step_order' => $data['step_order'],
        ]);

        return response()->json(
            [
                'stepFaq' => $stepFaq,
                'status' => 'ok'
            ]
        );
    }

    // Delete the specified resource from storage.
    public function deleteStepFaqById(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'id' => 'required',
        ]);
        // Eliminar faq step
        $stepFaq = FaqStep::find($data['id']);
        // Actualizar deleted
        $stepFaq->update([
            'deleted' => 1
        ]);
        return response()->json(
            [
                'status' => 'ok'
            ]
        );
    }

    // Obtain all step of one faq by id
    public function getAllStepFaqByFaqId(Request $request)
    {
        // Obtener los pasos del FAQ
        $stepFaqs = FaqStep::where('faq_id', $request->faq_id)->get();

        // Obtener la información del FAQ con la relación de categoría
        $faq = Faq::with('category')->find($request->faq_id);

        // Mapear el FAQ para incluir el nombre de la categoría
        $mappedFaq = [
            'id' => $faq->id,
            'question' => $faq->question,
            'summary' => $faq->summary,
            'category_id' => $faq->category_id,
            'category' => $faq->category->name ?? null,
            'order' => $faq->order,
            'is_published' => $faq->is_published,
            'created_at' => $faq->created_at,
            'updated_at' => $faq->updated_at
        ];

        return response()->json([
            'faq' => $mappedFaq,       // Información del FAQ mapeada
            'stepFaqs' => $stepFaqs,   // Pasos asociados
            'status' => 'ok'
        ]);
    }
}
