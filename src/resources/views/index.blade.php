@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::index'])

@section('title', 'SeAT-FAT')
@section('page_header', 'FAT Dashboard')

@section('full')
<div class="row">
    <div class="col-md-12">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Recent Activity</h3>
        </div>
        <div class="card-body">

        <table class="table table-bordered">
          <thead>
              <tr>
                  <th>Fleet Name</th>
                  <th>Fleet Type</th>
                  <th>Ship</th>
                  <th>Eve Time</th>
              </tr>
          </thead>
        </table>

        </div>
      </div>

    </div>
</div>
@stop