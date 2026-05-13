# Requirements Document

## Introduction

Cette fonctionnalité ajoute un chatbot IA intégré à la page `projects.php` de l'application SkillBridge, accessible exclusivement aux clients (role=2). Le chatbot utilise l'API Google Gemini (modèle `gemini-2.0-flash`) pour répondre aux questions des clients sur leurs projets : statut, tâches, budget, freelancers assignés, et conseils de gestion de projet. Il se présente sous la forme d'un widget flottant persistant dans la section "Mes Projets" (onglet `tab=mes`).

L'appel à l'API Gemini se fait côté serveur (PHP) via un endpoint AJAX dédié, afin de ne pas exposer la clé API dans le code JavaScript côté client. Le contexte des projets du client est injecté dans le prompt système pour permettre des réponses personnalisées et pertinentes.

---

## Glossaire

- **Chatbot** : Widget de conversation IA intégré à la page projets, visible uniquement pour les clients.
- **Client** : Utilisateur avec `role=2` dans la table `user`.
- **Gemini_API** : Service Google Generative Language API, modèle `gemini-2.0-flash`, appelé via `POST https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent`.
- **Chatbot_Endpoint** : Fichier PHP serveur (`Controllers/ChatbotController.php`) qui reçoit les requêtes AJAX du chatbot, construit le prompt avec le contexte projet, appelle la Gemini_API et retourne la réponse.
- **Contexte_Projet** : Données structurées sur les projets du client (titre, statut, budget, avancement, tâches, freelancers) injectées dans le prompt système envoyé à la Gemini_API.
- **Widget_Flottant** : Interface de chat positionnée en bas à droite de la page, superposée au contenu, avec bouton d'ouverture/fermeture.
- **Historique_Session** : Liste des échanges (messages utilisateur + réponses IA) conservée en mémoire JavaScript pour la durée de la session de navigation.
- **Prompt_Système** : Instructions initiales envoyées à la Gemini_API définissant le rôle du chatbot et incluant le Contexte_Projet.

---

## Requirements

### Requirement 1 : Accès et visibilité du chatbot

**User Story :** En tant que client, je veux voir le chatbot IA uniquement dans ma section "Mes Projets", afin de ne pas perturber l'expérience des autres utilisateurs.

#### Acceptance Criteria

1. WHILE l'utilisateur connecté a `role=2`, THE Widget_Flottant SHALL être affiché sur la page `projects.php` quel que soit l'onglet actif.
2. WHILE l'utilisateur connecté a `role=1` ou `role=3`, THE Widget_Flottant SHALL rester masqué et aucun code HTML du chatbot ne SHALL être rendu dans la page.
3. IF l'utilisateur n'est pas connecté (`$_SESSION['user_id']` absent), THEN THE Widget_Flottant SHALL rester masqué.
4. THE Widget_Flottant SHALL être positionné en bas à droite de la page avec un z-index supérieur à tous les autres éléments de la page.

---

### Requirement 2 : Interface du widget flottant

**User Story :** En tant que client, je veux un widget de chat discret et facile à utiliser, afin de pouvoir poser mes questions sans quitter la page projets.

#### Acceptance Criteria

1. THE Widget_Flottant SHALL afficher un bouton circulaire avec une icône IA (robot ou étincelle) en état fermé.
2. WHEN le client clique sur le bouton du Widget_Flottant, THE Chatbot SHALL s'ouvrir et afficher la fenêtre de conversation.
3. WHEN le client clique sur le bouton de fermeture (×) de la fenêtre de conversation, THE Chatbot SHALL se fermer et revenir à l'état bouton.
4. THE Widget_Flottant SHALL afficher un message de bienvenue personnalisé au premier chargement, mentionnant le prénom du client et listant les types de questions possibles.
5. THE Widget_Flottant SHALL inclure une zone de saisie de texte et un bouton "Envoyer" pour soumettre les messages.
6. WHEN le client appuie sur la touche Entrée dans la zone de saisie, THE Chatbot SHALL soumettre le message (comportement identique au clic sur "Envoyer").
7. THE Widget_Flottant SHALL afficher visuellement les messages du client et les réponses de l'IA de manière distincte (alignement et couleurs différents).
8. WHILE une réponse de la Gemini_API est en cours de chargement, THE Widget_Flottant SHALL afficher un indicateur de chargement (animation de points ou spinner) dans la zone de messages.
9. THE Widget_Flottant SHALL conserver l'Historique_Session et l'afficher dans la zone de messages pendant toute la durée de la session de navigation.
10. WHEN de nouveaux messages sont ajoutés, THE Widget_Flottant SHALL faire défiler automatiquement la zone de messages vers le bas.

---

### Requirement 3 : Appel sécurisé à l'API Gemini

**User Story :** En tant que développeur, je veux que la clé API Gemini ne soit jamais exposée côté client, afin de protéger les credentials de l'application.

#### Acceptance Criteria

1. THE Chatbot_Endpoint SHALL être un fichier PHP (`Controllers/ChatbotController.php`) qui reçoit les requêtes AJAX via `POST` avec les paramètres `message` (texte du client) et `history` (tableau JSON de l'historique).
2. THE Chatbot_Endpoint SHALL vérifier que `$_SESSION['user_id']` est défini et que `$_SESSION['user_role'] === 2` avant de traiter toute requête, et SHALL retourner une erreur HTTP 403 si la vérification échoue.
3. THE Chatbot_Endpoint SHALL appeler la Gemini_API via `curl` PHP avec le header `X-goog-api-key` contenant la clé API définie comme constante PHP côté serveur uniquement.
4. THE Chatbot_Endpoint SHALL retourner une réponse JSON avec la structure `{"success": true, "reply": "..."}` en cas de succès.
5. IF la Gemini_API retourne une erreur HTTP (4xx ou 5xx), THEN THE Chatbot_Endpoint SHALL retourner `{"success": false, "message": "Service IA temporairement indisponible."}` sans exposer les détails de l'erreur.
6. IF le paramètre `message` est vide ou dépasse 1000 caractères, THEN THE Chatbot_Endpoint SHALL retourner `{"success": false, "message": "Message invalide."}` sans appeler la Gemini_API.
7. THE Chatbot_Endpoint SHALL utiliser `Config::getConnexion()` (PDO) pour récupérer le Contexte_Projet du client depuis la base de données MySQL.

---

### Requirement 4 : Contexte projet injecté dans le prompt

**User Story :** En tant que client, je veux que le chatbot connaisse mes projets, afin d'obtenir des réponses précises et personnalisées sans avoir à tout réexpliquer.

#### Acceptance Criteria

1. WHEN le Chatbot_Endpoint reçoit une requête valide, THE Chatbot_Endpoint SHALL récupérer depuis la base de données tous les projets du client (`id_client = $_SESSION['user_id']`) avec leurs champs : `id`, `titre`, `statut`, `etat`, `budget`, `avancement`, `date_creation`.
2. WHEN le Chatbot_Endpoint reçoit une requête valide, THE Chatbot_Endpoint SHALL récupérer pour chaque projet les tâches associées avec leurs champs : `titre`, `statut`, `prix`, `payee`, ainsi que le nom et prénom du freelancer assigné.
3. THE Chatbot_Endpoint SHALL construire un Prompt_Système en français qui définit le rôle du chatbot comme "assistant de gestion de projet pour SkillBridge" et inclut le Contexte_Projet formaté en texte structuré.
4. THE Chatbot_Endpoint SHALL transmettre l'Historique_Session reçu en paramètre à la Gemini_API dans le champ `contents` pour maintenir la cohérence de la conversation.
5. IF le client n'a aucun projet, THEN THE Chatbot_Endpoint SHALL inclure dans le Prompt_Système une mention indiquant qu'aucun projet n'est encore créé, et le chatbot SHALL proposer d'aider à créer un premier projet.

---

### Requirement 5 : Qualité et pertinence des réponses

**User Story :** En tant que client, je veux que le chatbot réponde uniquement aux questions liées à mes projets et à la gestion de projet, afin d'avoir un assistant focalisé et utile.

#### Acceptance Criteria

1. THE Prompt_Système SHALL instruire la Gemini_API de répondre exclusivement en français.
2. THE Prompt_Système SHALL instruire la Gemini_API de limiter ses réponses aux sujets suivants : statut et avancement des projets du client, détail des tâches, budget et paiements, conseils de gestion de projet, et fonctionnalités de la plateforme SkillBridge.
3. THE Prompt_Système SHALL instruire la Gemini_API de refuser poliment toute question hors sujet (non liée aux projets ou à la gestion de projet) et de rediriger le client vers les sujets pertinents.
4. THE Prompt_Système SHALL instruire la Gemini_API de limiter ses réponses à 300 mots maximum pour rester concis et lisible dans le widget.
5. WHEN le client pose une question sur un projet spécifique, THE Chatbot SHALL utiliser les données du Contexte_Projet pour fournir une réponse basée sur les données réelles du projet.

---

### Requirement 6 : Gestion des erreurs côté client

**User Story :** En tant que client, je veux être informé clairement en cas de problème avec le chatbot, afin de comprendre ce qui se passe et de pouvoir réessayer.

#### Acceptance Criteria

1. IF le Chatbot_Endpoint retourne `success: false`, THEN THE Widget_Flottant SHALL afficher le message d'erreur reçu dans la zone de messages avec un style visuel distinct (couleur rouge ou orange).
2. IF une erreur réseau se produit lors de l'appel AJAX au Chatbot_Endpoint, THEN THE Widget_Flottant SHALL afficher le message "Erreur de connexion. Veuillez réessayer." dans la zone de messages.
3. WHILE une réponse est en cours de chargement, THE Widget_Flottant SHALL désactiver le bouton "Envoyer" et la zone de saisie pour éviter les soumissions multiples.
4. WHEN la réponse est reçue (succès ou erreur), THE Widget_Flottant SHALL réactiver le bouton "Envoyer" et la zone de saisie.
5. IF le client tente d'envoyer un message vide, THEN THE Widget_Flottant SHALL ne pas soumettre la requête et SHALL mettre le focus sur la zone de saisie.

---

### Requirement 7 : Intégration dans la page existante

**User Story :** En tant que développeur, je veux que le chatbot s'intègre proprement dans `projects.php` sans perturber les fonctionnalités existantes (kanban, messagerie, modals), afin de garantir la stabilité de l'application.

#### Acceptance Criteria

1. THE Widget_Flottant SHALL utiliser des identifiants CSS et JavaScript avec le préfixe `ai-chat-` pour éviter tout conflit avec les éléments existants de `projects.php`.
2. THE Widget_Flottant SHALL être injecté dans le DOM via un bloc PHP conditionnel `<?php if (role === 2): ?>` placé avant la balise `</body>` de `projects.php`.
3. THE Chatbot_Endpoint SHALL être inclus dans `projects.php` via `require_once` et son action AJAX SHALL être traitée dans le bloc `if ($_SERVER['REQUEST_METHOD'] === 'POST')` existant avec l'action `chatbot_gemini`.
4. THE Widget_Flottant SHALL ne pas interférer avec les modals existants (`addProjectOverlay`, `editProjectOverlay`, `editTacheOverlay`) ni avec le système de notifications Pusher.
5. THE Widget_Flottant SHALL être responsive et s'adapter aux écrans mobiles (largeur maximale de 90vw sur petits écrans).
