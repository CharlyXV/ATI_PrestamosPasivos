
<?php

return [
    'auth' => [
        'login' => [
            'heading' => 'Iniciar sesión en Administración',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'remember' => 'Recordar sesión',
            'button' => 'Iniciar sesión',
            'forgot' => '¿Olvidaste tu contraseña?'
        ]
    ],
    'dashboard' => 'Panel de Control',
    'account' => [
        'widget' => [
            'heading' => 'Bienvenido, :name'
        ]
    ]
];

return [
    // ... (las traducciones anteriores que ya creamos)

    'resources' => [
        'prestamo' => [
            'label' => 'Préstamo',
            'plural_label' => 'Préstamos',
            'navigation_label' => 'Gestión de Préstamos',
            'create' => 'Nuevo Préstamo',
            'edit' => 'Editar Préstamo',
            'view' => 'Ver Préstamo',
        ],
    ],

    'tables' => [
        'columns' => [
            'numero_prestamo' => 'N° Préstamo',
            'formalizacion' => 'Fecha Formalización',
            'monto_prestamo' => 'Monto',
            'saldo_prestamo' => 'Saldo Actual',
            'estado' => 'Estado',
            'banco' => 'Banco',
        ],
        'filters' => [
            'estado' => 'Estado',
            'empresa' => 'Empresa',
        ],
    ],

    'forms' => [
        'sections' => [
            'prestamo' => 'Condiciones del Préstamo',
            'tasas' => 'Condiciones de Tasas',
            'detalles' => 'Desembolsos / Saldos / Estados',
        ],
        'labels' => [
            'empresa_id' => 'Empresa',
            'numero_prestamo' => 'Número de Préstamo',
            'banco_id' => 'Origen Fondos',
            'cuenta_desembolso' => 'Cuenta de Desembolso',
            'linea_id' => 'Línea de Crédito',
            'forma_pago' => 'Forma de Pago',
            'moneda' => 'Moneda',
            'formalizacion' => 'Fecha Formalización',
            'monto_prestamo' => 'Monto del Préstamo',
            'estado' => 'Estado',
            'tipotasa_id' => 'Tipo de Tasa',
            'periodicidad_pago' => 'Periodicidad de Pago',
            'plazo_meses' => 'Plazo',
            'tasa_interes' => 'Tasa de Interés',
            'tasa_spreed' => 'Spread de Interés',
            'vencimiento' => 'Fecha de Vencimiento',
            'proximo_pago' => 'Próximo Pago',
            'observacion' => 'Observaciones',
        ],
        'options' => [
            'forma_pago' => [
                'V' => 'Vencimiento',
                'A' => 'Adelantado',
            ],
            'moneda' => [
                'USD' => 'USD (Dólar)',
                'CRC' => 'CRC (Colón)',
                'EUR' => 'EUR (Euro)',
            ],
            'estado' => [
                'A' => 'Activo',
                'L' => 'Liquidado',
                'I' => 'Incluido',
            ],
            'periodicidad_pago' => [
                '1' => 'Anual (1 pago/año)',
                '2' => 'Semestral (2 pagos/año)',
                '3' => 'Cuatrimestral (3 pagos/año)',
                '4' => 'Trimestral (4 pagos/año)',
                '6' => 'Bimestral (6 pagos/año)',
                '12' => 'Mensual (12 pagos/año)',
            ],
        ],
    ],

    'actions' => [
        'importar' => [
            'label' => 'Importar Excel',
            'modal_heading' => 'Importar Préstamos desde Excel',
            'file_upload_label' => 'Archivo Excel',
            'success_notification' => 'Importación completada exitosamente',
            'failure_notification' => 'Error en la importación: :error',
        ],
    ],
];