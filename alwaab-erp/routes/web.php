<?php

use App\Http\Controllers\Web\Audit\AuditLogController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Chat\ConversationController;
use App\Http\Controllers\Web\Portal\PortalController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Http\Controllers\Web\CRM\ContactController;
use App\Http\Controllers\Web\CRM\LeadController;
use App\Http\Controllers\Web\Access\AccessController;
use App\Http\Controllers\Web\AIStudio\AIController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\FieldVisit\VisitController;
use App\Http\Controllers\Web\HR\AttendanceController;
use App\Http\Controllers\Web\HR\EmployeeController;
use App\Http\Controllers\Web\HR\EmployeeDocumentController;
use App\Http\Controllers\Web\HR\LeaveController;
use App\Http\Controllers\Web\Invoices\InvoiceController;
use App\Http\Controllers\Web\Inventory\InventoryController;
use App\Http\Controllers\Web\Inventory\ProductController;
use App\Http\Controllers\Web\Projects\ProjectController;
use App\Http\Controllers\Web\Notification\DirectiveController;
use App\Http\Controllers\Web\Notification\NotificationController;
use App\Http\Controllers\Web\Profile\ProfileController;
use App\Http\Controllers\Web\Quotations\QuotationController;
use App\Http\Controllers\Web\Quotations\RfqController;
use App\Http\Controllers\Web\Settings\SettingController;
use App\Http\Controllers\Web\Settings\UserController;
use Illuminate\Support\Facades\Route;

Route::post('locale', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:contacts.view')->group(function () {
        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    });
    Route::post('contacts', [ContactController::class, 'store'])
        ->middleware('permission:contacts.create')->name('contacts.store');
    Route::put('contacts/{contact}', [ContactController::class, 'update'])
        ->middleware('permission:contacts.update')->name('contacts.update');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])
        ->middleware('permission:contacts.delete')->name('contacts.destroy');

    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/preview-number', [InvoiceController::class, 'previewNumber'])->name('invoices.preview-number');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('invoices/{invoice}/excel', [InvoiceController::class, 'excel'])->name('invoices.excel');
    });
    Route::post('invoices', [InvoiceController::class, 'store'])
        ->middleware('permission:invoices.create')->name('invoices.store');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])
        ->middleware('permission:invoices.create')->name('invoices.update');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])
        ->middleware('permission:invoices.create')->name('invoices.send');

    Route::middleware('permission:leads.view')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    });
    Route::post('leads', [LeadController::class, 'store'])
        ->middleware('permission:leads.create')->name('leads.store');
    Route::post('leads/{lead}/stage', [LeadController::class, 'updateStage'])
        ->middleware('permission:leads.update')->name('leads.stage');

    Route::middleware('permission:projects.view')->group(function () {
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/map', [ProjectController::class, 'map'])->name('projects.map');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    });
    Route::post('projects', [ProjectController::class, 'store'])
        ->middleware('permission:projects.create')->name('projects.store');

    Route::middleware('permission:products.view')->group(function () {
        Route::get('catalog/products', [ProductController::class, 'index'])->name('catalog.products.index');
    });
    Route::put('catalog/products/{product}/price', [ProductController::class, 'updatePrice'])
        ->middleware('permission:products.update')->name('catalog.products.price');

    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    });
    Route::put('inventory/{inventory}/stock', [InventoryController::class, 'updateStock'])
        ->middleware('permission:inventory.manage')->name('inventory.stock');

    Route::middleware('permission:quotations.view')->group(function () {
        Route::get('rfqs', [RfqController::class, 'index'])->name('rfqs.index');
        Route::get('rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
        Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    });
    Route::post('rfqs', [RfqController::class, 'store'])
        ->middleware('permission:quotations.create')->name('rfqs.store');
    Route::post('rfqs/{rfq}/boq', [RfqController::class, 'uploadBoq'])
        ->middleware('permission:quotations.create')->name('rfqs.boq');
    Route::post('rfqs/{rfq}/quotation', [RfqController::class, 'createQuotation'])
        ->middleware('permission:quotations.create')->name('rfqs.quotation');
    Route::post('quotations', [QuotationController::class, 'store'])
        ->middleware('permission:quotations.create')->name('quotations.store');
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])
        ->middleware('permission:quotations.approve')->name('quotations.approve');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])
        ->middleware('permission:quotations.create')->name('quotations.send');

    Route::middleware('permission:visits.view')->group(function () {
        Route::get('field-visits', [VisitController::class, 'index'])->name('field-visits.index');
        Route::get('field-visits/map', [VisitController::class, 'map'])->name('field-visits.map');
    });
    Route::get('field-visits/live-gps', [VisitController::class, 'liveGps'])
        ->middleware('permission:visits.manage')->name('field-visits.live-gps');
    Route::get('field-visits/create', [VisitController::class, 'create'])
        ->middleware('permission:visits.create')->name('field-visits.create');
    Route::middleware('permission:visits.view')->group(function () {
        Route::get('field-visits/{visit}', [VisitController::class, 'show'])->name('field-visits.show');
    });
    Route::post('field-visits', [VisitController::class, 'store'])
        ->middleware('permission:visits.create')->name('field-visits.store');
    Route::put('field-visits/{visit}/location', [VisitController::class, 'updateLocation'])
        ->middleware('permission:visits.create')->name('field-visits.location');
    Route::post('field-visits/{visit}/photos', [VisitController::class, 'uploadPhoto'])
        ->middleware('permission:visits.create')->name('field-visits.photos');
    Route::post('field-visits/{visit}/complete', [VisitController::class, 'complete'])
        ->middleware('permission:visits.create')->name('field-visits.complete');

    Route::middleware('permission:audit.view')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });

    Route::middleware('permission:hr.view')->group(function () {
        Route::get('hr/employees', [EmployeeController::class, 'index'])->name('hr.employees');
        Route::get('hr/employees/{employee}', [EmployeeController::class, 'show'])->name('hr.employees.show');
        Route::get('hr/attendance', [AttendanceController::class, 'index'])->name('hr.attendance');
        Route::get('hr/leaves', [LeaveController::class, 'index'])->name('hr.leaves');
        Route::post('hr/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('hr.employees.documents.store');
        Route::delete('hr/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('hr.employees.documents.destroy');
    });
    Route::put('hr/employees/{employee}', [EmployeeController::class, 'update'])
        ->middleware('permission:hr.manage')->name('hr.employees.update');
    Route::post('hr/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->middleware('permission:hr.view')->name('hr.check-in');
    Route::post('hr/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->middleware('permission:hr.view')->name('hr.check-out');
    Route::post('hr/leaves', [LeaveController::class, 'store'])
        ->middleware('permission:hr.view')->name('hr.leaves.store');
    Route::post('hr/leaves/{leave}/approve', [LeaveController::class, 'approve'])
        ->middleware('permission:hr.manage')->name('hr.leaves.approve');
    Route::post('hr/leaves/{leave}/reject', [LeaveController::class, 'reject'])
        ->middleware('permission:hr.manage')->name('hr.leaves.reject');

    Route::middleware('permission:access-ai-studio')->prefix('ai-studio')->name('ai-studio.')->group(function () {
        Route::get('/', [AIController::class, 'index'])->name('index');
        Route::post('text', [AIController::class, 'generateText'])->name('text');
        Route::post('image', [AIController::class, 'generateImage'])->name('image');
        Route::post('video', [AIController::class, 'generateVideo'])->name('video');
        Route::get('contents/{content}/download', [AIController::class, 'download'])->name('download');
        Route::post('credits/top-up', [AIController::class, 'topUpCredits'])
            ->middleware('permission:users.manage')->name('credits.top-up');
    });

    Route::middleware('permission:chat.view')->group(function () {
        Route::get('chat', [ConversationController::class, 'index'])->name('chat.index');
    });
    Route::post('chat', [ConversationController::class, 'store'])
        ->middleware('permission:chat.send')->name('chat.store');
    Route::post('chat/{conversation}/messages', [ConversationController::class, 'send'])
        ->middleware('permission:chat.send')->name('chat.send');

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('permission:users.view')->prefix('access')->name('access.')->group(function () {
        Route::get('/', [AccessController::class, 'index'])->name('index');
        Route::get('users/{user}', [AccessController::class, 'show'])->name('users.show');
        Route::post('users', [AccessController::class, 'store'])
            ->middleware('permission:users.create')->name('users.store');
        Route::put('users/{user}', [AccessController::class, 'update'])
            ->middleware('permission:users.update')->name('users.update');
        Route::put('roles/{role}/permissions', [AccessController::class, 'updateRolePermissions'])
            ->middleware('permission:roles.manage')->name('roles.permissions');
    });

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('directives', [DirectiveController::class, 'index'])->name('directives.index');
        Route::post('directives', [DirectiveController::class, 'store'])->name('directives.store');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    Route::prefix('portal')->middleware(['auth', 'role:customer'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('portal.dashboard');
        Route::get('catalog', [PortalController::class, 'catalog'])->name('portal.catalog');
        Route::get('rfqs', [PortalController::class, 'rfqs'])->name('portal.rfqs');
        Route::post('rfqs', [PortalController::class, 'storeRfq'])->name('portal.rfqs.store');
        Route::get('quotations', [PortalController::class, 'quotations'])->name('portal.quotations');
    });
});
