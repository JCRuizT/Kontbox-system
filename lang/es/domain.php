<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes del dominio de negocio
    |--------------------------------------------------------------------------
    */

    // Microservicios
    'microservice' => [
        'created' => 'Microservicio creado exitosamente.',
        'updated' => 'Microservicio actualizado exitosamente.',
        'activated' => 'Microservicio reactivado exitosamente.',
        'deactivated' => 'Microservicio desactivado. No disponible para nuevas cotizaciones.',
        'not_found' => 'Microservicio no encontrado.',
    ],

    // Planes
    'plan' => [
        'created' => 'Plan creado exitosamente.',
        'updated' => 'Plan actualizado exitosamente.',
        'activated' => 'Plan reactivado exitosamente.',
        'deactivated' => 'Plan desactivado.',
        'not_found' => 'Plan no encontrado.',
        'activity_enabled' => 'Actividad habilitada en el plan.',
        'activity_disabled' => 'Actividad deshabilitada en el plan.',
        'activity_not_in_plan' => 'La actividad no pertenece a este plan.',
    ],

    // Prospectos
    'prospect' => [
        'created' => 'Prospecto registrado exitosamente.',
        'updated' => 'Prospecto actualizado exitosamente.',
        'not_found' => 'Prospecto no encontrado.',
        'statuses' => [
            'new' => 'Nuevo',
            'contacted' => 'Contactado',
            'negotiation' => 'En negociación',
            'won' => 'Ganado',
            'lost' => 'Perdido',
        ],
    ],

    // Cotizaciones
    'quotation' => [
        'created' => 'Cotización creada exitosamente.',
        'immutable' => 'Las cotizaciones emitidas son inmutables. Cree una nueva versión.',
        'sent_for_approval' => 'Cotización enviada a revisión.',
        'approved' => 'Cotización aprobada.',
        'rejected' => 'Cotización rechazada.',
        'new_version_created' => 'Nueva versión creada a partir de la cotización rechazada.',
        'not_found' => 'Cotización no encontrada.',
        'must_be_under_review_to_approve' => 'La cotización debe estar en revisión para ser aprobada.',
        'must_be_under_review_to_reject' => 'La cotización debe estar en revisión para ser rechazada.',
        'version_only_from_rejected' => 'Solo se puede versionar una cotización rechazada.',
        'at_least_one_service' => 'Debe seleccionar al menos un servicio.',
        'statuses' => [
            'draft' => 'Borrador',
            'under_review' => 'En revisión',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
        ],
    ],

    // Contratos
    'contract' => [
        'created' => 'Contrato creado. Cargue el PDF firmado para activarlo.',
        'not_found' => 'Contrato no encontrado.',
        'cannot_activate_without_pdf' => 'No se puede activar el contrato sin el documento PDF firmado cargado en la plataforma.',
        'only_active_can_be_cancelled' => 'Solo se pueden anular contratos activos.',
        'pending_document_status_required' => 'El contrato no est� en estado pendiente de documento.',
        'document_uploaded' => 'Documento PDF firmado cargado exitosamente.',
        'activated_successfully' => 'Contrato activado exitosamente.',
        'anulated' => 'Contrato anulado exitosamente.',
        'cannot_modify_inactive' => 'Solo se pueden modificar contratos activos.',
        'quotation_not_approved' => 'La cotizaci�n debe estar aprobada para generar un contrato.',
        'quotation_already_contracted' => 'Esta cotizaci�n ya tiene un contrato asociado.',
        'pdf_not_available' => 'El PDF no est� disponible.',
        'statuses' => [
            'pending_document' => 'Pendiente de documento',
            'document_loaded' => 'Documento cargado',
            'active' => 'Contrato activo',
            'cancelled' => 'Contrato anulado',
        ],
        'timeline' => [
            'created' => 'Creado',
            'pdf_uploaded' => 'PDF firmado cargado',
            'activated' => 'Activado',
            'cancelled' => 'Anulado',
        ],
    ],

    // Modificaciones (Otrosí)
    'amendment' => [
        'created' => 'Modificación (Otrosí) registrada exitosamente. El PDF firmado ha sido vinculado.',
        'not_found' => 'Modificación no encontrada.',
        'pdf_required' => 'El sistema exige cargar el documento legal firmado que respalda el cambio para poder procesarlo.',
        'only_active_contracts' => 'Solo se pueden modificar contratos activos.',
    ],

    // Facturación
    'invoice' => [
        'created' => 'Factura generada exitosamente.',
        'not_found' => 'Factura no encontrada.',
        'contract_must_be_active' => 'Solo se pueden facturar contratos activos.',
        'fiscal_disclaimer' => 'Esta factura es una representación gráfica para el cliente y control interno, sin poseer validez fiscal electrónica.',
        'statuses' => [
            'issued' => 'Emitida',
            'paid' => 'Pagada',
            'cancelled' => 'Anulada',
        ],
    ],

    // Roles
    'roles' => [
        'admin' => 'Administrador',
        'vendor' => 'Vendedor',
        'commercial_manager' => 'Gerente Comercial',
        'administrative' => 'Área Administrativa',
    ],

    // Actividades
    'activity' => [
        'created' => 'Actividad creada exitosamente.',
        'updated' => 'Actividad actualizada exitosamente.',
        'activated' => 'Actividad reactivada exitosamente.',
        'deactivated' => 'Actividad desactivada.',
        'permanently_deleted' => 'Actividad eliminada físicamente del sistema.',
        'deactivated_with_relations' => 'Actividad desactivada (tiene :count instancia(s) en contratos activos).',
        'essential_cannot_deactivate' => 'No se puede desactivar una actividad esencial.',
        'essential_permission_required' => 'No tiene permisos para cambiar el estado esencial de una actividad.',
        'essential_cannot_disable' => 'No se puede deshabilitar una actividad esencial en el contrato.',
        'cannot_activate_without_microservice' => 'No se puede reactivar la actividad porque su microservicio padre está inactivo.',
        'instance_enabled' => 'Actividad habilitada para este contrato.',
        'instance_disabled' => 'Actividad deshabilitada para este contrato.',
        'not_found' => 'Actividad no encontrada.',
        'statuses' => [
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completada',
        ],
    ],

    'audit' => [
        'title' => 'Auditoría y Trazabilidad',
        'description' => 'Historial inalterable de cambios en cotizaciones, contratos y modificaciones.',
    ],

    // Administración
    'admin' => [
        'user_created' => 'Usuario creado exitosamente.',
        'user_updated' => 'Usuario actualizado exitosamente.',
        'user_deleted' => 'Usuario eliminado permanentemente.',
        'user_restored' => 'Usuario restaurado exitosamente.',
        'cannot_delete_self' => 'No puede eliminarse a sí mismo.',
        'cannot_delete_with_interactions' => 'No se puede eliminar: el usuario tiene interacciones registradas en el sistema. Puede deshabilitarlo en su lugar.',
        'role_created' => 'Rol :name creado exitosamente.',
        'role_renamed' => 'Rol renombrado de ":old" a ":new".',
        'role_permissions_updated' => 'Permisos actualizados para el rol :name.',
    ],

    // Auditoría - descripciones de logs
    'audit_log' => [
        'view_pdf_contract' => 'Visualizó PDF del Contrato :number',
        'view_pdf_amendment' => 'Visualizó PDF del Otrosí :number',
        'created_entity' => 'Creó :entity #:id',
        'updated_entity' => 'Actualizó :entity #:id',
        'deleted_entity' => 'Desactivó :entity #:id',
        'status_changed' => 'Cambió estado de :entity #:id: :from → :to',
        'user_seeded' => 'Usuario :email listo con rol: :role',
    ],

    // Errores generales
    'error' => [
        'forbidden' => 'No tiene permisos para acceder a esta sección.',
        'negative_amount' => 'El monto no puede ser negativo.',
        'different_currencies' => 'No se pueden sumar monedas diferentes.',
        'forbidden_action' => 'No tiene permisos para realizar esta acción.',
        'not_found' => 'Recurso no encontrado.',
        'validation_failed' => 'Error de validación.',
        'invalid_credentials' => 'Credenciales inválidas.',
    ],
];
