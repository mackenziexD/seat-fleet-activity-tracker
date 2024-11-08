@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::settings'])

@section('title', 'SeAT-FAT')
@section('page_header', 'FAT Settings')

@section('full')
<div class="row">
    <div class="col-md-4">
      <div class="card card-default">
        <div class="card-header">
          <h3 class="card-title">Tracking Corporations</h3>
        </div>
        <div class="card-body">
          <form role="form" action="{{ route('seat-fleet-activity-tracker::postAddTrackedCorp') }}"  method="post">
            @csrf
            <div class="form-group">
                <label for="corporations">{{ trans('web::seat.available_corporations') }}</label>
                <select name="corporations[]" id="available_corporations" style="width: 100%" multiple>

                    @foreach($all_corporations as $corporation)
                        <option value="{{ $corporation->corporation_id }}">
                            {{ $corporation->name }}
                        </option>
                    @endforeach

                </select>
            </div>
            <button type="submit" class="btn btn-success btn-block">
              Add Corps
            </button>
          </form>
          @if($tracked_corps->count() > 0)

          <table class="table table-hover table-condensed">
            <tbody>

            <tr>
                <th colspan="3" class="text-center">Current Tracked Corps</th>
            </tr>

            @foreach($tracked_corps as $corp)

              <tr>
                  <td>
                    @include('web::partials.corporation', ['corporation' => $corp->corporation])
                  </td>
                  <td>
                      <a href="{{ route('seat-fleet-activity-tracker::deletedTrackedCorp', ['id' => $corp->corporation_id]) }}"
                        type="button" class="btn btn-danger btn-xs pull-right">
                          {{ trans('web::seat.remove') }}
                      </a>
                  </td>
              </tr>

              @endforeach
            </tbody>
          </table>
          
          @else

          <p>
            No Corps Being Tracked
          </p>

          @endif

        </div>
      </div>

    </div>
</div>
@stop

@push('javascript')

<script type="text/javascript">
  $('select#available_corporations').select2();
</script>

@endpush