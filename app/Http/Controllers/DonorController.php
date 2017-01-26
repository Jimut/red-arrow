<?php

namespace App\Http\Controllers;

use Auth;
use App\Donor;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    protected $imageStorageService;

    /**
    * Validation rules for hospital model
    *
    * @var Array
    */
    public $rules = [
        'name' => 'required',
        'dob' => 'required',
        'address' => 'required',
        'map_lat' => 'required',
        'map_lng' => 'required',
        'contact_no' => 'required',
        'blood_type' => 'required',
    ];

    public function __construct(ImageStorageService $imageStorageService)
    {
        $this->middleware('auth', ['except' => [
            'index', 'show'
        ]]);

        $this->imageStorageService = $imageStorageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $donors = Donor::all();

        if ($request->ajax()) {
            return response()->json($donors->toArray());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->hospital)
            abort(403);

        return view('donor.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->hospital)
            abort(403);

        $this->validate($request, $this->rules);

        $donor = new Donor;
        
        if ($request->hasFile('avatar')) {
            $donor->avatar = $this->imageStorageService->storeAvatar($request->file('avatar'));
        }

        $donor->name = $request->name;
        $donor->dob = $request->dob;
        $donor->address = $request->address;
        $donor->map_lat = $request->map_lat;
        $donor->map_lng = $request->map_lng;
        $donor->contact_no = $request->contact_no;
        $donor->blood_type = $request->blood_type;
        $donor->health_issues = $request->health_issues;
        $donor->user_id = $request->user()->id;
        $donor->save();

        return redirect()->route('donor.show',[$donor]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $donor = Donor::find($id);

        return view('donor.show',[
            'donor' => $donor,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $donor = Donor::find($id);

        return view('donor.edit', [
            'donor' => $donor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,$this->rules);

        $donor = Donor::find($id);

        if ($request->hasFile('avatar')) {
            $donor->avatar = $this->imageStorageService->storeAvatar($request->file('avatar'));
        }

        $donor->name = $request->name;
        $donor->dob = $request->dob;
        $donor->address = $request->address;
        $donor->map_lat = $request->map_lat;
        $donor->map_lng = $request->map_lng;
        $donor->contact_no = $request->contact_no;
        $donor->blood_type = $request->blood_type;
        $donor->health_issues = $request->health_issues;
        $donor->save();

        return redirect()->route('donor.show', [$donor]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Find donors
     *
     * @return \Illuminate\Http\Response
     */
    public function find() {
        return view('donor.find');
    }

    /**
     * Show notifications of current user
     *
     * @return \Illuminate\Http\Response
     */
    public function showNotification()
    {
        $user = Auth::user();

        if (!$user->donor)
            abort(403);

        $appointments = $user->donor->appointments;
        $newNotifications = [];
        foreach ($appointments as $appointment) {
            if ($appointment->status === 'SENT') {
                $newNotifications[] = $appointment;
            }
        }

        return view('donor.notification', [
            'newNotifications' => $newNotifications,
            'donor' => $user->donor
        ]);
    }
}
