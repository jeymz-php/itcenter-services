<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ServiceRequestController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\Admin\ComputerController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\GuestRequestController;
use App\Http\Controllers\Admin\GuestRequestController as AdminGuestRequestController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;

Route::middleware('auth')->get('/user/notifications/poll', [UserNotificationController::class, 'poll'])->name('user.notifications.poll');

Route::post('/requests/detect-pages', [ServiceRequestController::class, 'detectPages'])->name('requests.detect-pages');

// ── PUBLIC AUTH ──
Route::get('/',          [AuthController::class, 'showLogin'])->name('login');
Route::get('/login',     [AuthController::class, 'showLogin']);
Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password',  [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showForm'])->name('password.reset.form');
Route::post('/reset-password',        [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// ── USER ROUTES ──
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // Profile
    Route::get('/profile',                     [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update',             [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password',    [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/update-photo',       [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/request-deactivate', [ProfileController::class, 'requestDeactivate'])->name('profile.deactivate');
    Route::post('/profile/request-reactivate', [ProfileController::class, 'requestReactivate'])->name('profile.reactivate');
    Route::post('/profile/request-delete',     [ProfileController::class, 'requestDelete'])->name('profile.delete');

    // Service Requests (user)
    Route::get('/requests/printing',   [ServiceRequestController::class, 'printing'])->name('requests.printing');
    Route::post('/requests/printing',  [ServiceRequestController::class, 'storePrinting'])->name('requests.printing.store');
    Route::get('/requests/{serviceRequest}/printing/edit', [ServiceRequestController::class, 'editPrinting'])->name('requests.printing.edit');
    Route::put('/requests/{serviceRequest}/printing',      [ServiceRequestController::class, 'updatePrinting'])->name('requests.printing.update');
    Route::get('/requests/photocopy',  [ServiceRequestController::class, 'photocopy'])->name('requests.photocopy');
    Route::post('/requests/photocopy', [ServiceRequestController::class, 'storePhotocopy'])->name('requests.photocopy.store');
    Route::get('/requests/research',   [ServiceRequestController::class, 'research'])->name('requests.research');
    Route::post('/requests/research',  [ServiceRequestController::class, 'storeResearch'])->name('requests.research.store');
    Route::get('/requests/history',    [ServiceRequestController::class, 'history'])->name('requests.history');
    Route::get('/requests/{serviceRequest}/details', [ServiceRequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{serviceRequest}/file',    [ServiceRequestController::class, 'openFile'])->name('requests.file');
    Route::post('/requests/{serviceRequest}/request-extend', [ServiceRequestController::class, 'requestExtend'])->name('requests.request-extend');
    Route::post('/ratings', [App\Http\Controllers\User\RatingController::class, 'store'])->name('ratings.store');

    // Messages
    Route::get('/messages',              [App\Http\Controllers\User\MessageController::class, 'index'])->name('user.messages.index');
    Route::post('/messages/send',        [App\Http\Controllers\User\MessageController::class, 'send'])->name('user.messages.send');
    Route::get('/messages/poll',         [App\Http\Controllers\User\MessageController::class, 'poll'])->name('user.messages.poll');
    Route::get('/messages/unread-count', [App\Http\Controllers\User\MessageController::class, 'unreadCount'])->name('user.messages.unread-count');
    Route::post('/messages/typing', [App\Http\Controllers\User\MessageController::class, 'typing'])->name('user.messages.typing');
    Route::post('/messages/end-session', [App\Http\Controllers\User\MessageController::class, 'endSession'])->name('user.messages.end-session');

    // PDF report
    Route::get('/requests/{serviceRequest}/report', [ServiceRequestController::class, 'downloadReport'])->name('requests.report');
});

// ── ADMIN AUTH ──
Route::get('/admin_login',   [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin_login',  [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin_logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Real-time polling endpoints
Route::get('/admin/notifications/poll', [NotificationController::class, 'poll'])->name('admin.notifications.poll');
Route::middleware('auth')->get('/user/notifications/poll', [App\Http\Controllers\User\NotificationController::class, 'poll'])->name('user.notifications.poll');

// ── ADMIN PANEL ──
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('dashboard');

// ── MESSAGES ──
Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/',              [App\Http\Controllers\Admin\MessageController::class, 'index'])->name('index');
    Route::post('/send',         [App\Http\Controllers\Admin\MessageController::class, 'send'])->name('send');
    Route::get('/poll',          [App\Http\Controllers\Admin\MessageController::class, 'poll'])->name('poll');
    Route::post('/typing',       [App\Http\Controllers\Admin\MessageController::class, 'typing'])->name('typing');
    Route::get('/search-users',  [App\Http\Controllers\Admin\MessageController::class, 'searchUsers'])->name('search-users');
    Route::get('/unread-count',  [App\Http\Controllers\Admin\MessageController::class, 'unreadCount'])->name('unread-count');
    Route::post('/end-session', [App\Http\Controllers\Admin\MessageController::class, 'endSession'])->name('end-session');
});

    // Notifications
    Route::get('/notifications',               [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{n}/read',     [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/unread-count',  [NotificationController::class, 'unreadCount'])->name('notifications.count');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // ── USER MANAGEMENT ──
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                    [UserManagementController::class, 'index'])->name('index');
        Route::post('/',                   [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}',              [UserManagementController::class, 'show'])->name('show');
        Route::put('/{user}',              [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}',           [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/approve',     [UserManagementController::class, 'approve'])->name('approve');
        Route::post('/{user}/reject',      [UserManagementController::class, 'reject'])->name('reject');
        Route::post('/{user}/activate',    [UserManagementController::class, 'activate'])->name('activate');
        Route::post('/{user}/deactivate',  [UserManagementController::class, 'deactivate'])->name('deactivate');
        Route::post('/{user}/archive',     [UserManagementController::class, 'archive'])->name('archive');
        Route::post('/{user}/restrict-research',   [UserManagementController::class, 'restrictResearch'])->name('restrict-research');
        Route::post('/{user}/unrestrict-research', [UserManagementController::class, 'unrestrictResearch'])->name('unrestrict-research');
        Route::post('/{user}/transfer-role',       [UserManagementController::class, 'transferRole'])->name('transfer-role');
        Route::post('/{user}/reset-password',      [UserManagementController::class, 'resetPassword'])->name('reset-password');
    });

    // ── ACCOUNT REQUESTS (deactivate/reactivate/delete) ──
    Route::prefix('account-requests')->name('account-requests.')->group(function () {
        Route::post('/{accountRequest}/approve', [UserManagementController::class, 'approveRequest'])->name('approve');
        Route::post('/{accountRequest}/reject',  [UserManagementController::class, 'rejectRequest'])->name('reject');
    });

    // ── ADMIN MANAGEMENT ──
    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/',                [AdminManagementController::class, 'index'])->name('index');
        Route::post('/',               [AdminManagementController::class, 'store'])->name('store');
        Route::put('/{admin}',         [AdminManagementController::class, 'update'])->name('update');
        Route::delete('/{admin}',      [AdminManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{admin}/toggle', [AdminManagementController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{admin}/transfer-student', [AdminManagementController::class, 'transferToStudent'])->name('transfer-student');
    });

    // ── SYSTEM SETTINGS ──
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',  [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::put('/',  [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');

        Route::put('/hours',       [App\Http\Controllers\Admin\SettingsController::class, 'updateHours'])->name('hours');
        Route::put('/version',     [App\Http\Controllers\Admin\SettingsController::class, 'updateVersion'])->name('version');
        Route::put('/maintenance', [App\Http\Controllers\Admin\SettingsController::class, 'updateMaintenance'])->name('maintenance');
    });

    // ── SERVICE REQUESTS (admin) ──
    Route::prefix('service-requests')->name('service-requests.')->group(function () {
        Route::get('/',                          [AdminServiceRequestController::class, 'index'])->name('index');
        Route::get('/{serviceRequest}',          [AdminServiceRequestController::class, 'show'])->name('show');
        Route::post('/{serviceRequest}/approve', [AdminServiceRequestController::class, 'approve'])->name('approve');
        Route::post('/{serviceRequest}/reject',  [AdminServiceRequestController::class, 'reject'])->name('reject');
        Route::post('/{serviceRequest}/complete',   [AdminServiceRequestController::class, 'complete'])->name('complete');
        Route::post('/{serviceRequest}/processing', [AdminServiceRequestController::class, 'processing'])->name('processing');
        Route::post('/{serviceRequest}/assign-pc',       [AdminServiceRequestController::class, 'assignPC'])->name('assign-pc');
        Route::post('/{serviceRequest}/extend-session',  [AdminServiceRequestController::class, 'extendSession'])->name('extend-session');
        Route::post('/{serviceRequest}/end-session',     [AdminServiceRequestController::class, 'endSession'])->name('end-session');
        Route::delete('/{serviceRequest}', [AdminServiceRequestController::class, 'destroy'])->name('destroy');
        Route::get('/{serviceRequest}/session-status',   [AdminServiceRequestController::class, 'sessionStatus'])->name('session-status');
        Route::get('/{serviceRequest}/report', [AdminServiceRequestController::class, 'downloadReport'])->name('report');
    });

    // ── COMPUTERS ──
    Route::prefix('computers')->name('computers.')->group(function () {
        Route::get('/',                    [ComputerController::class, 'index'])->name('index');
        Route::post('/',                   [ComputerController::class, 'store'])->name('store');
        Route::put('/{computer}',          [ComputerController::class, 'update'])->name('update');
        Route::post('/{computer}/activate',   [ComputerController::class, 'activate'])->name('activate');
        Route::post('/{computer}/deactivate', [ComputerController::class, 'deactivate'])->name('deactivate');
        Route::delete('/{computer}',       [ComputerController::class, 'destroy'])->name('destroy');
    });

    // ── INVENTORY ──
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',                       [InventoryController::class, 'index'])->name('index');
        Route::post('/',                      [InventoryController::class, 'store'])->name('store');
        Route::put('/{inventoryItem}',        [InventoryController::class, 'update'])->name('update');
        Route::post('/{inventoryItem}/stock', [InventoryController::class, 'addStock'])->name('stock');
        Route::post('/{inventoryItem}/reduce-stock', [InventoryController::class, 'reduceStock'])->name('reduce-stock');
        Route::get('/{inventoryItem}/logs', [InventoryController::class, 'logs'])->name('logs');
        Route::delete('/{inventoryItem}',     [InventoryController::class, 'destroy'])->name('destroy');
    });

    // ── REPORTS ──
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');

    Route::get('/review-ratings', [App\Http\Controllers\Admin\RatingController::class, 'index'])->name('review-ratings.index');

    // PUBLIC ADMIN PREFIX GROUP
    Route::prefix('guest-requests')->name('guest-requests.')->group(function () {
        Route::get('/',                           [AdminGuestRequestController::class, 'index'])->name('index');
        Route::get('/{guestRequest}',             [AdminGuestRequestController::class, 'show'])->name('show');
        Route::post('/{guestRequest}/approve',    [AdminGuestRequestController::class, 'approve'])->name('approve');
        Route::post('/{guestRequest}/reject',     [AdminGuestRequestController::class, 'reject'])->name('reject');
        Route::post('/{guestRequest}/processing', [AdminGuestRequestController::class, 'processing'])->name('processing');
        Route::post('/{guestRequest}/complete',   [AdminGuestRequestController::class, 'complete'])->name('complete');
        Route::post('/{guestRequest}/assign-pc',  [AdminGuestRequestController::class, 'assignPC'])->name('assign-pc');
        Route::post('/{guestRequest}/end-session',[AdminGuestRequestController::class, 'endSession'])->name('end-session');
        Route::get('/{guestRequest}/session-status',[AdminGuestRequestController::class, 'sessionStatus'])->name('session-status');
    });

});

// ── PUBLIC GUEST REQUESTS ──
Route::get('/public-request',         [GuestRequestController::class, 'index'])->name('public.request');
Route::post('/public-request',        [GuestRequestController::class, 'store'])->name('public.request.store');
Route::get('/public-request/usage',   [GuestRequestController::class, 'checkUsage'])->name('public.request.usage');
Route::post('/public-request/detect-pages', [GuestRequestController::class, 'detectPages'])->name('public.request.detect-pages');
Route::get('/public-request/success', [GuestRequestController::class, 'success'])->name('public.request.success');
Route::post('/public-request/ratings', [GuestRequestController::class, 'storeRating'])->name('public.request.ratings');
Route::get('/track',                  [GuestRequestController::class, 'track'])->name('public.track');
Route::get('/track/session-status/{guestRequest}', [GuestRequestController::class, 'publicSessionStatus'])->name('public.session-status');