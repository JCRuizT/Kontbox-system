<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business domain messages
    |--------------------------------------------------------------------------
    */

    'microservice' => [
        'created' => 'Microservice created successfully.',
        'updated' => 'Microservice updated successfully.',
        'activated' => 'Microservice reactivated successfully.',
        'deactivated' => 'Microservice deactivated. Not available for new quotations.',
        'not_found' => 'Microservice not found.',
    ],

    'plan' => [
        'created' => 'Plan created successfully.',
        'updated' => 'Plan updated successfully.',
        'activated' => 'Plan reactivated successfully.',
        'deactivated' => 'Plan deactivated.',
        'not_found' => 'Plan not found.',
        'activity_enabled' => 'Activity enabled in plan.',
        'activity_disabled' => 'Activity disabled in plan.',
        'activity_not_in_plan' => 'The activity does not belong to this plan.',
    ],

    'prospect' => [
        'created' => 'Prospect registered successfully.',
        'updated' => 'Prospect updated successfully.',
        'not_found' => 'Prospect not found.',
        'statuses' => [
            'new' => 'New',
            'contacted' => 'Contacted',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
        ],
    ],

    'quotation' => [
        'created' => 'Quotation created successfully.',
        'immutable' => 'Issued quotations are immutable. Create a new version.',
        'sent_for_approval' => 'Quotation sent for approval.',
        'approved' => 'Quotation approved.',
        'rejected' => 'Quotation rejected.',
        'new_version_created' => 'New version created from rejected quotation.',
        'not_found' => 'Quotation not found.',
        'must_be_under_review_to_approve' => 'The quotation must be under review to be approved.',
        'must_be_under_review_to_reject' => 'The quotation must be under review to be rejected.',
        'version_only_from_rejected' => 'Only rejected quotations can be versioned.',
        'at_least_one_service' => 'You must select at least one service.',
        'statuses' => [
            'draft' => 'Draft',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ],
    ],

    'contract' => [
        'created' => 'Contract created. Upload the signed PDF to activate it.',
        'not_found' => 'Contract not found.',
        'cannot_activate_without_pdf' => 'Cannot activate contract without signed PDF uploaded to the platform.',
        'only_active_can_be_cancelled' => 'Only active contracts can be cancelled.',
        'pending_document_status_required' => 'Contract is not in pending document status.',
        'document_uploaded' => 'Signed PDF document uploaded successfully.',
        'activated_successfully' => 'Contract activated successfully.',
        'anulated' => 'Contract cancelled successfully.',
        'cannot_modify_inactive' => 'Only active contracts can be modified.',
        'quotation_not_approved' => 'The quotation must be approved to generate a contract.',
        'quotation_already_contracted' => 'This quotation already has an associated contract.',
        'pdf_not_available' => 'The PDF is not available.',
        'statuses' => [
            'pending_document' => 'Pending Document',
            'document_loaded' => 'Document Loaded',
            'active' => 'Active Contract',
            'cancelled' => 'Cancelled',
        ],
        'timeline' => [
            'created' => 'Created',
            'pdf_uploaded' => 'Signed PDF uploaded',
            'activated' => 'Activated',
            'cancelled' => 'Cancelled',
        ],
    ],

    'amendment' => [
        'created' => 'Amendment (Otrosí) registered successfully. Signed PDF has been linked.',
        'not_found' => 'Amendment not found.',
        'pdf_required' => 'The system requires uploading the signed legal document that supports the change in order to process it.',
        'only_active_contracts' => 'Only active contracts can be modified.',
    ],

    'invoice' => [
        'created' => 'Invoice generated successfully.',
        'not_found' => 'Invoice not found.',
        'contract_must_be_active' => 'Only active contracts can be invoiced.',
        'fiscal_disclaimer' => 'This invoice is a graphic representation for the client and internal control, without electronic fiscal validity.',
        'statuses' => [
            'issued' => 'Issued',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ],
    ],

    'roles' => [
        'admin' => 'Administrator',
        'vendor' => 'Vendor',
        'commercial_manager' => 'Commercial Manager',
        'administrative' => 'Administrative Area',
    ],

    'activity' => [
        'created' => 'Activity created successfully.',
        'updated' => 'Activity updated successfully.',
        'activated' => 'Activity reactivated successfully.',
        'deactivated' => 'Activity deactivated.',
        'permanently_deleted' => 'Activity permanently deleted from the system.',
        'deactivated_with_relations' => 'Activity deactivated (has :count instance(s) in active contracts).',
        'essential_cannot_deactivate' => 'Cannot deactivate an essential activity.',
        'essential_permission_required' => 'You do not have permission to change the essential status of an activity.',
        'essential_cannot_disable' => 'Cannot disable an essential activity in the contract.',
        'cannot_activate_without_microservice' => 'Cannot reactivate the activity because its parent microservice is inactive.',
        'instance_enabled' => 'Activity enabled for this contract.',
        'instance_disabled' => 'Activity disabled for this contract.',
        'not_found' => 'Activity not found.',
        'statuses' => [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
        ],
    ],

    'audit' => [
        'title' => 'Audit & Traceability',
        'description' => 'Immutable history of changes in quotations, contracts and amendments.',
    ],

    // Administration
    'admin' => [
        'user_created' => 'User created successfully.',
        'user_updated' => 'User updated successfully.',
        'user_deleted' => 'User permanently deleted.',
        'user_restored' => 'User restored successfully.',
        'cannot_delete_self' => 'You cannot delete yourself.',
        'cannot_delete_with_interactions' => 'Cannot delete: the user has interactions recorded in the system. You can disable it instead.',
        'role_created' => 'Role :name created successfully.',
        'role_renamed' => 'Role renamed from ":old" to ":new".',
        'role_permissions_updated' => 'Permissions updated for role :name.',
    ],

    // Audit log descriptions
    'audit_log' => [
        'view_pdf_contract' => 'Viewed PDF of Contract :number',
        'view_pdf_amendment' => 'Viewed PDF of Amendment :number',
        'created_entity' => 'Created :entity #:id',
        'updated_entity' => 'Updated :entity #:id',
        'deleted_entity' => 'Deactivated :entity #:id',
        'status_changed' => 'Changed status of :entity #:id: :from → :to',
        'user_seeded' => 'User :email ready with role: :role',
    ],

    'error' => [
        'forbidden' => 'You do not have permission to access this section.',
        'negative_amount' => 'The amount cannot be negative.',
        'different_currencies' => 'Cannot add different currencies.',
        'forbidden_action' => 'You do not have permission to perform this action.',
        'not_found' => 'Resource not found.',
        'validation_failed' => 'Validation error.',
        'invalid_credentials' => 'Invalid credentials.',
    ],
];
