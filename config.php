<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'offrjob');
define('BASE_URL', 'http://localhost:8000/');
define('USE_MOCK_DATA', false);

// ── Gemini AI ─────────────────────────────────────────────────────────────────
// Put your Gemini API key below (get one free at https://aistudio.google.com/apikey)
// Replace VOTRE_CLE_GEMINI_ICI with your real key, e.g. AIzaSy...
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'AIzaSyCoxlmpAb8m_2oDn7JetIeXLQykr1pq9IQ');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash');

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
    public $insert_id = 0;
    
    public function __construct() {
    }
    
    public function query($sql) {
        return new MockResult([]);
    }
    
    public function prepare($sql) {
        return new MockStatement($sql, $this);
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
    
    public function __construct($sql, $db) {
        $this->sql = $sql;
        $this->db = $db;
    }
    
    public function bind_param($types, ...$values) {
        $this->params = $values;
    }
    
    public function execute() {
        return true;
    }
    
    public function get_result() {
        return new MockResult([]);
    }
}
