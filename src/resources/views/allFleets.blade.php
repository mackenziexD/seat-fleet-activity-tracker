@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::allFleets'])

@section('title', 'All Fleets')
@section('page_header', 'All Fleets Overview')

@section('full')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">All Fleets</h3>
            </div>
            <div class="card-body">

                <table class="table" id="fleets-table">
                    <thead>
                        <tr>
                            <th>Fleet Name</th>
                            <th>Fleet Type</th>
                            <th>Fleet Commander</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($fleets->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center">No fleets found.</td>
                            </tr>
                        @else
                            @foreach($fleets as $fleet)
                                <tr>
                                    <td>{{ $fleet->fleetName }} @if($fleet->fleetActive) <span class="badge bg-primary ml-2">Active</span> @endif</td>
                                    <td>{{ $fleet->fleetType }}</td>
                                    <td>{{ $fleet->fleet_commander ? $fleet->fleet_commander->name : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('seat-fleet-activity-tracker::fleet', $fleet->fleetID) }}">
                                          <button class="btn btn-primary">View</button>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@stop

@push('javascript')
<script>
  $(document).ready(function() {
    var table = $('#fleets-table').DataTable();
  });
</script>
@endpush