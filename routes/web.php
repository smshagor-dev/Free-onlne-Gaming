<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Middleware\CheckKyc;
use App\Http\Middleware\CheckDepositForWithdraw;
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
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\User\KycSubmissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DepositSettingController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\PointConverterController;
use App\Http\Controllers\LottaryController;
use App\Http\Controllers\Admin\BonusController;
use App\Http\Controllers\Admin\BonusDepositSettingController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\Admin\ReferralSettingController;
use App\Http\Controllers\User\UserReferralController;
use App\Http\Controllers\User\TwoFactorController;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\CasinoBonusController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\CashbackSettingController;
use App\Http\Controllers\CasinoCashbackController;
use App\Http\Controllers\Admin\VipBonusController;
use App\Http\Controllers\CasinoVipBonusController;
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
Route::get('/paid-games/home', [CasinoController::class, 'indexhome'])->name('casino.home');
Route::get('/countries', [RegisterController::class, 'getCountries']);

Route::get('/', [GameController::class, 'viewIndex'])->name('games.viewIndex')->middleware('2fa');
Route::get('/free-games', [GameController::class, 'viewfreegames'])->name('free.games.index')->middleware('2fa');

Route::get('/bonus', [BonusController::class, 'bonus'])->name('bonus.page')->middleware('auth', '2fa', 'ban');
Route::get('/my-bonus', [BonusDepositSettingController::class, 'showBonus'])->name('my.bonus')->middleware('auth', '2fa', 'ban');
Route::post('/bonus-popup-seen', [UtilityController::class, 'bonusPopupSeen']);


// Google Authentication
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Facebook Authentication
Route::get('auth/facebook', [FacebookController::class, 'redirectToFacebook'])->name('facebook.login');
Route::get('auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);

// Telegram Authentication
Route::get('/auth/telegram/callback', [TelegramController::class, 'handleTelegramCallback'])->name('telegram.callback');

// Casino Routes
Route::any('/casino', [CasinoController::class, 'index'])->name('casino.index')->middleware('ban');
Route::any('/casino/close', [CasinoController::class, 'closeCasinoGame'])->name('casino.close')->middleware('auth', 'ban');
Route::any('/casino/play', [CasinoController::class, 'casinoGameOpen'])->name('casino.play')->middleware('auth', 'ban');
Route::any('/casino/history', [CasinoController::class, 'casinoHistory'])->name('casino.history')->middleware('auth', 'ban');
Route::any('/casino/session/{session}', [CasinoController::class, 'casinoSession'])->name('casino.session')->middleware('auth', 'ban');

// Casino Bonus Routes
Route::any('/bonus-play-games', [CasinoBonusController::class, 'index'])->name('casino.bonus.index')->middleware('ban');
Route::any('/bonus-play/close', [CasinoBonusController::class, 'closeCasinoGame'])->name('casino.bonus.close')->middleware('auth', 'ban');
Route::any('/bonus-play/play', [CasinoBonusController::class, 'casinoGameOpen'])->name('casino.bonus.play')->middleware('auth', 'ban');
Route::any('/bonus-play/history', [CasinoBonusController::class, 'casinoHistory'])->name('casino.bonus.history')->middleware('auth', 'ban');
Route::any('/bonus-play/session/{session}', [CasinoBonusController::class, 'casinoSession'])->name('casino.bonus.session')->middleware('auth', 'ban');

// Casino Bonus Routes
Route::any('/vip-bonus-play-games', [CasinoVipBonusController::class, 'index'])->name('casino.vip.bonus.index')->middleware('ban');
Route::any('/vip-bonus-play/close', [CasinoVipBonusController::class, 'closeCasinoGame'])->name('casino.vip.bonus.close')->middleware('auth', 'ban');
Route::any('/vip-bonus-play/play', [CasinoVipBonusController::class, 'casinoGameOpen'])->name('casino.vip.bonus.play')->middleware('auth', 'ban');
Route::any('/vip-bonus-play/history', [CasinoVipBonusController::class, 'casinoHistory'])->name('casino.vip.bonus.history')->middleware('auth', 'ban');
Route::any('/vip-bonus-play/session/{session}', [CasinoVipBonusController::class, 'casinoSession'])->name('casino.vip.bonus.session')->middleware('auth', 'ban');

// Casino cashback Routes
Route::any('/casino-cashback-games', [CasinoCashbackController::class, 'index'])->name('casino.cashback.index')->middleware('ban');
Route::any('/casino-cashback/close', [CasinoCashbackController::class, 'closeCasinoGame'])->name('casino.cashback.close')->middleware('auth', 'ban');
Route::any('/casino-cashback/play', [CasinoCashbackController::class, 'casinoGameOpen'])->name('casino.cashback.play')->middleware('auth', 'ban');
Route::any('/casino-cashback/history', [CasinoCashbackController::class, 'casinoHistory'])->name('casino.cashback.history')->middleware('auth', 'ban');
Route::any('/casino-cashback/session/{session}', [CasinoCashbackController::class, 'casinoSession'])->name('casino.cashback.session')->middleware('auth', 'ban');

// Bunus View
Route::get('/bonuses', [BonusController::class, 'view'])->name('bonuses.index')->middleware('ban');
Route::get('/vip-bonuses', [VipBonusController::class, 'showbonus'])->name('vip.bonuses.index')->middleware('ban');
Route::post('/user/bonus/claim/welcome', [BonusController::class, 'claimWelcome'])->name('user.bonus.claim.welcome')->middleware('ban');
Route::post('/user/bonus/claim/first_deposit', [BonusController::class, 'claimFirstDeposit'])->name('user.bonus.claim.first_deposit')->middleware('ban');
Route::get('/promotions', [BonusDepositSettingController::class, 'View'])->name('promotion.index')->middleware('ban');


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
    Route::get('/transction', [DashboardController::class, 'transction'])->name('transction');

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
    
    Route::post('/paid-game/favourite', [CasinoController::class, 'toggleFavourite'])->name('game.toggleFavourite');
    Route::post('/paid-game/bookmark', [CasinoController::class, 'toggleBookmark'])->name('game.toggleBookmark');


    //Level and Achivement
    Route::get('/games/level', [UserLevelController::class, 'getUserLevel'])->name('games.level');
    Route::get('/achievement', [PointManagementController::class, 'userAchievement'])->name('achievement');
    Route::get('/games/levels', [PointManagementController::class, 'allLevelsWithUserLevel'])->name('levels');

    // Game Open Management
    Route::post('/games/{id}/open/post', [GameController::class, 'postGameOpen'])->name('games.open.post');

    Route::get('kyc/create', [KycSubmissionController::class, 'create'])->name('kyc.create');
    Route::post('kyc/store', [KycSubmissionController::class, 'store'])->name('kyc.store');
    Route::get('kyc', [KycSubmissionController::class, 'index'])->name('kyc.index')->middleware('checkprofile');

    // Deposit Management
    Route::get('deposit', [DepositController::class, 'index'])->name('deposit.index');
    Route::get('deposit/create/{gateway}', [DepositController::class, 'create'])->name('deposit.create');
    Route::post('deposit/store', [DepositController::class, 'store'])->name('deposit.store');
    Route::get('deposit/history', [DepositController::class, 'depositHistory'])->name('deposit.history');

    // Withdrawal Management
    Route::get('withdrew', [DepositController::class, 'withdrewindex'])->name('withdrew.index');
    Route::get('withdrew/create/{gateway}', [DepositController::class, 'withdrewcreate'])->name('withdrew.create')->middleware(CheckKyc::class, CheckDepositForWithdraw::class);
    Route::post('withdrew/store', [DepositController::class, 'withdrewstore'])->name('withdrew.store')->middleware(CheckKyc::class);;
    Route::get('withdrew/history', [DepositController::class, 'withdrewHistory'])->name('withdrew.history');

    // Point Conversion
    Route::get('/point-converter', [PointConverterController::class, 'showConverter'])->name('convert.points');
    Route::post('/convert-points', [PointConverterController::class, 'convertPoints'])->name('points.convert')->middleware(CheckKyc::class);

    // Referral Management
    Route::get('referral-earn', [UserReferralController::class, 'index'])->name('referral.index');
    Route::get('my-referral', [UserReferralController::class, 'myrefarral'])->name('referral.my-referral');
    Route::post('referral/collect-balance', [UserReferralController::class, 'collectBalance'])->name('referral.collectBalance')->middleware(CheckKyc::class);

    Route::get('/unlock-form', [UserController::class, 'showUnbanForm'])->name('unban.form');
    Route::post('/unban-submit', [UserController::class, 'submitUnbanDocuments'])->name('unban.submit');

    Route::get('/cashback', [CashbackSettingController::class, 'viewCashback'])->name('cashback.index');
    Route::post('/cashback/claim', [CashbackSettingController::class, 'claim'])->name('cashback.claim');


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

    // KYC Management
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/create', [KycController::class, 'create'])->name('kyc.create');
    Route::post('/kyc/store', [KycController::class, 'store'])->name('kyc.store');
    Route::get('/kyc/{kycField}/edit', [KycController::class, 'edit'])->name('kyc.edit');
    Route::put('/kyc/{kycField}/update', [KycController::class, 'update'])->name('kyc.update');
    Route::delete('/kyc/{kycField}/delete', [KycController::class, 'destroy'])->name('kyc.destroy');

    Route::get('/kyc/submissions/users', [KycController::class, 'kycUserList'])->name('kyc.submissions.users');
    Route::get('kyc/approved-users', [KycController::class, 'kycApprovedUserList'])->name('kyc.approved');
    Route::get('/kyc/user/{userId}/submissions', [KycController::class, 'viewUserSubmissions'])->name('kyc.view_submissions');
    Route::post('/kyc/user/{userId}/submissions', [KycController::class, 'updateSubmissions'])->name('kyc.update_submissions');

    // Admin Deposit Settings

    Route::get('deposit-settings', [DepositSettingController::class, 'index'])->name('deposit-settings.index');
    Route::get('deposit-settings/create', [DepositSettingController::class, 'create'])->name('deposit-settings.create');
    Route::post('deposit-settings/store', [DepositSettingController::class, 'store'])->name('deposit-settings.store');
    Route::get('deposit-settings/{gateway}/edit', [DepositSettingController::class, 'edit'])->name('deposit-settings.edit');
    Route::put('deposit-settings/{gateway}/update', [DepositSettingController::class, 'update'])->name('deposit-settings.update');
    Route::delete('deposit-settings/{gateway}/delete', [DepositSettingController::class, 'destroy'])->name('deposit-settings.destroy');

    Route::get('user/deposits/request', [DepositSettingController::class, 'viewdepositindex'])->name('user.deposits.index');
    Route::post('deposits/{id}/update-status', [DepositSettingController::class, 'updateStatus'])->name('user.deposits.update-status');
    Route::get('user/deposits/pending', [DepositSettingController::class, 'pending'])->name('user.deposits.pending');
    Route::get('user/deposits/approved', [DepositSettingController::class, 'approved'])->name('user.deposits.approved');
    Route::get('user/deposits/rejected', [DepositSettingController::class, 'rejected'])->name('user.deposits.rejected');

    Route::get('user/withdrews/request', [DepositSettingController::class, 'viewwithdrewindex'])->name('user.withdrews.index');
    Route::post('withdrews/{id}/update-status', [DepositSettingController::class, 'updateStatuswithdrew'])->name('user.withdrews.update-status');
    Route::get('user/withdrews/pending', [DepositSettingController::class, 'withdrewpending'])->name('user.withdrews.pending');
    Route::get('user/withdrews/approved', [DepositSettingController::class, 'withdrewapproved'])->name('user.withdrews.approved');
    Route::get('user/withdrews/rejected', [DepositSettingController::class, 'withdrewrejected'])->name('user.withdrews.rejected');

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


    // Admin Bonus Management
    Route::get('bonuses', [BonusController::class, 'index'])->name('bonuses.index');
    Route::get('bonuses/create', [BonusController::class, 'create'])->name('bonuses.create');
    Route::post('bonuses', [BonusController::class, 'store'])->name('bonuses.store');
    Route::get('bonuses/{id}/edit', [BonusController::class, 'edit'])->name('bonuses.edit');
    Route::put('bonuses/{id}', [BonusController::class, 'update'])->name('bonuses.update');
    Route::delete('bonuses/{id}', [BonusController::class, 'destroy'])->name('bonuses.destroy');

    // Bonus Deposit Settings
    Route::get('bonus/deposit-settings', [BonusDepositSettingController::class, 'index'])->name('depositsettings.index');
    Route::get('bonus/deposit-settings/create', [BonusDepositSettingController::class, 'create'])->name('depositsettings.create');
    Route::post('bonus/deposit-settings', [BonusDepositSettingController::class, 'store'])->name('depositsettings.store');
    Route::get('bonus/deposit-settings/{depositSetting}/edit', [BonusDepositSettingController::class, 'edit'])->name('depositsettings.edit');
    Route::put('bonus/deposit-settings/{depositSetting}', [BonusDepositSettingController::class, 'update'])->name('depositsettings.update');
    Route::delete('bonus/deposit-settings/{depositSetting}', [BonusDepositSettingController::class, 'destroy'])->name('depositsettings.destroy');

    // Referral Settings
    Route::get('referral', [ReferralSettingController::class, 'index'])->name('referral.index');
    Route::get('referral/create', [ReferralSettingController::class, 'create'])->name('referral.create');
    Route::post('referral/store', [ReferralSettingController::class, 'store'])->name('referral.store');
    Route::get('referral/edit/{id}', [ReferralSettingController::class, 'edit'])->name('referral.edit');
    Route::put('referral/update/{id}', [ReferralSettingController::class, 'update'])->name('referral.update');
    Route::delete('referral/delete/{id}', [ReferralSettingController::class, 'destroy'])->name('referral.destroy');

    Route::get('/user/transactions', [AdminController::class, 'userTransactions'])->name('user.transactions');

    Route::get('/ban-documents/users', [UserController::class, 'listUsersWithSubmissions'])->name('ban_documents.users');
    Route::get('/ban-documents/pending/{user}', [UserController::class, 'viewPendingSubmissions'])->name('ban_documents.pending');
    Route::put('/ban-documents/update/{user}', [UserController::class, 'updateBanDocuments'])->name('ban_documents.update');
    
    Route::get('active/bonus-user', [BonusDepositSettingController::class, 'active'])->name('bonus.active');
    Route::get('expired/bonus-user', [BonusDepositSettingController::class, 'expired'])->name('bonus.expired');
    
    Route::get('bonus-user/search', [BonusDepositSettingController::class, 'searchUser'])->name('bonus.search');
    Route::post('user-bonus/send', [BonusDepositSettingController::class, 'sendBonus'])->name('bonus.send');

    Route::post('/casino/cache', [CasinoController::class, 'cacheCasinoData'])->name('casino.cache');
    Route::post('/casino/bonus/cache', [CasinoBonusController::class, 'cacheCasinoData'])->name('casino.bonus.cache');
    Route::post('/casino/cashback/cache', [CasinoCashbackController::class, 'cacheCasinoData'])->name('casino.cashback.cache');
    Route::post('/casino/vip/cache', [CasinoVipBonusController::class, 'cacheCasinoData'])->name('casino.vip.cache');
    Route::get('/casino', [CasinoController::class, 'adminCasino'])->name('casino.view');
    Route::get('/casino/bonus', [CasinoBonusController::class, 'adminCasino'])->name('casino.bonuscache.view');
    Route::get('/casino/cashback', [CasinoCashbackController::class, 'adminCasino'])->name('casino.cashbackcache.view');
    Route::get('/casino/vip', [CasinoVipBonusController::class, 'adminCasino'])->name('casino.vipcache.view');

 
    // Cashback Setting Routes
    Route::get('cashback-settings', [CashbackSettingController::class, 'index'])->name('cashback.index');
    Route::get('cashback-settings/create', [CashbackSettingController::class, 'create'])->name('cashback.create');
    Route::post('cashback-settings', [CashbackSettingController::class, 'store'])->name('cashback.store');
    Route::get('cashback-settings/{cashback}/edit', [CashbackSettingController::class, 'edit'])->name('cashback.edit');
    Route::put('cashback-settings/{cashback}', [CashbackSettingController::class, 'update'])->name('cashback.update');
    Route::delete('cashback-settings/{cashback}', [CashbackSettingController::class, 'destroy'])->name('cashback.destroy');

    Route::get('/vip-bonuses/users', [VipBonusController::class, 'index'])->name('vipbonuses.index');
    Route::get('/vip-bonuses/create/{user}', [VipBonusController::class, 'create'])->name('vipbonuses.create');
    Route::post('/vip-bonuses', [VipBonusController::class, 'store'])->name('vipbonuses.store');
    
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [LogController::class, 'clearLaravelLog'])->name('logs.clear');
    
    Route::get('/scheduler-logs', [LogController::class, 'scheduler'])->name('scheduler.logs.index');
    Route::post('/scheduler-logs/clear', [LogController::class, 'clearSchedulerLog'])->name('scheduler.logs.clear');



});




Route::get('/pages/{slug}', [PageController::class, 'show'])->name('page.show');
