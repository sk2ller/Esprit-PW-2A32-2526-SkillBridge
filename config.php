<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'services_platform');
define('BASE_URL', 'http://localhost:8000/');
define('USE_MOCK_DATA', false); // Utiliser la base de données MySQL réelle

$MOCK_DB = null;

function getDB() {
    global $MOCK_DB;
    
    if (USE_MOCK_DATA) {
        if ($MOCK_DB === null) {
            $MOCK_DB = new MockDatabase();
        }
        return $MOCK_DB;
    }
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

class MockDatabase {
    private $categories = [];
    private $services = [];
    public $insert_id = 0;
    private static $nextServiceId = 5;
    private static $nextCategoryId = 9;
    
    public function __construct() {
        $this->initMockData();
    }
    
    private function initMockData() {
        // Mock Categories
        $this->categories = [
            ['id_categorie' => 1, 'nom_categorie' => 'Web Development', 'description' => 'Sites web, applications web, APIs', 'icone' => 'fas fa-code', 'nb_services' => 1],
            ['id_categorie' => 2, 'nom_categorie' => 'Design', 'description' => 'Graphic design, UI/UX, identité visuelle', 'icone' => 'fas fa-palette', 'nb_services' => 1],
            ['id_categorie' => 3, 'nom_categorie' => 'Mobile App', 'description' => 'Applications iOS et Android', 'icone' => 'fas fa-mobile-alt', 'nb_services' => 0],
            ['id_categorie' => 4, 'nom_categorie' => 'Marketing', 'description' => 'SEO, réseaux sociaux, publicité', 'icone' => 'fas fa-bullhorn', 'nb_services' => 1],
            ['id_categorie' => 5, 'nom_categorie' => 'Music', 'description' => 'Production musicale, mixage, mastering', 'icone' => 'fas fa-music', 'nb_services' => 0],
            ['id_categorie' => 6, 'nom_categorie' => 'Photography', 'description' => 'Retouche photo, shooting, vidéo', 'icone' => 'fas fa-camera', 'nb_services' => 0],
            ['id_categorie' => 7, 'nom_categorie' => 'Business', 'description' => 'Consulting, stratégie, finance', 'icone' => 'fas fa-briefcase', 'nb_services' => 0],
            ['id_categorie' => 8, 'nom_categorie' => 'IT Software', 'description' => 'Sécurité, cloud, DevOps', 'icone' => 'fas fa-server', 'nb_services' => 1],
        ];
        
        // Mock Services (simplifié - sans références à d'autres tables)
        $this->services = [
            ['id_service' => 1, 'titre' => 'Création site WordPress professionnel', 'description' => 'Je crée votre site WordPress sur mesure, responsive, optimisé SEO.', 'prix' => 299.99, 'delai_livraison' => 7, 'statut' => 'actif', 'id_categorie' => 1, 'nom_categorie' => 'Web Development'],
            ['id_service' => 2, 'titre' => 'Logo + Identité visuelle complète', 'description' => 'Design de logo professionnel avec charte graphique complète.', 'prix' => 149.00, 'delai_livraison' => 5, 'statut' => 'actif', 'id_categorie' => 2, 'nom_categorie' => 'Design'],
            ['id_service' => 3, 'titre' => 'Application React.js moderne', 'description' => 'Développement d\'une SPA React avec backend API REST.', 'prix' => 599.00, 'delai_livraison' => 14, 'statut' => 'actif', 'id_categorie' => 1, 'nom_categorie' => 'Web Development'],
            ['id_service' => 4, 'titre' => 'SEO On-page complet', 'description' => 'Audit et optimisation SEO complète de votre site.', 'prix' => 199.00, 'delai_livraison' => 10, 'statut' => 'en_attente', 'id_categorie' => 4, 'nom_categorie' => 'Marketing'],
        ];
    }
    
    public function query($sql) {
        if (strpos($sql, 'COUNT(s.id_service)') !== false) {
            return new MockResult($this->categories);
        }
        if (strpos($sql, 'FROM services') !== false) {
            return new MockResult($this->services);
        }
        return new MockResult([]);
    }
    
    public function prepare($sql) {
        return new MockStatement($sql, $this, $this->categories, $this->services);
    }
}

class MockResult {
    private $data;
    private $index = 0;
    
    public function __construct($data) {
        $this->data = $data;
        $this->index = 0;
    }
    
    public function fetch_all($type = MYSQLI_ASSOC) {
        return $this->data;
    }
    
    public function fetch_assoc() {
        if ($this->index < count($this->data)) {
            return $this->data[$this->index++];
        }
        return null;
    }
}

class MockStatement {
    private $sql;
    private $params = [];
    private $db;
    private $categories;
    private $services;
    
    public function __construct($sql, $db, $categories, $services) {
        $this->sql = $sql;
        $this->db = $db;
        $this->categories = $categories;
        $this->services = $services;
    }
    
    public function bind_param($types, ...$values) {
        $this->params = $values;
    }
    
    public function execute() {
        // Handle INSERT for categories
        if (strpos($this->sql, 'INSERT INTO categorie') !== false) {
            if (isset($this->params[0])) {
                $newId = count($this->categories) + 1;
                $this->categories[] = [
                    'id_categorie' => $newId,
                    'nom_categorie' => $this->params[0],
                    'description' => $this->params[1] ?? '',
                    'icone' => $this->params[2] ?? 'fas fa-folder',
                    'nb_services' => 0
                ];
                $this->db->insert_id = $newId;
            }
        }
        // Handle INSERT for services
        else if (strpos($this->sql, 'INSERT INTO services') !== false) {
            if (isset($this->params[0])) {
                $newId = count($this->services) + 1;
                $this->services[] = [
                    'id_service' => $newId,
                    'titre' => $this->params[0],
                    'description' => $this->params[1] ?? '',
                    'prix' => $this->params[2] ?? 0,
                    'delai_livraison' => $this->params[3] ?? 0,
                    'statut' => 'en_attente',
                    'id_categorie' => $this->params[4] ?? 1,
                    'nom_categorie' => ''
                ];
                $this->db->insert_id = $newId;
            }
        }
        // Handle UPDATE
        else if (strpos($this->sql, 'UPDATE') !== false) {
            if (strpos($this->sql, 'categorie') !== false && isset($this->params[3])) {
                foreach ($this->categories as &$cat) {
                    if ($cat['id_categorie'] == $this->params[3]) {
                        $cat['nom_categorie'] = $this->params[0];
                        $cat['description'] = $this->params[1];
                        $cat['icone'] = $this->params[2];
                        break;
                    }
                }
            } else if (strpos($this->sql, 'services') !== false && isset($this->params[5])) {
                foreach ($this->services as &$svc) {
                    if ($svc['id_service'] == $this->params[5]) {
                        $svc['titre'] = $this->params[0];
                        $svc['description'] = $this->params[1];
                        $svc['prix'] = $this->params[2];
                        $svc['delai_livraison'] = $this->params[3];
                        $svc['id_categorie'] = $this->params[4];
                        break;
                    }
                }
            }
        }
        // Handle DELETE
        else if (strpos($this->sql, 'DELETE') !== false) {
            if (strpos($this->sql, 'categorie') !== false && isset($this->params[0])) {
                $this->categories = array_filter($this->categories, function($cat) {
                    return $cat['id_categorie'] != $this->params[0];
                });
            } else if (strpos($this->sql, 'services') !== false && isset($this->params[0])) {
                $this->services = array_filter($this->services, function($svc) {
                    return $svc['id_service'] != $this->params[0];
                });
            }
        }
        return true;
    }
    
    public function get_result() {
        $result = [];
        
        // Catégories avec count
        if (strpos($this->sql, 'COUNT(s.id_service)') !== false) {
            $result = $this->categories;
        }
        // Services list
        else if (strpos($this->sql, 'FROM services') !== false) {
            $result = $this->services;
            // Filter by category if needed
            if (isset($this->params[0]) && is_numeric($this->params[0]) && strpos($this->sql, 'id_categorie') !== false) {
                $result = array_filter($result, function($s) {
                    return $s['id_categorie'] == $this->params[0];
                });
            }
            // Filter by status if needed
            if (isset($this->params[0]) && !is_numeric($this->params[0]) && strpos($this->sql, 'statut') !== false) {
                $result = array_filter($result, function($s) {
                    return $s['statut'] == $this->params[0];
                });
            }
        }
        // Single service
        else if (strpos($this->sql, 'id_service') !== false && isset($this->params[0])) {
            foreach ($this->services as $s) {
                if ($s['id_service'] == $this->params[0]) {
                    $result = [$s];
                    break;
                }
            }
        }
        // Single category
        else if (strpos($this->sql, 'id_categorie=?') !== false && isset($this->params[0])) {
            foreach ($this->categories as $c) {
                if ($c['id_categorie'] == $this->params[0]) {
                    $result = [$c];
                    break;
                }
            }
        }
        
        return new MockResult($result);
    }
}
?>

