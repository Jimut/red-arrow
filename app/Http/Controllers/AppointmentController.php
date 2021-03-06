<?php

namespace App\Http\Controllers;

use App\Donor;
use App\Appointment;
use App\Events\AppointmentCreated;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected $appointmentService;

    private $blockFor = 90;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->middleware('auth');

        $this->appointmentService = $appointmentService;
    }

    public function store(Request $request)
    {
        if ($request->user()->donor) abort(403);
        
        $lastReview = Donor::find($request->donor_id)->appointments()->orderBy('updated_at', 'desc')->where('status', 'COMPLETED')->first();
        if ($lastReview && strtotime($lastReview->updated_at) + 60 * 60 * 24 * $this->blockFor > time()) {
            return response()->json([
                'success' => 0,
                'error' => 'Blocked' 
            ]);
        }

        $appointment = $this->appointmentService->createAppointment($request->user()->hospital->id, $request->donor_id);

        event(new AppointmentCreated($appointment));

        return response()->json([
            'success' => 1
        ]);
    }

    public function sent(Request $request)
    {
        if ($request->user()->donor) abort(403);

        $sent = $this->appointmentService->getVirginAppointments($request->user()->hospital);

        return view('appointment.hospital.sent', [
            'sent' => $sent
        ]);
    }

    public function received(Request $request)
    {
        if ($request->user()->hospital) abort(403);

        $received = $this->appointmentService->getVirginAppointments($request->user()->donor);

        return view('appointment.donor.received', [
            'received' => $received
        ]);
    }

    public function accepted(Request $request)
    {
        $appointee = $request->user()->hospital ?: $request->user()->donor;

        $accepted = $this->appointmentService->getAcceptedAppointments($appointee);

        if (get_class($appointee) === 'App\Hospital') {
            $view = 'appointment.hospital.accepted';
        } else {
            $view = 'appointment.donor.accepted';
        }

        return view($view, [
            'accepted' => $accepted
        ]);
    }

    public function approved(Request $request)
    {
        if ($request->user()->donor) abort(403);

        $approved = $this->appointmentService->getCompletedAppointments($request->user()->hospital);

        return view('appointment.hospital.approved', [
            'approved' => $approved
        ]);
    }

    public function accept(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if ($request->user()->hospital) abort(403);

        if ($request->user()->donor->id !== $appointment->donor_id) abort(403);

        $this->appointmentService->acceptAppointment($appointment);

        return redirect()->route('appointment.accepted');
    }

    public function reject(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if ($request->user()->hospital) abort(403);

        if ($request->user()->donor->id !== $appointment->donor_id) abort(403);

        $this->appointmentService->rejectAppointment($appointment);

        return redirect()->route('appointment.received');
    }

    public function approve(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if ($request->user()->donor) abort(403);

        if ($request->user()->hospital->id !== $appointment->hospital_id) abort(403);

        $this->appointmentService->approveAppointment($appointment);

        return redirect()->route('appointment.accepted');
    }
}
