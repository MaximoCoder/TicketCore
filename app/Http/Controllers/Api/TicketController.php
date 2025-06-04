<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Str;
use App\Events\CommentAdded;
use Illuminate\Http\Request;
use App\Models\TicketAttachment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * 
     * Crear un nuevo ticket
     * 
     */

    public function store(Request $request)
    {
        $validatedData = $this->validateTicketRequest($request);

        // Obtener el usuario para sacar el department_id
        $user = User::findOrFail($validatedData['user_id']);

        // Generar el ticket_number
        $ticketNumber = $this->generateTicketNumber(
            $validatedData['category_id'],
            $user->department_id,
            $validatedData['priority_id']
        );

        // Crear el ticket
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'subject' => $validatedData['subject'],
            'description' => $validatedData['description'],
            'user_id' => $validatedData['user_id'],
            'anydesk_id' => $validatedData['anydesk_id'] ?? null,
            'assigned_to' => null,
            'department_id' => $user->department_id,
            'category_id' => $validatedData['category_id'],
            'priority_id' => $validatedData['priority_id'],
            'status_id' => 1, // Status inicial: Abierto
        ]);

        // Procesar los attachments si existen
        if (!empty($validatedData['attachments'])) {
            $this->processAttachments($validatedData['attachments'], $ticket->id);
        }

        return response()->json([
            'status' => 'ok',
            'ticket' => $ticket,
            'message' => 'Ticket creado exitosamente'
        ], 201);
    }

    protected function validateTicketRequest(Request $request)
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'anydesk_id' => 'nullable|string|max:50',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'attachments' => 'nullable|array',
            'attachments.*.filename' => 'required|string|max:255',
            'attachments.*.content' => 'required|string', // Base64
            'attachments.*.mime_type' => 'required|string|max:100',
            'attachments.*.size' => 'required|integer|min:0',
            'attachments.*.uploaded_by' => 'required|exists:users,id',
        ]);
    }

    protected function generateTicketNumber($categoryId, $departmentId, $priorityId)
    {
        $now = Carbon::now();

        return sprintf(
            '%s-%02d-%02d-%d-%04d',
            $now->format('Ymd'),
            $categoryId,
            $departmentId,
            $priorityId,
            rand(0, 9999)
        );
    }

    protected function processAttachments(array $attachments, $ticketId)
    {
        foreach ($attachments as $attachment) {
            // Decodificar el contenido Base64
            $fileContent = base64_decode($attachment['content']);

            // Generar un nombre único para el archivo
            $extension = pathinfo($attachment['filename'], PATHINFO_EXTENSION);
            $fileName = Str::random(40) . '.' . $extension;
            $path = 'tickets/' . $ticketId . '/' . $fileName;

            // Guardar el archivo en el almacenamiento
            Storage::disk('public')->put($path, $fileContent);

            // Crear el registro en la base de datos
            TicketAttachment::create([
                'ticket_id' => $ticketId,
                'comment_id' => null,
                'filename' => $attachment['filename'],
                'path' => $path,
                'mime_type' => $attachment['mime_type'],
                'size' => $attachment['size'],
                'uploaded_by' => $attachment['uploaded_by']
            ]);
        }
    }

    /**
     * 
     *  Obtener todos los tickets paginados de un user
     * 
     */
    public function getUserTickets(Request $request)
    {
        $data = $request->json()->all();
        $userId = $data['user_id'];
        $perPage = $data['pageSize'];
        $page = $data['page'];

        // Iniciar la consulta con relaciones
        $query = Ticket::with([
            'status:id,name',
            'category:id,name',
            'department:id,name',
            'priority:id,name',
            'assignedUser:id,name'
        ])->where('user_id', $userId);

        // Ordenar por fecha de creación descendente (más nuevo al más viejo)
        $query->orderBy('created_at', 'desc');

        // Obtener resultados paginados
        $tickets = $query->paginate($perPage, ['*'], 'page', $page);

        // Mapear los resultados para incluir los nombres de las relaciones
        $mappedTickets = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->name,
                'description' => $ticket->description,
                'is_active' => $ticket->is_active,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'category_id' => $ticket->category_id,
                'category_name' => $ticket->category->name ?? null,
                'department_id' => $ticket->department_id,
                'department_name' => $ticket->department->name ?? null,
                'priority_id' => $ticket->priority_id,
                'priority_name' => $ticket->priority->name ?? null,
                'assigned_to' => $ticket->assigned_to,
                'assigned_user_name' => $ticket->assignedUser->name ?? null,
            ];
        });

        return response()->json([
            'status' => 'ok',
            'tickets' => $mappedTickets,
            'totalCount' => $tickets->total(),
            'page' => $tickets->currentPage(),
            'pageSize' => $tickets->perPage()
        ]);
    }

    /**
     * 
     * Obtener los tickets paginados
     * 
     *  */
    public function getPaginatedTickets(Request $request)
    {
        // Obtener parámetros del cuerpo de la solicitud
        $data = $request->json()->all();

        $perPage = $data['pageSize'];
        $page = $data['page'];
        $searchTerm = $data['searchTerm'] ?? '';
        $statusIds = array_filter($data['statusids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $categoryIds = array_filter($data['category_ids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $departmentIds = array_filter($data['department_ids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $priorityIds = array_filter($data['priority_ids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });

        $sortBy = 'created_at';
        $sortDirection = 'asc';

        // Iniciar la consulta con relaciones
        $query = Ticket::with([
            'status:id,name',
            'category:id,name',
            'department:id,name',
            'priority:id,name',
            'assignedUser:id,name'
        ]);

        // Filtrar por statusids si está presente 
        if (!empty($statusIds)) {
            $query->whereIn('status_id', $statusIds);
        }

        // Filtrar por category_ids si está presente
        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Filtrar por department_ids si está presente
        if (!empty($departmentIds)) {
            $query->whereIn('department_id', $departmentIds);
        }

        // Filtrar por priority_ids si está presente
        if (!empty($priorityIds)) {
            $query->whereIn('priority_id', $priorityIds);
        }

        // Aplicar filtro de búsqueda si existe
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('ticket_number', 'like', "%{$searchTerm}%")
                    ->orWhere('subject', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar orden
        $query->orderBy($sortBy, $sortDirection);
        // Condicion deleted == 0
        // $query->where('deleted', 0);

        // Obtener resultados paginados
        $tickets = $query->paginate($perPage, ['*'], 'page', $page);

        // Mapear los resultados para incluir los nombres de las relaciones
        $mappedTickets = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->name,
                'description' => $ticket->description,
                'is_active' => $ticket->is_active,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'category_id' => $ticket->category_id,
                'category_name' => $ticket->category->name ?? null,
                'department_id' => $ticket->department_id,
                'department_name' => $ticket->department->name ?? null,
                'priority_id' => $ticket->priority_id,
                'priority_name' => $ticket->priority->name ?? null,
                'assigned_to' => $ticket->assigned_to,
                'assigned_user_name' => $ticket->assignedUser->name ?? null,
            ];
        });

        return response()->json([
            'status' => 'ok',
            'tickets' => $mappedTickets,
            'totalCount' => $tickets->total(),
            'page' => $tickets->currentPage(),
            'pageSize' => $tickets->perPage()
        ]);
    }

    /**
     * Obtener un ticket completo por su ID con toda la información relacionada
     * 
     * @param int $id ID del ticket
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTicketById(Request $request)
    {

        $data = $request->json()->all();
        $id = $data['id'];

        // Obtener el ticket con todas las relaciones principales
        $ticket = Ticket::with([
            'status:id,name',
            'category:id,name',
            'department:id,name',
            'priority:id,name',
            'user:id,name,email', // Usuario que creó el ticket
            'assignedUser:id,name,email', // Usuario asignado
            'attachments:id,ticket_id,filename,path,mime_type,size,uploaded_by,created_at',
            'comments.user:id,name,email' // Comentarios con información del usuario
        ])->findOrFail($id);

        // Formatear la respuesta
        $response = [
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'status' => $ticket->status->name,
                'status_id' => $ticket->status_id,
                'is_active' => $ticket->is_active,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'category' => $ticket->category->name,
                'category_id' => $ticket->category_id,
                'department' => $ticket->department->name,
                'department_id' => $ticket->department_id,
                'priority' => $ticket->priority->name,
                'priority_id' => $ticket->priority_id,
                'anydesk_id' => $ticket->anydesk_id,
                'user' => $ticket->user->name,
                'user_id' => $ticket->user_id,
                'user_email' => $ticket->user->email,
                'assigned_to' => $ticket->assigned_to,
                'assigned_user_name' => $ticket->assignedUser->name ?? null,
                'assigned_user_email' => $ticket->assignedUser->email ?? null,
            ],
            'attachments' => $ticket->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'path' => $attachment->path,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'uploaded_by' => $attachment->uploaded_by,
                    'created_at' => $attachment->created_at
                ];
            }),
            // 'comments' => $ticket->comments->map(function ($comment) {
            //     return [
            //         'id' => $comment->id,
            //         'comment' => $comment->comment,
            //         'is_private' => $comment->is_private,
            //         'created_at' => $comment->created_at,
            //         'updated_at' => $comment->updated_at,
            //         'user' => [
            //             'id' => $comment->user->id,
            //             'name' => $comment->user->name,
            //             'email' => $comment->user->email
            //         ]
            //     ];
            // })
        ];

        return response()->json([
            'status' => 'ok',
            'data' => $response
        ]);
    }

    /**
     * 
     * Method for obtain all comments of a ticket by ticket id
     */

    public function getTicketComments(Request $request)
    {
        $data = $request->json()->all();
        $id = $data['id'];

        $ticket = Ticket::with([
            'comments.user:id,name,email'
        ])->findOrFail($id);

        return response()->json([
            'status' => 'ok',
            'data' => $ticket->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'is_private' => $comment->is_private,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email
                    ]
                ];
            })
        ]);
    }

    // Crear un comment en un ticket
    public function storeComment(Request $request)
    {
        $data = $request->json()->all();
        $id = $data['ticket_id'];
        $comment = $data['comment'];
        $user_id = $data['user_id'];

        $ticket = Ticket::findOrFail($id);
        $newComment = $ticket->comments()->create([
            'ticket_id' => $id,
            'comment' => $comment,
            'user_id' => $user_id
        ]);

        // Cargar la relación user
        $newComment->load('user:id,name,email');

        // Disparar el evento
        broadcast(new CommentAdded($newComment, $data['ticket_id']));

        return response()->json([
            'status' => 'ok',
            'data' => $newComment,
            'message' => 'Comment created successfully'
        ]);
    }

    /**
     *  Method for assign one ticket or more to a user
     * 
     */
    public function assignTicket(Request $request)
    {
        $data = $request->json()->all();

        $assignedTo = $data['user_id'];
        $ticketIds = $data['ticket_ids'];

        Ticket::whereIn('id', $ticketIds)->update(['assigned_to' => $assignedTo, 'status_id' => 2]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Tickets assigned successfully'
        ]);
    }

    /**
     * 
     *  Method for unassign one ticket or more from a user 
     *
     */
    public function unassignTicket(Request $request)
    {
        $data = $request->json()->all();

        $ticketIds = $data['ticket_ids'];

        Ticket::whereIn('id', $ticketIds)->update(['assigned_to' => null, 'status_id' => 1]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Tickets unassigned successfully'
        ]);
    }

    /** 
     * Close ticket
     */

    public function closeTicket(Request $request)
    {
        $data = $request->json()->all();
        $id = $data['id'];
        $ticket = Ticket::findOrFail($id);
        $ticket->status_id = 6;
        $ticket->save();
        return response()->json([
            'status' => 'ok',
            'message' => 'Ticket closed successfully'
        ]);
    }
}
