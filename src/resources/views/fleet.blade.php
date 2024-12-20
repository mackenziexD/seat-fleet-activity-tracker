@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::allFleets'])

@section('title', 'SeAT-FAT')
@section('page_header', 'Fleet - '. $fleet->fleetName )

@section('full')
<div class="row mb-3">
  <div class="col-md-6">
    <p>FC: {{ $fleet->fleet_commander ? $fleet->fleet_commander->name : 'N/A' }}</p>
  </div>
  <div class="col-md-6 text-end text-right">
    <span data-toggle="tooltip" title="" data-original-title="{{$fleet->created_at}}">
      FAT Created: {{ $fleet->created_at->diffForHumans() }}
    </span>
  </div>
</div>
<div class="row">
  <div class="col-md-3">
    <div class="card">
      <div class="card-header">
        Ship Type Overview
      </div>
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Ship Type</th>
              <th>Numbers</th>
            </tr>
          </thead>
          <tbody>
            @foreach($shipTypeCounts as $typeName => $count)
              <tr>
                <td>{{ $typeName }}</td>
                <td>{{ $count }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        Characters In Fleet
      </div>
      <div class="card-body">

        <table class="table" id="fleets-table">
          <thead>
              <tr>
                  <th>Character</th>
                  <th>Location</th>
                  <th>Ship Type</th>
                  <th></th>
              </tr>
          </thead>
          <tbody>
            @foreach($members as $member)
            <tr>
              <td>
                <img src="//images.evetech.net/characters/{{ $member->character_id }}/portrait?size=64" class="img-circle eve-icon small-icon">
                {{$member->character->name ?? 'Unknown Character'}}
              </td>
              <td>{{$member->solar_system->name}}</td>
              <td>
                <img src="//images.evetech.net/types/{{ $member->ship->typeID }}/icon?size=64" class="img-circle eve-icon small-icon">
                {{$member->ship->typeName}}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>
@stop