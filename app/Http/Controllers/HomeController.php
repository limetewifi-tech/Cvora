<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\ResumeTemplate;
use App\Support\Guides;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
            'templates' => ResumeTemplate::query()->active()->orderBy('sort_order')->get(),
            'faqs' => $this->faqs(),
        ]);
    }

    public function templates()
    {
        return view('public.templates', [
            'templates' => ResumeTemplate::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function pricing()
    {
        return view('public.pricing', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function resources()
    {
        return view('public.resources', ['guides' => Guides::all()]);
    }

    public function resource(string $slug)
    {
        $guide = Guides::find($slug);
        abort_unless($guide, 404);

        return view('public.resource', ['guide' => $guide]);
    }

    private function faqs(): array
    {
        return [
            ['q' => 'CVora rédige-t-il un parcours à ma place ?', 'a' => 'Non. L\'assistant reformule les informations que vous fournissez. Il ne doit pas inventer de diplôme, d\'expérience ou de compétence. Vous validez chaque proposition avant qu\'elle ne soit enregistrée.'],
            ['q' => 'Puis-je importer un CV existant ?', 'a' => 'Oui, au format PDF texte ou Word .docx. Les champs détectés sont présentés pour correction. Un PDF scanné sans texte exploitable est signalé : la reconnaissance optique n\'est pas encore branchée.'],
            ['q' => 'Mes documents sont-ils visibles par l\'administrateur ?', 'a' => 'Non. L\'administration gère les comptes, les offres et les statistiques globales. Elle n\'ouvre pas le contenu des CV.'],
            ['q' => 'Le paiement Mobile Money est-il déjà encaissé ?', 'a' => 'Pas tant que les identifiants marchands ne sont pas configurés et qu\'une notification signée du prestataire n\'a pas été vérifiée. Le mode bac à sable est clairement séparé et ne représente pas un paiement réel.'],
            ['q' => 'Puis-je supprimer mon compte ?', 'a' => 'Oui, depuis les paramètres. Les CV, lettres et fichiers importés sont supprimés. Le compte est anonymisé. Les transactions restent conservées sans le contenu des documents, pour le suivi comptable.'],
        ];
    }
}
