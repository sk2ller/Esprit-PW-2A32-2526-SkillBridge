# Plan d'implémentation : Chatbot IA Gemini (project-ai-chatbot)

## Vue d'ensemble

Intégration d'un widget de chat IA flottant dans `projects.php`, accessible uniquement aux clients (role=2). L'implémentation se décompose en deux fichiers : `Controllers/ChatbotController.php` (nouveau) et `Views/Frontoffice/projects.php` (modification). Toutes les communications avec l'API Gemini passent exclusivement par le serveur PHP.

## Tâches

- [ ] 1. Créer `Controllers/ChatbotController.php` — structure de base et constantes
  - Créer le fichier avec la classe `ChatbotController`
  - Déclarer les constantes : `GEMINI_API_KEY`, `GEMINI_API_URL` (`gemini-2.0-flash:generateContent`), `MAX_MESSAGE_LENGTH` (1000), `MAX_RESPONSE_WORDS` (300)
  - Ajouter `require_once __DIR__ . '/../config.php';` en tête de fichier
  - Déclarer les quatre méthodes statiques avec leurs signatures : `handleRequest()`, `getClientContext()`, `buildSystemPrompt()`, `callGeminiApi()`
  - _Requirements: 3.1, 3.3, 7.3_

- [ ] 2. Implémenter la méthode `handleRequest()` — point d'entrée AJAX
  - [ ] 2.1 Implémenter la vérification de session et le contrôle d'accès
    - Vérifier que `$_SESSION['user_id']` est défini et que `(int)$_SESSION['user_role'] === 2`
    - Retourner HTTP 403 + `{"success": false, "message": "Accès refusé."}` et `exit` si la vérification échoue
    - Poser `header('Content-Type: application/json')` en début de méthode
    - _Requirements: 3.2_

  - [ ]* 2.2 Écrire le test de propriété — Propriété 2 : Rejet des accès non autorisés
    - **Propriété 2 : Sécurité de l'endpoint — rejet des accès non autorisés**
    - Générer des états de session invalides (role=1, role=3, user_id absent) avec PHPUnit
    - Vérifier que `handleRequest()` retourne HTTP 403 + `success: false` sans appel DB ni curl
    - **Valide : Requirements 3.2**

  - [ ] 2.3 Implémenter la validation du message
    - Lire `$_POST['message']` et appliquer `trim()`
    - Retourner `{"success": false, "message": "Message invalide."}` si vide ou `mb_strlen() > 1000`
    - _Requirements: 3.6_

  - [ ]* 2.4 Écrire le test de propriété — Propriété 3 : Validation de la longueur du message
    - **Propriété 3 : Validation de la longueur du message**
    - Générer des messages de longueur 0 (whitespace) et > 1000 caractères aléatoires
    - Vérifier que la réponse est `{"success": false, "message": "Message invalide."}` et qu'aucun appel curl n'est effectué (mock curl)
    - **Valide : Requirements 3.6**

  - [ ] 2.5 Implémenter le flux principal de `handleRequest()`
    - Décoder `$_POST['history']` via `json_decode()` (tableau vide par défaut si absent/invalide)
    - Appeler `self::getClientContext((int)$_SESSION['user_id'])`
    - Récupérer le prénom depuis `$_SESSION['user_prenom']` (ou équivalent disponible en session)
    - Appeler `self::buildSystemPrompt($context, $prenom)`
    - Appeler `self::callGeminiApi($systemPrompt, $history, $message)`
    - Retourner le résultat JSON et `exit`
    - _Requirements: 3.1, 3.4, 4.4_

  - [ ]* 2.6 Écrire le test de propriété — Propriété 4 : Structure de réponse JSON en cas de succès
    - **Propriété 4 : Structure de réponse JSON en cas de succès**
    - Pour toute requête valide (session role=2, message 1–1000 chars) avec mock Gemini valide
    - Vérifier que la réponse a `success === true` et un champ `reply` non vide de type string
    - **Valide : Requirements 3.4**

- [ ] 3. Implémenter `getClientContext(int $userId): array`
  - Obtenir la connexion PDO via `Config::getConnexion()`
  - Exécuter la requête SQL projets : `SELECT id, titre, statut, etat, budget, avancement, date_creation FROM projet WHERE id_client = :user_id ORDER BY id DESC`
  - Pour chaque projet, exécuter la requête SQL tâches : `SELECT t.titre, t.statut, t.prix, t.payee, u.nom AS nom_freelancer, u.prenom AS prenom_freelancer FROM tache t JOIN user u ON u.id = t.id_freelancer WHERE t.id_projet = :id_projet ORDER BY t.id ASC`
  - Retourner le tableau structuré `[['projet' => [...], 'taches' => [...]], ...]`
  - Encapsuler dans un bloc `try/catch PDOException` — en cas d'erreur, logger via `error_log()` et retourner `[]`
  - _Requirements: 4.1, 4.2, 3.7_

- [ ] 4. Implémenter `buildSystemPrompt(array $context, string $prenom): string`
  - [ ] 4.1 Construire le prompt système avec les instructions obligatoires
    - Inclure l'instruction de répondre exclusivement en français
    - Inclure la limitation aux sujets : statut/avancement projets, tâches, budget/paiements, conseils gestion de projet, fonctionnalités SkillBridge
    - Inclure l'instruction de refus poli des questions hors sujet avec redirection
    - Inclure la limite de 300 mots par réponse
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ]* 4.2 Écrire le test de propriété — Propriété 8 : Instructions du prompt système
    - **Propriété 8 : Instructions du prompt système**
    - Pour tout contexte (vide ou non), vérifier la présence des 4 instructions dans le prompt généré
    - **Valide : Requirements 5.1, 5.2, 5.3, 5.4**

  - [ ] 4.3 Intégrer le contexte projet dans le prompt
    - Si `$context` est vide : inclure la mention explicite qu'aucun projet n'est créé et proposer d'aider à en créer un
    - Si `$context` non vide : formater chaque projet avec titre, statut, budget, avancement, date_creation, et la liste de ses tâches (titre, statut, prix, payee, nom/prénom freelancer)
    - _Requirements: 4.3, 4.5_

  - [ ]* 4.4 Écrire le test de propriété — Propriété 6 : Complétude du contexte projet dans le prompt
    - **Propriété 6 : Complétude du contexte projet dans le prompt**
    - Générer des listes de 0 à 10 projets aléatoires (titres aléatoires)
    - Vérifier que `buildSystemPrompt()` contient le titre de chacun des N projets, leur statut, budget et avancement
    - Si N=0, vérifier la mention explicite d'absence de projet
    - **Valide : Requirements 4.1, 4.2, 4.3, 4.5**

- [ ] 5. Implémenter `callGeminiApi(string $systemPrompt, array $history, string $userMessage): array`
  - [ ] 5.1 Construire le payload JSON pour l'API Gemini
    - Construire le tableau `contents` : historique existant + message actuel `{role: "user", parts: [{text: $userMessage}]}`
    - Construire le corps JSON avec `system_instruction`, `contents`, et `generationConfig` (`maxOutputTokens: 500`)
    - _Requirements: 4.4_

  - [ ]* 5.2 Écrire le test de propriété — Propriété 7 : Transmission fidèle de l'historique
    - **Propriété 7 : Transmission fidèle de l'historique à l'API**
    - Générer des historiques de 0 à 20 messages aléatoires
    - Vérifier que la requête JSON envoyée à Gemini contient exactement ces K messages dans `contents`, dans le même ordre, suivis du message actuel
    - **Valide : Requirements 4.4**

  - [ ] 5.3 Implémenter l'appel curl à l'API Gemini
    - Initialiser curl avec `CURLOPT_URL` = `self::GEMINI_API_URL`
    - Ajouter les headers : `Content-Type: application/json` et `X-goog-api-key: self::GEMINI_API_KEY`
    - Configurer `CURLOPT_POST`, `CURLOPT_POSTFIELDS`, `CURLOPT_RETURNTRANSFER`, `CURLOPT_TIMEOUT` (15s)
    - Exécuter et récupérer le code HTTP via `curl_getinfo(CURLINFO_HTTP_CODE)`
    - _Requirements: 3.3_

  - [ ] 5.4 Gérer les réponses et erreurs de l'API Gemini
    - Si erreur curl (`curl_errno() !== 0`) : logger l'erreur, retourner `['success' => false, 'message' => 'Service IA temporairement indisponible.']`
    - Si code HTTP ≥ 400 : logger le code et user_id (sans clé API), retourner `['success' => false, 'message' => 'Service IA temporairement indisponible.']`
    - Parser la réponse JSON et extraire `candidates[0].content.parts[0].text`
    - Si structure malformée : logger et retourner `['success' => false, 'message' => 'Service IA temporairement indisponible.']`
    - En cas de succès : retourner `['success' => true, 'reply' => $text]`
    - _Requirements: 3.4, 3.5_

  - [ ]* 5.5 Écrire le test de propriété — Propriété 5 : Masquage des erreurs API Gemini
    - **Propriété 5 : Masquage des erreurs API Gemini**
    - Générer des codes HTTP d'erreur (400, 401, 403, 404, 429, 500, 502, 503)
    - Vérifier que la réponse ne contient jamais le code HTTP ni le message brut de l'erreur
    - **Valide : Requirements 3.5**

- [ ] 6. Checkpoint — Vérifier le contrôleur PHP
  - Vérifier que `ChatbotController.php` est syntaxiquement valide (`php -l`)
  - Vérifier que tous les cas d'erreur retournent bien du JSON valide
  - S'assurer que la clé API n'apparaît dans aucun log ni réponse JSON
  - Demander à l'utilisateur si des ajustements sont nécessaires avant de passer à la vue.

- [ ] 7. Modifier `Views/Frontoffice/projects.php` — intégration du contrôleur
  - [ ] 7.1 Ajouter le `require_once` du contrôleur en tête de fichier
    - Ajouter `require_once __DIR__ . '/../../Controllers/ChatbotController.php';` avec les autres `require_once` existants (lignes 2–5)
    - _Requirements: 7.3_

  - [ ] 7.2 Ajouter le branchement AJAX `chatbot_gemini` dans le bloc POST existant
    - Dans le bloc `if ($_SERVER['REQUEST_METHOD'] === 'POST')`, après les actions existantes, ajouter :
      ```php
      if ($ajaxAction === 'chatbot_gemini') {
          ChatbotController::handleRequest();
      }
      ```
    - _Requirements: 7.3_

- [ ] 8. Ajouter le widget HTML/CSS dans `projects.php` — structure et styles
  - [ ] 8.1 Injecter le bloc conditionnel PHP avec la structure HTML du widget
    - Ajouter juste avant `</body>` le bloc `<?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_role'] === 2): ?>`
    - Créer la structure HTML complète : `#ai-chat-widget` > `#ai-chat-toggle-btn` + `#ai-chat-window` (`.ai-chat-header`, `#ai-chat-messages`, `#ai-chat-typing`, `.ai-chat-input-row`)
    - Inclure `#ai-chat-close-btn`, `#ai-chat-input` (textarea), `#ai-chat-send-btn`
    - Fermer avec `<?php endif; ?>`
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 2.5, 7.1, 7.2_

  - [ ]* 8.2 Écrire le test de propriété — Propriété 1 : Rendu conditionnel selon le rôle
    - **Propriété 1 : Rendu conditionnel du widget selon le rôle**
    - Générer des rôles aléatoires (1, 2, 3, null) et vérifier présence/absence du widget dans le HTML rendu
    - Pour role=2 : HTML contient `ai-chat-widget` ; pour tout autre rôle : aucun élément `ai-chat-` présent
    - **Valide : Requirements 1.1, 1.2, 1.3**

  - [ ] 8.3 Ajouter les styles CSS du widget dans le bloc `<style>` conditionnel
    - `#ai-chat-widget` : `position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; font-family: 'DM Sans', sans-serif;`
    - `#ai-chat-toggle-btn` : bouton circulaire 56px, fond dégradé amber, ombre portée, curseur pointer
    - `#ai-chat-window` : `display: none; width: 360px; max-width: 90vw; height: 480px; border-radius: 16px; box-shadow; flex-direction: column;`
    - `.ai-chat-header` : fond charcoal, couleur blanche, padding, flex avec titre et bouton fermeture
    - `#ai-chat-messages` : `flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;`
    - `.ai-chat-msg` : `max-width: 80%; padding: 0.65rem 0.9rem; border-radius: 12px; font-size: 0.88rem; line-height: 1.5;`
    - `.ai-chat-msg--user` : aligné à droite, fond amber, couleur blanche
    - `.ai-chat-msg--ai` : aligné à gauche, fond creme/beige, couleur charcoal
    - `.ai-chat-msg--error` : fond rouge clair, couleur rouge foncé
    - `#ai-chat-typing` : `display: none;` avec animation 3 points (keyframes bounce)
    - `.ai-chat-input-row` : flex, padding, border-top, gap
    - `#ai-chat-input` : flex 1, resize none, border, border-radius, padding, focus amber
    - `#ai-chat-send-btn` : bouton amber, border-radius, padding, disabled opacity
    - Media query `@media (max-width: 640px)` : `#ai-chat-window { width: 90vw; }`
    - _Requirements: 1.4, 2.7, 7.1, 7.5_

- [ ] 9. Ajouter le JavaScript du widget dans `projects.php`
  - [ ] 9.1 Initialiser les variables globales et le message de bienvenue
    - Déclarer `let aiChatHistory = [];` et `let aiChatLoading = false;`
    - Au chargement DOM, injecter le message de bienvenue dans `#ai-chat-messages` avec la classe `ai-chat-msg--ai`
    - Le message doit mentionner le prénom du client (injecté via PHP `<?= htmlspecialchars($_SESSION['user_prenom'] ?? '') ?>`) et lister les types de questions possibles
    - _Requirements: 2.4, 2.9_

  - [ ] 9.2 Implémenter l'ouverture/fermeture du widget
    - Clic sur `#ai-chat-toggle-btn` : basculer `display` de `#ai-chat-window` (none ↔ flex), focus sur `#ai-chat-input`
    - Clic sur `#ai-chat-close-btn` : masquer `#ai-chat-window`
    - _Requirements: 2.2, 2.3_

  - [ ] 9.3 Implémenter la fonction `aiChatSendMessage()`
    - Lire et `trim()` la valeur de `#ai-chat-input`
    - Si vide : replacer le focus sur `#ai-chat-input` et `return` (aucun appel AJAX)
    - Si `aiChatLoading === true` : `return` (verrou anti-double-soumission)
    - Appeler `aiChatAppendMessage(message, 'user')`, vider `#ai-chat-input`, passer `aiChatLoading = true`
    - Désactiver `#ai-chat-send-btn` et `#ai-chat-input` (`disabled = true`)
    - Afficher `#ai-chat-typing`
    - Construire le `FormData` avec `action=chatbot_gemini`, `message`, `history=JSON.stringify(aiChatHistory)`
    - Appeler `fetch(window.location.href, {method: 'POST', body: formData})`
    - _Requirements: 2.6, 6.3, 6.5_

  - [ ]* 9.4 Écrire le test de propriété — Propriété 10 : Rejet des messages vides côté client
    - **Propriété 10 : Rejet des messages vides côté client**
    - Générer des strings whitespace-only (espaces, tabs, newlines, combinaisons)
    - Vérifier qu'aucun appel `fetch` n'est déclenché (mock fetch)
    - **Valide : Requirements 6.5**

  - [ ] 9.5 Implémenter le traitement de la réponse AJAX
    - Dans le `.then()` du fetch : parser le JSON, masquer `#ai-chat-typing`
    - Si `data.success === true` : appeler `aiChatAppendMessage(data.reply, 'ai')`, pousser les deux messages dans `aiChatHistory`
    - Si `data.success === false` : appeler `aiChatAppendMessage(data.message, 'error')`
    - Dans le `.catch()` : appeler `aiChatAppendMessage('Erreur de connexion. Veuillez réessayer.', 'error')`
    - Dans le `.finally()` : réactiver `#ai-chat-send-btn` et `#ai-chat-input`, passer `aiChatLoading = false`
    - _Requirements: 6.1, 6.2, 6.4_

  - [ ]* 9.6 Écrire le test de propriété — Propriété 9 : Désactivation des contrôles pendant le chargement
    - **Propriété 9 : Désactivation des contrôles pendant le chargement**
    - Pour tout état de chargement (requête AJAX en cours), vérifier `disabled=true` sur `#ai-chat-input` et `#ai-chat-send-btn`
    - Après réception de la réponse (succès ou erreur), vérifier `disabled=false`
    - **Valide : Requirements 6.3, 6.4**

  - [ ] 9.7 Implémenter `aiChatAppendMessage(text, type)` et le scroll automatique
    - Créer un `<div>` avec les classes `ai-chat-msg` + `ai-chat-msg--{type}` (user / ai / error)
    - Utiliser `textContent` (pas `innerHTML`) pour éviter les injections XSS
    - Appender dans `#ai-chat-messages`
    - Après ajout : `messagesDiv.scrollTop = messagesDiv.scrollHeight`
    - _Requirements: 2.7, 2.8, 2.10_

  - [ ]* 9.8 Écrire le test de propriété — Propriété 11 : Affichage différencié des messages
    - **Propriété 11 : Affichage différencié des messages**
    - Pour tout message ajouté (user ou ai), vérifier la présence de la classe CSS correcte (`ai-chat-msg--user` ou `ai-chat-msg--ai`)
    - **Valide : Requirements 2.7**

  - [ ] 9.9 Implémenter la soumission par touche Entrée
    - Sur `keydown` de `#ai-chat-input` : si `event.key === 'Enter'` et pas `event.shiftKey`, appeler `aiChatSendMessage()` et `event.preventDefault()`
    - _Requirements: 2.6_

- [ ] 10. Checkpoint final — Vérifier l'intégration complète
  - Vérifier que tous les IDs et classes du widget utilisent le préfixe `ai-chat-`
  - Vérifier que les modals existants (`addProjectOverlay`, `editProjectOverlay`, `editTacheOverlay`) s'ouvrent et se ferment normalement
  - Vérifier que le système de notifications (polling `get_notifs`) fonctionne toujours
  - Vérifier que le widget est absent du HTML pour role=1, role=3 et session absente
  - S'assurer que la clé API n'apparaît nulle part dans le HTML rendu ni dans les réponses JSON
  - Demander à l'utilisateur si des ajustements sont nécessaires.

## Notes

- Les tâches marquées `*` sont optionnelles et peuvent être ignorées pour un MVP rapide
- Chaque tâche référence les exigences spécifiques pour la traçabilité
- Les checkpoints garantissent une validation incrémentale
- Les tests de propriétés valident les invariants universels du système
- La clé API Gemini est définie comme constante PHP côté serveur uniquement — elle ne doit jamais apparaître dans le HTML, les logs ou les réponses JSON
- Le préfixe `ai-chat-` sur tous les IDs/classes garantit l'absence de conflits avec les éléments existants de `projects.php`
