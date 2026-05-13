# Document de Conception Technique — Chatbot IA Gemini (project-ai-chatbot)

## Vue d'ensemble

Cette fonctionnalité intègre un assistant conversationnel IA dans la page `projects.php` de SkillBridge, accessible exclusivement aux clients (role=2). Le chatbot exploite l'API Google Gemini (`gemini-2.0-flash`) pour répondre aux questions des clients sur leurs projets : statut, tâches, budget, freelancers assignés et conseils de gestion de projet.

**Principes directeurs :**
- La clé API Gemini n'est jamais exposée côté client — tous les appels à l'API passent par un endpoint PHP serveur.
- Le contexte des projets du client est injecté dynamiquement dans le prompt système à chaque requête, garantissant des réponses personnalisées et à jour.
- L'intégration est non-intrusive : préfixe `ai-chat-` sur tous les identifiants CSS/JS, injection conditionnelle PHP, aucune interférence avec les fonctionnalités existantes.

---

## Architecture

### Vue d'ensemble du flux

```mermaid
sequenceDiagram
    participant Client as Navigateur (Client role=2)
    participant View as projects.php (View)
    participant Ctrl as ChatbotController.php
    participant DB as MySQL (PDO)
    participant Gemini as API Gemini

    Client->>View: Saisit un message + clic Envoyer
    View->>Ctrl: POST AJAX action=chatbot_gemini<br/>{message, history[]}
    Ctrl->>Ctrl: Vérif. session (user_id + role=2)
    Ctrl->>Ctrl: Validation message (non vide, ≤1000 chars)
    Ctrl->>DB: SELECT projets + tâches du client
    DB-->>Ctrl: Données projets/tâches
    Ctrl->>Ctrl: Construction Prompt_Système + contents[]
    Ctrl->>Gemini: POST curl /generateContent<br/>X-goog-api-key: [clé serveur]
    Gemini-->>Ctrl: {"candidates": [...]}
    Ctrl-->>View: {"success": true, "reply": "..."}
    View->>Client: Affiche la réponse dans le widget
```

### Composants principaux

```mermaid
graph TD
    A[projects.php] -->|require_once| B[ChatbotController.php]
    A -->|PHP conditionnel role=2| C[Widget HTML/CSS/JS]
    C -->|AJAX POST action=chatbot_gemini| A
    A -->|délègue| B
    B -->|PDO| D[(MySQL skillbridge)]
    B -->|curl| E[API Gemini]
    D -->|projets + tâches| B
    E -->|réponse texte| B
```

---

## Composants et Interfaces

### 1. `Controllers/ChatbotController.php`

Fichier PHP autonome inclus dans `projects.php` via `require_once`. Il expose une méthode statique principale et gère l'intégralité du traitement serveur.

**Interface publique :**

```php
class ChatbotController
{
    // Constante clé API (côté serveur uniquement)
    const GEMINI_API_KEY = 'AIzaSyApZHjYH3f0ku_5hdX3WdFWVNusZRJo_CQ';
    const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    const MAX_MESSAGE_LENGTH = 1000;
    const MAX_RESPONSE_WORDS = 300;

    /**
     * Point d'entrée AJAX principal.
     * Vérifie la session, valide l'entrée, construit le prompt, appelle Gemini.
     * Retourne JSON et appelle exit.
     */
    public static function handleRequest(): void;

    /**
     * Récupère les projets du client avec leurs tâches depuis MySQL.
     * @param int $userId
     * @return array  [ ['projet' => [...], 'taches' => [...]], ... ]
     */
    private static function getClientContext(int $userId): array;

    /**
     * Construit le prompt système en français avec le contexte projet.
     * @param array $context  Résultat de getClientContext()
     * @param string $prenom  Prénom du client
     * @return string
     */
    private static function buildSystemPrompt(array $context, string $prenom): string;

    /**
     * Appelle l'API Gemini via curl.
     * @param string $systemPrompt
     * @param array  $history      Tableau de messages [{role, parts:[{text}]}]
     * @param string $userMessage
     * @return array  ['success' => bool, 'reply' => string|null, 'message' => string|null]
     */
    private static function callGeminiApi(string $systemPrompt, array $history, string $userMessage): array;
}
```

**Intégration dans `projects.php` :**

```php
// En haut du fichier, avec les autres require_once
require_once __DIR__ . '/../../Controllers/ChatbotController.php';

// Dans le bloc if ($_SERVER['REQUEST_METHOD'] === 'POST')
if ($ajaxAction === 'chatbot_gemini') {
    ChatbotController::handleRequest();
}
```

### 2. Widget Flottant (HTML/CSS/JS)

Bloc PHP conditionnel injecté dans `projects.php` juste avant `</body>` :

```php
<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>
<!-- Widget HTML + <style> + <script> -->
<?php endif; ?>
```

**Structure HTML du widget :**

```
#ai-chat-widget                    ← conteneur racine (position: fixed, bottom-right)
├── #ai-chat-toggle-btn            ← bouton circulaire (état fermé)
│   └── icône ✨ ou 🤖
└── #ai-chat-window                ← fenêtre de conversation (display: none par défaut)
    ├── .ai-chat-header            ← titre + bouton fermeture #ai-chat-close-btn
    ├── #ai-chat-messages          ← zone de messages (overflow-y: auto)
    │   ├── .ai-chat-msg.ai-chat-msg--ai    ← message IA
    │   └── .ai-chat-msg.ai-chat-msg--user  ← message utilisateur
    ├── #ai-chat-typing            ← indicateur de chargement (3 points animés)
    └── .ai-chat-input-row         ← zone de saisie
        ├── #ai-chat-input         ← textarea
        └── #ai-chat-send-btn      ← bouton Envoyer
```

**Variables JavaScript globales :**

```javascript
let aiChatHistory = [];          // Historique session [{role, parts:[{text}]}]
let aiChatLoading = false;       // Verrou anti-double-soumission
```

---

## Modèles de Données

### Contexte Projet (structure interne PHP)

```php
// Résultat de getClientContext($userId)
[
    [
        'projet' => [
            'id'            => int,
            'titre'         => string,
            'statut'        => string,  // 'en_attente' | 'en_cours' | 'termine'
            'etat'          => string,  // 'publie' | 'en_attente_validation'
            'budget'        => float,
            'avancement'    => int,     // 0-100
            'date_creation' => string,  // 'YYYY-MM-DD'
        ],
        'taches' => [
            [
                'titre'           => string,
                'statut'          => string,  // 'a_faire' | 'en_cours' | 'termine'
                'prix'            => float,
                'payee'           => bool,
                'nom_freelancer'  => string,
                'prenom_freelancer' => string,
            ],
            // ...
        ],
    ],
    // ...
]
```

### Requête AJAX (client → serveur)

```
POST ?action=projects
Content-Type: multipart/form-data

action   = "chatbot_gemini"
message  = string (1–1000 caractères)
history  = JSON string  // tableau [{role: "user"|"model", parts: [{text: string}]}]
```

### Réponse JSON (serveur → client)

```json
// Succès
{ "success": true, "reply": "Votre projet X est actuellement en cours..." }

// Erreur métier
{ "success": false, "message": "Message invalide." }
{ "success": false, "message": "Service IA temporairement indisponible." }

// Erreur d'accès
HTTP 403 + { "success": false, "message": "Accès refusé." }
```

### Format `contents` envoyé à l'API Gemini

```json
{
  "system_instruction": {
    "parts": [{ "text": "<prompt_système_en_français>" }]
  },
  "contents": [
    { "role": "user",  "parts": [{ "text": "Bonjour, quel est le statut de mon projet Alpha ?" }] },
    { "role": "model", "parts": [{ "text": "Votre projet Alpha est actuellement en cours..." }] },
    { "role": "user",  "parts": [{ "text": "<message_actuel_du_client>" }] }
  ],
  "generationConfig": {
    "maxOutputTokens": 500
  }
}
```

### Requêtes SQL utilisées

**Projets du client :**
```sql
SELECT id, titre, statut, etat, budget, avancement, date_creation
FROM projet
WHERE id_client = :user_id
ORDER BY id DESC
```

**Tâches par projet :**
```sql
SELECT t.titre, t.statut, t.prix, t.payee,
       u.nom AS nom_freelancer, u.prenom AS prenom_freelancer
FROM tache t
JOIN user u ON u.id = t.id_freelancer
WHERE t.id_projet = :id_projet
ORDER BY t.id ASC
```

---

## Propriétés de Correction

*Une propriété est une caractéristique ou un comportement qui doit être vrai pour toutes les exécutions valides d'un système — essentiellement, un énoncé formel de ce que le système doit faire. Les propriétés servent de pont entre les spécifications lisibles par l'humain et les garanties de correction vérifiables par machine.*

---

### Propriété 1 : Rendu conditionnel du widget selon le rôle

*Pour tout* état de session (role=1, role=2, role=3, ou session absente), le HTML rendu de `projects.php` doit contenir le widget chatbot si et seulement si `$_SESSION['user_role'] === 2`. Pour tout autre rôle ou session absente, aucun élément avec le préfixe `ai-chat-` ne doit apparaître dans le HTML.

**Valide : Exigences 1.1, 1.2, 1.3**

---

### Propriété 2 : Sécurité de l'endpoint — rejet des accès non autorisés

*Pour toute* requête POST à l'action `chatbot_gemini` avec un état de session invalide (user_id absent, ou user_role ≠ 2), l'endpoint doit retourner HTTP 403 avec `{"success": false}` sans effectuer aucun appel à la base de données ni à l'API Gemini.

**Valide : Exigences 3.2**

---

### Propriété 3 : Validation de la longueur du message

*Pour tout* message dont la longueur est 0 (vide, whitespace uniquement) ou supérieure à 1000 caractères, l'endpoint doit retourner `{"success": false, "message": "Message invalide."}` sans appeler l'API Gemini ni interroger la base de données.

**Valide : Exigences 3.6**

---

### Propriété 4 : Structure de réponse JSON en cas de succès

*Pour toute* requête valide (session role=2, message entre 1 et 1000 caractères) avec une réponse Gemini simulée valide, la réponse JSON de l'endpoint doit avoir `success === true` et un champ `reply` non vide de type string.

**Valide : Exigences 3.4**

---

### Propriété 5 : Masquage des erreurs API Gemini

*Pour tout* code de réponse HTTP d'erreur (4xx ou 5xx) retourné par l'API Gemini, l'endpoint doit retourner `{"success": false, "message": "Service IA temporairement indisponible."}` sans inclure aucun détail technique de l'erreur (code HTTP, message d'erreur brut, stack trace).

**Valide : Exigences 3.5**

---

### Propriété 6 : Complétude du contexte projet dans le prompt

*Pour tout* client ayant N projets en base de données (N ≥ 0), le prompt système construit par `buildSystemPrompt()` doit contenir le titre de chacun des N projets, leur statut, budget et avancement. Si N = 0, le prompt doit contenir une mention explicite qu'aucun projet n'est créé.

**Valide : Exigences 4.1, 4.2, 4.3, 4.5**

---

### Propriété 7 : Transmission fidèle de l'historique à l'API

*Pour tout* historique de K messages (K ≥ 0) transmis dans le paramètre `history`, la requête JSON envoyée à l'API Gemini doit contenir exactement ces K messages dans le champ `contents`, dans le même ordre, suivis du message actuel de l'utilisateur.

**Valide : Exigences 4.4**

---

### Propriété 8 : Instructions du prompt système

*Pour tout* contexte projet (vide ou non), le prompt système généré doit contenir : (a) une instruction de répondre exclusivement en français, (b) une limitation aux sujets projets/gestion de projet/SkillBridge, (c) une instruction de refus poli des questions hors sujet, et (d) une limite de 300 mots par réponse.

**Valide : Exigences 5.1, 5.2, 5.3, 5.4**

---

### Propriété 9 : Désactivation des contrôles pendant le chargement

*Pour tout* état de chargement (requête AJAX en cours), le bouton `#ai-chat-send-btn` et le champ `#ai-chat-input` doivent être désactivés (`disabled=true`). Dès que la réponse est reçue (succès ou erreur), ces deux éléments doivent être réactivés (`disabled=false`).

**Valide : Exigences 6.3, 6.4**

---

### Propriété 10 : Rejet des messages vides côté client

*Pour tout* message composé uniquement de caractères whitespace (espaces, tabulations, retours à la ligne), le widget ne doit déclencher aucun appel AJAX et doit replacer le focus sur le champ de saisie.

**Valide : Exigences 6.5**

---

### Propriété 11 : Affichage différencié des messages

*Pour tout* message ajouté à la zone de conversation, les messages de l'utilisateur doivent avoir la classe CSS `ai-chat-msg--user` et les messages de l'IA doivent avoir la classe `ai-chat-msg--ai`, garantissant une distinction visuelle systématique.

**Valide : Exigences 2.7**

---

## Gestion des Erreurs

### Erreurs côté serveur (ChatbotController.php)

| Situation | Code HTTP | Réponse JSON |
|-----------|-----------|--------------|
| Session absente ou role ≠ 2 | 403 | `{"success": false, "message": "Accès refusé."}` |
| Message vide ou > 1000 chars | 200 | `{"success": false, "message": "Message invalide."}` |
| Erreur PDO (base de données) | 200 | `{"success": false, "message": "Service IA temporairement indisponible."}` |
| Erreur curl (réseau) | 200 | `{"success": false, "message": "Service IA temporairement indisponible."}` |
| Erreur HTTP Gemini (4xx/5xx) | 200 | `{"success": false, "message": "Service IA temporairement indisponible."}` |
| Réponse Gemini malformée | 200 | `{"success": false, "message": "Service IA temporairement indisponible."}` |

**Principe :** Toutes les erreurs internes sont loguées via `error_log()` mais jamais exposées au client. Le message retourné est toujours générique.

### Erreurs côté client (Widget JS)

| Situation | Comportement |
|-----------|-------------|
| `success: false` dans la réponse | Affiche `data.message` avec classe `ai-chat-msg--error` (couleur rouge/orange) |
| Erreur réseau fetch (catch) | Affiche "Erreur de connexion. Veuillez réessayer." avec classe `ai-chat-msg--error` |
| Message vide soumis | Aucun appel AJAX, focus replacé sur `#ai-chat-input` |
| Soumission pendant chargement | Ignorée (verrou `aiChatLoading`) |

### Logging

Toutes les erreurs serveur sont loguées avec `error_log()` incluant :
- L'identifiant utilisateur (sans données sensibles)
- Le type d'erreur (PDO, curl, HTTP)
- Le timestamp

La clé API n'est jamais incluse dans les logs.

---

## Stratégie de Tests

### Approche duale

La stratégie combine des tests unitaires/exemples pour les comportements spécifiques et des tests basés sur les propriétés pour les invariants universels.

### Tests basés sur les propriétés (PBT)

La bibliothèque recommandée est **[fast-check](https://fast-check.dev/)** (JavaScript) pour les propriétés du widget côté client, et **[PHPUnit](https://phpunit.de/) avec des générateurs manuels** pour les propriétés PHP côté serveur.

Chaque test de propriété doit s'exécuter avec un minimum de **100 itérations**.

Format de tag : `Feature: project-ai-chatbot, Property {N}: {texte_propriété}`

#### Tests de propriétés PHP (ChatbotController)

**Propriété 2 — Sécurité accès :**
```php
// Feature: project-ai-chatbot, Property 2: Rejet accès non autorisés
// Générer des états de session invalides (role=1, role=3, absent)
// Vérifier que handleRequest() retourne HTTP 403 dans tous les cas
```

**Propriété 3 — Validation longueur message :**
```php
// Feature: project-ai-chatbot, Property 3: Validation longueur message
// Générer des messages de longueur 0 et > 1000 (strings aléatoires)
// Vérifier que la réponse est {"success": false, "message": "Message invalide."}
// et qu'aucun appel curl n'est effectué (mock curl)
```

**Propriété 5 — Masquage erreurs API :**
```php
// Feature: project-ai-chatbot, Property 5: Masquage erreurs API Gemini
// Générer des codes HTTP d'erreur (400, 401, 403, 404, 429, 500, 502, 503)
// Vérifier que la réponse ne contient jamais le code HTTP ni le message brut
```

**Propriété 6 — Complétude contexte projet :**
```php
// Feature: project-ai-chatbot, Property 6: Complétude contexte projet dans le prompt
// Générer des listes de projets aléatoires (0 à 10 projets, titres aléatoires)
// Vérifier que buildSystemPrompt() contient tous les titres générés
```

**Propriété 7 — Transmission historique :**
```php
// Feature: project-ai-chatbot, Property 7: Transmission fidèle de l'historique
// Générer des historiques de 0 à 20 messages aléatoires
// Vérifier que la requête JSON envoyée à Gemini contient exactement ces messages
```

**Propriété 8 — Instructions prompt système :**
```php
// Feature: project-ai-chatbot, Property 8: Instructions du prompt système
// Pour tout contexte (vide ou non), vérifier la présence des 4 instructions
// dans le prompt généré par buildSystemPrompt()
```

#### Tests de propriétés JavaScript (Widget)

**Propriété 1 — Rendu conditionnel :**
```javascript
// Feature: project-ai-chatbot, Property 1: Rendu conditionnel selon le rôle
// Générer des rôles aléatoires (1, 2, 3, null)
// Vérifier présence/absence du widget dans le HTML rendu
```

**Propriété 9 — Désactivation contrôles :**
```javascript
// Feature: project-ai-chatbot, Property 9: Désactivation contrôles pendant chargement
// Pour tout état de chargement, vérifier disabled=true sur input et button
// Après réponse, vérifier disabled=false
```

**Propriété 10 — Rejet messages vides :**
```javascript
// Feature: project-ai-chatbot, Property 10: Rejet messages vides côté client
// Générer des strings whitespace-only (espaces, tabs, newlines, combinaisons)
// Vérifier qu'aucun appel fetch n'est déclenché
```

**Propriété 11 — Classes CSS messages :**
```javascript
// Feature: project-ai-chatbot, Property 11: Affichage différencié des messages
// Pour tout message ajouté (user ou ai), vérifier la classe CSS correcte
```

### Tests unitaires / exemples

| Test | Type | Description |
|------|------|-------------|
| Widget présent pour role=2 | Exemple | HTML contient `#ai-chat-widget` |
| Widget absent pour role=1 | Exemple | HTML ne contient pas `ai-chat-` |
| Message de bienvenue avec prénom | Exemple | Bienvenue contient le prénom du client |
| Éléments HTML présents | Exemple | `#ai-chat-input`, `#ai-chat-send-btn` présents |
| Indicateur chargement visible | Exemple | `#ai-chat-typing` visible pendant loading |
| Erreur réseau affichée | Exemple | Message "Erreur de connexion" affiché |
| Scroll automatique | Exemple | `scrollTop === scrollHeight` après ajout message |
| Préfixe `ai-chat-` sur tous les IDs | Exemple | Tous les IDs/classes du widget respectent le préfixe |

### Tests d'intégration

| Test | Description |
|------|-------------|
| Appel curl avec header API key | Vérifier que `X-goog-api-key` est présent dans les headers curl |
| Connexion PDO utilisée | Vérifier que `Config::getConnexion()` est appelé pour les requêtes DB |
| Flux complet avec mock Gemini | Requête AJAX → ChatbotController → mock Gemini → réponse widget |
| Non-régression modals existants | Vérifier que `addProjectOverlay`, `editProjectOverlay`, `editTacheOverlay` fonctionnent toujours |
| Non-régression Pusher/notifications | Vérifier que le polling de notifications fonctionne toujours |

### Tests de fumée (smoke tests)

| Test | Description |
|------|-------------|
| Widget positionné en bas à droite | Vérification visuelle CSS `position: fixed; bottom: ...; right: ...` |
| z-index supérieur aux autres éléments | Vérifier `z-index` du widget > z-index des modals existants |
| Responsive mobile | Widget ≤ 90vw sur écran < 640px |
| Aucune interférence avec modals | Ouverture/fermeture des modals existants non affectée |
