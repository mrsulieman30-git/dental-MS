<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    // Patient Management
    case PATIENTS_READ = 'patients:read';
    case PATIENTS_WRITE = 'patients:write';
    case PATIENTS_DELETE = 'patients:delete';
    
    // Clinical
    case CHART_READ = 'chart:read';
    case CHART_WRITE = 'chart:write';
    case PERIO_READ = 'perio:read';
    case PERIO_WRITE = 'perio:write';
    case NOTES_READ = 'notes:read';
    case NOTES_WRITE = 'notes:write';
    case NOTES_LOCK = 'notes:lock';
    case VITAL_SIGNS_READ = 'vital_signs:read';
    case VITAL_SIGNS_WRITE = 'vital_signs:write';
    
    // Prescriptions
    case PRESCRIPTIONS_READ = 'prescriptions:read';
    case PRESCRIPTIONS_CREATE = 'prescriptions:create';
    case PRESCRIPTIONS_CANCEL = 'prescriptions:cancel';
    
    // Treatment Plans
    case TREATMENT_PLANS_READ = 'treatment_plans:read';
    case TREATMENT_PLANS_WRITE = 'treatment_plans:write';
    case TREATMENT_PLANS_APPROVE = 'treatment_plans:approve';
    
    // Insurance & Claims
    case INSURANCE_READ = 'insurance:read';
    case INSURANCE_WRITE = 'insurance:write';
    case CLAIMS_READ = 'claims:read';
    case CLAIMS_CREATE = 'claims:create';
    case CLAIMS_SUBMIT = 'claims:submit';
    case ERA_POST = 'era:post';
    
    // Billing
    case BILLING_READ = 'billing:read';
    case BILLING_POST_PAYMENT = 'billing:post_payment';
    case BILLING_ADJUSTMENTS = 'billing:adjustments';
    case BILLING_WRITEOFF = 'billing:writeoff';
    case STATEMENTS_GENERATE = 'statements:generate';
    
    // Imaging
    case IMAGING_READ = 'imaging:read';
    case IMAGING_UPLOAD = 'imaging:upload';
    case IMAGING_DELETE = 'imaging:delete';
    case AI_ANALYSIS_VIEW = 'ai_analysis:view';
    
    // Reports
    case REPORTS_VIEW = 'reports:view';
    case REPORTS_ADMIN = 'reports:admin';
    
    // Staff & Users
    case STAFF_READ = 'staff:read';
    case STAFF_MANAGE = 'staff:manage';
    
    // Settings
    case SETTINGS_READ = 'settings:read';
    case SETTINGS_ADMIN = 'settings:admin';
    
    // Audit
    case AUDIT_VIEW = 'audit:view';
    
    // Scheduling
    case SCHEDULE_READ = 'schedule:read';
    case SCHEDULE_WRITE = 'schedule:write';
    case WAITLIST_MANAGE = 'waitlist:manage';
    
    // Recall
    case RECALL_READ = 'recall:read';
    case RECALL_WRITE = 'recall:write';
    
    // Labs
    case LAB_CASES_READ = 'lab_cases:read';
    case LAB_CASES_WRITE = 'lab_cases:write';
    
    // Referrals
    case REFERRALS_READ = 'referrals:read';
    case REFERRALS_WRITE = 'referrals:write';
    
    // Inventory
    case INVENTORY_READ = 'inventory:read';
    case INVENTORY_WRITE = 'inventory:write';
    case INVENTORY_PO_CREATE = 'inventory:po_create';
    
    // Forms
    case FORMS_READ = 'forms:read';
    case FORMS_WRITE = 'forms:write';
    
    // Communications
    case COMMUNICATIONS_READ = 'communications:read';
    case COMMUNICATIONS_SEND = 'communications:send';
    case CAMPAIGNS_MANAGE = 'campaigns:manage';
}
