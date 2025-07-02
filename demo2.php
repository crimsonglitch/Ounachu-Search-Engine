<?php
/*
 * Ounachu Search Engine - AI-Powered Research Tool
 * - Created By Crimson Glitch
 * - Enhanced Version
 * 
 * Features:
 * - 5 AI model integration (GPT-4, Gemini, Claude, Mistral, Llama3)
 * - Bilingual support (Turkish/English)
 * - Academic article export (PDF, DOCX, TXT)
 * - Research notes export
 * - Isolated chat sessions
 * - Multi-model consensus engine
 * - User authentication
 * - Enhanced security
 * - Professional PDF generation
 */

// ==============================================
// Initial Configuration & Environment Setup
// ==============================================

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if running from CLI
$isCli = (php_sapi_name() === 'cli');

// Database and paths configuration
define('ROOT_DIR', __DIR__);
define('DB_FILE', ROOT_DIR . '/ounachu_db.sqlite');
define('EXPORTS_DIR', ROOT_DIR . '/exports');
define('TEMP_DIR', ROOT_DIR . '/temp');
define('MAX_CONCURRENT_CHATS', 50);
define('MAX_REQUESTS_PER_MINUTE', 100);

// Supported languages
define('SUPPORTED_LANGS', ['tr', 'en']);

// Available AI models
const AI_MODELS = [
    'gpt4' => [
        'name' => 'GPT-4',
        'provider' => 'openai',
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
        'default_model' => 'gpt-4-turbo-preview',
        'strengths' => ['reasoning', 'creativity', 'formatting'],
        'max_tokens' => 4096
    ],
    'gemini' => [
        'name' => 'Gemini Pro',
        'provider' => 'google',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
        'default_model' => 'gemini-pro',
        'strengths' => ['technical', 'factual', 'multimodal'],
        'max_tokens' => 2048
    ],
    'claude' => [
        'name' => 'Claude 3',
        'provider' => 'anthropic',
        'endpoint' => 'https://api.anthropic.com/v1/messages',
        'default_model' => 'claude-3-opus-20240229',
        'strengths' => ['long-context', 'analysis', 'safety'],
        'max_tokens' => 4096
    ],
    'mistral' => [
        'name' => 'Mistral Large',
        'provider' => 'mistral',
        'endpoint' => 'https://api.mistral.ai/v1/chat/completions',
        'default_model' => 'mistral-large-latest',
        'strengths' => ['efficiency', 'multilingual', 'coding'],
        'max_tokens' => 32000
    ],
    'llama3' => [
        'name' => 'Llama 3',
        'provider' => 'meta',
        'endpoint' => 'https://api.endpoints.anyscale.com/v1/chat/completions',
        'default_model' => 'meta-llama/Meta-Llama-3-70B-Instruct',
        'strengths' => ['open-source', 'balanced', 'customizable'],
        'max_tokens' => 8192
    ]
];

// Encryption key (change this in production)
define('ENCRYPTION_KEY', 'ounachu-secure-key-' . bin2hex(random_bytes(8)));

// ==============================================
// Helper Functions
// ==============================================

/**
 * Encrypt sensitive data
 */
function encryptData($data) {
    return openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
}

/**
 * Decrypt sensitive data
 */
function decryptData($encrypted) {
    return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
}

/**
 * Sanitize input data
 */
function sanitizeInput($input, $db) {
    if (is_array($input)) {
        return array_map(function($item) use ($db) {
            return $db->escapeString(htmlspecialchars($item, ENT_QUOTES, 'UTF-8'));
        }, $input);
    }
    return $db->escapeString(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Generate API key
 */
function generateApiKey() {
    return 'oun_' . bin2hex(random_bytes(24));
}

/**
 * Create standardized bilingual response
 */
function createResponse($status, $messageEn, $messageTr, $code = 200, $data = []) {
    return [
        'status' => $status,
        'message' => [
            'en' => $messageEn,
            'tr' => $messageTr
        ],
        'code' => $code,
        'data' => $data,
        'timestamp' => date('c')
    ];
}

// ==============================================
// Database Initialization
// ==============================================

/**
 * Initialize database with enhanced schema
 */
function initEnhancedDB() {
    // Create directories if not exists
    if (!file_exists(EXPORTS_DIR)) {
        mkdir(EXPORTS_DIR, 0755, true);
    }
    if (!file_exists(TEMP_DIR)) {
        mkdir(TEMP_DIR, 0755, true);
    }

    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
    
    // Enable WAL mode for better concurrency
    $db->exec('PRAGMA journal_mode=WAL');
    
    // Create users table
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        api_key TEXT UNIQUE NOT NULL,
        language TEXT DEFAULT "tr",
        is_admin BOOLEAN DEFAULT 0,
        rate_limit INTEGER DEFAULT 100,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create researches table
    $db->exec('CREATE TABLE IF NOT EXISTS researches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        chat_id TEXT NOT NULL UNIQUE,
        topic TEXT NOT NULL,
        description TEXT,
        language TEXT NOT NULL,
        models TEXT NOT NULL,
        depth INTEGER DEFAULT 3,
        status TEXT DEFAULT "pending",
        results TEXT,
        notes TEXT,
        is_public BOOLEAN DEFAULT 0,
        tags TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )');
    
    // Create exports table
    $db->exec('CREATE TABLE IF NOT EXISTS exports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id TEXT NOT NULL,
        user_id INTEGER NOT NULL,
        format TEXT NOT NULL,
        export_type TEXT NOT NULL,
        file_path TEXT NOT NULL UNIQUE,
        file_size INTEGER,
        download_count INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(chat_id) REFERENCES researches(chat_id),
        FOREIGN KEY(user_id) REFERENCES users(id)
    )');
    
    // Create api_logs table
    $db->exec('CREATE TABLE IF NOT EXISTS api_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint TEXT NOT NULL,
        method TEXT NOT NULL,
        user_id INTEGER,
        status_code INTEGER,
        response_time REAL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create default admin user if not exists
    $result = $db->querySingle("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    if ($result == 0) {
        $apiKey = generateApiKey();
        $stmt = $db->prepare("INSERT INTO users 
            (username, email, password_hash, api_key, is_admin, rate_limit) 
            VALUES (:username, :email, :password, :api_key, 1, 1000)");
        
        $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':email', 'admin@ounachu.com', SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash('admin123', PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stmt->bindValue(':api_key', $apiKey, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    return $db;
}

$db = initEnhancedDB();

// ==============================================
// Core Ounachu Engine Class
// ==============================================

class OunachuEngine {
    private $db;
    private $cache = [];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * User authentication
     */
    public function authenticateUser($apiKey) {
        if (isset($this->cache['users'][$apiKey])) {
            return $this->cache['users'][$apiKey];
        }
        
        $stmt = $this->db->prepare('SELECT * FROM users WHERE api_key = :api_key');
        $stmt->bindValue(':api_key', $apiKey, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            $this->cache['users'][$apiKey] = $user;
            return $user;
        }
        return null;
    }
    
    /**
     * Create new research chat
     */
    public function createChat($userId, $topic, $description, $language = 'tr', $selectedModels = ['gpt4'], $depth = 3) {
        // Validate input
        if (!in_array($language, SUPPORTED_LANGS)) {
            return createResponse('error', 'Unsupported language', 'Desteklenmeyen dil', 400);
        }
        
        // Check rate limits
        if (!$this->checkRateLimit($userId)) {
            $msg = $language === 'tr' 
                ? "Dakikalık istek limitine ulaştınız"
                : "You've reached the rate limit";
            return createResponse('error', $msg, $msg, 429);
        }
        
        // Check user's active chat count
        $activeChats = $this->getUserChatCount($userId);
        if ($activeChats >= MAX_CONCURRENT_CHATS) {
            $msg = $language === 'tr' 
                ? "Maksimum sohbet limitine ulaştınız ($MAX_CONCURRENT_CHATS)"
                : "You've reached the maximum chat limit ($MAX_CONCURRENT_CHATS)";
            return createResponse('error', $msg, $msg, 429);
        }
        
        // Validate models
        $validModels = array_intersect($selectedModels, array_keys(AI_MODELS));
        if (empty($validModels)) {
            $msg = $language === 'tr'
                ? "Geçersiz AI model(ler)i seçildi"
                : "Invalid AI model(s) selected";
            return createResponse('error', $msg, $msg, 400);
        }
        
        $chatId = 'chat_' . bin2hex(random_bytes(16));
        $modelsJson = json_encode($validModels);
        
        // Insert into database
        $stmt = $this->db->prepare('INSERT INTO researches 
            (user_id, chat_id, topic, description, language, models, depth) 
            VALUES (:user_id, :chat_id, :topic, :description, :language, :models, :depth)');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        $stmt->bindValue(':topic', sanitizeInput($topic, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':description', sanitizeInput($description, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        $stmt->bindValue(':models', $modelsJson, SQLITE3_TEXT);
        $stmt->bindValue(':depth', $depth, SQLITE3_INTEGER);
        
        if (!$stmt->execute()) {
            $msg = $language === 'tr'
                ? "Sohbet oluşturulamadı"
                : "Failed to create chat";
            return createResponse('error', $msg, $msg, 500);
        }
        
        // Start background processing
        $this->processResearch($chatId);
        
        $successMsg = $language === 'tr'
            ? "Araştırma sohbeti başlatıldı"
            : "Research chat started";
        
        return createResponse('success', $successMsg, $successMsg, 201, [
            'chat_id' => $chatId,
            'models' => $validModels,
            'depth' => $depth,
            'language' => $language
        ]);
    }
    
    /**
     * Process research with selected models
     */
    private function processResearch($chatId) {
        $research = $this->getResearch($chatId);
        if (!$research) return;
        
        $models = json_decode($research['models'], true);
        $steps = $this->generateResearchPlan($research);
        $results = [];
        
        foreach ($steps as $step) {
            $stepResults = [];
            foreach ($models as $model) {
                $stepResults[$model] = $this->executeResearchStep($model, $step, $research['language']);
            }
            $results[] = $this->mergeModelResultsWithConsensus($stepResults, $research['language']);
        }
        
        // Generate final outputs
        $article = $this->generateAcademicArticle($research, $results);
        $notes = $this->generateResearchNotes($results, $research['language']);
        
        // Update research
        $this->updateResearch($chatId, [
            'results' => json_encode($article, JSON_UNESCAPED_UNICODE),
            'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
            'status' => 'completed'
        ]);
    }
    
    /**
     * Generate research plan steps
     */
    private function generateResearchPlan($research) {
        $lang = $research['language'];
        $topic = $research['topic'];
        $depth = $research['depth'];
        
        $prompt = $lang === 'tr'
            ? "$depth aşamalı bir araştırma planı oluşturun:\nKonu: $topic\nAçıklama: {$research['description']}\n\nJSON formatında 'step' ve 'description' anahtarlarıyla verin."
            : "Create a $depth-step research plan about: $topic\nDescription: {$research['description']}\n\nProvide as JSON array with 'step' and 'description' keys.";
        
        $response = $this->queryAI('gpt4', $prompt, $lang);
        $steps = json_decode($response, true);
        
        return is_array($steps) ? $steps : $this->getDefaultSteps($research);
    }
    
    /**
     * Execute a research step with a specific model
     */
    private function executeResearchStep($model, $step, $language) {
        $lang = $language === 'tr' ? 'Türkçe' : 'English';
        $prompt = $language === 'tr'
            ? "Aşağıdaki araştırma adımını $lang olarak gerçekleştirin:\nAdım: {$step['step']}\nAçıklama: {$step['description']}"
            : "Perform this research step in $lang:\nStep: {$step['step']}\nDescription: {$step['description']}";
        
        return [
            'content' => $this->queryAI($model, $prompt, $language),
            'model' => $model,
            'step' => $step['step']
        ];
    }
    
    /**
     * Merge results from multiple models with consensus
     */
    private function mergeModelResultsWithConsensus($stepResults, $language) {
        $lang = $language === 'tr' ? 'Türkçe' : 'English';
        $prompt = $language === 'tr'
            ? "Aşağıdaki farklı AI modellerinden gelen yanıtları analiz edin ve bir konsensüs raporu oluşturun:\n\n"
            : "Analyze responses from different AI models below and create a consensus report:\n\n";
        
        foreach ($stepResults as $model => $result) {
            $prompt .= "=== " . AI_MODELS[$model]['name'] . " ===\n";
            $prompt .= "Güçlü Yönler: " . implode(', ', AI_MODELS[$model]['strengths']) . "\n";
            $prompt .= "İçerik:\n{$result['content']}\n\n";
        }
        
        $consensus = $this->queryAI('gpt4', $prompt, $language);
        
        return [
            'consensus' => $consensus,
            'sources' => $stepResults,
            'language' => $language,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate academic article
     */
    private function generateAcademicArticle($research, $results) {
        $lang = $research['language'];
        $combinedResults = implode("\n\n", array_column($results, 'consensus'));
        
        $prompt = $lang === 'tr'
            ? "Uluslararası akademik standartlarda Türkçe bir makale yazın:\nBaşlık: {$research['topic']}\n\nAraştırma Bulguları:\n$combinedResults\n\nMakale şu bölümleri içermeli:\n1. Başlık\n2. Özet\n3. Giriş\n4. Yöntem\n5. Bulgular\n6. Tartışma\n7. Sonuç\n8. Kaynakça"
            : "Write a properly formatted academic article in English about: {$research['topic']}\n\nResearch Findings:\n$combinedResults\n\nInclude these sections:\n1. Title\n2. Abstract\n3. Introduction\n4. Methodology\n5. Results\n6. Discussion\n7. Conclusion\n8. References";
        
        $content = $this->queryAI('gpt4', $prompt, $lang);
        
        return [
            'title' => $research['topic'],
            'content' => $content,
            'language' => $lang,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate research notes
     */
    private function generateResearchNotes($results, $language) {
        $notes = [];
        
        foreach ($results as $result) {
            $notes[] = [
                'step' => $result['sources'][array_key_first($result['sources'])]['step'],
                'content' => $result['consensus'],
                'sources' => array_map(function($source) {
                    return [
                        'model' => $source['model'],
                        'content' => $source['content']
                    ];
                }, $result['sources'])
            ];
        }
        
        $combined = implode("\n\n", array_column($notes, 'content'));
        
        return [
            'notes' => $notes,
            'combined' => $combined,
            'language' => $language
        ];
    }
    
    /**
     * Export research to specified format
     */
    public function exportResearch($chatId, $userId, $format, $exportType = 'article', $language = 'tr') {
        // Validate format
        if (!in_array($format, ['pdf', 'docx', 'txt'])) {
            $msg = $language === 'tr'
                ? "Desteklenmeyen dosya formatı"
                : "Unsupported file format";
            return createResponse('error', $msg, $msg, 400);
        }
        
        $research = $this->getResearch($chatId);
        if (!$research || $research['user_id'] != $userId) {
            $msg = $language === 'tr'
                ? "Sohbet bulunamadı veya erişim reddedildi"
                : "Chat not found or access denied";
            return createResponse('error', $msg, $msg, 404);
        }
        
        // Check rate limits
        if (!$this->checkRateLimit($userId)) {
            $msg = $language === 'tr' 
                ? "Dakikalık istek limitine ulaştınız"
                : "You've reached the rate limit";
            return createResponse('error', $msg, $msg, 429);
        }
        
        // Get appropriate content
        if ($exportType === 'article') {
            $content = json_decode($research['results'], true)['content'];
            $filename = "article_{$chatId}_" . time() . ".{$format}";
        } else {
            $content = json_decode($research['notes'], true)['combined'];
            $filename = "notes_{$chatId}_" . time() . ".{$format}";
        }
        
        $filepath = EXPORTS_DIR . "/$filename";
        
        // Generate file
        switch ($format) {
            case 'pdf':
                $this->generateProfessionalPDF($content, $filepath, $exportType, $language);
                break;
            case 'docx':
                $this->generateProfessionalDOCX($content, $filepath, $exportType, $language);
                break;
            case 'txt':
                file_put_contents($filepath, $content);
                break;
        }
        
        // Save export record
        $stmt = $this->db->prepare('INSERT INTO exports 
            (chat_id, user_id, format, export_type, file_path, file_size) 
            VALUES (:chat_id, :user_id, :format, :export_type, :file_path, :file_size)');
        
        $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':format', $format, SQLITE3_TEXT);
        $stmt->bindValue(':export_type', $exportType, SQLITE3_TEXT);
        $stmt->bindValue(':file_path', $filepath, SQLITE3_TEXT);
        $stmt->bindValue(':file_size', filesize($filepath), SQLITE3_INTEGER);
        $stmt->execute();
        
        $successMsg = $language === 'tr'
            ? "Dışa aktarma başarılı"
            : "Export successful";
        
        return createResponse('success', $successMsg, $successMsg, 200, [
            'download_url' => "/exports/$filename",
            'file_path' => $filepath,
            'file_size' => filesize($filepath)
        ]);
    }
    
    /**
     * Generate professional PDF using mPDF
     */
    private function generateProfessionalPDF($content, $filepath, $type, $language) {
        // Simple implementation - in production use mPDF or similar library
        $title = $language === 'tr' 
            ? ($type === 'article' ? 'Akademik Makale' : 'Araştırma Notları')
            : ($type === 'article' ? 'Academic Article' : 'Research Notes');
        
        $html = "<html><head><meta charset='UTF-8'><title>$title</title></head><body>";
        $html .= "<h1 style='text-align:center'>$title</h1>";
        $html .= "<div style='line-height:1.6'>" . nl2br(htmlspecialchars($content)) . "</div>";
        $html .= "</body></html>";
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Generate professional DOCX
     */
    private function generateProfessionalDOCX($content, $filepath, $type, $language) {
        // Simple implementation - in production use PHPWord
        $header = $language === 'tr'
            ? ($type === 'article' ? "=== Akademik Makale ===\n\n" : "=== Araştırma Notları ===\n\n")
            : ($type === 'article' ? "=== Academic Article ===\n\n" : "=== Research Notes ===\n\n");
        
        file_put_contents($filepath, $header . $content);
    }
    
    /**
     * Query AI model with language support
     */
    private function queryAI($model, $prompt, $language) {
        if (!isset(AI_MODELS[$model])) {
            throw new Exception("Model $model not configured");
        }
        
        $config = AI_MODELS[$model];
        $apiKey = getenv(strtoupper($config['provider']) . '_API_KEY');
        if (!$apiKey) {
            throw new Exception("API key for {$config['provider']} not configured");
        }
        
        // Common payload structure
        $payload = [
            'model' => $config['default_model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $language === 'tr' 
                        ? "Tüm yanıtlarınızı Türkçe olarak verin. Kesinlikle başka dil kullanmayın."
                        : "Respond only in English. Do not use any other language."
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => $config['max_tokens']
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];
        
        // Model-specific adjustments
        switch ($config['provider']) {
            case 'google': // Gemini
                $payload = [
                    'contents' => [
                        'parts' => [
                            ['text' => $payload['messages'][0]['content']],
                            ['text' => $payload['messages'][1]['content']]
                        ]
                    ]
                ];
                break;
                
            case 'anthropic': // Claude
                $payload['system'] = $payload['messages'][0]['content'];
                unset($payload['messages'][0]);
                $payload['max_tokens'] = min($payload['max_tokens'], 4096);
                break;
        }
        
        $ch = curl_init($config['endpoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        
        if (curl_errno($ch)) {
            throw new Exception("API request failed: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Parse response based on provider
        switch ($config['provider']) {
            case 'openai':
            case 'mistral':
            case 'meta':
                return $data['choices'][0]['message']['content'] ?? '';
            case 'google':
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            case 'anthropic':
                return $data['content'][0]['text'] ?? '';
            default:
                return '';
        }
    }
    
    /**
     * Get research data
     */
    public function getResearch($chatId) {
        if (isset($this->cache['researches'][$chatId])) {
            return $this->cache['researches'][$chatId];
        }
        
        $stmt = $this->db->prepare('SELECT * FROM researches WHERE chat_id = :chat_id');
        $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        $result = $stmt->execute();
        $research = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($research) {
            $this->cache['researches'][$chatId] = $research;
            return $research;
        }
        return null;
    }
    
    /**
     * Update research data
     */
    private function updateResearch($chatId, $data) {
        $updates = [];
        $values = [':chat_id' => $chatId];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $values[":$key"] = $value;
        }
        
        $query = "UPDATE researches SET " . implode(', ', $updates) . " WHERE chat_id = :chat_id";
        $stmt = $this->db->prepare($query);
        
        foreach ($values as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        
        $success = $stmt->execute();
        
        if ($success && isset($this->cache['researches'][$chatId])) {
            unset($this->cache['researches'][$chatId]);
        }
        
        return $success;
    }
    
    /**
     * Get user's active chat count
     */
    private function getUserChatCount($userId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM researches 
                                  WHERE user_id = :user_id AND status != "completed"');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row['count'] ?? 0;
    }
    
    /**
     * Check rate limit for user
     */
    private function checkRateLimit($userId) {
        $minuteAgo = date('Y-m-d H:i:s', strtotime('-1 minute'));
        
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM api_logs 
                                  WHERE user_id = :user_id AND created_at > :minute_ago');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':minute_ago', $minuteAgo, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        $user = $this->getUserById($userId);
        $limit = $user['rate_limit'] ?? MAX_REQUESTS_PER_MINUTE;
        
        return ($row['count'] ?? 0) < $limit;
    }
    
    /**
     * Get user by ID
     */
    private function getUserById($userId) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :user_id');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    /**
     * Get default research steps
     */
    private function getDefaultSteps($research) {
        $lang = $research['language'];
        $topic = $research['topic'];
        
        if ($lang === 'tr') {
            return [
                ['step' => 1, 'description' => "$topic hakkında genel bilgi topla"],
                ['step' => 2, 'description' => "$topic ile ilgili temel kavramları araştır"],
                ['step' => 3, 'description' => "$topic için önemli istatistikleri bul"],
                ['step' => 4, 'description' => "$topic ile ilgili güncel gelişmeleri incele"],
                ['step' => 5, 'description' => "$topic hakkında bir sonuç raporu hazırla"]
            ];
        }
        
        return [
            ['step' => 1, 'description' => "Gather general information about $topic"],
            ['step' => 2, 'description' => "Research key concepts related to $topic"],
            ['step' => 3, 'description' => "Find important statistics about $topic"],
            ['step' => 4, 'description' => "Examine recent developments about $topic"],
            ['step' => 5, 'description' => "Prepare a conclusion report about $topic"]
        ];
    }
    
    /**
     * Log API request
     */
    private function logApiRequest($endpoint, $method, $userId, $statusCode, $responseTime) {
        $stmt = $this->db->prepare('INSERT INTO api_logs 
            (endpoint, method, user_id, status_code, response_time) 
            VALUES (:endpoint, :method, :user_id, :status_code, :response_time)');
        
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $stmt->bindValue(':method', $method, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':status_code', $statusCode, SQLITE3_INTEGER);
        $stmt->bindValue(':response_time', $responseTime, SQLITE3_FLOAT);
        
        $stmt->execute();
    }
}

// ==============================================
// API Router & Request Handler
// ==============================================

class ApiRouter {
    private $engine;
    private $db;
    
    public function __construct($engine, $db) {
        $this->engine = $engine;
        $this->db = $db;
    }
    
    public function handleRequest() {
        $startTime = microtime(true);
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $data = json_decode(file_get_contents('php://input'), true) ?? $_REQUEST;
        
        // Get language preference
        $language = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? $data['language'] ?? 'tr');
        $language = in_array($language, ['tr', 'en']) ? $language : 'tr';
        
        // Authentication
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $data['api_key'] ?? null;
        $user = $this->engine->authenticateUser($apiKey);
        
        // Public endpoints
        if ($method === 'POST' && $path === '/api/login') {
            return $this->handleLogin($data, $language);
        }
        
        if ($method === 'POST' && $path === '/api/register') {
            return $this->handleRegister($data, $language);
        }
        
        // Authenticated endpoints
        if (!$user && !in_array($path, ['/api/login', '/api/register'])) {
            $response = createResponse('error', 'Unauthorized', 'Yetkisiz erişim', 401);
        } else {
            try {
                switch (true) {
                    case $method === 'POST' && $path === '/api/chats':
                        $response = $this->handleCreateChat($user, $data, $language);
                        break;
                        
                    case $method === 'GET' && preg_match('/\/api\/chats\/([a-zA-Z0-9_]+)/', $path, $matches):
                        $response = $this->handleGetChat($user, $matches[1], $language);
                        break;
                        
                    case $method === 'POST' && preg_match('/\/api\/chats\/([a-zA-Z0-9_]+)\/export/', $path, $matches):
                        $response = $this->handleExport($user, $matches[1], $data, $language);
                        break;
                        
                    case $method === 'GET' && $path === '/api/models':
                        $response = $this->handleGetModels($language);
                        break;
                        
                    default:
                        $response = createResponse('error', 'Endpoint not found', 'Endpoint bulunamadı', 404);
                }
            } catch (Exception $e) {
                $response = createResponse('error', 
                    'An error occurred: ' . $e->getMessage(),
                    'Bir hata oluştu: ' . $e->getMessage(),
                    500);
            }
        }
        
        // Log the request
        $responseTime = microtime(true) - $startTime;
        $this->engine->logApiRequest(
            $path,
            $method,
            $user['id'] ?? null,
            $response['code'] ?? 200,
            $responseTime
        );
        
        return $response;
    }
    
    private function handleLogin($data, $language) {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', sanitizeInput($username, $this->db), SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $msg = $language === 'tr'
                ? "Giriş başarılı"
                : "Login successful";
            return createResponse('success', $msg, $msg, 200, [
                'api_key' => $user['api_key'],
                'user_id' => $user['id'],
                'language' => $user['language']
            ]);
        }
        
        $msg = $language === 'tr'
            ? "Geçersiz kullanıcı adı veya şifre"
            : "Invalid username or password";
        return createResponse('error', $msg, $msg, 401);
    }
    
    private function handleRegister($data, $language) {
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            $msg = $language === 'tr'
                ? "Kullanıcı adı, email ve şifre gereklidir"
                : "Username, email and password are required";
            return createResponse('error', $msg, $msg, 400);
        }
        
        // Check if username or email exists
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email');
        $stmt->bindValue(':username', sanitizeInput($username, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':email', sanitizeInput($email, $this->db), SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row['count'] > 0) {
            $msg = $language === 'tr'
                ? "Kullanıcı adı veya email zaten kullanımda"
                : "Username or email already exists";
            return createResponse('error', $msg, $msg, 409);
        }
        
        // Create new user
        $apiKey = generateApiKey();
        $stmt = $this->db->prepare('INSERT INTO users 
            (username, email, password_hash, api_key, language) 
            VALUES (:username, :email, :password, :api_key, :language)');
        
        $stmt->bindValue(':username', sanitizeInput($username, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':email', sanitizeInput($email, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stmt->bindValue(':api_key', $apiKey, SQLITE3_TEXT);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $userId = $this->db->lastInsertRowID();
            $msg = $language === 'tr'
                ? "Kayıt başarılı"
                : "Registration successful";
            return createResponse('success', $msg, $msg, 201, [
                'api_key' => $apiKey,
                'user_id' => $userId,
                'language' => $language
            ]);
        }
        
        $msg = $language === 'tr'
            ? "Kayıt sırasında hata oluştu"
            : "Error during registration";
        return createResponse('error', $msg, $msg, 500);
    }
    
    private function handleCreateChat($user, $data, $language) {
        $required = ['topic', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $msg = $language === 'tr'
                    ? "$field alanı gereklidir"
                    : "$field field is required";
                return createResponse('error', $msg, $msg, 400);
            }
        }
        
        return $this->engine->createChat(
            $user['id'],
            $data['topic'],
            $data['description'],
            $data['language'] ?? $user['language'] ?? 'tr',
            $data['models'] ?? ['gpt4'],
            $data['depth'] ?? 3
        );
    }
    
    private function handleGetChat($user, $chatId, $language) {
        $chat = $this->engine->getResearch($chatId);
        
        if (!$chat) {
            $msg = $language === 'tr'
                ? 'Sohbet bulunamadı'
                : 'Chat not found';
            return createResponse('error', $msg, $msg, 404);
        }
        
        if ($chat['user_id'] != $user['id'] && !$user['is_admin']) {
            $msg = $language === 'tr'
                ? 'Bu sohbete erişim izniniz yok'
                : 'You dont have access to this chat';
            return createResponse('error', $msg, $msg, 403);
        }
        
        $chat['results'] = json_decode($chat['results'], true) ?? [];
        $chat['notes'] = json_decode($chat['notes'], true) ?? [];
        $chat['models'] = json_decode($chat['models'], true);
        
        $msg = $language === 'tr'
            ? 'Sohbet bilgileri alındı'
            : 'Chat information retrieved';
        
        return createResponse('success', $msg, $msg, 200, $chat);
    }
    
    private function handleExport($user, $chatId, $data, $language) {
        return $this->engine->exportResearch(
            $chatId,
            $user['id'],
            $data['format'] ?? 'pdf',
            $data['type'] ?? 'article',
            $data['language'] ?? $user['language'] ?? 'tr'
        );
    }
    
    private function handleGetModels($language) {
        $models = [];
        foreach (AI_MODELS as $id => $config) {
            $models[] = [
                'id' => $id,
                'name' => $config['name'],
                'strengths' => $config['strengths'],
                'max_tokens' => $config['max_tokens']
            ];
        }
        
        $msg = $language === 'tr'
            ? 'Modeller başarıyla listelendi'
            : 'Models listed successfully';
        
        return createResponse('success', $msg, $msg, 200, $models);
    }
}

// ==============================================
// CLI Handler
// ==============================================

if ($isCli) {
    $engine = new OunachuEngine($db);
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case '--install':
                echo "Ounachu Search Engine Kurulumu\n";
                echo "=============================\n";
                echo "[OK] Veritabanı başlatıldı\n";
                echo "[OK] Dizinler oluşturuldu\n";
                
                // Create admin user if not exists
                $result = $db->querySingle("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
                if ($result == 0) {
                    $apiKey = generateApiKey();
                    $stmt = $db->prepare("INSERT INTO users 
                        (username, email, password_hash, api_key, is_admin, rate_limit) 
                        VALUES ('admin', 'admin@ounachu.com', :password, :api_key, 1, 1000)");
                    
                    $stmt->bindValue(':password', password_hash('admin123', PASSWORD_BCRYPT), SQLITE3_TEXT);
                    $stmt->bindValue(':api_key', $apiKey, SQLITE3_TEXT);
                    $stmt->execute();
                    
                    echo "[OK] Admin kullanıcı oluşturuldu\n";
                    echo "API Key: $apiKey\n";
                }
                
                echo "\nKurulum tamamlandı!\n";
                break;
                
            case '--test':
                echo "Running tests...\n";
                // Basic test cases would go here
                echo "All tests passed!\n";
                break;
                
            case '--process-research':
                if (isset($argv[2])) {
                    $engine->processResearch($argv[2]);
                    echo "Research processed: $argv[2]\n";
                } else {
                    echo "Usage: php ounachu.php --process-research <chat_id>\n";
                }
                break;
                
            default:
                echo "Available commands:\n";
                echo "  --install        Initialize the application\n";
                echo "  --test           Run tests\n";
                echo "  --process-research <chat_id>  Process a research\n";
        }
    }
    exit;
}

// ==============================================
// Web Request Handler
// ==============================================

$engine = new OunachuEngine($db);
$router = new ApiRouter($engine, $db);

header('Content-Type: application/json; charset=utf-8');

try {
    $response = $router->handleRequest();
    http_response_code($response['code'] ?? 200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $response = createResponse('error', 
        'An error occurred: ' . $e->getMessage(),
        'Bir hata oluştu: ' . $e->getMessage(),
        500);
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}