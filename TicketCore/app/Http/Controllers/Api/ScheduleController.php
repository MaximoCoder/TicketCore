<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ScheduleController extends Controller
{
    public function getSchedule()
    {
        $json = Storage::get('support_schedule.json');
        $schedules = json_decode($json, true) ?? [];

        // Si no hay horarios, devolver vacío
        if (empty($schedules)) {
            return response()->json([
                'status' => 'ok',
                'schedules' => []
            ]);
        }

        // Obtener todos los IDs de usuarios únicos de todos los horarios
        $userIds = [];
        foreach ($schedules as $schedule) {
            if (isset($schedule['ids']) && is_array($schedule['ids'])) {
                $userIds = array_merge($userIds, $schedule['ids']);
            }
        }
        $userIds = array_unique($userIds);

        // Obtener los nombres de los usuarios
        $users = [];
        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)
                ->select('id', 'name') // Ajusta según los campos que necesites
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        // Mapear los horarios incluyendo los nombres
        $mappedSchedules = array_map(function ($schedule) use ($users) {
            $mappedIds = [];
            if (isset($schedule['ids']) && is_array($schedule['ids'])) {
                foreach ($schedule['ids'] as $id) {
                    $mappedIds[] = [
                        'id' => $id,
                        'name' => $users[$id]['name'] ?? 'Usuario desconocido'
                    ];
                }
            }

            return [
                'id' => $schedule['id'] ?? null,
                'date' => $schedule['date'] ?? null,
                'ids' => $mappedIds,
                'status' => $schedule['status'] ?? 'inactive'
            ];
        }, $schedules);

        return response()->json([
            'status' => 'ok',
            'schedules' => $mappedSchedules
        ]);
    }


    // Guardar o actualizar una fecha con nuevos IDs
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'id' => 'sometimes|string', // Cambiado a string para uniqid/UUID
            'date' => 'required|date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $json = Storage::get('support_schedule.json');
        $data = json_decode($json, true) ?? [];

        // Generar datos del horario (con ID nuevo si no existe)
        $scheduleData = [
            'id' => $request->has('id') ? $request->id : uniqid(),
            'date' => $request->date,
            'ids' => $request->user_ids,
            'status' => $request->get('status', 'inactive')
        ];

        // Buscar índice por ID (si está presente)
        $existingIndex = $request->has('id')
            ? array_search($request->id, array_column($data, 'id'))
            : false;

        // Si el estado es "active", desactivar todos los demás
        if ($scheduleData['status'] === 'active') {
            $data = array_map(function ($entry) {
                $entry['status'] = 'inactive';
                return $entry;
            }, $data);
        }

        // Actualizar o agregar
        if ($existingIndex !== false) {
            $data[$existingIndex] = $scheduleData;
        } else {
            $data[] = $scheduleData;
        }

        Storage::put('support_schedule.json', json_encode($data, JSON_PRETTY_PRINT));
        return response()->json(['status' => 'ok', 'message' => 'Horario actualizado.']);
    }

    // Eliminar una fecha completa del archivo
    public function deleteDate(Request $request)
    {
        $dates = $request->input('dates');

        if (empty($dates)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se proporcionaron fechas para eliminar.'
            ], 400);
        }

        $json = Storage::get('support_schedule.json');
        $data = json_decode($json, true) ?? [];

        $initialCount = count($data);
        $filteredData = array_filter($data, function ($entry) use ($dates) {
            return !in_array($entry['date'], $dates);
        });

        if (count($filteredData) !== $initialCount) {
            Storage::put('support_schedule.json', json_encode(array_values($filteredData), JSON_PRETTY_PRINT));
            return response()->json([
                'status' => 'ok',
                'message' => 'Fechas eliminadas correctamente.',
                'deleted_count' => $initialCount - count($filteredData)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No se encontraron las fechas especificadas.'
        ], 404);
    }

    public function unassignUserFromDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // Cargar datos actuales
        $json = Storage::get('support_schedule.json');
        $data = json_decode($json, true) ?? [];

        // Buscar la entrada para la fecha
        $entryIndex = array_search($request->date, array_column($data, 'date'));

        if ($entryIndex !== false) {
            // Eliminar el ID del arreglo si existe
            $data[$entryIndex]['ids'] = array_filter($data[$entryIndex]['ids'], function ($id) use ($request) {
                return $id !== $request->user_id;
            });

            // Si después de eliminar no quedan usuarios, eliminar la fecha completa
            if (empty($data[$entryIndex]['ids'])) {
                unset($data[$entryIndex]);
                $data = array_values($data); // Reindexar el array
            }

            // Guardar cambios
            Storage::put('support_schedule.json', json_encode($data, JSON_PRETTY_PRINT));

            return response()->json([
                'status' => 'ok',
                'message' => 'Usuario desasignado correctamente.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'La fecha no existe o no tiene usuarios asignados.'
        ], 404);
    }

    // Método adicional para activar una fecha específica
    public function activateDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $json = Storage::get('support_schedule.json');
        $data = json_decode($json, true) ?? [];

        // Primero desactivar todas las fechas
        $data = array_map(function ($entry) {
            $entry['status'] = 'inactive';
            return $entry;
        }, $data);

        // Buscar y activar la fecha solicitada
        $entryIndex = array_search($request->date, array_column($data, 'date'));

        if ($entryIndex !== false) {
            $data[$entryIndex]['status'] = 'active';
            Storage::put('support_schedule.json', json_encode($data, JSON_PRETTY_PRINT));

            return response()->json([
                'status' => 'ok',
                'message' => 'Fecha activada correctamente.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Fecha no encontrada.'
        ], 404);
    }
}
