# CVora

CVora est une plateforme pour créer, améliorer et actualiser un CV, puis préparer une lettre de motivation. Le slogan est : « Votre talent mérite le meilleur CV. »

L'interface est en français. Les montants d'exemple sont en francs congolais (CDF). Le fuseau horaire par défaut est `Africa/Kinshasa`. Rien dans l'application ne promet une embauche, et les aperçus publics utilisent des données fictives explicitement signalées.

## Prérequis

- PHP 8.3 avec les extensions `mbstring`, `xml`, `curl`, `zip`, `sqlite3`, `mysql`, `bcmath`, `intl`, `gd` et `tokenizer`
- Composer 2
- Node.js 22 et npm
- SQLite pour le développement, MySQL 8 pour la production

`upload_max_filesize` et `post_max_size` doivent être au moins égaux à `CVORA_IMPORT_MAX_KB` (2 Mo par défaut).

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve

L'interface utilise Bootstrap 5.3, Bootstrap Icons et un JavaScript compilé par Vite (`resources/css/app.css`, `resources/js/app.js`). Un seul bundle Bootstrap est chargé. Le mode clair ou sombre est mémorisé dans le navigateur sous la clé `cvora-theme`.
```

Pour créer les comptes locaux de démonstration :

```bash
# Dans .env, uniquement en local :
# APP_ENV=local
# SEED_DEMO=true
php artisan db:seed --class=DemoSeeder
```

Comptes créés seulement si `APP_ENV=local` et `SEED_DEMO=true` :

| Adresse | Rôle | Offre | Mot de passe |
| --- | --- | --- | --- |
| admin@cvora.test | Administrateur | Pro | Password123! |
| demo@cvora.test | Utilisateur | Gratuit | Password123! |

Ces comptes ne doivent pas exister en production. Le mot de passe ci-dessus n'est pas un secret de production.

## Production

1. `APP_ENV=production`, `APP_DEBUG=false`, `SEED_DEMO=false`.
2. Base MySQL : décommenter `DB_CONNECTION=mysql` et renseigner l'hôte, la base et l'utilisateur.
3. `php artisan migrate --force` puis `php artisan db:seed --force --class=PlanSeeder` (et `TemplateSeeder`, `SettingSeeder`). Ne pas lancer `DemoSeeder`.
4. `npm run build`.
5. Le disque `local` pointe vers `storage/app/private`. Ne pas rendre ce dossier public. Les téléchargements passent par des routes authentifiées.
6. Planifier le renouvellement des crédits : `php artisan schedule:work`, ou une tâche cron `* * * * * php artisan schedule:run`. La commande quotidienne est `cvora:renew-credits`.
7. Les files d'attente ne sont pas nécessaires pour le parcours principal. Les notifications d'abonnement sont enregistrées en base.
8. Renseigner dans l'administration l'identité légale (nom, adresse, e-mail, RCCM) avant d'ouvrir le service. Les champs vides s'affichent comme « à compléter ».
9. Configurer un vrai `MAIL_MAILER` avant d'activer `MUST_VERIFY_EMAIL=true` ou d'attendre des e-mails de réinitialisation. En local, le driver `log` écrit dans `storage/logs`.

## Intelligence artificielle

Le service central est `App\Services\Ai\AiCareerService`. Les prompts sont dans `App\Services\Ai\Prompts`. La clé reste dans `AI_API_KEY` et n'est jamais envoyée au navigateur.

- Sans clé, l'assistant répond qu'il n'est pas configuré. Aucun texte n'est inventé à la place.
- Avec une clé, le driver `openai` appelle une API compatible `/chat/completions` (`AI_BASE_URL`, `AI_MODEL`, `AI_TIMEOUT`).
- Le driver `anthropic` appelle l'API Messages (`POST /v1/messages`, en-tête `x-api-key`). Si l'URL ou le modèle restent ceux d'OpenAI, le pilote utilise `https://api.anthropic.com` et `claude-sonnet-4-5`.
- `AI_DRIVER=fake` ne sert qu'aux tests. Il est refusé si `APP_ENV=production`.
- Chaque appel réserve un crédit. Le crédit est rendu si l'appel échoue. Une proposition n'est enregistrée qu'après confirmation de la personne.
- Les textes importés et les offres d'emploi sont transmis comme des données, pas comme des instructions.

## Paiements

`PAYMENT_DRIVER=sandbox` affiche un écran de bac à sable. Confirmer ou refuser cet écran ne représente pas un encaissement réel.

`PAYMENT_DRIVER=unipay` envoie une intention de paiement à `{UNIPAY_API_URL}/v1/payments`. Ce contrat doit être aligné sur la documentation marchande officielle avant tout usage. L'offre n'est activée qu'après une notification signée :

- en-tête `X-Cvora-Signature` = HMAC SHA-256 du corps brut, clé `PAYMENT_WEBHOOK_SECRET` ;
- montant et devise identiques à la transaction ;
- référence fournisseur unique ;
- un second envoi de la même notification réussie ne réactive pas l'offre.

Sans les trois identifiants UniPay, le paiement est refusé et la transaction reste échouée ou en attente. Aucun paiement n'est simulé comme réussi.

Les prix, quotas et fonctionnalités des offres Gratuit, Premium et Pro sont en base et modifiables dans l'administration. Les valeurs du seeder (0, 15 000 et 35 000 CDF) sont un point de départ, pas une règle figée dans le code métier.

## Ce qui fonctionne

- Site public : accueil, fonctionnalités, modèles, tarifs lus en base, ressources, FAQ, contact, confidentialité, conditions, mentions légales.
- Inscription, connexion, déconnexion, réinitialisation du mot de passe, profil, préférences, suppression de compte avec anonymisation.
- Vérification d'e-mail uniquement si `MUST_VERIFY_EMAIL=true`.
- Rôles utilisateur et administrateur, policies, isolation des CV (un administrateur ne peut pas ouvrir un CV).
- Tableau de bord avec les compteurs du compte connecté.
- Éditeur de CV en dix étapes, enregistrement, conflit de version, duplication, huit mises en page distinctes, aperçu.
- Export PDF (Dompdf, police DejaVu) et DOCX Word modifiable (PHPWord), selon l'offre.
- Import PDF texte et DOCX, relecture avant enregistrement, aucun écrasement sans case confirmée.
- Lettres de motivation, historique manuel réservé à l'offre Pro.
- Quotas IA, offres, transactions, bac à sable, webhook signé, espace d'administration.

## Ce qui demande encore une configuration externe

- Appels IA réels : renseigner `AI_API_KEY`.
- Mobile Money : identifiants marchands UniPay, secret de webhook, et validation du contrat HTTP avec la documentation du prestataire.
- OCR : un PDF scanné est signalé. `OcrExtractor` est branché sur `NullOcrExtractor`. Aucun service OCR n'est appelé.
- Connexion par téléphone : `PhoneAuthenticator` indique que le SMS n'est pas configuré. Il n'existe pas de bouton de connexion fictif.
- E-mails réels : driver mail de production.
- Identité légale de l'éditeur : à saisir dans l'administration.

## Tests

```bash
php artisan test
```

Les tests utilisent SQLite en mémoire, `AI_DRIVER=fake` et `PAYMENT_DRIVER=sandbox`. Ils ne contactent aucun service payant.

## Sécurité

Les secrets restent dans `.env`, qui n'est pas versionné. `.env.example` ne contient aucune clé réelle. Les documents importés sont stockés sur le disque privé. Les routes sensibles et les appels IA sont limités en fréquence. Le webhook de paiement est exclu de la protection CSRF et vérifié par signature.
