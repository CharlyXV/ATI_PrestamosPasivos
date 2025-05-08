<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Error interno del servidor</h1>
        <p class="text-gray-600 mb-4">Ha ocurrido un error interno en el servidor. Por favor, contacte a soporte técnico para la resolución del problema.</p>
        
        @if(isset($error_id))
            <p class="text-sm text-gray-500 mb-4">Referencia de error: {{ $error_id }}</p>
        @endif
        
        <div class="flex justify-between">
            <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300">
                Ir al inicio
            </a>
            <button onclick="window.history.back()" class="inline-flex items-center px-4 py-2 bg-blue-600 rounded-md text-white hover:bg-blue-700">
                Volver atrás
            </button>
        </div>
    </div>
</body>
</html>