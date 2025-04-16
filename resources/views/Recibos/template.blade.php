<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo {{ $recibo->numero_recibo }} | FACILEASING S.A.</title>
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
            line-height: 1.5;
        }
        
        /* Encabezado con logo */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            text-align: center;
        }
        
        .logo {
            max-height: 40px;
            width: auto;
            margin-bottom: 10px;
        }
        
        .company-info {
            font-size: 12px;
            line-height: 1.5;
        }
        
        /* Títulos */
        h1 {
            color: #2c3e50;
            font-size: 22px;
            margin: 5px 0;
            font-weight: bold;
            text-align: center;
        }
        
        h2 {
            color: #3498db;
            font-size: 16px;
            margin: 5px 0;
            padding-bottom: 5px;
        }
        
        /* Información del recibo */
        .recibo-info {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        
        .recibo-info p {
            margin: 5px 0;
        }
        
        .recibo-numero {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
        }
        
        .info-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .info-left, .info-right {
            width: 48%;
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
        
        /* Resumen de pago */
        .payment-summary {
            margin-top: 20px;
            border-top: 2px dashed #ddd;
            padding-top: 15px;
        }
        
        .summary-table {
            width: 50%;
            float: right;
            margin-right: 0;
        }
        
        .summary-table th {
            text-align: right;
            font-weight: normal;
            background-color: transparent;
            color: #333;
            border: none;
            padding: 5px;
        }
        
        .summary-table td {
            text-align: right;
            font-weight: bold;
            border: none;
            padding: 5px;
        }
        
        .summary-table tr.total {
            border-top: 2px solid #2c3e50;
            font-size: 14px;
        }
        
        .summary-table tr.total td {
            color: #2c3e50;
        }
        
        /* Notas y Firmas */
        .notes {
            margin-top: 30px;
            padding: 15px;
            border: 1px dashed #ddd;
            font-size: 11px;
            color: #7f8c8d;
        }
        
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-line {
            width: 45%;
            border-top: 1px solid #333;
            padding-top: 5px;
            text-align: center;
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
        
        .text-bold {
            font-weight: bold;
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
        <img src="{{ base_path('public/images/atilogojpg.jpg') }}" alt="ATI Logo" class="logo">
    </div>
    <div class="company-info">
        <h2>ATI Capital Solutions S.A.</h2>
        <p>Cédula Jurídica: 3-101-129386</p>
        <p> Teléfono: +506 1234-5678, Email: info@aticapital.com, San José, Costa Rica</p>
    </div>
</div>

    <!-- Título principal -->
    <h1>RECIBO DE PAGO</h1>
    
    <!-- Información del recibo en dos columnas -->
    <div class="info-container">

    <div class="info-right">
            <div class="recibo-info text-center">
                <div class="recibo-numero">
                    RECIBO N° {{ $recibo->numero_recibo }}
                </div>
            </div>
        </div>
        <div class="info-left">
            <div class="recibo-info">
                <p><strong>Cliente:</strong> {{ $recibo->prestamo->empresa->nombre_empresa }}</p>
                <p><strong>Concepto:</strong> {{ $recibo->detalle }}</p>
                <p><strong>Fecha de pago:</strong> {{ $recibo->fecha_pago->format('d-m-Y') }}</p>
                <p><strong>Hora:</strong> {{ $recibo->fecha_pago->format('H:i:s') }}</p>
                <p><strong>Forma de pago:</strong> {{ $recibo->prestamo->moneda }}</p>
            </div>
        </div>
        
    </div>

    <!-- Detalle de cuotas -->
    <table>
        <thead>
            <tr>
                <th class="text-center"># Cuota</th>
                <th class="text-right">Principal</th>
                <th class="text-right">Intereses</th>
                <th class="text-right">Seguro</th>
                <th class="text-right">Otros</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recibo->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->numero_cuota }}</td>
                <td class="text-right">{{ number_format($detalle->monto_principal, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->monto_intereses, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->monto_seguro, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->monto_otros, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->monto_cuota, 2) }}</td>
            </tr>
            @endforeach
            <!-- Total general -->
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="5" class="text-right">TOTAL PAGADO:</td>
                <td class="text-right">{{ number_format($recibo->monto_recibo, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Resumen de pago -->
    <div class="payment-summary">
        <table class="summary-table">
            <tr>
                <th>Saldo Anterior:</th>
                <td>{{ number_format($recibo->prestamo->saldo_prestamo + $recibo->detalles->sum('monto_principal'), 2) }}</td>
            </tr>
            <tr>
                <th>Monto Pagado:</th>
                <td>{{ number_format($recibo->monto_recibo, 2) }}</td>
            </tr>
            <tr class="total">
                <th>Saldo Actual:</th>
                <td>{{ number_format($recibo->prestamo->saldo_prestamo, 2) }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <!-- Notas y términos -->
    <div class="notes">
        <p>Nota: Este documento es un comprobante oficial de pago. Conserve este recibo para futuras referencias.</p>
        <p>En caso de consultas sobre este pago, por favor comuníquese con nuestro departamento de servicio al cliente
           e indique el número de recibo.</p>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-line">
            Recibido por
        </div>
        <div class="signature-line">
            Cliente
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Fecha de emisión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} | FACILEASING S.A. © {{ date('Y') }}</p>
        <p>Este documento es válido sin firma ni sello</p>
    </div>
</body>
</html>