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
                            <th>Actions</th>
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
                                    <td>{{ $fleet->fleetName }}</td>
                                    <td>{{ $fleet->fleetType }}</td>
                                    <td>{{ $fleet->fleet_commander ? $fleet->fleet_commander->name : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('seat-fleet-activity-tracker::fleet', $fleet->id) }}">
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
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
<script>
  // once the datatable is drawn if any of the 3rd column values are less than 7 days, highlight the row
  $(document).ready(function() {
    var table = $('#fleets-table').DataTable();
  });
</script>
@endpush