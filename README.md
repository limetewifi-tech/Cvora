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

Le service métier reste `App\Services\Ai\AiCareerService`. Les prompts sont dans `App\Services\Ai\Prompts`. `App\Services\Ai\AiRouter` choisit un seul fournisseur et, en cas d'échec, peut appeler au plus une fois un repli différent.

Les clés restent dans l'environnement et ne sont ni envoyées au navigateur, ni écrites dans les réglages, ni recopiées dans les journaux :

- `ANTHROPIC_API_KEY` et `ANTHROPIC_MODEL` pour l'API Messages d'Anthropic (`POST /v1/messages`, en-tête `x-api-key`) ;
- `GEMINI_API_KEY` et `GEMINI_MODEL` pour l'API Gemini (`POST /v1beta/models/{modèle}:generateContent`, en-tête `x-goog-api-key`) ;
- `GROQ_API_KEY` et `GROQ_MODEL` pour l'API compatible OpenAI de Groq (`POST /chat/completions`).

Un fournisseur est utilisable seulement si sa clé et son identifiant de modèle sont tous les deux renseignés. Aucun modèle de remplacement n'est inventé. `AI_DRIVER` ou l'administration choisit `anthropic`, `gemini` ou `groq`. `openai` n'est pas branché : cette valeur, comme toute valeur inconnue, est refusée et aucun autre fournisseur n'est appelé à sa place. `AI_FALLBACK_DRIVER` est facultatif : il est ignoré s'il est vide, identique au fournisseur principal, inconnu, ou non configuré. Le repli n'est tenté qu'après l'échec d'un fournisseur principal déjà configuré. Une réponse JSON déjà reçue n'est pas suivie d'un second appel. L'identifiant Groq peut contenir une barre (`organisation/modele`). Gemini refuse ce format, car son identifiant est placé dans l'adresse de l'appel.

`AI_DRIVER=fake` ne sert qu'aux tests. Il est refusé si `APP_ENV=production`. `php artisan cvora:ai-status` affiche la présence des clés et les identifiants de modèles, sans appel distant. Il signale un pilote non pris en charge sans afficher une valeur qui ressemble à une clé.

Une sonde locale antérieure a reçu HTTP 200 pour `claude-sonnet-4-6`, `gemini-3.8-flash` et `qwen/qwen3.8-27b`. Cette sonde n'est pas rejouée par les tests. Elle ne garantit pas un appel ultérieur.

Chaque appel métier réserve un crédit. Le crédit est rendu si toute la chaîne échoue. Une proposition n'est enregistrée qu'après confirmation de la personne. Les textes importés et les offres d'emploi sont transmis comme des données, pas comme des instructions.

Aucun appel réel vers Anthropic, Gemini ou Groq n'a été effectué dans cette version. La commande de statut et les tests simulés ne prouvent pas qu'une clé ou qu'un modèle est accepté par le fournisseur.

## Studio CV

`/studio` compare un CV enregistré au texte d'une offre. Le score est local : pourcentage d'exigences déjà écrites dans le résumé, les compétences ou les expériences. Une compétence du CV citée dans l'offre compte aussi. Aucune compétence absente n'est ajoutée. Les suggestions d'un fournisseur ne sont demandées que si la personne l'autorise et si son offre inclut l'adaptation. Un échec du fournisseur laisse le score local et n'invente pas de proposition. Le CV ne change qu'après une case de confirmation.

## Paiements

`PAYMENT_DRIVER=sandbox` affiche un écran de bac à sable. Confirmer ou refuser cet écran ne représente pas un encaissement réel. Cette confirmation exige encore l'en-tête `X-Cvora-Signature` (HMAC SHA-256 du corps brut, clé `PAYMENT_WEBHOOK_SECRET`) lorsqu'elle arrive par webhook.

`PAYMENT_DRIVER=ikeepay` utilise le paiement direct documenté par iKeePay : en-tête `x-api-key`, `POST /h2h-payin`, puis `GET /h2h-verify/{référence}`. Le formulaire demande le numéro, le code pays et l'opérateur. L'offre n'est activée que si la vérification renvoie un encaissement `completed`, de type `payin`, avec le même montant et la même devise. Une notification sans signature n'est pas considérée comme une preuve : elle déclenche seulement cette vérification. Un statut `pending` laisse la transaction en attente. Un statut `failed` la marque échouée. Un second succès ne réactive pas l'offre.

Sans `IKEEPAY_API_KEY` et une URL `https`, le paiement est refusé avant tout appel. Aucun paiement n'est simulé comme réussi. L'acceptation de la devise CDF n'a pas été constatée : ce dépôt n'a pas contacté `api.ikeepay.com`, et aucun paiement réel n'a été lancé. Une valeur de `PAYMENT_DRIVER` autre que `ikeepay` utilise le bac à sable.

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
- Quotas IA, offres, transactions, bac à sable, vérification iKeePay simulée, espace d'administration.

## Ce qui demande encore une configuration externe

- Appels IA réels : renseigner la clé et l'identifiant de modèle du fournisseur choisi, puis vérifier le résultat hors de la suite de tests. `php artisan cvora:ai-status` ne contacte pas le fournisseur.
- Mobile Money : `IKEEPAY_API_KEY`, l'URL de notification saisie dans le tableau de bord iKeePay, et un paiement réel de contrôle. Le bac à sable de CVora n'encaisse rien.
- OCR : un PDF scanné est signalé. `OcrExtractor` est branché sur `NullOcrExtractor`. Aucun service OCR n'est appelé.
- Connexion par téléphone : `PhoneAuthenticator` indique que le SMS n'est pas configuré. Il n'existe pas de bouton de connexion fictif.
- E-mails réels : driver mail de production.
- Identité légale de l'éditeur : à saisir dans l'administration.

## Tests

```bash
php artisan test
php artisan cvora:ai-status
```

Les tests utilisent SQLite en mémoire, `AI_DRIVER=fake` et `PAYMENT_DRIVER=sandbox`. Ils simulent Anthropic, Gemini, Groq et iKeePay. Ils ne contactent aucun service payant. `cvora:ai-status` ne lance pas non plus d'appel distant.

## Sécurité

Les secrets restent dans `.env`, qui n'est pas versionné. `.env.example` ne contient aucune clé réelle. Les documents importés sont stockés sur le disque privé. Les routes sensibles et les appels IA sont limités en fréquence. Le webhook de paiement est exclu de la protection CSRF. Le bac à sable vérifie la signature HMAC. iKeePay est revérifié auprès de son API avant d'activer une offre, et le numéro de téléphone n'est pas recopié dans le journal.
