<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Plan de Pagos | ATI Capital Solutions S.A.</title>
    <style>
        /* --- FUENTES --- */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path("fonts/dejavu-sans/DejaVuSans.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path("fonts/dejavu-sans/DejaVuSans-Bold.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        /* --- ESTILOS GENERALES --- */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        
        /* Encabezado con logo */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        
        .logo {
            max-height: 30px;
            width: auto;
        }
        
        .company-info {
            text-align: right;
            font-size: 12px;
            line-height: 1.5;
            float: right;
            margin-top: -80px;
        }
        
        /* Títulos */
        h1 {
            color: #2c3e50;
            font-size: 22px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        h2 {
            color: #3498db;
            font-size: 16px;
            margin: 25px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Pie de página */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

       
        
        
        /* Alineaciones especiales */
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Corrige problema con headers de tablas */
        thead { display: table-header-group; }
        tfoot { display: table-row-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <!-- Encabezado con logo y datos de la empresa -->
    <div class="header">
        <div>
            <img src="{{ base_path('public/images/atilogo.png') }}" alt="ATI Capital Solutions S.A." class="logo">
        </div>
        <div class="company-info">
            <strong>ATI Capital Solutions S.A.</strong><br>
            Teléfono: +506 1234-5678<br>
            Email: info@aticapital.com<br>
            San José, Costa Rica
        </div>
    </div>

    <!-- Título principal -->
    <h1 class="text-center">Plan de Pagos - Préstamo {{ $prestamo->numero_prestamo }}</h1>
    
    <!-- Información del préstamo -->
    <h2>Datos del Préstamo</h2>
    <table>
        <tr>
            <th width="30%">Cliente</th>
            <td>{{ $prestamo->empresa->nombre_empresa }}</td>
        </tr>
        <tr>
            <th>Monto del Préstamo</th>
            <td>{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($prestamo->monto_prestamo, 2) }}</td>
        </tr>
        <tr>
            <th>Tasa de Interés Anual</th>
            <td>{{ number_format($prestamo->tasa_interes, 2) }}%</td>
        </tr>
        <tr>
            <th>Plazo</th>
            <td>{{ $prestamo->plazo_meses }} meses</td>
        </tr>
        <tr>
            <th>Fecha de Formalización</th>
            <td>{{ \Carbon\Carbon::parse($prestamo->formalizacion)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Fecha de Vencimiento</th>
            <td>{{ isset($prestamo->vencimiento) ? \Carbon\Carbon::parse($prestamo->vencimiento)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
    </table>

    <!-- Detalle de cuotas -->
    <h2>Plan de Pagos</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center"># Cuota</th>
                <th class="text-center">Fecha Pago</th>
                <th class="text-right">Capital</th>
                <th class="text-right">Interés</th>
                <th class="text-right">Total Cuota</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($planPagos as $cuota)
            <tr>
                <td class="text-center">{{ $cuota->numero_cuota }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($cuota->monto_principal, 2) }}</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($cuota->monto_interes, 2) }}</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($cuota->monto_principal + $cuota->monto_interes, 2) }}</td>
                <td class="text-center">{{ ucfirst($cuota->plp_estados) }}</td>
            </tr>
            @endforeach
            <!-- Total general -->
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="2" class="text-right">TOTAL:</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($planPagos->sum('monto_principal'), 2) }}</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($planPagos->sum('monto_interes'), 2) }}</td>
                <td class="text-right">{{ isset($prestamo->moneda) ? $prestamo->moneda : '' }} {{ number_format($planPagos->sum('monto_principal') + $planPagos->sum('monto_interes'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- Observaciones y firma -->
    <h2>Observaciones</h2>
    <p style="font-size: 12px; line-height: 1.5;">
        Este documento ha sido generado automáticamente por el sistema de gestión de ATI Capital Solutions S.A. 
        Para cualquier aclaración, por favor contacte a su ejecutivo de cuenta.
    </p>

    <!-- Pie de página -->
    <div class="footer">
        <p>Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} | ATI Capital Solutions S.A. © {{ date('Y') }}</p>
        <p>Documento confidencial - Uso exclusivo del cliente</p>
    </div>
</body>
</html>