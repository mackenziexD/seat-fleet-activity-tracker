@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::index'])

@section('title', 'SeAT-FAT')
@section('page_header', 'FAT Dashboard')

@section('full')
<div class="row">
    <div class="col-md-6">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Recent Activity</h3>
        </div>
        <div class="card-body">

        <table class="table">
          <thead>
              <tr>
                  <th>Fleet Name</th>
                  <th>Fleet Type</th>
                  <th>Ship</th>
                  <th>Eve Time</th>
              </tr>
          </thead>
          <tbody>
            @foreach($fleets as $fleet)
            <tr>
              <td>
                <img src="//images.evetech.net/characters/{{ $fleet->character_id }}/portrait?size=64" class="img-circle eve-icon small-icon">
                {{$fleet->character->name}}
              </td>
              <td>{{$fleet->fleet->fleetType}}</td>
              <td>
                <img src="//images.evetech.net/types/{{ $fleet->ship->typeID }}/icon?size=64" class="img-circle eve-icon small-icon">
                {{$fleet->ship->typeName}}
              </td>
              <td>
                {{ $fleet->created_at }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        </div>
      </div>

    </div>
    <div class="col-md-6">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Total Fats</h3>
        </div>
        <div class="card-body">

        <table class="table">
          <thead>
              <tr>
                  <th>Character Name</th>
                  <th>Total FATs</th>
              </tr>
          </thead>
          <tbody>
            @foreach($topCharacterStats as $character)
            <tr>
              <td>
                <img src="//images.evetech.net/characters/{{ $character['id'] }}/portrait?size=64" class="img-circle eve-icon small-icon">
                {{$character['name']}}
              </td>
              <td>
                {{ $character['fats_count'] }}
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