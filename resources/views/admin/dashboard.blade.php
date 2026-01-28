@extends('layouts.admin')

@section('titulo', 'Dashboard')

@section('content')
    <div class="row">
        
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-primary">
                <div class="inner">
                    <h3>150</h3>
                    <p>Nuevas Reservas</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-bag"></i>
                </div>
                <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Ver más <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>

        </div> <div class="row">
        <div class="col-lg-7 connectedSortable">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Ventas del Mes</h3>
                </div>
                <div class="card-body">
                    <div id="revenue-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 connectedSortable">
        </div>
    </div> 
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
    
    <script>
        const sales_chart_options = {
            series: [{
                name: 'Servicios',
                data: [28, 48, 40, 19, 86, 27, 90],
            }, {
                name: 'Productos',
                data: [65, 59, 80, 81, 56, 55, 40],
            }],
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false }
            },
            legend: { show: false },
            colors: ['#0d6efd', '#20c997'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: {
                type: 'datetime',
                categories: [
                    '2023-01-01', '2023-02-01', '2023-03-01',
                    '2023-04-01', '2023-05-01', '2023-06-01', '2023-07-01'
                ],
            },
            tooltip: {
                x: { format: 'MMMM yyyy' },
            },
        };

        const sales_chart = new ApexCharts(
            document.querySelector('#revenue-chart'),
            sales_chart_options,
        );
        sales_chart.render();
    </script>
@endpush