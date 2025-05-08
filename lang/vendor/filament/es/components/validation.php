
<?php

return [
    'accepted' => 'El campo :attribute debe ser aceptado.',
    'active_url' => 'El campo :attribute no es una URL válida.',
    'between' => [
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'file' => 'El campo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'array' => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor a :max.',
        'file' => 'El campo :attribute no debe pesar más de :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'unique' => 'El valor del campo :attribute ya está en uso.',

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'name' => 'nombre',
    ],
];

return [
    // ... (los mensajes anteriores)

    'custom' => [
        'empresa_id' => [
            'required' => 'Debe seleccionar una empresa.',
        ],
        'numero_prestamo' => [
            'required' => 'El número de préstamo es obligatorio.',
            'unique' => 'Este número de préstamo ya está registrado.',
        ],
        'monto_prestamo' => [
            'required' => 'El monto del préstamo es obligatorio.',
            'numeric' => 'El monto debe ser un valor numérico.',
            'min' => 'El monto mínimo debe ser :min.',
        ],
        'tasa_interes' => [
            'required' => 'La tasa de interés es obligatoria.',
            'numeric' => 'La tasa debe ser un valor numérico.',
            'min' => 'La tasa mínima debe ser :min.',
        ],
    ],

    'attributes' => [
        // ... (los atributos anteriores)
        'empresa_id' => 'empresa',
        'numero_prestamo' => 'número de préstamo',
        'banco_id' => 'origen de fondos',
        'linea_id' => 'línea de crédito',
        'monto_prestamo' => 'monto del préstamo',
        'tasa_interes' => 'tasa de interés',
        'plazo_meses' => 'plazo',
    ],
];