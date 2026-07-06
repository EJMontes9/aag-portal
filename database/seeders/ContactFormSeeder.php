<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Seeder;

class ContactFormSeeder extends Seeder
{
    public function run(): void
    {
        // Evitar duplicados si se corre más de una vez
        if (Form::where('slug', 'contacto')->exists()) {
            $this->command->info('El formulario de contacto ya existe, se omite.');
            return;
        }

        $form = Form::create([
            'name'              => 'Contáctanos',
            'slug'              => 'contacto',
            'description'       => 'Escríbenos y te responderemos en menos de 24 horas hábiles.',
            'success_message'   => '¡Gracias por contactarnos! Hemos recibido tu mensaje y nos pondremos en contacto contigo a la brevedad.',
            'submit_label'      => 'Enviar mensaje',
            'notify_emails'     => [],   // agrega tu email desde el panel
            'store_submissions' => true,
            'is_active'         => true,
        ]);

        $fields = [
            [
                'label'       => 'Nombre completo',
                'field_key'   => 'nombre_completo',
                'type'        => 'text',
                'placeholder' => 'Ej: Juan Pérez',
                'help_text'   => null,
                'required'    => true,
                'max_length'  => 120,
                'sort_order'  => 1,
            ],
            [
                'label'       => 'Correo electrónico',
                'field_key'   => 'correo_electronico',
                'type'        => 'email',
                'placeholder' => 'ejemplo@correo.com',
                'help_text'   => 'Te responderemos a esta dirección.',
                'required'    => true,
                'max_length'  => 200,
                'sort_order'  => 2,
            ],
            [
                'label'       => 'Teléfono',
                'field_key'   => 'telefono',
                'type'        => 'tel',
                'placeholder' => '+593 99 123 4567',
                'help_text'   => 'Opcional. Incluye el código de país.',
                'required'    => false,
                'max_length'  => 25,
                'sort_order'  => 3,
            ],
            [
                'label'       => 'Asunto',
                'field_key'   => 'asunto',
                'type'        => 'select',
                'placeholder' => '— Selecciona el motivo de tu consulta —',
                'help_text'   => null,
                'required'    => true,
                'options'     => [
                    ['label' => 'Consulta general',          'value' => 'consulta_general'],
                    ['label' => 'Información de vuelos',     'value' => 'informacion_vuelos'],
                    ['label' => 'Servicios al pasajero',     'value' => 'servicios_pasajero'],
                    ['label' => 'Proveedores / comercial',   'value' => 'proveedores'],
                    ['label' => 'Prensa y comunicación',     'value' => 'prensa'],
                    ['label' => 'Otro',                      'value' => 'otro'],
                ],
                'sort_order'  => 4,
            ],
            [
                'label'       => 'Mensaje',
                'field_key'   => 'mensaje',
                'type'        => 'textarea',
                'placeholder' => 'Escribe aquí tu consulta o comentario…',
                'help_text'   => 'Mínimo 20 caracteres.',
                'required'    => true,
                'min_length'  => 20,
                'max_length'  => 3000,
                'sort_order'  => 5,
            ],
            [
                'label'       => 'Acepto que mis datos sean usados para responder esta consulta',
                'field_key'   => 'acepta_privacidad',
                'type'        => 'checkbox',
                'placeholder' => null,
                'help_text'   => null,
                'required'    => true,
                'sort_order'  => 6,
            ],
        ];

        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, ['form_id' => $form->id]));
        }

        $this->command->info("Formulario 'Contáctanos' creado con {$form->fields()->count()} campos.");
    }
}
