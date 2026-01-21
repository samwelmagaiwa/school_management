@extends('layouts.master')
@section('page_title', 'Exam Statistics - ' . $exam->name)
@section('content')

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="icon-stats-bars"></i> {{ $exam->name }} - Term {{ $exam->term }} ({{ $year }})</h5>
    </div>
</div>

{{-- Overall Statistics --}}
<div class="row mt-3">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $overall_stats['total_students'] }}</h3>
                        <span>Total Students</span>
                    </div>
                    <i class="icon-users icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $overall_stats['present_students'] }}</h3>
                        <span>Present</span>
                    </div>
                    <i class="icon-checkmark3 icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $overall_stats['absent_students'] }}</h3>
                        <span>Absent ({{ $overall_stats['absence_rate'] }}%)</span>
                    </div>
                    <i class="icon-user-block icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0">{{ $overall_stats['avg_score'] }}%</h3>
                        <span>Average Score</span>
                    </div>
                    <i class="icon-trophy3 icon-3x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Performance Metrics --}}
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title font-weight-bold">Grade Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="gradeChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title font-weight-bold">Performance Summary</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Distinctions (A/B)</strong></td>
                        <td class="text-right">{{ $overall_stats['distinctions'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Credits (C)</strong></td>
                        <td class="text-right">{{ $overall_stats['credits'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Passes (D/E)</strong></td>
                        <td class="text-right">{{ $overall_stats['passes'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Failures (F)</strong></td>
                        <td class="text-right text-danger">{{ $overall_stats['failures'] }}</td>
                    </tr>
                    <tr class="bg-light">
                        <td><strong>Pass Rate</strong></td>
                        <td class="text-right font-weight-bold text-success">{{ $overall_stats['pass_rate'] }}%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Subject Statistics --}}
<div class="card mt-3">
    <div class="card-header">
        <h6 class="card-title font-weight-bold">Subject Performance</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Avg Score</th>
                        <th class="text-center">Highest</th>
                        <th class="text-center">Lowest</th>
                        <th class="text-center">Passed</th>
                        <th class="text-center">Failed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subject_stats as $stat)
                        <tr>
                            <td><strong>{{ $stat['subject']->name }}</strong></td>
                            <td class="text-center">{{ $stat['total_entries'] }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $stat['avg_score'] >= 70 ? 'success' : ($stat['avg_score'] >= 50 ? 'warning' : 'danger') }}">
                                    {{ $stat['avg_score'] }}%
                                </span>
                            </td>
                            <td class="text-center">{{ $stat['highest'] }}</td>
                            <td class="text-center">{{ $stat['lowest'] }}</td>
                            <td class="text-center text-success">{{ $stat['pass_count'] }}</td>
                            <td class="text-center text-danger">{{ $stat['fail_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Class Statistics --}}
<div class="card mt-3">
    <div class="card-header">
        <h6 class="card-title font-weight-bold">Class Performance</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Average</th>
                        <th class="text-center">Class Avg</th>
                        <th class="text-center">Highest</th>
                        <th class="text-center">Lowest</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($class_stats as $stat)
                        <tr>
                            <td><strong>{{ $stat['class']->name }}</strong></td>
                            <td class="text-center">{{ $stat['student_count'] }}</td>
                            <td class="text-center">{{ $stat['avg_score'] }}%</td>
                            <td class="text-center">{{ $stat['class_avg'] }}%</td>
                            <td class="text-center">{{ $stat['highest_total'] }}</td>
                            <td class="text-center">{{ $stat['lowest_total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    const gradeData = @json($grade_distribution);
    
    new Chart(gradeCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(gradeData),
            datasets: [{
                label: 'Grade Distribution',
                data: Object.values(gradeData),
                backgroundColor: [
                    '#28a745', '#5cb85c', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false
                }
            }
        }
    });
});
</script>

@endsection
