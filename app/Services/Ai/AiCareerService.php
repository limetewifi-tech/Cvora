<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use App\Exceptions\PlanLimitException;
use App\Models\User;
use App\Services\Billing\QuotaService;

class AiCareerService
{
    public function configured(): bool
    {
        return $this->provider->configured();
    }

    public function __construct(
        private AiProvider $provider,
        private Prompts $prompts,
        private QuotaService $quota,
    ) {}

    public function improveSummary(User $user, string $summary, string $tone = 'professionnel'): AiProposal
    {
        return $this->run($user, 'summary', [
            'text' => $summary,
            'tone' => $tone,
            'instruction' => 'Reformule le résumé sans ajouter de fait.',
        ]);
    }

    public function improveExperiences(User $user, array $experiences): AiProposal
    {
        return $this->run($user, 'experiences', [
            'experiences' => $experiences,
            'instruction' => 'Améliore uniquement la clarté des descriptions fournies.',
        ]);
    }

    public function reformulateSkills(User $user, array $skills): AiProposal
    {
        return $this->run($user, 'skills', [
            'skills' => $skills,
            'instruction' => 'Reformule les intitulés sans ajouter de compétence absente.',
        ]);
    }

    public function correct(User $user, string $text): AiProposal
    {
        return $this->run($user, 'grammar', [
            'text' => $text,
            'instruction' => 'Corrige l\'orthographe et la grammaire sans changer le sens.',
        ]);
    }

    public function adaptTone(User $user, string $text, string $tone): AiProposal
    {
        return $this->run($user, 'tone', [
            'text' => $text,
            'tone' => $tone,
            'instruction' => 'Adapte le ton demandé sans ajouter de fait.',
        ]);
    }

    public function coverLetter(User $user, array $context): AiProposal
    {
        return $this->guarded($user, 'cover_letter', function () use ($user) {
            $this->quota->assertModule('feature_letters', 'Les lettres de motivation sont temporairement désactivées.');
            $this->quota->assertFeature($user, 'cover_letters', 'Les lettres de motivation ne sont pas incluses dans votre offre.');
        }, $context + [
            'instruction' => 'Rédige une lettre uniquement à partir des faits du candidat.',
        ]);
    }

    public function analyzeJob(User $user, array $context): AiProposal
    {
        return $this->guarded($user, 'job_analysis', function () use ($user) {
            $this->quota->assertFeature($user, 'job_adaptation', 'L\'adaptation aux offres est incluse dans l\'offre Pro.');
        }, $context + [
            'instruction' => 'Compare l\'offre au profil. N\'ajoute aucune compétence que le candidat ne possède pas.',
        ]);
    }

    public function refreshResume(User $user, array $context): AiProposal
    {
        return $this->guarded($user, 'refresh', function () use ($user) {
            $this->quota->assertFeature($user, 'resume_refresh', 'L\'actualisation assistée est incluse dans les offres Premium et Pro.');
        }, $context + [
            'instruction' => 'Propose un résumé et des descriptions à partir des seuls faits fournis.',
        ]);
    }

    private function guarded(User $user, string $feature, callable $check, array $data): AiProposal
    {
        try {
            $check();
        } catch (PlanLimitException $exception) {
            return new AiProposal(false, $feature, error: $exception->getMessage());
        }

        return $this->run($user, $feature, $data);
    }

    private function run(User $user, string $feature, array $data): AiProposal
    {
        if (! $this->provider->configured()) {
            return new AiProposal(false, $feature, error: 'Le fournisseur d\'IA n\'est pas configuré. Ajoutez une clé API dans l\'environnement du serveur.');
        }

        try {
            $this->quota->reserveAiCredit($user, $feature);
        } catch (PlanLimitException $exception) {
            return new AiProposal(false, $feature, error: $exception->getMessage());
        }

        $started = microtime(true);

        try {
            $result = $this->provider->complete($this->prompts->system($feature), $this->prompts->user($data));
        } catch (AiException $exception) {
            $this->quota->refundAiCredit($user, $feature, $exception->getMessage());

            return new AiProposal(false, $feature, error: $exception->getMessage(), creditsRemaining: $this->quota->creditsRemaining($user));
        }

        $missing = array_values(array_filter((array) ($result['missing_information'] ?? [])));
        $warnings = array_values(array_filter((array) ($result['warnings'] ?? [])));
        unset($result['missing_information'], $result['warnings']);

        return new AiProposal(
            successful: true,
            feature: $feature,
            data: $result,
            missing: $missing,
            warnings: $warnings,
            creditsRemaining: $this->quota->logAiSuccess($user, $feature, (int) ((microtime(true) - $started) * 1000)),
        );
    }
}
