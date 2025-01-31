<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte del Préstamo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reporte del Préstamo</h1>
    <h2>Encabezado del Préstamo</h2>
    <table>
        <tr>
            <th>Monto</th>
            <td>{{ $prestamo->monto_prestamo }}</td>
        </tr>
        <tr>
            <th>Tasa de Interés</th>
            <td>{{ $prestamo->tasa_interes }}</td>
        </tr>
        <tr>
            <th>Plazo</th>
            <td>{{ $prestamo->plazo_meses }}</td>
        </tr>
        <tr>
            <th>Periodicidad</th>
            <td>{{ $prestamo->periodicidad_pago }}</td>
        </tr>
        <tr>
            <th>Tipo de Calendario</th>
            <td>{{ $prestamo->tipo_calendario }}</td>
        </tr>
    </table>
    
    <h2>Plan de Pagos</h2>
    <table>
        <thead>
            <tr>
                <th>Número de Cuota</th>
                <th>Monto de Pago</th>
                <th>Interés Mensual</th>
                <th>Fecha de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($planPagos as $planPago)
                <tr>
                    <td>{{ $planPago->numero_cuota }}</td>
                    <td>{{ $planPago->monto_principal }}</td>
                    <td>{{ $planPago->monto_interes }}</td>
                    <td>{{ \Carbon\Carbon::parse($planPago->fecha_pago)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>