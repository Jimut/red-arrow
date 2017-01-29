@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Notifications</div>

                    <div class="panel-body">
                        <p>These hospitals are requesting your blood type right now.</p>
                    </div>

                    <ul class="list-group">
                        @foreach ($received as $appointment)
                            <li class="list-group-item">
                                <div class="row">
                                    <p class="col-md-6" style="font-size: 18px;">{{ $appointment->hospital->name }}</p>
                                    <div class="col-md-6">
                                        <a href="https://www.google.co.in/maps?saddr={{ Auth::user()->donor->map_lat }},{{ Auth::user()->donor->map_lng }}&daddr={{ $appointment->hospital->map_lat }},{{ $appointment->hospital->map_lng }}" target="_blank" class="btn btn-primary pull-right space-left">Show Directions</a>
                                        <a href="{{ route('appointment.accept', $appointment) }}" class="btn btn-primary pull-right space-left">Accept Appointment</a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
