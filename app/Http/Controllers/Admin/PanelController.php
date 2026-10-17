<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\ErrorLog;
use App\Models\Plan;
use App\Models\ResumeTemplate;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Account\AccountDeleter;
use App\Services\Ai\AiStatus;
use App\Services\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PanelController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'users' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'resumes' => \App\Models\Resume::query()->count(),
            'letters' => \App\Models\CoverLetter::query()->count(),
            'subscriptions' => Subscription::query()->where('status', SubscriptionStatus::Active->value)->count(),
            'succeeded' => Transaction::query()->where('status', 'succeeded')->count(),
            'aiCalls' => AiUsageLog::query()->where('status', 'success')->count(),
            'errorCount' => ErrorLog::query()->count(),
        ]);
    }

    public function users()
    {
        return view('admin.users', [
            'users' => User::query()->with('subscription.plan')->latest()->paginate(20),
        ]);
    }

    public function updateUser(Request $request, User $user, AccountDeleter $deleter)
    {
        $data = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['nullable', 'boolean'],
            'anonymize' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('anonymize')) {
            try {
                $deleter->delete($user);
            } catch (\RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
            }

            return back()->with('success', 'Le compte a été anonymisé. Son contenu de CV n\'est pas consulté par cette action.');
        }

        $role = $data['role'] instanceof UserRole ? $data['role']->value : $data['role'];
        $active = $request->boolean('is_active');
        $otherAdmins = User::query()->where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->exists();

        if ($user->isAdmin() && ! $otherAdmins && ($role !== UserRole::Admin->value || ! $active)) {
            return back()->with('error', 'Impossible de retirer ou de désactiver le dernier administrateur.');
        }

        $user->forceFill([
            'role' => $role,
            'is_active' => $active,
        ])->save();

        return back()->with('success', 'Compte mis à jour.');
    }

    public function plans()
    {
        return view('admin.plans', ['plans' => Plan::query()->orderBy('sort_order')->get()]);
    }

    public function savePlan(Request $request, ?Plan $plan = null)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'interval' => ['required', 'in:month,year'],
            'resume_limit' => ['nullable', 'integer', 'min:0'],
            'letter_limit' => ['nullable', 'integer', 'min:0'],
            'ai_credits' => ['required', 'integer', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'features' => ['nullable', 'array'],
        ]);

        $features = [];

        foreach (['export_pdf', 'export_docx', 'premium_templates', 'version_history', 'job_adaptation', 'resume_refresh', 'cover_letters'] as $feature) {
            $features[$feature] = $request->boolean('features.'.$feature);
        }

        $payload = [
            'name' => $data['name'],
            'slug' => Str::slug($data['slug']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => strtoupper($data['currency']),
            'interval' => $data['interval'],
            'resume_limit' => $request->filled('resume_limit') ? $data['resume_limit'] : null,
            'letter_limit' => $request->filled('letter_limit') ? $data['letter_limit'] : null,
            'ai_credits' => $data['ai_credits'],
            'sort_order' => $data['sort_order'],
            'is_active' => $request->boolean('is_active'),
            'features' => $features,
        ];

        if ($plan) {
            $plan->update($payload);
        } else {
            Plan::query()->create($payload);
        }

        return back()->with('success', 'Offre enregistrée.');
    }

    public function templates()
    {
        return view('admin.templates', [
            'templates' => ResumeTemplate::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function updateTemplate(Request $request, ResumeTemplate $template)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_premium' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $template->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'],
        ]);

        return back()->with('success', 'Modèle mis à jour.');
    }

    public function transactions()
    {
        return view('admin.transactions', [
            'transactions' => Transaction::query()->with(['user:id,name,email', 'plan:id,name'])->latest()->paginate(30),
        ]);
    }

    public function subscriptions()
    {
        return view('admin.subscriptions', [
            'subscriptions' => Subscription::query()->with(['user:id,name,email', 'plan:id,name'])->latest()->paginate(30),
        ]);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->forceFill(['status' => SubscriptionStatus::Cancelled])->save();

        return back()->with('success', 'Abonnement annulé. Le contenu des CV n\'a pas été ouvert.');
    }

    public function ai(AiStatus $status)
    {
        return view('admin.ai', [
            'usage' => AiUsageLog::query()->selectRaw('feature, status, count(*) as total')->groupBy('feature', 'status')->orderBy('feature')->get(),
            'status' => $status->snapshot(),
        ]);
    }

    public function settings(SettingsRepository $settings, AiStatus $status)
    {
        return view('admin.settings', [
            'settings' => $settings,
            'paymentDriver' => config('cvora.payments.driver'),
            'ikeepayConfigured' => filled(config('cvora.payments.ikeepay.api_key')) && filled(config('cvora.payments.ikeepay.api_url')),
            'aiStatus' => $status->snapshot(),
        ]);
    }

    public function updateSettings(Request $request, SettingsRepository $settings)
    {
        $data = $request->validate([
            'ai_driver' => ['nullable', Rule::in(['anthropic', 'gemini', 'groq'])],
            'ai_fallback_driver' => ['nullable', Rule::in(['anthropic', 'gemini', 'groq'])],
            'anthropic_model' => ['nullable', 'regex:/^[A-Za-z0-9._-]{1,80}$/'],
            'gemini_model' => ['nullable', 'regex:/^[A-Za-z0-9._-]{1,80}$/'],
            'groq_model' => ['nullable', 'regex:/^[A-Za-z0-9._-]{1,80}$/'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'legal_address' => ['nullable', 'string', 'max:255'],
            'legal_email' => ['nullable', 'email', 'max:180'],
            'legal_rccm' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email', 'max:180'],
        ]);

        $driver = $data['ai_driver'] ?? '';
        $fallback = $data['ai_fallback_driver'] ?? '';
        $effective = $driver !== '' ? $driver : (string) config('cvora.ai.driver');

        if ($fallback !== '' && $fallback === $effective) {
            throw ValidationException::withMessages([
                'ai_fallback_driver' => 'Le repli doit désigner un autre fournisseur.',
            ]);
        }

        foreach (['feature_ai', 'feature_import', 'feature_letters', 'feature_payments', 'feature_exports'] as $flag) {
            $settings->set($flag, $request->boolean($flag) ? '1' : '0', 'features');
        }

        foreach (['ai_driver', 'ai_fallback_driver', 'anthropic_model', 'gemini_model', 'groq_model'] as $key) {
            $settings->set($key, $data[$key] ?? '', 'ai');
        }

        foreach (['legal_name', 'legal_address', 'legal_email', 'legal_rccm', 'contact_email'] as $key) {
            $settings->set($key, $data[$key] ?? '', 'legal');
        }

        return back()->with('success', 'Configuration enregistrée. Les clés API restent uniquement dans l\'environnement.');
    }

    public function errors()
    {
        return view('admin.errors', [
            'logs' => ErrorLog::query()->latest()->paginate(30),
        ]);
    }
}
