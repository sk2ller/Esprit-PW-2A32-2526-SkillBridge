<?php
/**
 * AiAssistantController — SkillBridge
 * Gère les requêtes vers l'API Anthropic Claude
 * Chaque rôle a un assistant personnalisé avec son propre system prompt
 */
require_once(__DIR__ . '/../config_ai.php');

class AiAssistantController
{
    // URL de l'API Anthropic Messages
    private $apiUrl = 'https://api.anthropic.com/v1/messages';

    // Version de l'API
    private $apiVersion = '2023-06-01';

    /**
     * System prompts par rôle
     * Nova = Client, Forge = Freelancer/Vendeur, Sigma = Admin
     */
    private function getSystemPrompt($role, $context = '')
    {
        $prompts = [
            'client' => 'You are Nova, a friendly shopping assistant for SkillBridge, a web design marketplace. Your job is to help clients find the perfect web templates and designs for their needs.

You can:
- Help clients describe what they are looking for and suggest relevant categories or search terms
- Explain the difference between products (templates, UI kits, full website designs, etc.)
- Answer questions about how purchasing and downloading works
- Help clients understand licensing terms
- Suggest products based on their described needs or industry
- Assist with any issues regarding their purchases or account

You are warm, enthusiastic, and non-technical. Avoid jargon. When you do not know specific product details, encourage the client to use the search and filters. Always be encouraging and positive. Respond in the same language as the user (French or English).

Current context about the client: ' . $context,

            'vendeur' => 'You are Forge, a professional mentor and assistant for freelancers selling digital products on SkillBridge, a web design marketplace.

You can:
- Help freelancers write compelling product titles and descriptions that convert
- Suggest competitive pricing strategies based on product type and complexity
- Give tips on what types of products sell well (landing pages, dashboards, e-commerce templates, etc.)
- Help improve product presentation (what screenshots to include, how to write feature lists)
- Answer questions about the platform upload process, file formats, and listing requirements
- Provide motivation and best practices for building a successful freelance product business
- Help with SEO tags and keywords for product listings

You are direct, professional, and results-oriented. You speak like a successful senior freelancer mentoring a junior one. Be specific and actionable. Respond in the same language as the user.

Current context about the freelancer and their products: ' . $context,

            'admin' => 'You are Sigma, an intelligent platform management assistant for the admin of SkillBridge, a web design marketplace.

You can:
- Summarize platform activity based on data provided to you (new users, new products, recent sales)
- Help the admin interpret analytics and spot trends or anomalies
- Suggest moderation actions when flagged content is described to you
- Help draft announcements, emails, or policy text for the platform
- Answer questions about best practices for running a digital marketplace
- Help prioritize tasks and suggest what to focus on

You are precise, analytical, and efficient. You communicate like a senior business analyst. Always back your suggestions with reasoning. Respond in the same language as the user.

Current platform context and stats: ' . $context
        ];

        return $prompts[$role] ?? $prompts['client'];
    }

    /**
     * Noms des assistants par rôle
     */
    public static function getAssistantName($role)
    {
        $names = [
            'client'  => 'Nova',
            'vendeur' => 'Forge',
            'admin'   => 'Sigma'
        ];
        return $names[$role] ?? 'Nova';
    }

    /**
     * Envoie un message à l'assistant IA et retourne la réponse
     * @param string $message — Le message de l'utilisateur
     * @param string $role — client, vendeur, admin
     * @param string $context — JSON avec données contextuelles
     * @param array $history — Historique des messages [{role, content}, ...]
     * @return array ['success' => bool, 'response' => string, 'error' => string]
     */
    public function chat($message, $role = 'client', $context = '', $history = [])
    {
        // TOUJOURS utiliser le mode local (pas besoin d'API externe)
        return $this->simulateChat($message, $role);
        
        // Note: Le code API Anthropic ci-dessous est conservé pour référence
        // mais n'est jamais exécuté. Pour l'activer, commentez la ligne ci-dessus
        // et décommentez le bloc suivant avec une vraie clé API.
        
        /*
        // Vérifier la clé API
        if (!defined('ANTHROPIC_API_KEY') || 
            ANTHROPIC_API_KEY === 'YOUR_ANTHROPIC_API_KEY_HERE') {
            return $this->simulateChat($message, $role);
        }

        // Construire les messages pour l'API
        $messages = [];

        // Ajouter l'historique (max 10 messages)
        $recentHistory = array_slice($history, -10);
        foreach ($recentHistory as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['content']
                ];
            }
        }

        // Ajouter le message actuel
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        // Construire le corps de la requête
        $requestBody = [
            'model' => ANTHROPIC_MODEL,
            'max_tokens' => ANTHROPIC_MAX_TOKENS,
            'system' => $this->getSystemPrompt($role, $context),
            'messages' => $messages
        ];

        // Appel API via cURL
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . ANTHROPIC_API_KEY,
                'anthropic-version: ' . $this->apiVersion
            ],
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,        // Timeout 30 secondes
            CURLOPT_CONNECTTIMEOUT => 10  // Timeout de connexion 10s
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        */

        // Gestion des erreurs cURL
        if ($curlError) {
            return [
                'success' => false,
                'response' => '',
                'error' => 'Connection error: ' . $curlError
            ];
        }

        // Décoder la réponse JSON
        $data = json_decode($response, true);

        // Gestion des erreurs HTTP
        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Unknown API error';

            // Messages conviviaux selon le code d'erreur
            if ($httpCode === 429) {
                $errorMsg = 'Too many requests. Please wait a moment and try again.';
            } elseif ($httpCode === 401) {
                $errorMsg = 'Invalid API key. Please check your configuration.';
            } elseif ($httpCode === 500 || $httpCode === 503) {
                $errorMsg = 'The AI service is temporarily unavailable. Please try again later.';
            }

            return [
                'success' => false,
                'response' => '',
                'error' => $errorMsg
            ];
        }

        // Extraire le texte de la réponse
        $text = $data['content'][0]['text'] ?? '';

        if (empty($text)) {
            return [
                'success' => false,
                'response' => '',
                'error' => 'Empty response from AI'
            ];
        }

        return [
            'success' => true,
            'response' => $text,
            'error' => ''
        ];
    }

    /**
     * Mode simulation IA sans clé d'API réelle (basé sur mots-clés et patterns)
     * Fournit des réponses intelligentes et contextuelles sans API externe
     */
    private function simulateChat($message, $role)
    {
        // Simuler un temps de réflexion réaliste
        usleep(500000); // 0.5 secondes

        $messageLower = mb_strtolower($message, 'UTF-8');
        $response = "";

        // Réponses spécifiques selon le rôle
        if ($role === 'client') {
            // Salutations
            if (preg_match('/\b(bonjour|salut|hello|hi|hey|bonsoir)\b/i', $messageLower)) {
                $responses = [
                    "Bonjour ! 👋 Je suis Nova, votre assistante shopping. Comment puis-je vous aider à trouver le produit parfait aujourd'hui ?",
                    "Salut ! ✨ Bienvenue sur SkillBridge. Je suis là pour vous aider à découvrir nos meilleurs templates et produits numériques.",
                    "Hello ! Je suis Nova. Que recherchez-vous aujourd'hui ? Un template web, un kit UI, ou autre chose ?"
                ];
                $response = $responses[array_rand($responses)];
            }
            // Recherche de produits/templates
            elseif (preg_match('/\b(template|produit|cherch|trouv|besoin|veux|voudr|acheter|e-commerce|dashboard|landing|ui kit)\b/i', $messageLower)) {
                if (preg_match('/\b(e-commerce|boutique|shop|vente)\b/i', $messageLower)) {
                    $response = "Excellent choix ! 🛍️ Nos **templates e-commerce** sont parfaits pour créer une boutique en ligne professionnelle. Ils incluent :\n\n• Panier et checkout intégrés\n• Design responsive mobile-first\n• Intégration paiement facile\n• Pages produits optimisées\n\nJe vous recommande de filtrer par catégorie **Templates Web** et chercher 'e-commerce'. Prix entre 40€ et 80€.";
                } elseif (preg_match('/\b(dashboard|admin|panel)\b/i', $messageLower)) {
                    $response = "Les **dashboards admin** sont très populaires ! 📊 Nous avons des templates avec :\n\n• Graphiques interactifs (Chart.js)\n• Tables de données avancées\n• Mode sombre/clair\n• Composants UI modernes\n\nRegardez dans la catégorie **Templates Web** → filtrez par 'dashboard'. Prix moyens : 30-60€.";
                } elseif (preg_match('/\b(landing|page|site)\b/i', $messageLower)) {
                    $response = "Les **landing pages** sont idéales pour convertir vos visiteurs ! 🚀 Cherchez des templates avec :\n\n• Design moderne et épuré\n• Sections hero impactantes\n• Formulaires de contact\n• Optimisation SEO\n\nCatégorie **Templates Web** → 'landing page'. Prix : 15-35€.";
                } else {
                    $response = "Pour trouver le produit idéal, utilisez nos **filtres par catégorie** :\n\n📱 **Templates Web** - Sites complets, dashboards, landing pages\n🎨 **Graphisme** - Logos, illustrations, mockups\n📚 **E-books** - Guides et tutoriels\n🔌 **Plugins & Scripts** - Extensions WordPress, scripts PHP/JS\n\nQue recherchez-vous précisément ?";
                }
            }
            // Prix et paiement
            elseif (preg_match('/\b(prix|coût|pay|paiement|acheter|carte|paypal|sécuris)\b/i', $messageLower)) {
                $response = "💳 **Paiement 100% sécurisé** sur SkillBridge :\n\n✅ Cartes bancaires (Visa, Mastercard)\n✅ PayPal\n✅ Téléchargement immédiat après achat\n✅ Licence d'utilisation incluse\n✅ Support du vendeur\n\nNos prix varient selon le type de produit :\n• Templates simples : 15-30€\n• Templates avancés : 40-80€\n• E-books : 10-30€\n• Plugins : 20-50€\n\nTous les prix sont affichés TTC.";
            }
            // Téléchargement et licence
            elseif (preg_match('/\b(télécharg|download|licence|utilisation|droit|commercial)\b/i', $messageLower)) {
                $response = "📥 **Téléchargement et Licences** :\n\n✅ Téléchargement **immédiat** après paiement\n✅ Fichiers sources complets inclus\n✅ **Licence d'utilisation personnelle** par défaut\n✅ Licence commerciale disponible (vérifiez la description du produit)\n✅ Mises à jour gratuites pendant 6 mois\n\nVous recevrez un email avec le lien de téléchargement. Conservez-le précieusement !";
            }
            // Support et aide
            elseif (preg_match('/\b(aide|support|problème|bug|marche pas|erreur|contact)\b/i', $messageLower)) {
                $response = "🆘 **Besoin d'aide ?** Voici comment obtenir du support :\n\n1. **Support vendeur** : Contactez directement le vendeur via le bouton 'Contacter' sur la page produit\n2. **FAQ** : Consultez notre centre d'aide\n3. **Email** : support@skillbridge.com\n4. **Délai** : Réponse sous 24-48h\n\nPour un problème technique, précisez :\n• Le produit concerné\n• Le navigateur utilisé\n• Une capture d'écran si possible";
            }
            // Qualité et avis
            elseif (preg_match('/\b(qualité|avis|review|note|étoile|recommand|bon|meilleur)\b/i', $messageLower)) {
                $response = "⭐ **Qualité garantie** sur SkillBridge :\n\n✅ Tous les produits sont **vérifiés** par notre équipe\n✅ Système de **notes et avis** clients\n✅ Vendeurs **certifiés** avec portfolio\n✅ Garantie satisfait ou remboursé 14 jours\n\nPour choisir un bon produit :\n• Regardez les **notes** (minimum 4/5 étoiles)\n• Lisez les **avis clients**\n• Vérifiez les **captures d'écran**\n• Consultez le **portfolio du vendeur**";
            }
            // Remerciements
            elseif (preg_match('/\b(merci|thanks|super|génial|parfait|ok)\b/i', $messageLower)) {
                $responses = [
                    "Avec grand plaisir ! 😊 N'hésitez pas si vous avez d'autres questions. Bon shopping sur SkillBridge !",
                    "De rien ! ✨ Je suis là si vous avez besoin d'aide. Bonne découverte de nos produits !",
                    "Content de vous aider ! 🎉 Profitez bien de votre achat et à bientôt sur SkillBridge !"
                ];
                $response = $responses[array_rand($responses)];
            }
            // Réponse par défaut
            else {
                $response = "Je suis Nova, votre assistante shopping ! 🛍️ Je peux vous aider avec :\n\n• 🔍 **Trouver des produits** (templates, e-books, plugins...)\n• 💰 **Questions sur les prix** et paiements\n• 📥 **Téléchargement** et licences\n• ⭐ **Conseils qualité** et avis\n• 🆘 **Support** et aide\n\nQue puis-je faire pour vous ?";
            }
        } 
        elseif ($role === 'vendeur') {
            // Salutations
            if (preg_match('/\b(bonjour|salut|hello|hi|hey)\b/i', $messageLower)) {
                $responses = [
                    "Salut ! 🔥 Je suis Forge, ton mentor freelance. Prêt à booster tes ventes ?",
                    "Hey ! Je suis Forge. Comment puis-je t'aider à améliorer tes produits et augmenter tes revenus ?",
                    "Hello ! 💪 Forge ici. Parlons stratégie pour faire décoller ton business sur SkillBridge."
                ];
                $response = $responses[array_rand($responses)];
            }
            // Ajout de produits
            elseif (preg_match('/\b(ajout|créer|nouveau|upload|publier|vend|produit)\b/i', $messageLower)) {
                $response = "📦 **Créer un produit qui vend** :\n\n**1. Titre accrocheur** (max 60 caractères)\n• Inclus le type : 'Template', 'Kit', 'Plugin'\n• Mentionne la techno : 'React', 'WordPress', 'Figma'\n• Exemple : 'Dashboard Admin React - Dark Mode & Charts'\n\n**2. Description détaillée**\n• Fonctionnalités principales (bullet points)\n• Technologies utilisées\n• Ce qui est inclus dans le package\n• Instructions d'installation\n\n**3. Visuels de qualité**\n• Minimum 3 captures d'écran (1200x800px)\n• Mockups professionnels\n• Démo live si possible\n\n**4. Prix compétitif**\n• Regarde les produits similaires\n• Commence à 20-30€ pour tester le marché\n\nVa dans **Mes Produits** → **Ajouter un produit** pour commencer !";
            }
            // Stratégie de prix
            elseif (preg_match('/\b(prix|tarif|combien|vendre|coût)\b/i', $messageLower)) {
                $response = "💰 **Stratégie de prix gagnante** :\n\n**Templates simples** (15-30€)\n• Landing page basique\n• Kit UI avec 20-30 composants\n• Template WordPress simple\n\n**Templates intermédiaires** (35-60€)\n• Dashboard avec graphiques\n• E-commerce complet\n• Template multi-pages\n\n**Templates premium** (70-120€)\n• Système complet (admin + front)\n• Intégrations API avancées\n• Support premium inclus\n\n**Astuce** : Commence à 20% moins cher que la concurrence pour les 10 premières ventes, puis augmente progressivement. Propose des **packs** (3 templates = -30%) pour booster le panier moyen !";
            }
            // Optimisation SEO et visibilité
            elseif (preg_match('/\b(seo|visibilité|référenc|trouv|recherch|tag|mot-clé)\b/i', $messageLower)) {
                $response = "🔍 **Optimiser la visibilité de tes produits** :\n\n**1. Mots-clés stratégiques**\n• Utilise 5-8 tags pertinents\n• Inclus la techno : 'react', 'wordpress', 'figma'\n• Ajoute le type : 'template', 'dashboard', 'landing'\n• Exemple : react, dashboard, admin, dark-mode, charts\n\n**2. Titre optimisé**\n• Commence par le type de produit\n• Inclus les features principales\n• Max 60 caractères\n\n**3. Description riche**\n• Utilise les mots-clés naturellement\n• Sections claires avec titres\n• Liste à puces pour les features\n\n**4. Catégorie précise**\n• Choisis LA bonne catégorie\n• Ne mets pas tout dans 'Divers'\n\n**Résultat** : +300% de visibilité en moyenne !";
            }
            // Améliorer les ventes
            elseif (preg_match('/\b(vente|vend|client|achet|conversion|améliorer|augment)\b/i', $messageLower)) {
                $response = "📈 **Booster tes ventes - Les 5 règles d'or** :\n\n**1. Visuels impeccables** 🎨\n• Screenshots HD (min 1200x800px)\n• Mockups professionnels (Figma, Photoshop)\n• GIF animé pour montrer les interactions\n• Démo live hébergée\n\n**2. Description qui convertit** ✍️\n• Commence par les bénéfices (pas les features)\n• Utilise des bullet points\n• Ajoute un FAQ\n• Mentionne le support inclus\n\n**3. Prix psychologique** 💰\n• 29€ au lieu de 30€\n• Offre de lancement (-20% les 7 premiers jours)\n• Bundle deals\n\n**4. Preuve sociale** ⭐\n• Demande des avis aux premiers clients\n• Affiche ton portfolio\n• Mentionne tes stats (téléchargements, projets)\n\n**5. Support réactif** 🆘\n• Réponds en moins de 24h\n• Propose des mises à jour\n• Crée une communauté Discord\n\n**Résultat attendu** : x3 à x5 tes ventes en 30 jours !";
            }
            // Statistiques et analytics
            elseif (preg_match('/\b(stat|analytics|performance|vues|télécharg|revenu)\b/i', $messageLower)) {
                $response = "📊 **Analyser tes performances** :\n\n**Dashboard vendeur** → **Statistiques**\n\n**Métriques clés à suivre** :\n• 👁️ **Vues** : Combien de personnes voient ton produit\n• 🛒 **Taux de conversion** : Vues → Achats (objectif : 2-5%)\n• 💰 **Revenu** : Total et par produit\n• ⭐ **Note moyenne** : Maintiens au-dessus de 4.5/5\n• 📥 **Téléchargements** : Popularité réelle\n\n**Optimisations selon les stats** :\n• Beaucoup de vues, peu d'achats → Améliore description/prix\n• Peu de vues → Travaille le SEO et les visuels\n• Bonne note → Augmente le prix progressivement\n• Mauvaise note → Améliore le produit et le support\n\n**Objectif** : 10+ ventes/mois par produit = 300-500€/mois !";
            }
            // Remerciements
            elseif (preg_match('/\b(merci|thanks|super|génial|parfait|ok)\b/i', $messageLower)) {
                $responses = [
                    "De rien champion ! 🔥 Continue comme ça, tu vas cartonner !",
                    "Avec plaisir ! 💪 N'hésite pas si tu as d'autres questions. Go vendre !",
                    "Content de t'aider ! 🚀 Maintenant, au boulot - ces produits ne vont pas se vendre tout seuls ! 😉"
                ];
                $response = $responses[array_rand($responses)];
            }
            // Réponse par défaut
            else {
                $response = "Je suis Forge, ton mentor freelance ! 🔥 Je peux t'aider avec :\n\n• 📦 **Créer des produits** qui se vendent\n• 💰 **Stratégie de prix** optimale\n• 🔍 **SEO et visibilité** sur la plateforme\n• 📈 **Booster tes ventes** (conversion x3-x5)\n• 📊 **Analyser tes stats** et performances\n\nQuelle est ta question ?";
            }
        } 
        elseif ($role === 'admin') {
            // Salutations
            if (preg_match('/\b(bonjour|salut|hello|hi)\b/i', $messageLower)) {
                $response = "Bonjour. ⚡ Je suis Sigma, votre assistant de gestion. Comment puis-je vous aider à piloter la plateforme ?";
            }
            // Statistiques
            elseif (preg_match('/\b(stat|dashboard|performance|chiffre|revenu|vente)\b/i', $messageLower)) {
                $response = "📊 **Tableau de bord administrateur** :\n\n**Métriques clés disponibles** :\n• 💰 **Revenus** : Total, par jour, par mois\n• 📦 **Produits** : Total, par catégorie, en attente de validation\n• 👥 **Utilisateurs** : Clients, vendeurs, nouveaux inscrits\n• 🛒 **Commandes** : Total, statuts, taux de conversion\n• ⭐ **Satisfaction** : Notes moyennes, avis récents\n\n**Graphiques disponibles** :\n• Évolution des ventes (7 jours)\n• Top 5 produits\n• Répartition par catégorie\n• Croissance utilisateurs\n\nConsultez votre **Dashboard Admin** pour les données en temps réel.";
            }
            // Gestion utilisateurs
            elseif (preg_match('/\b(utilisateur|user|client|vendeur|compte|ban|suspend)\b/i', $messageLower)) {
                $response = "👥 **Gestion des utilisateurs** :\n\n**Actions disponibles** :\n• ✅ **Valider** un compte vendeur\n• ⏸️ **Suspendre** un compte (violation des règles)\n• 🚫 **Bannir** définitivement\n• 🔄 **Changer le rôle** (client ↔ vendeur)\n• 📧 **Contacter** par email\n\n**Filtres** :\n• Par rôle (client, vendeur, admin)\n• Par date d'inscription\n• Par statut (actif, suspendu)\n• Par nombre de ventes\n\n**Bonnes pratiques** :\n• Avertir avant de suspendre\n• Documenter les raisons\n• Donner une chance de correction\n\nAllez dans **Utilisateurs** pour gérer les comptes.";
            }
            // Modération produits
            elseif (preg_match('/\b(produit|modération|valid|approuv|refus|qualité)\b/i', $messageLower)) {
                $response = "🔍 **Modération des produits** :\n\n**Critères de validation** :\n✅ **Qualité technique**\n• Code propre et fonctionnel\n• Pas de malware ou code malveillant\n• Documentation incluse\n\n✅ **Qualité visuelle**\n• Screenshots de qualité (min 800x600px)\n• Description claire et complète\n• Pas de contenu copié\n\n✅ **Conformité légale**\n• Pas de contenu protégé par copyright\n• Licence d'utilisation claire\n• Prix raisonnable\n\n**Actions** :\n• ✅ **Approuver** : Produit en ligne immédiatement\n• ❌ **Refuser** : Envoyer un message avec les raisons\n• ⏸️ **Demander modifications** : Feedback constructif\n\n**Délai** : Traiter sous 48h maximum.";
            }
            // Catégories
            elseif (preg_match('/\b(catégorie|category|organis|class)\b/i', $messageLower)) {
                $response = "📁 **Gestion des catégories** :\n\n**Catégories actuelles** :\n• Templates Web\n• E-books\n• Graphisme\n• Audio & Musique\n• Plugins & Scripts\n• Photos & Vidéos\n• Formations\n• Outils Business\n\n**Actions disponibles** :\n• ➕ **Ajouter** une nouvelle catégorie\n• ✏️ **Modifier** nom, description, icône\n• 🗑️ **Supprimer** (si aucun produit)\n• 🔄 **Réorganiser** l'ordre d'affichage\n\n**Bonnes pratiques** :\n• Max 12 catégories (lisibilité)\n• Noms clairs et explicites\n• Icônes cohérentes (Font Awesome)\n• Description SEO-friendly\n\nAllez dans **Catégories** pour gérer.";
            }
            // Réponse par défaut
            else {
                $response = "Je suis Sigma, votre assistant administrateur. ⚡ Je peux vous informer sur :\n\n• 📊 **Statistiques** et performances\n• 👥 **Gestion utilisateurs** (validation, suspension)\n• 🔍 **Modération produits** (approbation, refus)\n• 📁 **Catégories** et organisation\n• 🛒 **Commandes** et transactions\n\nQue souhaitez-vous savoir ?";
            }
        } 
        else {
            $response = "Bonjour, je suis l'assistant IA de SkillBridge (mode local).";
        }

        return [
            'success' => true,
            'response' => $response,
            'error' => ''
        ];
    }
}
