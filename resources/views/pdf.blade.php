<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos</title>
    <style>
        /* Configuración de página y márgenes */
        @page {
            margin: 100px 25px 50px 25px;
        }

        /* Estilos generales */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
        }

        /* Encabezado fijo en todas las páginas */
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 60px;
            border-bottom: 2px solid #f3d83e;
        }

        /* Pie de página fijo con numeración */
        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        /* Numeración automática de páginas en DomPDF */
        .page-number:before {
            content: counter(page);
        }

        /* Layout con tablas en lugar de Flexbox/Grid */
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #f3d83e;
            margin: 0;
        }

        /* Tabla de datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th {
            background-color: #f3d83e;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            font-size: 11px;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Filas alternadas (Zebra) */
        .data-table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        /* Utilidades de alineación */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Encabezado repetido en cada página -->
    <header>
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="title">REPORTE DE {{ strtoupper($data['title']) }}</h1>
                </td>
                <td class="text-right">
                    <strong>Fecha:</strong> {{ date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </header>

    <!-- Pie de página repetido -->
    <footer>
        Página <span class="page-number"></span> | Prueba Técnica TAP
    </footer>

    <!-- Contenido principal -->
    <main>
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($data['headers'] as $header)
                        <th style="width: {{$data['width']}}%;" class="text-center">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($data['items'] as $item)
                    <tr>
                        @foreach($item as $key => $value)
                            <td class="text-center">{{ $value }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay productos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

</body>
</html>