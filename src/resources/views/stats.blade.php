@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::allFleets'])

@section('title', 'Stats')
@section('page_header', 'Corporation Stats')

@section('full')
<div class="row mb-3">
  <div class="col-md-6">
    Last Month
  </div>
  <div class="col-md-6 disabled text-right">
    Next Month
  </div>
</div>
<div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          Corporation Contributions
        </div>
        <div class="card-body">
          <table class="table table-striped">
              <thead>
                  <tr>
                      <th>Corporation Name</th>
                      <th>Avg FATS</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($monthlyStats as $month => $corporations)
                      @foreach($corporations as $corporation)
                          <tr>
                              <td>{{ $corporation['corporation_name'] }}</td>
                              <td>{{ number_format($corporation['avg_pap'], 2) }}</td>
                          </tr>
                      @endforeach
                  @endforeach
              </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          Corporation Contributions
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:400px; width:400px;display:block;margin:0 auto;">
            <canvas id="contributionPieChart"></canvas>
          </div>
        </div>
      </div>
    </div>
</div>

@stop

@push('javascript')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.0.3/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#fleets-table').DataTable();
        createContributionPieChart();
    });

    function createContributionPieChart() {
        const monthlyStats = @json($monthlyStats);
        const contributions = {};

        // Aggregate FATS counts for all corporations
        Object.values(monthlyStats).forEach(corporations => {
            corporations.forEach(corp => {
                if (!contributions[corp.corporation_name]) {
                    contributions[corp.corporation_name] = 0;
                }
                contributions[corp.corporation_name] += corp.fats_count;
            });
        });

        const labels = Object.keys(contributions);
        const data = Object.values(contributions);
        const ctx = document.getElementById('contributionPieChart').getContext('2d');

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Corporation Contributions',
                    data: data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
