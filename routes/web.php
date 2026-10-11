<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Middleware\TwoFactorMiddleware;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\GamesCategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\EmbeddableGameController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\User\GameManagementController;
use App\Http\Controllers\Admin\PointManagementController;
use App\Http\Controllers\User\UserLevelController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Auth\TelegramController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LottaryController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\TwoFactorController;
use App\Http\Controllers\Admin\LogController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\UtilityController;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Route::get('/test-broadcast', [UtilityController::class, 'testBroadcast']);

Route::redirect('/home', '/');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/private-files/{path}', [SecureFileController::class, 'show'])
    ->where('path', '.*')
    ->name('files.private');
Route::get('/countries', [RegisterController::class, 'getCountries']);

Route::get('/', [GameController::class, 'viewIndex'])->name('games.viewIndex')->middleware('2fa');
Route::get('/play-games', [EmbeddableGameController::class, 'index'])->name('playable.index');
Route::get('/play-games/load', [EmbeddableGameController::class, 'homeBatch'])->name('playable.load');
Route::get('/play-games/{id}', [EmbeddableGameController::class, 'play'])->where('id', '[A-Za-z0-9_-]+')->name('playable.play');
Route::get('/free-games', [GameController::class, 'viewfreegames'])->name('free.games.index')->middleware('2fa');

// Google Authentication
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Facebook Authentication
Route::get('auth/facebook', [FacebookController::class, 'redirectToFacebook'])->name('facebook.login');
Route::get('auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);

// Telegram Authentication
Route::get('/auth/telegram/callback', [TelegramController::class, 'handleTelegramCallback'])->name('telegram.callback');

// Bunus View

// Lottery Routes
Route::get('/lottary', [LottaryController::class, 'view'])->name('user.lottaries.view')->middleware('ban');
Route::get('/lottary/drwn', [LottaryController::class, 'viewdrwn'])->name('user.lottaries.drew')->middleware('ban');
Route::get('/lottary/{id}', [LottaryController::class, 'show'])->name('user.lottaries.show')->middleware('ban');
Route::post('/lottaries/{id}/buy', [LottaryController::class, 'buy'])->name('user.lottaries.buy')->middleware('auth', 'ban');
Route::get('/user/my-lottaries', [LottaryController::class, 'myLottaries'])->name('user.lottaries.my')->middleware('auth', 'ban');
Route::get('/user/my-lottaries-win', [LottaryController::class, 'mywin'])->name('user.lottaries.mywin')->middleware('auth', 'ban');
Route::get('/ticket/{ticketNumber}', [LottaryController::class, 'viewTicket'])->name('user.lottaries.view_ticket')->middleware('auth', 'ban');
Route::get('/lottaries/{lottaryId}/winners', [LottaryController::class, 'userwinners'])->name('lottaries.winners')->middleware('ban');

// Authentication Routes

Auth::routes();

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/registration', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/registration', [RegisterController::class, 'register'])->name('registration.store');
Route::get('/verify', [VerificationController::class, 'showVerifyForm'])->name('verify.form');
Route::post('/verify', [VerificationController::class, 'verifyCode'])->name('verify.code');
Route::post('/resend-verification', [VerificationController::class, 'resendCode'])->name('verify.resend');
Route::post('/quick-register', [RegisterController::class, 'quickRegistration'])->name('quick.register');

// Reset Password Routes
Route::view('forgot-password', 'auth.forgot-password')->name('auth.forgotPasswordForm');
Route::post('search-user', [ForgotPasswordController::class, 'searchUser'])->name('auth.searchUser');

// Contact Us

Route::get('/contact', [ContactController::class, 'create'])->name('contacts.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/messages', [ContactController::class, 'index'])->name('contacts.index');

// basic game routes

// Route::get('/category/{slug}', [GameController::class, 'viewCategory'])->name('category.view')->middleware('ban');

Route::get('/category/{id}', [GameController::class, 'viewCategory'])->name('category.view')->middleware('ban');
Route::get('/category/{category_id}/tag/{tag_id}', [GameController::class, 'viewTag'])->name('tag.view')->middleware('ban');
Route::get('/games/new-come', [GameManagementController::class, 'viewNewGames'])->name('games.viewNewGames')->middleware('ban');
Route::get('/games/popular', [GameManagementController::class, 'popularGames'])->name('games.popular')->middleware('ban');
Route::get('/games/trending', [GameManagementController::class, 'trendingGames'])->name('games.trending')->middleware('ban');
Route::get('/games/recommended', [GameManagementController::class, 'recommendedGames'])->name('games.recommended')->middleware('ban');

Route::get('/games/open/{id}', [GameController::class, 'open'])->name('games.open')->middleware('auth', 'ban');
// Route::get('/games/open/{slug}', [GameController::class, 'open'])->name('games.open')->middleware('auth', 'ban');

    // Two Factor Authentication
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup') ->middleware('auth', 'ban');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable')->middleware('auth', 'ban');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable')->middleware('auth', 'ban');
    Route::get('/2fa/verify', [TwoFactorController::class, 'verifyForm'])->name('2fa.verify')->middleware('auth', 'ban');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post')->middleware('auth', 'ban');
    Route::post('/2fa/toggle', [TwoFactorController::class, 'toggle2fa'])->name('2fa.toggle')->middleware('auth', 'ban');

Route::prefix('user')->name('user.')->middleware(['auth','2fa', 'ban'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Management
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');

    // User Notification Management
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    //games management
    Route::get('/games/last-played/', [GameManagementController::class, 'lastplay'])->name('games.lastplay');
    Route::post('/games/{id}/favorite', [GameManagementController::class, 'favorite'])->name('games.favorite');
    Route::delete('/games/{id}/unfavorite', [GameManagementController::class, 'unfavorite'])->name('games.unfavorite');
    Route::get('/games/favorites', [GameManagementController::class, 'viewFavorites'])->name('games.viewFavorites');
    Route::get('/games/bookmarks', [GameManagementController::class, 'bookmarks'])->name('games.bookmarks');
    Route::delete('/games/bookmarks/{bookmark}', [GameManagementController::class, 'removeBookmark'])->name('games.bookmarks.remove');
    

    //Level and Achivement
    Route::get('/games/level', [UserLevelController::class, 'getUserLevel'])->name('games.level');
    Route::get('/achievement', [PointManagementController::class, 'userAchievement'])->name('achievement');
    Route::get('/games/levels', [PointManagementController::class, 'allLevelsWithUserLevel'])->name('levels');

    // Game Open Management
    Route::post('/games/{id}/open/post', [GameController::class, 'postGameOpen'])->name('games.open.post');

    Route::get('/unlock-form', [UserController::class, 'showUnbanForm'])->name('unban.form');
    Route::post('/unban-submit', [UserController::class, 'submitUnbanDocuments'])->name('unban.submit');

});

Route::get('/sm-shagor/free-games/admin-main/control-back-office/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/sm-shagor/free-games/admin-main/control-back-office/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/sm-shagor/free-games/admin-main/control-back-office/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('/sm-shagor/free-games/admin-main/control-back-office/')->name('admin.')->middleware(['auth:admin'])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [AdminController::class, 'viewProfile'])->name('profile');

    Route::get('/profile/edit', [AdminController::class, 'editProfile'])->name('editProfile');
    Route::put('/profile/update', [AdminController::class, 'updateProfile'])->name('updateProfile');

    Route::get('/password/edit', [AdminController::class, 'editPassword'])->name('editPassword');
    Route::put('/password/update', [AdminController::class, 'updatePassword'])->name('updatePassword');

    Route::get('/settings/edit', [SettingController::class, 'edit'])->name('setting.edit');
    Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('setting.update');
    
    Route::view('/global/settings', 'admin.settings_page')->name('settings.global');

    Route::get('/countries', [AdminController::class, 'index'])->name('countries.index');
    Route::get('/countries/{id}/edit-currency', [AdminController::class, 'editCurrency'])->name('countries.editCurrency');
    Route::put('/countries/{id}/update-currency', [AdminController::class, 'updateCurrency'])->name('countries.updateCurrency');
    Route::post('/countries/{id}/status', [AdminController::class, 'updateStatus'])->name('countries.updateStatus');

    // Banner Management

    Route::get('banners', [BannerController::class, 'bannerindex'])->name('banners.index');
    Route::get('banners/create', [BannerController::class, 'bannercreate'])->name('banners.create');
    Route::post('banners', [BannerController::class, 'bannerstore'])->name('banners.store');
    Route::get('banners/{banner}/edit', [BannerController::class, 'banneredit'])->name('banners.edit');
    Route::put('banners/{banner}', [BannerController::class, 'bannerupdate'])->name('banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'bannerdestroy'])->name('banners.destroy');

    // Games Categories and Tags Management

    Route::get('/games-categories', [GamesCategoryController::class, 'index'])->name('games_categories.index');
    Route::get('/games-categories/create', [GamesCategoryController::class, 'create'])->name('games_categories.create');
    Route::post('/games-categories', [GamesCategoryController::class, 'store'])->name('games_categories.store');
    Route::get('/games-categories/{id}/edit', [GamesCategoryController::class, 'edit'])->name('games_categories.edit');
    Route::put('/games-categories/{id}', [GamesCategoryController::class, 'update'])->name('games_categories.update');
    Route::delete('/games-categories/{id}', [GamesCategoryController::class, 'destroy'])->name('games_categories.destroy');

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/tags/create', [TagController::class, 'create'])->name('tags.create');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/tags/{id}/edit', [TagController::class, 'edit'])->name('tags.edit');
    Route::put('/tags/{id}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{id}', [TagController::class, 'destroy'])->name('tags.destroy');

    // Games Management

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{id}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::put('/games/{id}', [GameController::class, 'update'])->name('games.update');
    Route::delete('/games/{id}', [GameController::class, 'destroy'])->name('games.destroy');

    // User Management

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/pending', [UserController::class, 'pending'])->name('users.pending');
    Route::get('/users/active', [UserController::class, 'active'])->name('users.active');
    Route::get('/users/{id}/logins', [UserController::class, 'logins'])->name('users.logins');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/user/{id}', [UserController::class, 'userView'])->name('users.view');
    Route::get('/users/banned', [UserController::class, 'userBan'])->name('users.banned');
    Route::get('/users/banned/approved', [UserController::class, 'viewApprovedbanUser'])->name('users.banned.approved');
    Route::get('/users/approved/{userId}/documents', [UserController::class, 'viewApprovedUserDocuments'])->name('users.approved.documents');
    Route::get('users/ban/{id}', [UserController::class, 'banCreate'])->name('users.ban.create');
    Route::post('users/ban/{id}', [UserController::class, 'banStore'])->name('users.ban.store');

    // Game Points Management

    Route::get('/points', [PointManagementController::class, 'index'])->name('points.index');
    Route::get('/points/create', [PointManagementController::class, 'create'])->name('points.create');
    Route::post('/points/store', [PointManagementController::class, 'store'])->name('points.store');

    Route::get('/points/{id}/edit', [PointManagementController::class, 'edit'])->name('points.edit');
    Route::put('/points/{id}/update', [PointManagementController::class, 'update'])->name('points.update');
    Route::delete('/points/{id}/delete', [PointManagementController::class, 'delete'])->name('points.delete');

    // Level Management

    Route::get('levels', [PointManagementController::class, 'levelIndex'])->name('levels.index');
    Route::get('levels/create', [PointManagementController::class, 'levelCreate'])->name('levels.create');
    Route::post('levels/store', [PointManagementController::class, 'levelStore'])->name('levels.store');
    Route::get('levels/{id}/edit', [PointManagementController::class, 'levelEdit'])->name('levels.edit');
    Route::put('levels/{id}/update', [PointManagementController::class, 'levelupdate'])->name('levels.update');
    Route::delete('levels/{id}/delete', [PointManagementController::class, 'levelDelete'])->name('levels.delete');

    // Page Management

    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('pages/store', [PageController::class, 'store'])->name('pages.store');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');

    // Admin Contact Messages

    Route::get('/contacts', [ContactController::class, 'adminview'])->name('contacts.index');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/contacts/{id}/reply', [ContactController::class, 'reply'])->name('contacts.reply');
    Route::post('/contacts/{id}/status', [ContactController::class, 'updateStatus'])->name('contacts.updateStatus');

    // Lottary Management
    Route::get('lottaries', [LottaryController::class, 'index'])->name('lottaries.index');
    Route::get('lottaries/create', [LottaryController::class, 'create'])->name('lottaries.create');
    Route::post('lottaries', [LottaryController::class, 'store'])->name('lottaries.store');
    Route::get('lottaries/{lottary}/edit', [LottaryController::class, 'edit'])->name('lottaries.edit');
    Route::put('lottaries/{lottary}', [LottaryController::class, 'update'])->name('lottaries.update');
    Route::delete('lottaries/{lottary}', [LottaryController::class, 'destroy'])->name('lottaries.destroy');

    Route::get('/lottaries/{lottary}/prizes/create', [LottaryController::class, 'createPrizes'])->name('lottaries.prizes.create');
    Route::post('/lottaries/{lottary}/prizes', [LottaryController::class, 'storePrizes'])->name('lottaries.prizes.store');
    Route::get('/lottaries/{lottary}/prizes/edit', [LottaryController::class, 'editPrizes'])->name('lottaries.prizes.edit');
    Route::put('/lottaries/{lottary}/prizes/update', [LottaryController::class, 'updatePrizes'])->name('lottaries.prizes.update');

    Route::get('/lottaries/transactions', [LottaryController::class, 'transactions'])->name('lottaries.transactions');
    Route::get('/lottaries/{lottary}/transactions', [LottaryController::class, 'transactionsshow'])->name('lottaries.transactions.show');
    Route::post('/lottary/{id}/draw', [LottaryController::class, 'drawLotteryWinners'])->name('lottary.draw');
    Route::get('/lottaries/{lottaryId}/winners', [LottaryController::class, 'winners'])->name('lottaries.winners');

    Route::get('/ban-documents/users', [UserController::class, 'listUsersWithSubmissions'])->name('ban_documents.users');
    Route::get('/ban-documents/pending/{user}', [UserController::class, 'viewPendingSubmissions'])->name('ban_documents.pending');
    Route::put('/ban-documents/update/{user}', [UserController::class, 'updateBanDocuments'])->name('ban_documents.update');

    
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [LogController::class, 'clearLaravelLog'])->name('logs.clear');
    
    Route::get('/scheduler-logs', [LogController::class, 'scheduler'])->name('scheduler.logs.index');
    Route::post('/scheduler-logs/clear', [LogController::class, 'clearSchedulerLog'])->name('scheduler.logs.clear');

});

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');
