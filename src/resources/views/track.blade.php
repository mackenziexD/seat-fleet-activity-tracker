@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::track'])

@section('title', 'SeAT-FAT')
@section('page_header', 'FAT Track Fleet')

@section('full')
<div class="row">
    <div class="col-md-12">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Start Tracking A Fleet</h3>
        </div>
        <div class="card-body">

        <p>This will start tracking your fleet automatically and add pilots to it as they join until the fleet is either closed ingame (meaning you leave fleet) or you stop tracking here.</p>

        <form method="post" action="{{ route('seat-fleet-activity-tracker::trackPostRequest') }}">
          @csrf
          <div class="form-group">
            <label for="fleet_name">Fleet Name*</label>
            <input type="text" name="fleet_name" id="fleet_name" class="form-control" placeholder="Enter Fleet Name">
          </div>
          <div class="form-group">
            <label for="fleet_id">Fleet ID*</label>
            <input type="text" name="fleet_id" id="fleet_id" class="form-control" placeholder="Enter Fleet ID">
          </div>
          <div class="form-group">
            <label for="fleet_boss">Fleet Boss*</label>
            <select name="fleet_boss" id="fleet_boss" class="form-control select2" style="width: 100%;">
              <option value="" selected>Select Fleet Type</option>
              @foreach($characters as $character)
                <option value="{{$character->character_id}}">{{$character->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="fleet_type">Fleet Type</label>
            <select name="fleet_type" id="fleet_type" class="form-control">
              <option value="" selected>Select Fleet Type</option>
              <option value="1">Home Def</option>
            </select>
          </div>

          <input type="submit" class="btn btn-primary" value="Track Fleet">
        </form>

        </div>
      </div>

    </div>
</div>

@push('javascript')
<script>
  $("#fleet_boss").select2();
</script>

<script>
function parseFleetURL(fleet_str)
{
  var fleet_id = null;
  if(fleet_str.substr(0, 4) == "http")
  {
    var fleets_idx = fleet_str.indexOf('/fleets/');
    if(fleets_idx == -1) {
      displayStatus("Invalid Fleet URL");
      return null;
    }
    fleet_id = fleet_str.substring(fleets_idx + 8, fleet_str.lastIndexOf('/'));
  } else if(isNaN(fleet_str)) {
    displayStatus("Invalid Fleet ID");
    return null;
  } else{
    fleet_id = fleet_str;
  }
  return fleet_id;
}

$('input#fleet_id').on('paste', function(e){
  var pasteData = e.originalEvent.clipboardData.getData('text');
  if(pasteData != null) {
      $('input#fleet_id').val(parseFleetURL(pasteData));
      return false;
  }
  //console.log(pasteData);
});
</script>
@endpush

@stop