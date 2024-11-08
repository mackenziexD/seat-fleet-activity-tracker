@extends('web::layouts.grids.12', ['viewname' => 'seat-fleet-activity-tracker::allFleets'])

@section('title', 'Stats')
@section('page_header', 'Corporation Stats')

@section('full')
<div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Monthly Total Fats
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="monthlyTotalFats"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          FATs Per Day
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="FatsPerDay"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Fleets Per Day
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="FleetsPerDay"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Timezone FAT Split
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="TimezoneFatSlip"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Top Corp Total Fats
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="TopCorpTotalFats"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          FATS Relative To Corp Size
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="FatsRelativeToCorpSize"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          Top FC's
        </div>
        <div class="card-body">
          <div class="chart-container text-center" style="height:300px;display:block; margin:0 auto;">
            <canvas width="100" height="100" id="TopFCs"></canvas>
          </div>
        </div>
      </div>
    </div>
</div>

@stop

@push('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthlyTotalFats = @json($monthlyStats);
        const dailyFats = @json($dailyFats);
        const dailyFleets = @json($dailyFleets);
        const timezoneSplit = @json($timezoneSplit);
        const topFCs = @json($topFCs);
        const topCorps = @json($topCorps);
        const corpSizes = @json($corpSizes);
        const otherData = @json($otherData);
        console.log(Chart.version);

        // Monthly Total Fats - Vertical Bar Chart
        new Chart(document.getElementById('monthlyTotalFats'), {
            type: 'bar',
            data: {
                labels: Object.keys(monthlyTotalFats),
                datasets: [{
                    label: 'Monthly Total Fats',
                    data: Object.values(monthlyTotalFats),
                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                }]
            },
            options: {
                maintainAspectRatio: false, 
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value >= 1000 ? (value / 1000) + 'K' : value
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });

        // Fats Per Day - Vertical Bar Chart
        new Chart(document.getElementById('FatsPerDay'), {
            type: 'bar',
            data: {
                labels: Object.keys(dailyFats).map(date => new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit' })),
                datasets: [{
                    label: 'Fats Per Day',
                    data: Object.values(dailyFats),
                    backgroundColor: 'rgba(231, 76, 60, 0.8)',
                }]
            },
            options: {
                maintainAspectRatio: false, 
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false }, maxRotation: 45, minRotation: 45 }
                }
            }
        });

        // Fleets Per Day - Vertical Bar Chart
        new Chart(document.getElementById('FleetsPerDay'), {
            type: 'bar',
            data: {
                labels: Object.keys(dailyFleets).map(date => new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit' })),
                datasets: [{
                    label: 'Fleets Per Day',
                    data: Object.values(dailyFleets),
                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                }]
            },
            options: {
                maintainAspectRatio: false, 
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false }, maxRotation: 45, minRotation: 45 }
                }
            }
        });

        // Timezone FAT Split - Pie Chart
        new Chart(document.getElementById('TimezoneFatSlip'), {
            type: 'pie',
            data: {
                labels: ['EU', 'AU', 'US'],
                datasets: [{
                    data: Object.values(timezoneSplit),
                    backgroundColor: ['#3498db', '#f1c40f', '#2ecc71'],
                }]
            },
            options: {
                maintainAspectRatio: false, 
                responsive: true,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

        // Top Corp Total Fats - Horizontal Bar Chart
        new Chart(document.getElementById('TopCorpTotalFats'), {
            type: 'bar',
            data: {
                labels: Object.keys(topCorps), 
                datasets: [{
                    label: 'Top Corp Total Fats',
                    data: Object.values(topCorps), 
                    backgroundColor: 'rgba(39, 174, 96, 0.8)',
                }]
            },
          options: {
              indexAxis: 'y', 
              maintainAspectRatio: false,
              responsive: true,
              scales: {
                  x: { beginAtZero: true },
                  y: { grid: { display: false } },
              }
          }
        });


        // Top FCs - Horizontal Bar Chart
        new Chart(document.getElementById('TopFCs'), {
            type: 'bar',
            data: {
                labels: Object.keys(topFCs),
                datasets: [{
                    label: 'Top FCs',
                    data: Object.values(topFCs),
                    backgroundColor: 'rgba(142, 68, 173, 0.8)',
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false, 
                responsive: true,
            }
        });

        // FATS Relative to Corp Size - 100% Stacked Bar Chart
        new Chart(document.getElementById('FatsRelativeToCorpSize'), {
            type: 'bar',
            data: {
                labels: Object.keys(corpSizes), // Corporation names
                datasets: [
                    {
                        label: 'FATS Relative to Corp Size',
                        data: Object.values(otherData),
                        backgroundColor: 'rgba(52, 73, 94, 0.8)',
                        stack: 'Stack 0'
                    }
                ]
            },
            options: {
                indexAxis: 'y', // Horizontal bar chart
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                        max: 100, 
                        ticks: { 
                            callback: value => value + '%' 
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            callback: (value, index) => Object.keys(corpSizes)[index], 
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });


    });
</script>
@endpush
