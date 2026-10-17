<?php

use App\Http\Controllers\Admin\PanelController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobOfferController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeCoachController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeExportController;
use App\Http\Controllers\ResumeImportController;
use App\Http\Controllers\ResumeVersionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\TemplateExampleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/modeles', [HomeController::class, 'templates'])->name('templates.index');
Route::get('/modeles/{template:slug}/exemple', [TemplateExampleController::class, 'show'])->name('templates.example');
Route::get('/modeles/{template:slug}/papier', [TemplateExampleController::class, 'paper'])->name('templates.paper');
Route::get('/tarifs', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/ressources', [HomeController::class, 'resources'])->name('resources');
Route::get('/ressources/{slug}', [HomeController::class, 'resource'])->name('resources.show');
Route::view('/fonctionnalites', 'public.features')->name('features');
Route::get('/confidentialite', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/conditions', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/mentions-legales', [LegalController::class, 'notice'])->name('legal.notice');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:sensitive')->name('contact.store');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisteredUserController::class, 'store'])->middleware('throttle:sensitive');
    Route::get('/connexion', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/connexion', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:sensitive');
    Route::get('/mot-de-passe/oublie', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/mot-de-passe/oublie', [PasswordResetLinkController::class, 'store'])->middleware('throttle:sensitive')->name('password.email');
    Route::get('/mot-de-passe/reinitialiser/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [NewPasswordController::class, 'store'])->middleware('throttle:sensitive')->name('password.update');
});

Route::post('/webhooks/payments/{provider}', [PaymentWebhookController::class, 'store'])
    ->middleware('throttle:webhooks')
    ->name('payments.webhook');

Route::middleware(['auth', 'active', 'verified.if', 'plan'])->group(function () {
    Route::get('/email/verification', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:sensitive')->name('verification.send');
    Route::get('/email/verifier/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/parametres', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/parametres/mot-de-passe', [SettingsController::class, 'password'])->name('settings.password');
    Route::put('/parametres/preferences', [SettingsController::class, 'preferences'])->name('settings.preferences');
    Route::delete('/parametres/compte', [SettingsController::class, 'destroy'])->name('settings.destroy');
    Route::post('/notifications/{id}/lire', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/abonnement', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/abonnement/sandbox/{transaction}', [BillingController::class, 'sandbox'])->name('billing.sandbox');
    Route::post('/abonnement/sandbox/{transaction}/confirmer', [BillingController::class, 'confirmSandbox'])->name('billing.sandbox.confirm');
    Route::post('/abonnement/sandbox/{transaction}/echouer', [BillingController::class, 'failSandbox'])->name('billing.sandbox.fail');
    Route::post('/abonnement/{plan:slug}', [BillingController::class, 'checkout'])->name('billing.checkout');

    Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('/assistant/resume', [AssistantController::class, 'summary'])->middleware('throttle:ai')->name('assistant.summary');
    Route::post('/assistant/experiences', [AssistantController::class, 'experiences'])->middleware('throttle:ai')->name('assistant.experiences');
    Route::post('/assistant/competences', [AssistantController::class, 'skills'])->middleware('throttle:ai')->name('assistant.skills');
    Route::post('/assistant/correction', [AssistantController::class, 'grammar'])->middleware('throttle:ai')->name('assistant.grammar');
    Route::post('/assistant/ton', [AssistantController::class, 'tone'])->middleware('throttle:ai')->name('assistant.tone');
    Route::post('/assistant/lettre', [AssistantController::class, 'letter'])->middleware('throttle:ai')->name('assistant.letter');
    Route::post('/assistant/analyse', [AssistantController::class, 'analyze'])->middleware('throttle:ai')->name('assistant.analyze');
    Route::post('/assistant/actualiser', [AssistantController::class, 'refresh'])->middleware('throttle:ai')->name('assistant.refresh');

    Route::scopeBindings()->group(function () {
        Route::get('/cvs', [ResumeController::class, 'index'])->name('cvs.index');
        Route::get('/cvs/nouveau', [ResumeController::class, 'create'])->name('cvs.create');
        Route::post('/cvs', [ResumeController::class, 'store'])->name('cvs.store');
        Route::get('/cvs/importer', [ResumeImportController::class, 'create'])->name('imports.create');
        Route::post('/cvs/importer', [ResumeImportController::class, 'store'])->middleware('throttle:imports')->name('imports.store');
        Route::get('/cvs/importations/{import}', [ResumeImportController::class, 'show'])->name('imports.show');
        Route::post('/cvs/importations/{import}/confirmer', [ResumeImportController::class, 'confirm'])->name('imports.confirm');
        Route::get('/cvs/importations/{import}/fichier', [ResumeImportController::class, 'file'])->name('imports.file');
        Route::get('/cvs/{resume}/modifier', [ResumeController::class, 'edit'])->name('cvs.edit');
        Route::put('/cvs/{resume}', [ResumeController::class, 'update'])->name('cvs.update');
        Route::get('/cvs/{resume}/supprimer', [ResumeController::class, 'delete'])->name('cvs.delete');
        Route::delete('/cvs/{resume}', [ResumeController::class, 'destroy'])->name('cvs.destroy');
        Route::post('/cvs/{resume}/dupliquer', [ResumeController::class, 'duplicate'])->name('cvs.duplicate');
        Route::get('/cvs/{resume}/apercu', [ResumeController::class, 'preview'])->name('cvs.preview');
        Route::post('/cvs/{resume}/conversation', [ResumeCoachController::class, 'store'])->middleware('throttle:ai')->name('cvs.coach');
        Route::post('/cvs/{resume}/correction', [ResumeCoachController::class, 'proofread'])->middleware('throttle:ai')->name('cvs.proofread');
        Route::post('/cvs/{resume}/modele', [ResumeController::class, 'preferTemplate'])->name('cvs.prefer-template');
        Route::get('/cvs/{resume}/export/{format}', [ResumeExportController::class, 'download'])->whereIn('format', ['pdf', 'docx'])->name('cvs.export');
        Route::get('/cvs/{resume}/versions', [ResumeVersionController::class, 'index'])->name('cvs.versions');
        Route::post('/cvs/{resume}/versions', [ResumeVersionController::class, 'store'])->name('cvs.versions.store');
        Route::post('/cvs/{resume}/versions/{version}/restaurer', [ResumeVersionController::class, 'restore'])->name('cvs.versions.restore');
        Route::post('/cvs/{resume}/proposition', [ResumeController::class, 'applyProposal'])->name('cvs.proposal');

        Route::get('/lettres', [CoverLetterController::class, 'index'])->name('letters.index');
        Route::get('/lettres/nouvelle', [CoverLetterController::class, 'create'])->name('letters.create');
        Route::post('/lettres', [CoverLetterController::class, 'store'])->name('letters.store');
        Route::get('/lettres/{letter}/modifier', [CoverLetterController::class, 'edit'])->name('letters.edit');
        Route::put('/lettres/{letter}', [CoverLetterController::class, 'update'])->name('letters.update');
        Route::delete('/lettres/{letter}', [CoverLetterController::class, 'destroy'])->name('letters.destroy');
        Route::post('/lettres/{letter}/versions', [CoverLetterController::class, 'snapshot'])->name('letters.versions.store');
        Route::post('/lettres/{letter}/versions/{version}/restaurer', [CoverLetterController::class, 'restore'])->name('letters.versions.restore');
        Route::get('/lettres/{letter}/export/{format}', [CoverLetterController::class, 'export'])->whereIn('format', ['pdf', 'docx'])->name('letters.export');
        Route::post('/lettres/{letter}/proposition', [CoverLetterController::class, 'apply'])->name('letters.apply');

        Route::get('/studio', [StudioController::class, 'index'])->name('studio.index');
        Route::post('/studio', [StudioController::class, 'store'])->middleware('throttle:ai')->name('studio.store');
        Route::get('/studio/{analysis}', [StudioController::class, 'show'])->name('studio.show');
        Route::post('/studio/{analysis}/appliquer', [StudioController::class, 'apply'])->name('studio.apply');

        Route::get('/offres', [JobOfferController::class, 'index'])->name('jobs.index');
        Route::post('/offres', [JobOfferController::class, 'store'])->name('jobs.store');
        Route::get('/offres/{offer}', [JobOfferController::class, 'show'])->name('jobs.show');
        Route::put('/offres/{offer}', [JobOfferController::class, 'update'])->name('jobs.update');
        Route::delete('/offres/{offer}', [JobOfferController::class, 'destroy'])->name('jobs.destroy');
        Route::delete('/offres/{offer}/analyse', [JobOfferController::class, 'clearAnalysis'])->name('jobs.analysis.clear');
        Route::post('/offres/{offer}/adapter', [JobOfferController::class, 'adapt'])->name('jobs.adapt');

        Route::get('/candidatures', [ApplicationController::class, 'index'])->name('applications.index');
        Route::post('/candidatures', [ApplicationController::class, 'store'])->name('applications.store');
        Route::put('/candidatures/{application}', [ApplicationController::class, 'update'])->name('applications.update');
        Route::delete('/candidatures/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    });
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('index');
    Route::get('/utilisateurs', [PanelController::class, 'users'])->name('users');
    Route::put('/utilisateurs/{user}', [PanelController::class, 'updateUser'])->name('users.update');
    Route::get('/offres', [PanelController::class, 'plans'])->name('plans');
    Route::post('/offres', [PanelController::class, 'savePlan'])->name('plans.store');
    Route::put('/offres/{plan}', [PanelController::class, 'savePlan'])->name('plans.update');
    Route::get('/modeles', [PanelController::class, 'templates'])->name('templates');
    Route::put('/modeles/{template}', [PanelController::class, 'updateTemplate'])->name('templates.update');
    Route::get('/transactions', [PanelController::class, 'transactions'])->name('transactions');
    Route::get('/abonnements', [PanelController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/abonnements/{subscription}/annuler', [PanelController::class, 'cancelSubscription'])->name('subscriptions.cancel');
    Route::get('/ia', [PanelController::class, 'ai'])->name('ai');
    Route::get('/configuration', [PanelController::class, 'settings'])->name('settings');
    Route::put('/configuration', [PanelController::class, 'updateSettings'])->name('settings.update');
    Route::get('/journaux', [PanelController::class, 'errors'])->name('errors');
});
