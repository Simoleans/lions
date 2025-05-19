<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;
use Carbon\Carbon;



class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $date = $request->input('date');

        $query = Attendance::query()
            ->with(['user.subscriptions', 'subscription.plan'])
            ->orderBy('attended_at', 'desc');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->whereDate('attended_at', Carbon::parse($date)->format('Y-m-d'));
        }

        $attendances = $query->paginate(20);

        // Obtener estadísticas de los últimos 5 días
        $lastFiveDays = [];
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Attendance::whereDate('attended_at', $date->format('Y-m-d'))->count();
            $lastFiveDays[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
                'label' => $date->format('d/m')
            ];
        }

        // Estadísticas de asistencias
        $today = Carbon::now()->format('Y-m-d');
        $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');

        $stats = [
            'today' => Attendance::whereDate('attended_at', $today)->count(),
            'week' => Attendance::whereDate('attended_at', '>=', $weekStart)->count(),
            'month' => Attendance::whereDate('attended_at', '>=', $monthStart)->count(),
        ];

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
            'filters' => [
                'search' => $search,
                'date' => $date,
            ],
            'stats' => [
                'lastFiveDays' => $lastFiveDays,
                'attendanceStats' => $stats
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Attendance/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
        ]);

        $user = User::where('id_number', $request->code)->first();
        $attendance_count = Attendance::where('user_id', $user->id)->count();

        if ($attendance_count >= $user->subscriptions->first()->remaining_classes) {
            return response()->json([
                'error' => true,
                'message' => 'Usuario no tiene clases disponibles',
                'user' => $user,
            ]);
        }

        //dd($user,$user->subscriptions->first()->id);

        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        Attendance::create([
            'user_id' => $user->id,
            'subscription_id' => $user->subscriptions->id,
            'date' => now(),
        ]);

        return response()->json([
            'error' => false,
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
