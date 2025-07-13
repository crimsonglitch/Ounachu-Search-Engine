<?php
/*
 * Ounachu Search Engine - AI-Powered Research Tool
 * - Created By Crimson Glitch
 * - Enhanced Version with Multi-Language Support & End-to-End Encryption
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Features:
 * - 5 AI model integration (GPT-4, Gemini, Claude, Mistral, Llama3)
 * - End-to-end encryption for user data
 * - Multi-AI consensus engine with intelligent scoring
 * - Multi-language support (130+ languages)
 * - Academic article export (PDF, DOCX, TXT)
 * - Research notes export
 * - Isolated chat sessions
 * - User authentication with enhanced security
 * - Professional PDF generation
 * - Rate limiting and security monitoring
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
define('LANG_DIR', ROOT_DIR . '/languages');
define('MAX_CONCURRENT_CHATS', 50);
define('MAX_REQUESTS_PER_MINUTE', 100);

// Supported languages - ISO 639-1 and 639-2 codes with native names
const SUPPORTED_LANGUAGES = [
    // Major Languages
    'en' => ['name' => 'English', 'native' => 'English', 'rtl' => false, 'family' => 'Germanic'],
    'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'rtl' => false, 'family' => 'Turkic'],
    'zh' => ['name' => 'Chinese', 'native' => '中文', 'rtl' => false, 'family' => 'Sino-Tibetan'],
    'zh-cn' => ['name' => 'Chinese (Simplified)', 'native' => '简体中文', 'rtl' => false, 'family' => 'Sino-Tibetan'],
    'zh-tw' => ['name' => 'Chinese (Traditional)', 'native' => '繁體中文', 'rtl' => false, 'family' => 'Sino-Tibetan'],
    'es' => ['name' => 'Spanish', 'native' => 'Español', 'rtl' => false, 'family' => 'Romance'],
    'fr' => ['name' => 'French', 'native' => 'Français', 'rtl' => false, 'family' => 'Romance'],
    'de' => ['name' => 'German', 'native' => 'Deutsch', 'rtl' => false, 'family' => 'Germanic'],
    'it' => ['name' => 'Italian', 'native' => 'Italiano', 'rtl' => false, 'family' => 'Romance'],
    'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'rtl' => false, 'family' => 'Romance'],
    'ru' => ['name' => 'Russian', 'native' => 'Русский', 'rtl' => false, 'family' => 'Slavic'],
    'ja' => ['name' => 'Japanese', 'native' => '日本語', 'rtl' => false, 'family' => 'Japonic'],
    'ko' => ['name' => 'Korean', 'native' => '한국어', 'rtl' => false, 'family' => 'Koreanic'],
    'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'rtl' => true, 'family' => 'Semitic'],
    'hi' => ['name' => 'Hindi', 'native' => 'हिन्दी', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'bn' => ['name' => 'Bengali', 'native' => 'বাংলা', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'rtl' => true, 'family' => 'Indo-Aryan'],
    'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'rtl' => true, 'family' => 'Indo-Iranian'],
    'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'rtl' => true, 'family' => 'Semitic'],
    'th' => ['name' => 'Thai', 'native' => 'ไทย', 'rtl' => false, 'family' => 'Tai-Kadai'],
    'vi' => ['name' => 'Vietnamese', 'native' => 'Tiếng Việt', 'rtl' => false, 'family' => 'Austroasiatic'],
    'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'rtl' => false, 'family' => 'Austronesian'],
    'ms' => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'rtl' => false, 'family' => 'Austronesian'],
    'tl' => ['name' => 'Filipino', 'native' => 'Filipino', 'rtl' => false, 'family' => 'Austronesian'],
    'sw' => ['name' => 'Swahili', 'native' => 'Kiswahili', 'rtl' => false, 'family' => 'Niger-Congo'],
    
    // European Languages
    'nl' => ['name' => 'Dutch', 'native' => 'Nederlands', 'rtl' => false, 'family' => 'Germanic'],
    'sv' => ['name' => 'Swedish', 'native' => 'Svenska', 'rtl' => false, 'family' => 'Germanic'],
    'no' => ['name' => 'Norwegian', 'native' => 'Norsk', 'rtl' => false, 'family' => 'Germanic'],
    'da' => ['name' => 'Danish', 'native' => 'Dansk', 'rtl' => false, 'family' => 'Germanic'],
    'fi' => ['name' => 'Finnish', 'native' => 'Suomi', 'rtl' => false, 'family' => 'Uralic'],
    'is' => ['name' => 'Icelandic', 'native' => 'Íslenska', 'rtl' => false, 'family' => 'Germanic'],
    'pl' => ['name' => 'Polish', 'native' => 'Polski', 'rtl' => false, 'family' => 'Slavic'],
    'cs' => ['name' => 'Czech', 'native' => 'Čeština', 'rtl' => false, 'family' => 'Slavic'],
    'sk' => ['name' => 'Slovak', 'native' => 'Slovenčina', 'rtl' => false, 'family' => 'Slavic'],
    'hu' => ['name' => 'Hungarian', 'native' => 'Magyar', 'rtl' => false, 'family' => 'Uralic'],
    'ro' => ['name' => 'Romanian', 'native' => 'Română', 'rtl' => false, 'family' => 'Romance'],
    'bg' => ['name' => 'Bulgarian', 'native' => 'Български', 'rtl' => false, 'family' => 'Slavic'],
    'hr' => ['name' => 'Croatian', 'native' => 'Hrvatski', 'rtl' => false, 'family' => 'Slavic'],
    'sr' => ['name' => 'Serbian', 'native' => 'Српски', 'rtl' => false, 'family' => 'Slavic'],
    'sl' => ['name' => 'Slovenian', 'native' => 'Slovenščina', 'rtl' => false, 'family' => 'Slavic'],
    'mk' => ['name' => 'Macedonian', 'native' => 'Македонски', 'rtl' => false, 'family' => 'Slavic'],
    'sq' => ['name' => 'Albanian', 'native' => 'Shqip', 'rtl' => false, 'family' => 'Albanian'],
    'el' => ['name' => 'Greek', 'native' => 'Ελληνικά', 'rtl' => false, 'family' => 'Hellenic'],
    'lv' => ['name' => 'Latvian', 'native' => 'Latviešu', 'rtl' => false, 'family' => 'Baltic'],
    'lt' => ['name' => 'Lithuanian', 'native' => 'Lietuvių', 'rtl' => false, 'family' => 'Baltic'],
    'et' => ['name' => 'Estonian', 'native' => 'Eesti', 'rtl' => false, 'family' => 'Uralic'],
    'mt' => ['name' => 'Maltese', 'native' => 'Malti', 'rtl' => false, 'family' => 'Semitic'],
    'ga' => ['name' => 'Irish', 'native' => 'Gaeilge', 'rtl' => false, 'family' => 'Celtic'],
    'cy' => ['name' => 'Welsh', 'native' => 'Cymraeg', 'rtl' => false, 'family' => 'Celtic'],
    'eu' => ['name' => 'Basque', 'native' => 'Euskera', 'rtl' => false, 'family' => 'Basque'],
    'ca' => ['name' => 'Catalan', 'native' => 'Català', 'rtl' => false, 'family' => 'Romance'],
    'gl' => ['name' => 'Galician', 'native' => 'Galego', 'rtl' => false, 'family' => 'Romance'],
    
    // African Languages
    'af' => ['name' => 'Afrikaans', 'native' => 'Afrikaans', 'rtl' => false, 'family' => 'Germanic'],
    'zu' => ['name' => 'Zulu', 'native' => 'IsiZulu', 'rtl' => false, 'family' => 'Niger-Congo'],
    'xh' => ['name' => 'Xhosa', 'native' => 'IsiXhosa', 'rtl' => false, 'family' => 'Niger-Congo'],
    'am' => ['name' => 'Amharic', 'native' => 'አማርኛ', 'rtl' => false, 'family' => 'Semitic'],
    'ha' => ['name' => 'Hausa', 'native' => 'Hausa', 'rtl' => false, 'family' => 'Afroasiatic'],
    'ig' => ['name' => 'Igbo', 'native' => 'Asụsụ Igbo', 'rtl' => false, 'family' => 'Niger-Congo'],
    'yo' => ['name' => 'Yoruba', 'native' => 'Yorùbá', 'rtl' => false, 'family' => 'Niger-Congo'],
    
    // Asian Languages
    'ta' => ['name' => 'Tamil', 'native' => 'தமிழ்', 'rtl' => false, 'family' => 'Dravidian'],
    'te' => ['name' => 'Telugu', 'native' => 'తెలుగు', 'rtl' => false, 'family' => 'Dravidian'],
    'kn' => ['name' => 'Kannada', 'native' => 'ಕನ್ನಡ', 'rtl' => false, 'family' => 'Dravidian'],
    'ml' => ['name' => 'Malayalam', 'native' => 'മലയാളം', 'rtl' => false, 'family' => 'Dravidian'],
    'gu' => ['name' => 'Gujarati', 'native' => 'ગુજરાતી', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'pa' => ['name' => 'Punjabi', 'native' => 'ਪੰਜਾਬੀ', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'mr' => ['name' => 'Marathi', 'native' => 'मराठी', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'ne' => ['name' => 'Nepali', 'native' => 'नेपाली', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'si' => ['name' => 'Sinhala', 'native' => 'සිංහල', 'rtl' => false, 'family' => 'Indo-Aryan'],
    'my' => ['name' => 'Myanmar', 'native' => 'မြန်မာ', 'rtl' => false, 'family' => 'Sino-Tibetan'],
    'km' => ['name' => 'Khmer', 'native' => 'ខ្មែរ', 'rtl' => false, 'family' => 'Austroasiatic'],
    'lo' => ['name' => 'Lao', 'native' => 'ລາວ', 'rtl' => false, 'family' => 'Tai-Kadai'],
    'ka' => ['name' => 'Georgian', 'native' => 'ქართული', 'rtl' => false, 'family' => 'Kartvelian'],
    'hy' => ['name' => 'Armenian', 'native' => 'Հայերեն', 'rtl' => false, 'family' => 'Armenian'],
    'az' => ['name' => 'Azerbaijani', 'native' => 'Azərbaycan', 'rtl' => false, 'family' => 'Turkic'],
    'kk' => ['name' => 'Kazakh', 'native' => 'Қазақша', 'rtl' => false, 'family' => 'Turkic'],
    'ky' => ['name' => 'Kyrgyz', 'native' => 'Кыргызча', 'rtl' => false, 'family' => 'Turkic'],
    'uz' => ['name' => 'Uzbek', 'native' => 'Oʻzbekcha', 'rtl' => false, 'family' => 'Turkic'],
    'tk' => ['name' => 'Turkmen', 'native' => 'Türkmençe', 'rtl' => false, 'family' => 'Turkic'],
    'tg' => ['name' => 'Tajik', 'native' => 'Тоҷикӣ', 'rtl' => false, 'family' => 'Indo-Iranian'],
    'mn' => ['name' => 'Mongolian', 'native' => 'Монгол', 'rtl' => false, 'family' => 'Mongolic'],
    
    // American Languages
    'pt-br' => ['name' => 'Portuguese (Brazil)', 'native' => 'Português Brasileiro', 'rtl' => false, 'family' => 'Romance'],
    'es-mx' => ['name' => 'Spanish (Mexico)', 'native' => 'Español Mexicano', 'rtl' => false, 'family' => 'Romance'],
    'es-ar' => ['name' => 'Spanish (Argentina)', 'native' => 'Español Argentino', 'rtl' => false, 'family' => 'Romance'],
    'qu' => ['name' => 'Quechua', 'native' => 'Runasimi', 'rtl' => false, 'family' => 'Quechuan'],
    'gn' => ['name' => 'Guarani', 'native' => 'Avañeʼẽ', 'rtl' => false, 'family' => 'Tupian'],
    
    // Additional Languages
    'be' => ['name' => 'Belarusian', 'native' => 'Беларуская', 'rtl' => false, 'family' => 'Slavic'],
    'uk' => ['name' => 'Ukrainian', 'native' => 'Українська', 'rtl' => false, 'family' => 'Slavic'],
    'bs' => ['name' => 'Bosnian', 'native' => 'Bosanski', 'rtl' => false, 'family' => 'Slavic'],
    'me' => ['name' => 'Montenegrin', 'native' => 'Crnogorski', 'rtl' => false, 'family' => 'Slavic'],
    'fo' => ['name' => 'Faroese', 'native' => 'Føroyskt', 'rtl' => false, 'family' => 'Germanic'],
    'lb' => ['name' => 'Luxembourgish', 'native' => 'Lëtzebuergesch', 'rtl' => false, 'family' => 'Germanic'],
    'rm' => ['name' => 'Romansh', 'native' => 'Rumantsch', 'rtl' => false, 'family' => 'Romance'],
    'sc' => ['name' => 'Sardinian', 'native' => 'Sardu', 'rtl' => false, 'family' => 'Romance'],
    'co' => ['name' => 'Corsican', 'native' => 'Corsu', 'rtl' => false, 'family' => 'Romance'],
    'br' => ['name' => 'Breton', 'native' => 'Brezhoneg', 'rtl' => false, 'family' => 'Celtic'],
    'gd' => ['name' => 'Scottish Gaelic', 'native' => 'Gàidhlig', 'rtl' => false, 'family' => 'Celtic'],
    'kw' => ['name' => 'Cornish', 'native' => 'Kernewek', 'rtl' => false, 'family' => 'Celtic'],
    'ast' => ['name' => 'Asturian', 'native' => 'Asturianu', 'rtl' => false, 'family' => 'Romance'],
    'an' => ['name' => 'Aragonese', 'native' => 'Aragonés', 'rtl' => false, 'family' => 'Romance'],
    'ext' => ['name' => 'Extremaduran', 'native' => 'Estremeñu', 'rtl' => false, 'family' => 'Romance'],
    'mwl' => ['name' => 'Mirandese', 'native' => 'Mirandés', 'rtl' => false, 'family' => 'Romance'],
    'oc' => ['name' => 'Occitan', 'native' => 'Occitan', 'rtl' => false, 'family' => 'Romance'],
    'vec' => ['name' => 'Venetian', 'native' => 'Vèneto', 'rtl' => false, 'family' => 'Romance'],
    'nap' => ['name' => 'Neapolitan', 'native' => 'Napulitano', 'rtl' => false, 'family' => 'Romance'],
    'lij' => ['name' => 'Ligurian', 'native' => 'Ligure', 'rtl' => false, 'family' => 'Romance'],
    'pms' => ['name' => 'Piedmontese', 'native' => 'Piemontèis', 'rtl' => false, 'family' => 'Romance'],
    'fur' => ['name' => 'Friulian', 'native' => 'Furlan', 'rtl' => false, 'family' => 'Romance'],
    'lld' => ['name' => 'Ladin', 'native' => 'Ladin', 'rtl' => false, 'family' => 'Romance'],
    
    // Sign Languages
    'ase' => ['name' => 'American Sign Language', 'native' => 'ASL', 'rtl' => false, 'family' => 'Sign'],
    'bfi' => ['name' => 'British Sign Language', 'native' => 'BSL', 'rtl' => false, 'family' => 'Sign'],
    
    // Constructed Languages
    'eo' => ['name' => 'Esperanto', 'native' => 'Esperanto', 'rtl' => false, 'family' => 'Constructed'],
    'ia' => ['name' => 'Interlingua', 'native' => 'Interlingua', 'rtl' => false, 'family' => 'Constructed'],
    'ie' => ['name' => 'Interlingue', 'native' => 'Interlingue', 'rtl' => false, 'family' => 'Constructed'],
    'vo' => ['name' => 'Volapük', 'native' => 'Volapük', 'rtl' => false, 'family' => 'Constructed'],
    'jbo' => ['name' => 'Lojban', 'native' => 'Lojban', 'rtl' => false, 'family' => 'Constructed'],
    'tlh' => ['name' => 'Klingon', 'native' => 'tlhIngan Hol', 'rtl' => false, 'family' => 'Constructed'],
];

// Language families for AI model optimization
const LANGUAGE_FAMILIES = [
    'Germanic' => ['en', 'de', 'nl', 'sv', 'no', 'da', 'is', 'af', 'fo', 'lb'],
    'Romance' => ['es', 'fr', 'it', 'pt', 'ro', 'ca', 'gl', 'pt-br', 'es-mx', 'es-ar', 'rm', 'sc', 'co', 'ast', 'an', 'ext', 'mwl', 'oc', 'vec', 'nap', 'lij', 'pms', 'fur', 'lld'],
    'Slavic' => ['ru', 'pl', 'cs', 'sk', 'bg', 'hr', 'sr', 'sl', 'mk', 'be', 'uk', 'bs', 'me'],
    'Sino-Tibetan' => ['zh', 'zh-cn', 'zh-tw', 'my'],
    'Indo-Aryan' => ['hi', 'bn', 'ur', 'gu', 'pa', 'mr', 'ne', 'si'],
    'Semitic' => ['ar', 'he', 'mt', 'am'],
    'Turkic' => ['tr', 'az', 'kk', 'ky', 'uz', 'tk'],
    'Uralic' => ['fi', 'hu', 'et'],
    'Celtic' => ['ga', 'cy', 'br', 'gd', 'kw'],
    'Dravidian' => ['ta', 'te', 'kn', 'ml'],
    'Austronesian' => ['id', 'ms', 'tl'],
    'Niger-Congo' => ['sw', 'zu', 'xh', 'ig', 'yo'],
    'Japonic' => ['ja'],
    'Koreanic' => ['ko'],
    'Thai-Kadai' => ['th', 'lo'],
    'Austroasiatic' => ['vi', 'km'],
    'Albanian' => ['sq'],
    'Hellenic' => ['el'],
    'Baltic' => ['lv', 'lt'],
    'Basque' => ['eu'],
    'Kartvelian' => ['ka'],
    'Armenian' => ['hy'],
    'Indo-Iranian' => ['fa', 'tg'],
    'Mongolic' => ['mn'],
    'Afroasiatic' => ['ha'],
    'Quechuan' => ['qu'],
    'Tupian' => ['gn'],
    'Sign' => ['ase', 'bfi'],
    'Constructed' => ['eo', 'ia', 'ie', 'vo', 'jbo', 'tlh']
];

// Available AI models
const AI_MODELS = [
    'gpt4' => [
        'name' => 'GPT-4',
        'provider' => 'openai',
        'endpoint' => 'https://api.openai.com/v1/chat/completions',
        'default_model' => 'gpt-4-turbo-preview',
        'strengths' => ['reasoning', 'creativity', 'formatting', 'multilingual'],
        'max_tokens' => 4096,
        'languages' => 'all' // Supports most languages
    ],
    'gemini' => [
        'name' => 'Gemini Pro',
        'provider' => 'google',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
        'default_model' => 'gemini-pro',
        'strengths' => ['technical', 'factual', 'multimodal', 'multilingual'],
        'max_tokens' => 2048,
        'languages' => 'major' // Supports major languages
    ],
    'claude' => [
        'name' => 'Claude 3',
        'provider' => 'anthropic',
        'endpoint' => 'https://api.anthropic.com/v1/messages',
        'default_model' => 'claude-3-opus-20240229',
        'strengths' => ['long-context', 'analysis', 'safety', 'multilingual'],
        'max_tokens' => 4096,
        'languages' => 'major' // Supports major languages
    ],
    'mistral' => [
        'name' => 'Mistral Large',
        'provider' => 'mistral',
        'endpoint' => 'https://api.mistral.ai/v1/chat/completions',
        'default_model' => 'mistral-large-latest',
        'strengths' => ['efficiency', 'multilingual', 'coding', 'european'],
        'max_tokens' => 32000,
        'languages' => 'european' // Strong in European languages
    ],
    'llama3' => [
        'name' => 'Llama 3',
        'provider' => 'meta',
        'endpoint' => 'https://api.endpoints.anyscale.com/v1/chat/completions',
        'default_model' => 'meta-llama/Meta-Llama-3-70B-Instruct',
        'strengths' => ['open-source', 'balanced', 'customizable', 'multilingual'],
        'max_tokens' => 8192,
        'languages' => 'major' // Supports major languages
    ]
];

// Encryption key (change this in production)
define('ENCRYPTION_KEY', 'ounachu-secure-key-' . bin2hex(random_bytes(8)));

// ==============================================
// Multi-Language Support System
// ==============================================

/**
 * Language Manager for multi-language support
 */
class LanguageManager {
    private static $translations = [];
    private static $currentLanguage = 'en';
    private static $fallbackLanguage = 'en';
    private static $loadedLanguages = [];
    
    /**
     * Initialize language manager
     */
    public static function init($language = 'en') {
        self::$currentLanguage = $language;
        self::loadLanguage($language);
        
        // Load fallback language if different
        if ($language !== self::$fallbackLanguage) {
            self::loadLanguage(self::$fallbackLanguage);
        }
    }
    
    /**
     * Load language file
     */
    private static function loadLanguage($lang) {
        if (in_array($lang, self::$loadedLanguages)) {
            return;
        }
        
        $langFile = LANG_DIR . "/$lang.json";
        
        if (file_exists($langFile)) {
            $translations = json_decode(file_get_contents($langFile), true);
            if ($translations) {
                self::$translations[$lang] = $translations;
                self::$loadedLanguages[] = $lang;
                return;
            }
        }
        
        // Generate default translations for the language
        self::generateDefaultTranslations($lang);
        self::$loadedLanguages[] = $lang;
    }
    
    /**
     * Generate default translations using AI
     */
    private static function generateDefaultTranslations($lang) {
        $defaultTranslations = self::getDefaultTranslations();
        
        if ($lang === 'en') {
            self::$translations[$lang] = $defaultTranslations;
            return;
        }
        
        // If language info exists, try to generate translations
        if (isset(SUPPORTED_LANGUAGES[$lang])) {
            $langInfo = SUPPORTED_LANGUAGES[$lang];
            $nativeName = $langInfo['native'];
            
            // Use a simple translation approach for now
            // In production, this would use AI translation
            self::$translations[$lang] = self::translateToLanguage($defaultTranslations, $lang, $nativeName);
        } else {
            // Fallback to English
            self::$translations[$lang] = $defaultTranslations;
        }
    }
    
    /**
     * Get default English translations
     */
    private static function getDefaultTranslations() {
        return [
            // Common messages
            'welcome' => 'Welcome to Ounachu Search Engine',
            'success' => 'Success',
            'error' => 'Error',
            'warning' => 'Warning',
            'info' => 'Information',
            'loading' => 'Loading...',
            'saving' => 'Saving...',
            'processing' => 'Processing...',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'pending' => 'Pending',
            
            // Authentication
            'login' => 'Login',
            'logout' => 'Logout',
            'register' => 'Register',
            'username' => 'Username',
            'password' => 'Password',
            'email' => 'Email',
            'confirm_password' => 'Confirm Password',
            'forgot_password' => 'Forgot Password',
            'reset_password' => 'Reset Password',
            'login_successful' => 'Login successful',
            'login_failed' => 'Login failed',
            'registration_successful' => 'Registration successful',
            'registration_failed' => 'Registration failed',
            'invalid_credentials' => 'Invalid username or password',
            'user_exists' => 'Username or email already exists',
            'weak_password' => 'Password must be at least 8 characters',
            'invalid_email' => 'Invalid email address',
            'unauthorized' => 'Unauthorized access',
            
            // Chat & Research
            'new_chat' => 'New Chat',
            'chat_title' => 'Chat Title',
            'chat_description' => 'Chat Description',
            'research_topic' => 'Research Topic',
            'research_description' => 'Research Description',
            'research_depth' => 'Research Depth',
            'select_models' => 'Select AI Models',
            'create_chat' => 'Create Chat',
            'chat_created' => 'Research chat started',
            'chat_creation_failed' => 'Failed to create chat',
            'chat_not_found' => 'Chat not found',
            'access_denied' => 'Access denied to this chat',
            'research_completed' => 'Research completed',
            'research_processing' => 'Research is being processed',
            'research_failed' => 'Research failed',
            
            // Export
            'export' => 'Export',
            'export_format' => 'Export Format',
            'export_type' => 'Export Type',
            'export_article' => 'Article',
            'export_notes' => 'Notes',
            'export_successful' => 'Export successful',
            'export_failed' => 'Export failed',
            'download' => 'Download',
            'file_not_found' => 'File not found',
            'unsupported_format' => 'Unsupported file format',
            'no_content_to_export' => 'No content found to export',
            
            // Security & Encryption
            'enable_encryption' => 'Enable Encryption',
            'disable_encryption' => 'Disable Encryption',
            'encryption_enabled' => 'Encryption successfully enabled',
            'encryption_failed' => 'Failed to enable encryption',
            'encryption_already_enabled' => 'Encryption already enabled',
            'password_required_for_encryption' => 'Password required for encryption',
            'encryption_not_enabled' => 'Encryption not enabled. Please enable it in account settings first.',
            'decryption_failed' => 'Decryption failed',
            'secure_login' => 'Secure login successful',
            'security_event_logged' => 'Security event logged',
            'suspicious_activity' => 'Suspicious activity detected',
            'rate_limit_exceeded' => 'Rate limit exceeded',
            'too_many_requests' => 'Too many requests. Please try again later.',
            
            // Models & AI
            'ai_models' => 'AI Models',
            'model_selection' => 'Model Selection',
            'consensus_engine' => 'Consensus Engine',
            'model_performance' => 'Model Performance',
            'rate_models' => 'Rate Models',
            'model_accuracy' => 'Model Accuracy',
            'model_relevance' => 'Model Relevance',
            'model_ratings_updated' => 'Model ratings updated',
            'no_valid_responses' => 'No valid responses from AI models',
            'api_request_failed' => 'API request failed',
            'invalid_model_selection' => 'Invalid AI model(s) selected',
            
            // Languages
            'language' => 'Language',
            'select_language' => 'Select Language',
            'language_changed' => 'Language changed successfully',
            'unsupported_language' => 'Unsupported language',
            'auto_detect_language' => 'Auto-detect language',
            'translate_to' => 'Translate to',
            'original_language' => 'Original Language',
            'target_language' => 'Target Language',
            
            // File Management
            'file_size' => 'File Size',
            'file_type' => 'File Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'download_count' => 'Download Count',
            'file_uploaded' => 'File uploaded successfully',
            'file_upload_failed' => 'File upload failed',
            'file_deleted' => 'File deleted successfully',
            'file_delete_failed' => 'Failed to delete file',
            
            // User Management
            'profile' => 'Profile',
            'account_settings' => 'Account Settings',
            'user_preferences' => 'User Preferences',
            'change_password' => 'Change Password',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'password_changed' => 'Password changed successfully',
            'password_change_failed' => 'Failed to change password',
            'account_deleted' => 'Account deleted successfully',
            'account_deletion_failed' => 'Failed to delete account',
            
            // Statistics & Analytics
            'statistics' => 'Statistics',
            'analytics' => 'Analytics',
            'total_users' => 'Total Users',
            'total_chats' => 'Total Chats',
            'total_exports' => 'Total Exports',
            'active_users' => 'Active Users',
            'recent_activity' => 'Recent Activity',
            'usage_stats' => 'Usage Statistics',
            'performance_metrics' => 'Performance Metrics',
            
            // System Messages
            'system_maintenance' => 'System maintenance in progress',
            'service_unavailable' => 'Service temporarily unavailable',
            'database_error' => 'Database error occurred',
            'connection_error' => 'Connection error',
            'timeout_error' => 'Request timeout',
            'internal_server_error' => 'Internal server error',
            'not_found' => 'Not found',
            'method_not_allowed' => 'Method not allowed',
            'bad_request' => 'Bad request',
            'forbidden' => 'Forbidden',
            
            // Validation Messages
            'required_field' => 'This field is required',
            'invalid_format' => 'Invalid format',
            'field_too_short' => 'Field is too short',
            'field_too_long' => 'Field is too long',
            'invalid_characters' => 'Contains invalid characters',
            'value_out_of_range' => 'Value is out of range',
            'invalid_selection' => 'Invalid selection',
            
            // Time & Dates
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'tomorrow' => 'Tomorrow',
            'this_week' => 'This Week',
            'last_week' => 'Last Week',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
            'never' => 'Never',
            'always' => 'Always',
            'just_now' => 'Just now',
            'minutes_ago' => 'minutes ago',
            'hours_ago' => 'hours ago',
            'days_ago' => 'days ago',
            
            // Actions
            'create' => 'Create',
            'edit' => 'Edit',
            'update' => 'Update',
            'delete' => 'Delete',
            'remove' => 'Remove',
            'add' => 'Add',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'close' => 'Close',
            'open' => 'Open',
            'view' => 'View',
            'search' => 'Search',
            'filter' => 'Filter',
            'sort' => 'Sort',
            'refresh' => 'Refresh',
            'reload' => 'Reload',
            'copy' => 'Copy',
            'paste' => 'Paste',
            'cut' => 'Cut',
            'select_all' => 'Select All',
            'clear' => 'Clear',
            'reset' => 'Reset',
            'submit' => 'Submit',
            'send' => 'Send',
            'receive' => 'Receive',
            'import' => 'Import',
            'backup' => 'Backup',
            'restore' => 'Restore',
            'compress' => 'Compress',
            'decompress' => 'Decompress',
            'encrypt' => 'Encrypt',
            'decrypt' => 'Decrypt',
            
            // Navigation
            'home' => 'Home',
            'dashboard' => 'Dashboard',
            'chats' => 'Chats',
            'models' => 'Models',
            'exports' => 'Exports',
            'settings' => 'Settings',
            'help' => 'Help',
            'about' => 'About',
            'contact' => 'Contact',
            'privacy' => 'Privacy',
            'terms' => 'Terms of Service',
            'license' => 'License',
            'documentation' => 'Documentation',
            'api_docs' => 'API Documentation',
            'tutorials' => 'Tutorials',
            'faq' => 'FAQ',
            'support' => 'Support',
            
            // Status Messages
            'online' => 'Online',
            'offline' => 'Offline',
            'connected' => 'Connected',
            'disconnected' => 'Disconnected',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'enabled' => 'Enabled',
            'disabled' => 'Disabled',
            'public' => 'Public',
            'private' => 'Private',
            'encrypted' => 'Encrypted',
            'unencrypted' => 'Unencrypted',
            'verified' => 'Verified',
            'unverified' => 'Unverified',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }
    
    /**
     * Simple translation function (placeholder for AI translation)
     */
    private static function translateToLanguage($translations, $lang, $nativeName) {
        // This is a simplified approach
        // In production, you would use AI translation services
        $translated = [];
        
        foreach ($translations as $key => $value) {
            // For now, just use the English version with language indicator
            $translated[$key] = $value; // Would be replaced with actual translation
        }
        
        return $translated;
    }
    
    /**
     * Get translation for a key
     */
    public static function get($key, $params = [], $language = null) {
        $lang = $language ?? self::$currentLanguage;
        
        // Try current language first
        if (isset(self::$translations[$lang][$key])) {
            $translation = self::$translations[$lang][$key];
        } 
        // Fallback to default language
        elseif (isset(self::$translations[self::$fallbackLanguage][$key])) {
            $translation = self::$translations[self::$fallbackLanguage][$key];
        } 
        // Last resort: return the key itself
        else {
            $translation = $key;
        }
        
        // Replace parameters
        foreach ($params as $param => $value) {
            $translation = str_replace('{' . $param . '}', $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Set current language
     */
    public static function setLanguage($language) {
        if (isset(SUPPORTED_LANGUAGES[$language])) {
            self::$currentLanguage = $language;
            self::loadLanguage($language);
            return true;
        }
        return false;
    }
    
    /**
     * Get current language
     */
    public static function getCurrentLanguage() {
        return self::$currentLanguage;
    }
    
    /**
     * Get supported languages
     */
    public static function getSupportedLanguages() {
        return SUPPORTED_LANGUAGES;
    }
    
    /**
     * Detect language from text
     */
    public static function detectLanguage($text) {
        // Simple language detection based on character sets
        // In production, use a proper language detection library
        
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            return 'zh'; // Chinese
        }
        if (preg_match('/[\x{3040}-\x{309f}\x{30a0}-\x{30ff}]/u', $text)) {
            return 'ja'; // Japanese
        }
        if (preg_match('/[\x{ac00}-\x{d7af}]/u', $text)) {
            return 'ko'; // Korean
        }
        if (preg_match('/[\x{0600}-\x{06ff}]/u', $text)) {
            return 'ar'; // Arabic
        }
        if (preg_match('/[\x{0590}-\x{05ff}]/u', $text)) {
            return 'he'; // Hebrew
        }
        if (preg_match('/[\x{0400}-\x{04ff}]/u', $text)) {
            return 'ru'; // Russian (Cyrillic)
        }
        if (preg_match('/[\x{0370}-\x{03ff}]/u', $text)) {
            return 'el'; // Greek
        }
        if (preg_match('/[\x{0900}-\x{097f}]/u', $text)) {
            return 'hi'; // Hindi
        }
        if (preg_match('/[\x{0e00}-\x{0e7f}]/u', $text)) {
            return 'th'; // Thai
        }
        
        // Default to English for Latin script
        return 'en';
    }
    
    /**
     * Get language direction (LTR/RTL)
     */
    public static function getLanguageDirection($language = null) {
        $lang = $language ?? self::$currentLanguage;
        return SUPPORTED_LANGUAGES[$lang]['rtl'] ?? false ? 'rtl' : 'ltr';
    }
    
    /**
     * Get language family
     */
    public static function getLanguageFamily($language = null) {
        $lang = $language ?? self::$currentLanguage;
        return SUPPORTED_LANGUAGES[$lang]['family'] ?? 'Unknown';
    }
    
    /**
     * Get best AI models for language
     */
    public static function getBestModelsForLanguage($language) {
        $langFamily = self::getLanguageFamily($language);
        $bestModels = [];
        
        foreach (AI_MODELS as $modelId => $model) {
            $score = 1.0;
            
            // Check if model specifically supports the language
            if ($model['languages'] === 'all') {
                $score += 0.5;
            } elseif ($model['languages'] === 'major' && in_array($language, ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh', 'ar', 'hi'])) {
                $score += 0.3;
            } elseif ($model['languages'] === 'european' && in_array($langFamily, ['Germanic', 'Romance', 'Slavic', 'Celtic', 'Uralic'])) {
                $score += 0.4;
            }
            
            // Bonus for specific model strengths
            if (in_array('multilingual', $model['strengths'])) {
                $score += 0.2;
            }
            
            // Special bonuses for specific language families
            if ($langFamily === 'Romance' && $modelId === 'mistral') {
                $score += 0.3; // Mistral is strong with European languages
            }
            if ($langFamily === 'Sino-Tibetan' && in_array($modelId, ['gpt4', 'gemini'])) {
                $score += 0.2; // GPT-4 and Gemini handle Chinese well
            }
            
            $bestModels[$modelId] = $score;
        }
        
        arsort($bestModels);
        return array_keys($bestModels);
    }
    
    /**
     * Save language file
     */
    public static function saveLanguageFile($language, $translations) {
        if (!file_exists(LANG_DIR)) {
            mkdir(LANG_DIR, 0755, true);
        }
        
        $langFile = LANG_DIR . "/$language.json";
        $jsonData = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return file_put_contents($langFile, $jsonData) !== false;
    }
    
    /**
     * Generate language files for all supported languages
     */
    public static function generateAllLanguageFiles() {
        $defaultTranslations = self::getDefaultTranslations();
        
        foreach (SUPPORTED_LANGUAGES as $lang => $info) {
            if ($lang === 'en') {
                self::saveLanguageFile($lang, $defaultTranslations);
            } else {
                // In production, use AI translation here
                $translated = self::translateToLanguage($defaultTranslations, $lang, $info['native']);
                self::saveLanguageFile($lang, $translated);
            }
        }
    }
}

// ==============================================
// Enhanced Encryption & Security Classes
// ==============================================

/**
 * End-to-End Encryption Manager with multi-language support
 */
class EncryptionManager {
    private static $userKeys = [];
    
    /**
     * Generate user-specific encryption key
     */
    public static function generateUserKey($userId, $password) {
        $salt = random_bytes(32);
        $key = hash_pbkdf2('sha256', $password, $salt, 10000, 32, true);
        return [
            'key' => base64_encode($key),
            'salt' => base64_encode($salt)
        ];
    }
    
    /**
     * Derive encryption key from password
     */
    public static function deriveKey($password, $salt) {
        $saltBinary = base64_decode($salt);
        $key = hash_pbkdf2('sha256', $password, $saltBinary, 10000, 32, true);
        return base64_encode($key);
    }
    
    /**
     * Encrypt data with user's key (supports Unicode)
     */
    public static function encryptUserData($data, $userKey) {
        $key = base64_decode($userKey);
        $iv = random_bytes(16);
        
        // Ensure proper UTF-8 encoding
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $encrypted = openssl_encrypt($jsonData, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data with user's key (supports Unicode)
     */
    public static function decryptUserData($encryptedData, $userKey) {
        try {
            $key = base64_decode($userKey);
            $data = base64_decode($encryptedData);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
            return json_decode($decrypted, true);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Generate temporary session key
     */
    public static function generateSessionKey() {
        return base64_encode(random_bytes(32));
    }
    
    /**
     * Encrypt with session key (supports Unicode)
     */
    public static function encryptWithSession($data, $sessionKey) {
        $key = base64_decode($sessionKey);
        $iv = random_bytes(16);
        
        // Ensure proper UTF-8 encoding for Unicode text
        if (is_string($data)) {
            $data = mb_convert_encoding($data, 'UTF-8', 'auto');
        }
        
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt with session key (supports Unicode)
     */
    public static function decryptWithSession($encryptedData, $sessionKey) {
        try {
            $key = base64_decode($sessionKey);
            $data = base64_decode($encryptedData);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
            
            // Ensure proper UTF-8 output
            return mb_convert_encoding($decrypted, 'UTF-8', 'auto');
        } catch (Exception $e) {
            return null;
        }
    }
}

/**
 * Security audit and monitoring with multi-language support
 */
class SecurityManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Log security events with language context
     */
    public function logSecurityEvent($userId, $event, $details = '', $language = 'en') {
        $stmt = $this->db->prepare('INSERT INTO security_logs 
            (user_id, event_type, details, ip_address, user_agent, language, created_at) 
            VALUES (:user_id, :event, :details, :ip, :ua, :lang, datetime("now"))');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':event', $event, SQLITE3_TEXT);
        $stmt->bindValue(':details', $details, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', SQLITE3_TEXT);
        $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown', SQLITE3_TEXT);
        $stmt->bindValue(':lang', $language, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity($userId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM security_logs 
            WHERE user_id = :user_id AND created_at > datetime("now", "-1 hour") 
            AND event_type IN ("failed_login", "invalid_token", "rate_limit_exceeded")');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        return ($row['count'] ?? 0) > 10;
    }
    
    /**
     * Generate secure token
     */
    public function generateSecureToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $stmt = $this->db->prepare('INSERT INTO user_tokens 
            (user_id, token, expires_at) VALUES (:user_id, :token, :expires)');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':token', hash('sha256', $token), SQLITE3_TEXT);
        $stmt->bindValue(':expires', $expires, SQLITE3_TEXT);
        $stmt->execute();
        
        return $token;
    }
    
    /**
     * Validate token
     */
    public function validateToken($token, $userId) {
        $hashedToken = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT * FROM user_tokens 
            WHERE user_id = :user_id AND token = :token AND expires_at > datetime("now")');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':token', $hashedToken, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC) !== false;
    }
    
    /**
     * Check rate limit for specific endpoint
     */
    public function checkRateLimit($userId, $endpoint, $limit = 60) {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM api_logs 
            WHERE user_id = :user_id AND endpoint = :endpoint 
            AND created_at > datetime("now", "-1 minute")');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        return ($row['count'] ?? 0) < $limit;
    }
}

/**
 * Advanced Multi-AI Consensus Engine with language optimization
 */
class ConsensusEngine {
    private $db;
    private $models;
    
    public function __construct($db) {
        $this->db = $db;
        $this->models = AI_MODELS;
    }
    
    /**
     * Query multiple AI models with language optimization
     */
    public function queryMultipleModels($prompt, $selectedModels, $language, $userId) {
        // Optimize model selection for the language
        $optimizedModels = $this->optimizeModelsForLanguage($selectedModels, $language);
        
        $responses = [];
        
        // Query each model
        foreach ($optimizedModels as $model) {
            try {
                $response = $this->queryAI($model, $prompt, $language);
                $responses[$model] = [
                    'content' => $response,
                    'model' => $model,
                    'timestamp' => microtime(true),
                    'success' => true,
                    'language' => $language
                ];
            } catch (Exception $e) {
                $responses[$model] = [
                    'content' => '',
                    'model' => $model,
                    'error' => $e->getMessage(),
                    'success' => false,
                    'language' => $language
                ];
            }
        }
        
        // Apply consensus algorithm
        return $this->applyAdvancedConsensus($responses, $prompt, $language, $userId);
    }
    
    /**
     * Optimize model selection for specific language
     */
    private function optimizeModelsForLanguage($selectedModels, $language) {
        $bestModels = LanguageManager::getBestModelsForLanguage($language);
        $optimized = [];
        
        // Prioritize models based on language compatibility
        foreach ($bestModels as $model) {
            if (in_array($model, $selectedModels)) {
                $optimized[] = $model;
            }
        }
        
        // Add remaining selected models
        foreach ($selectedModels as $model) {
            if (!in_array($model, $optimized)) {
                $optimized[] = $model;
            }
        }
        
        return $optimized;
    }
    
    /**
     * Advanced consensus algorithm with weighted scoring and language considerations
     */
    private function applyAdvancedConsensus($responses, $originalPrompt, $language, $userId) {
        $validResponses = array_filter($responses, function($r) { return $r['success']; });
        
        if (empty($validResponses)) {
            throw new Exception(LanguageManager::get('no_valid_responses', [], $language));
        }
        
        if (count($validResponses) === 1) {
            return array_values($validResponses)[0];
        }
        
        // Calculate similarity scores between responses
        $similarities = $this->calculateResponseSimilarities($validResponses, $language);
        
        // Get user's model preferences and weights
        $weights = $this->getUserModelWeights($userId, $this->categorizePrompt($originalPrompt, $language));
        
        // Score each response
        $scores = [];
        foreach ($validResponses as $model => $response) {
            $scores[$model] = $this->calculateResponseScore(
                $response, 
                $validResponses, 
                $similarities, 
                $weights[$model] ?? 1.0,
                $originalPrompt,
                $language
            );
        }
        
        // Create consensus response
        return $this->createConsensusResponse($validResponses, $scores, $language);
    }
    
    /**
     * Calculate similarity between responses with language awareness
     */
    private function calculateResponseSimilarities($responses, $language) {
        $similarities = [];
        
        foreach ($responses as $model1 => $response1) {
            $similarities[$model1] = [];
            foreach ($responses as $model2 => $response2) {
                if ($model1 === $model2) {
                    $similarities[$model1][$model2] = 1.0;
                } else {
                    $similarities[$model1][$model2] = $this->calculateTextSimilarity(
                        $response1['content'], 
                        $response2['content'],
                        $language
                    );
                }
            }
        }
        
        return $similarities;
    }
    
    /**
     * Calculate text similarity with language-specific processing
     */
    private function calculateTextSimilarity($text1, $text2, $language) {
        // Ensure proper UTF-8 encoding
        $text1 = mb_convert_encoding($text1, 'UTF-8', 'auto');
        $text2 = mb_convert_encoding($text2, 'UTF-8', 'auto');
        
        // Language-specific text processing
        if (LanguageManager::getLanguageFamily($language) === 'Sino-Tibetan') {
            // For Chinese/Japanese, use character-based comparison
            $chars1 = preg_split('//u', $text1, -1, PREG_SPLIT_NO_EMPTY);
            $chars2 = preg_split('//u', $text2, -1, PREG_SPLIT_NO_EMPTY);
            
            $vector1 = array_count_values($chars1);
            $vector2 = array_count_values($chars2);
        } else {
            // For other languages, use word-based comparison
            $words1 = array_count_values(str_word_count(mb_strtolower($text1), 1, 'UTF-8'));
            $words2 = array_count_values(str_word_count(mb_strtolower($text2), 1, 'UTF-8'));
            
            $vector1 = $words1;
            $vector2 = $words2;
        }
        
        $allKeys = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));
        $vec1 = [];
        $vec2 = [];
        
        foreach ($allKeys as $key) {
            $vec1[] = $vector1[$key] ?? 0;
            $vec2[] = $vector2[$key] ?? 0;
        }
        
        $dotProduct = array_sum(array_map(function($a, $b) { return $a * $b; }, $vec1, $vec2));
        $magnitude1 = sqrt(array_sum(array_map(function($a) { return $a * $a; }, $vec1)));
        $magnitude2 = sqrt(array_sum(array_map(function($a) { return $a * $a; }, $vec2)));
        
        if ($magnitude1 == 0 || $magnitude2 == 0) return 0;
        
        return $dotProduct / ($magnitude1 * $magnitude2);
    }
    
    /**
     * Score individual response based on multiple factors with language considerations
     */
    private function calculateResponseScore($response, $allResponses, $similarities, $userWeight, $prompt, $language) {
        $content = $response['content'];
        $model = $response['model'];
        
        // Base score from model capabilities for this language
        $modelConfig = $this->models[$model];
        $baseScore = $this->getModelBaseScore($modelConfig, $prompt, $language);
        
        // Consensus score
        $consensusScore = 0;
        $similarityCount = 0;
        foreach ($similarities[$model] as $otherModel => $similarity) {
            if ($otherModel !== $model) {
                $consensusScore += $similarity;
                $similarityCount++;
            }
        }
        $consensusScore = $similarityCount > 0 ? $consensusScore / $similarityCount : 0;
        
        // Quality metrics with language considerations
        $lengthScore = $this->calculateLengthScore($content, $language);
        $relevanceScore = $this->calculateRelevanceScore($content, $prompt, $language);
        $languageQualityScore = $this->calculateLanguageQualityScore($content, $language);
        
        // Combined score
        $totalScore = (
            $baseScore * 0.2 +
            $consensusScore * 0.25 +
            $lengthScore * 0.15 +
            $relevanceScore * 0.25 +
            $languageQualityScore * 0.15
        ) * $userWeight;
        
        return $totalScore;
    }
    
    /**
     * Get base score for model based on language compatibility
     */
    private function getModelBaseScore($modelConfig, $prompt, $language) {
        $promptLower = mb_strtolower($prompt);
        $score = 1.0;
        
        // Language compatibility bonus
        $langFamily = LanguageManager::getLanguageFamily($language);
        
        if ($modelConfig['languages'] === 'all') {
            $score += 0.5;
        } elseif ($modelConfig['languages'] === 'major' && in_array($language, ['en', 'es', 'fr', 'de', 'it', 'pt', 'ru', 'ja', 'ko', 'zh', 'ar', 'hi'])) {
            $score += 0.3;
        } elseif ($modelConfig['languages'] === 'european' && in_array($langFamily, ['Germanic', 'Romance', 'Slavic', 'Celtic', 'Uralic'])) {
            $score += 0.4;
        }
        
        // Content type bonuses
        if (strpos($promptLower, 'code') !== false || strpos($promptLower, 'program') !== false) {
            if (in_array('coding', $modelConfig['strengths'])) $score += 0.3;
        }
        
        if (strpos($promptLower, 'creative') !== false || strpos($promptLower, 'story') !== false) {
            if (in_array('creativity', $modelConfig['strengths'])) $score += 0.3;
        }
        
        if (strpos($promptLower, 'technical') !== false || strpos($promptLower, 'scientific') !== false) {
            if (in_array('technical', $modelConfig['strengths'])) $score += 0.3;
        }
        
        if (strpos($promptLower, 'analysis') !== false || strpos($promptLower, 'analyze') !== false) {
            if (in_array('analysis', $modelConfig['strengths'])) $score += 0.3;
        }
        
        // Multilingual bonus
        if (in_array('multilingual', $modelConfig['strengths'])) {
            $score += 0.2;
        }
        
        return min(2.0, $score);
    }
    
    /**
     * Calculate length score with language considerations
     */
    private function calculateLengthScore($content, $language) {
        // Different languages have different average word/character lengths
        $langFamily = LanguageManager::getLanguageFamily($language);
        
        if ($langFamily === 'Sino-Tibetan') {
            // Character-based languages
            $length = mb_strlen($content, 'UTF-8');
            return min(1.0, $length / 500);
        } else {
            // Word-based languages
            $length = str_word_count($content);
            return min(1.0, $length / 200);
        }
    }
    
    /**
     * Calculate relevance score with language considerations
     */
    private function calculateRelevanceScore($content, $prompt, $language) {
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'auto');
        
        $langFamily = LanguageManager::getLanguageFamily($language);
        
        if ($langFamily === 'Sino-Tibetan') {
            // Character-based matching for Chinese/Japanese
            $promptChars = preg_split('//u', mb_strtolower($prompt), -1, PREG_SPLIT_NO_EMPTY);
            $contentChars = preg_split('//u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
            
            $matchCount = 0;
            foreach ($promptChars as $char) {
                if (in_array($char, $contentChars)) {
                    $matchCount++;
                }
            }
            
            return min(1.0, $matchCount / max(1, count($promptChars)));
        } else {
            // Word-based matching for other languages
            $promptWords = str_word_count(mb_strtolower($prompt), 1, 'UTF-8');
            $contentWords = str_word_count(mb_strtolower($content), 1, 'UTF-8');
            
            $matchCount = 0;
            foreach ($promptWords as $word) {
                if (mb_strlen($word) > 3 && in_array($word, $contentWords)) {
                    $matchCount++;
                }
            }
            
            return min(1.0, $matchCount / max(1, count($promptWords)));
        }
    }
    
    /**
     * Calculate language quality score
     */
    private function calculateLanguageQualityScore($content, $language) {
        // Basic language quality assessment
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        
        // Check for proper encoding
        if (!mb_check_encoding($content, 'UTF-8')) {
            return 0.3;
        }
        
        // Check length
        if (mb_strlen($content) < 10) {
            return 0.5;
        }
        
        // Check for repetitive content
        $sentences = preg_split('/[.!?]+/', $content);
        $uniqueSentences = array_unique($sentences);
        $uniquenessRatio = count($uniqueSentences) / max(1, count($sentences));
        
        // Check for appropriate language script
        $expectedScript = $this->getExpectedScript($language);
        $scriptMatch = $this->checkScriptMatch($content, $expectedScript);
        
        return min(1.0, ($uniquenessRatio + $scriptMatch) / 2);
    }
    
    /**
     * Get expected script for language
     */
    private function getExpectedScript($language) {
        $scriptMap = [
            'ar' => 'arabic',
            'he' => 'hebrew',
            'ru' => 'cyrillic',
            'bg' => 'cyrillic',
            'mk' => 'cyrillic',
            'sr' => 'cyrillic',
            'el' => 'greek',
            'zh' => 'han',
            'ja' => 'han_hiragana_katakana',
            'ko' => 'hangul',
            'th' => 'thai',
            'hi' => 'devanagari',
            'bn' => 'bengali',
            'ta' => 'tamil',
            'te' => 'telugu',
            'kn' => 'kannada',
            'ml' => 'malayalam',
            'gu' => 'gujarati',
            'pa' => 'gurmukhi',
            'mr' => 'devanagari',
            'ne' => 'devanagari',
            'si' => 'sinhala',
            'my' => 'myanmar',
            'km' => 'khmer',
            'lo' => 'lao',
            'ka' => 'georgian',
            'hy' => 'armenian',
            'am' => 'ethiopic'
        ];
        
        return $scriptMap[$language] ?? 'latin';
    }
    
    /**
     * Check if content matches expected script
     */
    private function checkScriptMatch($content, $expectedScript) {
        $scriptPatterns = [
            'latin' => '/[a-zA-Z]/',
            'arabic' => '/[\x{0600}-\x{06ff}]/u',
            'hebrew' => '/[\x{0590}-\x{05ff}]/u',
            'cyrillic' => '/[\x{0400}-\x{04ff}]/u',
            'greek' => '/[\x{0370}-\x{03ff}]/u',
            'han' => '/[\x{4e00}-\x{9fff}]/u',
            'han_hiragana_katakana' => '/[\x{3040}-\x{309f}\x{30a0}-\x{30ff}\x{4e00}-\x{9fff}]/u',
            'hangul' => '/[\x{ac00}-\x{d7af}]/u',
            'thai' => '/[\x{0e00}-\x{0e7f}]/u',
            'devanagari' => '/[\x{0900}-\x{097f}]/u',
            'bengali' => '/[\x{0980}-\x{09ff}]/u',
            'tamil' => '/[\x{0b80}-\x{0bff}]/u',
            'telugu' => '/[\x{0c00}-\x{0c7f}]/u',
            'kannada' => '/[\x{0c80}-\x{0cff}]/u',
            'malayalam' => '/[\x{0d00}-\x{0d7f}]/u',
            'gujarati' => '/[\x{0a80}-\x{0aff}]/u',
            'gurmukhi' => '/[\x{0a00}-\x{0a7f}]/u',
            'sinhala' => '/[\x{0d80}-\x{0dff}]/u',
            'myanmar' => '/[\x{1000}-\x{109f}]/u',
            'khmer' => '/[\x{1780}-\x{17ff}]/u',
            'lao' => '/[\x{0e80}-\x{0eff}]/u',
            'georgian' => '/[\x{10a0}-\x{10ff}]/u',
            'armenian' => '/[\x{0530}-\x{058f}]/u',
            'ethiopic' => '/[\x{1200}-\x{137f}]/u'
        ];
        
        if (!isset($scriptPatterns[$expectedScript])) {
            return 1.0; // Unknown script, assume it's fine
        }
        
        $pattern = $scriptPatterns[$expectedScript];
        $matches = preg_match_all($pattern, $content);
        $totalChars = mb_strlen(preg_replace('/\s/', '', $content));
        
        if ($totalChars === 0) return 0.0;
        
        return min(1.0, $matches / $totalChars);
    }
    
    /**
     * Create final consensus response with language considerations
     */
    private function createConsensusResponse($responses, $scores, $language) {
        arsort($scores);
        $topModel = array_key_first($scores);
        $topResponse = $responses[$topModel];
        
        $scoreValues = array_values($scores);
        if (count($scoreValues) > 1 && ($scoreValues[0] - $scoreValues[1]) < 0.1) {
            $secondModel = array_keys($scores)[1];
            $secondResponse = $responses[$secondModel];
            
            $mergedContent = $this->mergeResponses(
                $topResponse['content'], 
                $secondResponse['content'], 
                $language
            );
            
            return [
                'content' => $mergedContent,
                'primary_model' => $topModel,
                'secondary_model' => $secondModel,
                'consensus_score' => $scoreValues[0],
                'all_scores' => $scores,
                'method' => 'merged_consensus',
                'language' => $language
            ];
        }
        
        return [
            'content' => $topResponse['content'],
            'primary_model' => $topModel,
            'consensus_score' => $scoreValues[0],
            'all_scores' => $scores,
            'method' => 'best_score',
            'language' => $language
        ];
    }
    
    /**
     * Merge two responses intelligently with language awareness
     */
    private function mergeResponses($response1, $response2, $language) {
        $languageInstruction = $this->getLanguageInstruction($language);
        
        $prompt = $languageInstruction . "\n\n" . 
                 LanguageManager::get('merge_responses_instruction', [
                     'response1' => $response1,
                     'response2' => $response2
                 ], $language);
        
        try {
            $mergedResponse = $this->queryAI('gpt4', $prompt, $language);
            return $mergedResponse;
        } catch (Exception $e) {
            return $response1; // Fallback to first response
        }
    }
    
    /**
     * Get language instruction for AI models
     */
    private function getLanguageInstruction($language) {
        $langInfo = SUPPORTED_LANGUAGES[$language] ?? SUPPORTED_LANGUAGES['en'];
        $nativeName = $langInfo['native'];
        
        if ($language === 'en') {
            return "Respond only in English. Do not use any other language.";
        }
        
        return "Respond only in {$nativeName} ({$language}). Do not use any other language. Ensure proper grammar and natural flow in {$nativeName}.";
    }
    
    /**
     * Get user's model weights for specific category
     */
    private function getUserModelWeights($userId, $category) {
        $stmt = $this->db->prepare('SELECT model_name, accuracy_weight, relevance_weight 
            FROM model_weights WHERE user_id = :user_id AND topic_category = :category');
        
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $weights = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $weights[$row['model_name']] = ($row['accuracy_weight'] + $row['relevance_weight']) / 2;
        }
        
        // Default weights for models not in user preferences
        foreach (array_keys(AI_MODELS) as $model) {
            if (!isset($weights[$model])) {
                $weights[$model] = 1.0;
            }
        }
        
        return $weights;
    }
    
    /**
     * Categorize prompt for model weight selection with language awareness
     */
    private function categorizePrompt($prompt, $language) {
        $promptLower = mb_strtolower($prompt);
        
        // Language-specific keywords
        $categories = [
            'programming' => ['code', 'program', 'script', 'function', 'algorithm', 'debug'],
            'creative' => ['creative', 'story', 'poem', 'art', 'design', 'imagination'],
            'scientific' => ['scientific', 'research', 'study', 'analysis', 'experiment', 'theory'],
            'business' => ['business', 'market', 'finance', 'strategy', 'management', 'economics'],
            'academic' => ['academic', 'education', 'learning', 'teaching', 'university', 'paper'],
            'technical' => ['technical', 'engineering', 'system', 'process', 'technology', 'development'],
            'medical' => ['medical', 'health', 'medicine', 'treatment', 'diagnosis', 'healthcare'],
            'legal' => ['legal', 'law', 'court', 'contract', 'rights', 'regulation']
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Query AI with proper error handling and language support
     */
    private function queryAI($model, $prompt, $language) {
        $maxRetries = 3;
        $retryDelay = 1;
        
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                return $this->makeAPIRequest($model, $prompt, $language);
            } catch (Exception $e) {
                if ($i === $maxRetries - 1) {
                    throw $e;
                }
                sleep($retryDelay);
                $retryDelay *= 2;
            }
        }
    }
    
    /**
     * Make actual API request to AI model with language support
     */
    private function makeAPIRequest($model, $prompt, $language) {
        if (!isset(AI_MODELS[$model])) {
            throw new Exception(LanguageManager::get('model_not_configured', ['model' => $model], $language));
        }
        
        $config = AI_MODELS[$model];
        $apiKey = getenv(strtoupper($config['provider']) . '_API_KEY');
        if (!$apiKey) {
            throw new Exception(LanguageManager::get('api_key_missing', ['provider' => $config['provider']], $language));
        }
        
        $systemMessage = $this->getLanguageInstruction($language);
        
        $payload = [
            'model' => $config['default_model'],
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => $config['max_tokens']
        ];
        
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Bearer ' . $apiKey
        ];
        
        // Provider-specific adjustments
        switch ($config['provider']) {
            case 'google':
                $payload = [
                    'contents' => [
                        'parts' => [
                            ['text' => $systemMessage . "\n\n" . $prompt]
                        ]
                    ]
                ];
                $headers = [
                    'Content-Type: application/json; charset=utf-8'
                ];
                $config['endpoint'] .= '?key=' . $apiKey;
                break;
                
            case 'anthropic':
                $payload['system'] = $systemMessage;
                $payload['messages'] = [['role' => 'user', 'content' => $prompt]];
                $payload['max_tokens'] = min($payload['max_tokens'], 4096);
                $headers[] = 'anthropic-version: 2023-06-01';
                break;
        }
        
        $ch = curl_init($config['endpoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception(LanguageManager::get('api_request_failed', ['error' => $error], $language));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception(LanguageManager::get('api_request_failed_status', ['status' => $httpCode, 'response' => $response], $language));
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception(LanguageManager::get('invalid_json_response', [], $language));
        }
        
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
}

// ==============================================
// Helper Functions
// ==============================================

/**
 * Encrypt sensitive data (legacy function)
 */
function encryptData($data) {
    return openssl_encrypt($data, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
}

/**
 * Decrypt sensitive data (legacy function)
 */
function decryptData($encrypted) {
    return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, substr(ENCRYPTION_KEY, 0, 16));
}

/**
 * Sanitize input data with Unicode support
 */
function sanitizeInput($input, $db) {
    if (is_array($input)) {
        return array_map(function($item) use ($db) {
            return $db->escapeString(htmlspecialchars($item, ENT_QUOTES, 'UTF-8'));
        }, $input);
    }
    
    // Ensure proper UTF-8 encoding
    $input = mb_convert_encoding($input, 'UTF-8', 'auto');
    return $db->escapeString(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Generate API key
 */
function generateApiKey() {
    return 'oun_' . bin2hex(random_bytes(24));
}

/**
 * Create standardized multilingual response
 */
function createResponse($status, $messageKey, $data = [], $code = 200, $language = null) {
    $lang = $language ?? LanguageManager::getCurrentLanguage();
    
    // If messageKey is already a message, use it directly
    if (is_string($messageKey)) {
        $message = LanguageManager::get($messageKey, $data, $lang);
    } else {
        $message = $messageKey;
    }
    
    return [
        'status' => $status,
        'message' => $message,
        'code' => $code,
        'data' => $data,
        'language' => $lang,
        'timestamp' => date('c')
    ];
}

/**
 * Detect user's preferred language
 */
function detectUserLanguage($request = null) {
    // Priority order: URL parameter > Header > Browser > Default
    
    // 1. Check URL parameter
    if (isset($_GET['lang']) && isset(SUPPORTED_LANGUAGES[$_GET['lang']])) {
        return $_GET['lang'];
    }
    
    // 2. Check request data
    if ($request && isset($request['language']) && isset(SUPPORTED_LANGUAGES[$request['language']])) {
        return $request['language'];
    }
    
    // 3. Check Accept-Language header
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $acceptLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($acceptLanguages as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            $lang = strtolower(str_replace('_', '-', $lang));
            
            // Direct match
            if (isset(SUPPORTED_LANGUAGES[$lang])) {
                return $lang;
            }
            
            // Try language part only (e.g., 'en' from 'en-US')
            $langPart = explode('-', $lang)[0];
            if (isset(SUPPORTED_LANGUAGES[$langPart])) {
                return $langPart;
            }
        }
    }
    
    // 4. Default to English
    return 'en';
}

/**
 * Format file size for display
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Generate language-aware filename
 */
function generateLanguageAwareFilename($baseName, $language, $extension) {
    $langInfo = SUPPORTED_LANGUAGES[$language] ?? SUPPORTED_LANGUAGES['en'];
    $langCode = $language;
    
    // Sanitize base name for filename
    $safeBaseName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $baseName);
    $safeBaseName = trim($safeBaseName, '_');
    
    if (empty($safeBaseName)) {
        $safeBaseName = 'document';
    }
    
    return $safeBaseName . '_' . $langCode . '_' . time() . '.' . $extension;
}

// ==============================================
// Enhanced Database Initialization
// ==============================================

/**
 * Initialize database with enhanced schema and multi-language support
 */
function initEnhancedDB() {
    // Create directories if not exists
    $directories = [EXPORTS_DIR, TEMP_DIR, LANG_DIR];
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    $db = new SQLite3(DB_FILE);
    $db->busyTimeout(5000);
    
    // Enable WAL mode for better concurrency
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');
    
    // Create users table with language preferences
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        api_key TEXT UNIQUE NOT NULL,
        language TEXT DEFAULT "en",
        timezone TEXT DEFAULT "UTC",
        date_format TEXT DEFAULT "Y-m-d H:i:s",
        is_admin BOOLEAN DEFAULT 0,
        rate_limit INTEGER DEFAULT 100,
        encryption_salt TEXT DEFAULT NULL,
        session_key TEXT DEFAULT NULL,
        last_key_rotation DATETIME DEFAULT NULL,
        preferred_models TEXT DEFAULT NULL,
        ui_theme TEXT DEFAULT "light",
        email_notifications BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create researches table with enhanced multi-language support
    $db->exec('CREATE TABLE IF NOT EXISTS researches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        chat_id TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        topic TEXT NOT NULL,
        description TEXT,
        original_language TEXT NOT NULL,
        target_language TEXT DEFAULT NULL,
        detected_language TEXT DEFAULT NULL,
        language_confidence REAL DEFAULT 0.0,
        models TEXT NOT NULL,
        depth INTEGER DEFAULT 3,
        status TEXT DEFAULT "pending",
        progress INTEGER DEFAULT 0,
        results TEXT,
        notes TEXT,
        metadata TEXT DEFAULT NULL,
        encrypted_topic TEXT DEFAULT NULL,
        encrypted_description TEXT DEFAULT NULL,
        encrypted_results TEXT DEFAULT NULL,
        encrypted_notes TEXT DEFAULT NULL,
        is_encrypted BOOLEAN DEFAULT 0,
        is_public BOOLEAN DEFAULT 0,
        is_featured BOOLEAN DEFAULT 0,
        tags TEXT,
        category TEXT DEFAULT "general",
        word_count INTEGER DEFAULT 0,
        character_count INTEGER DEFAULT 0,
        reading_time INTEGER DEFAULT 0,
        quality_score REAL DEFAULT 0.0,
        consensus_method TEXT DEFAULT NULL,
        ai_confidence REAL DEFAULT 0.0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    
    // Create exports table with language information
    $db->exec('CREATE TABLE IF NOT EXISTS exports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id TEXT NOT NULL,
        user_id INTEGER NOT NULL,
        format TEXT NOT NULL,
        export_type TEXT NOT NULL,
        language TEXT NOT NULL,
        file_path TEXT NOT NULL UNIQUE,
        original_filename TEXT NOT NULL,
        file_size INTEGER DEFAULT 0,
        mime_type TEXT DEFAULT NULL,
        download_count INTEGER DEFAULT 0,
        is_public BOOLEAN DEFAULT 0,
        expires_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(chat_id) REFERENCES researches(chat_id) ON DELETE CASCADE,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    
    // Create api_logs table with enhanced tracking
    $db->exec('CREATE TABLE IF NOT EXISTS api_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        endpoint TEXT NOT NULL,
        method TEXT NOT NULL,
        user_id INTEGER,
        language TEXT DEFAULT "en",
        user_agent TEXT DEFAULT NULL,
        ip_address TEXT DEFAULT NULL,
        status_code INTEGER DEFAULT NULL,
        response_time REAL DEFAULT NULL,
        request_size INTEGER DEFAULT NULL,
        response_size INTEGER DEFAULT NULL,
        error_message TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create security_logs table with language context
    $db->exec('CREATE TABLE IF NOT EXISTS security_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        event_type TEXT NOT NULL,
        severity TEXT DEFAULT "info",
        details TEXT,
        language TEXT DEFAULT "en",
        ip_address TEXT,
        user_agent TEXT,
        country_code TEXT DEFAULT NULL,
        session_id TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
    )');
    
    // Create user_tokens table for secure sessions
    $db->exec('CREATE TABLE IF NOT EXISTS user_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL,
        token_type TEXT DEFAULT "session",
        language TEXT DEFAULT "en",
        expires_at DATETIME NOT NULL,
        last_used DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    
    // Create AI model consensus weights with language categories
    $db->exec('CREATE TABLE IF NOT EXISTS model_weights (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        language TEXT NOT NULL,
        topic_category TEXT NOT NULL,
        model_name TEXT NOT NULL,
        accuracy_weight REAL DEFAULT 1.0,
        relevance_weight REAL DEFAULT 1.0,
        speed_weight REAL DEFAULT 1.0,
        usage_count INTEGER DEFAULT 0,
        last_rating REAL DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(user_id, language, topic_category, model_name)
    )');
    
    // Create language_preferences table
    $db->exec('CREATE TABLE IF NOT EXISTS language_preferences (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        source_language TEXT NOT NULL,
        target_language TEXT NOT NULL,
        auto_detect BOOLEAN DEFAULT 1,
        auto_translate BOOLEAN DEFAULT 0,
        preferred_models TEXT DEFAULT NULL,
        custom_instructions TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(user_id, source_language, target_language)
    )');
    
    // Create research_steps table for detailed tracking
    $db->exec('CREATE TABLE IF NOT EXISTS research_steps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chat_id TEXT NOT NULL,
        step_number INTEGER NOT NULL,
        step_title TEXT NOT NULL,
        step_description TEXT,
        language TEXT NOT NULL,
        status TEXT DEFAULT "pending",
        ai_model TEXT NOT NULL,
        input_prompt TEXT,
        ai_response TEXT,
        response_quality REAL DEFAULT NULL,
        processing_time REAL DEFAULT NULL,
        tokens_used INTEGER DEFAULT NULL,
        error_message TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        FOREIGN KEY(chat_id) REFERENCES researches(chat_id) ON DELETE CASCADE
    )');
    
    // Create system_settings table
    $db->exec('CREATE TABLE IF NOT EXISTS system_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        setting_type TEXT DEFAULT "string",
        description TEXT DEFAULT NULL,
        is_public BOOLEAN DEFAULT 0,
        updated_by INTEGER DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
    )');
    
    // Create language_stats table for analytics
    $db->exec('CREATE TABLE IF NOT EXISTS language_stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        date DATE NOT NULL,
        language TEXT NOT NULL,
        total_requests INTEGER DEFAULT 0,
        total_words INTEGER DEFAULT 0,
        total_characters INTEGER DEFAULT 0,
        avg_response_time REAL DEFAULT 0.0,
        success_rate REAL DEFAULT 0.0,
        most_used_model TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(date, language)
    )');
    
    // Create indexes for better performance
    $indexes = [
        'CREATE INDEX IF NOT EXISTS idx_users_language ON users(language)',
        'CREATE INDEX IF NOT EXISTS idx_users_api_key ON users(api_key)',
        'CREATE INDEX IF NOT EXISTS idx_researches_user_language ON researches(user_id, original_language)',
        'CREATE INDEX IF NOT EXISTS idx_researches_status ON researches(status)',
        'CREATE INDEX IF NOT EXISTS idx_researches_language ON researches(original_language)',
        'CREATE INDEX IF NOT EXISTS idx_exports_language ON exports(language)',
        'CREATE INDEX IF NOT EXISTS idx_api_logs_language ON api_logs(language)',
        'CREATE INDEX IF NOT EXISTS idx_api_logs_created_at ON api_logs(created_at)',
        'CREATE INDEX IF NOT EXISTS idx_security_logs_user_created ON security_logs(user_id, created_at)',
        'CREATE INDEX IF NOT EXISTS idx_model_weights_user_lang ON model_weights(user_id, language)',
        'CREATE INDEX IF NOT EXISTS idx_research_steps_chat_step ON research_steps(chat_id, step_number)',
        'CREATE INDEX IF NOT EXISTS idx_language_stats_date_lang ON language_stats(date, language)'
    ];
    
    foreach ($indexes as $index) {
        $db->exec($index);
    }
    
    // Insert default system settings
    $defaultSettings = [
        ['maintenance_mode', '0', 'boolean', 'System maintenance mode'],
        ['default_language', 'en', 'string', 'Default system language'],
        ['max_file_size', '10485760', 'integer', 'Maximum file upload size in bytes'],
        ['session_timeout', '3600', 'integer', 'Session timeout in seconds'],
        ['enable_public_exports', '1', 'boolean', 'Allow public export sharing'],
        ['enable_analytics', '1', 'boolean', 'Enable system analytics'],
        ['rate_limit_anonymous', '10', 'integer', 'Rate limit for anonymous users'],
        ['supported_export_formats', 'pdf,docx,txt,html,md', 'string', 'Supported export formats'],
        ['ai_timeout', '30', 'integer', 'AI API timeout in seconds'],
        ['max_research_depth', '10', 'integer', 'Maximum research depth allowed'],
        ['enable_encryption', '1', 'boolean', 'Enable user data encryption'],
        ['backup_retention_days', '30', 'integer', 'Days to keep backup files']
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt = $db->prepare('INSERT OR IGNORE INTO system_settings 
            (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $setting[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $setting[1], SQLITE3_TEXT);
        $stmt->bindValue(3, $setting[2], SQLITE3_TEXT);
        $stmt->bindValue(4, $setting[3], SQLITE3_TEXT);
        $stmt->execute();
    }
    
    // Create default admin user if not exists
    $result = $db->querySingle("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    if ($result == 0) {
        $apiKey = generateApiKey();
        $stmt = $db->prepare("INSERT INTO users 
            (username, email, password_hash, api_key, is_admin, rate_limit, language) 
            VALUES (:username, :email, :password, :api_key, 1, 1000, 'en')");
        
        $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':email', 'admin@ounachu.com', SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash('admin123', PASSWORD_BCRYPT), SQLITE3_TEXT);
        $stmt->bindValue(':api_key', $apiKey, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    return $db;
}

$db = initEnhancedDB();

// Initialize Language Manager
LanguageManager::init(detectUserLanguage());

// ==============================================
// Core Ounachu Engine Class with Multi-Language Support
// ==============================================

class OunachuEngine {
    private $db;
    private $cache = [];
    private $encryptionManager;
    private $securityManager;
    private $consensusEngine;
    
    public function __construct($db) {
        $this->db = $db;
        $this->encryptionManager = new EncryptionManager();
        $this->securityManager = new SecurityManager($db);
        $this->consensusEngine = new ConsensusEngine($db);
    }
    
    /**
     * Enhanced user authentication with multi-language support
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
            // Set user's preferred language
            LanguageManager::setLanguage($user['language']);
            
            // Check for suspicious activity
            if ($this->securityManager->checkSuspiciousActivity($user['id'])) {
                $this->securityManager->logSecurityEvent($user['id'], 'suspicious_activity_detected', '', $user['language']);
                return null;
            }
            
            $this->cache['users'][$apiKey] = $user;
            $this->securityManager->logSecurityEvent($user['id'], 'api_access', '', $user['language']);
            return $user;
        }
        return null;
    }
    
    /**
     * Enhanced user authentication with encryption
     */
    public function authenticateUserWithEncryption($apiKey, $password = null) {
        $user = $this->authenticateUser($apiKey);
        if (!$user) return null;
        
        // Generate session key if password provided for encryption
        if ($password && $user['encryption_salt']) {
            $derivedKey = EncryptionManager::deriveKey($password, $user['encryption_salt']);
            $sessionKey = EncryptionManager::generateSessionKey();
            
            // Store session key encrypted with user's key
            $encryptedSessionKey = EncryptionManager::encryptUserData($sessionKey, $derivedKey);
            $this->updateUserSessionKey($user['id'], $encryptedSessionKey);
            
            $user['session_key'] = $sessionKey;
            $user['derived_key'] = $derivedKey;
        }
        
        return $user;
    }
    
    /**
     * Create new research chat with enhanced multi-language support
     */
    public function createChat($userId, $title, $topic, $description, $language = null, $targetLanguage = null, $selectedModels = null, $depth = 3, $userKey = null) {
        // Auto-detect language if not provided
        if (!$language) {
            $language = LanguageManager::detectLanguage($topic . ' ' . $description);
        }
        
        // Validate language
        if (!isset(SUPPORTED_LANGUAGES[$language])) {
            return createResponse('error', 'unsupported_language', [], 400, $language);
        }
        
        // Get user preferences for model selection
        if (!$selectedModels) {
            $selectedModels = $this->getUserPreferredModels($userId, $language);
        }
        
        // Optimize models for the language
        $selectedModels = LanguageManager::getBestModelsForLanguage($language);
        $selectedModels = array_slice($selectedModels, 0, 3); // Limit to top 3
        
        // Check rate limits
        if (!$this->checkRateLimit($userId)) {
            return createResponse('error', 'rate_limit_exceeded', [], 429, $language);
        }
        
        // Check user's active chat count
        $activeChats = $this->getUserChatCount($userId);
        if ($activeChats >= MAX_CONCURRENT_CHATS) {
            return createResponse('error', 'max_chats_reached', ['limit' => MAX_CONCURRENT_CHATS], 429, $language);
        }
        
        $chatId = 'chat_' . bin2hex(random_bytes(16));
        $modelsJson = json_encode($selectedModels);
        
        // Prepare data for insertion
        $insertData = [
            'user_id' => $userId,
            'chat_id' => $chatId,
            'title' => $title,
            'topic' => $topic,
            'description' => $description,
            'original_language' => $language,
            'target_language' => $targetLanguage,
            'detected_language' => $language,
            'language_confidence' => 0.9, // Would be calculated by detection algorithm
            'models' => $modelsJson,
            'depth' => $depth,
            'is_encrypted' => 0,
            'category' => $this->categorizeResearchTopic($topic, $language)
        ];
        
        // Encrypt sensitive data if user key provided
        if ($userKey) {
            $insertData['encrypted_title'] = EncryptionManager::encryptUserData($title, $userKey);
            $insertData['encrypted_topic'] = EncryptionManager::encryptUserData($topic, $userKey);
            $insertData['encrypted_description'] = EncryptionManager::encryptUserData($description, $userKey);
            $insertData['title'] = '[ENCRYPTED]';
            $insertData['topic'] = '[ENCRYPTED]';
            $insertData['description'] = '[ENCRYPTED]';
            $insertData['is_encrypted'] = 1;
        }
        
        // Insert into database
        $columns = implode(', ', array_keys($insertData));
        $placeholders = ':' . implode(', :', array_keys($insertData));
        
        $stmt = $this->db->prepare("INSERT INTO researches ($columns) VALUES ($placeholders)");
        
        foreach ($insertData as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        
        if (!$stmt->execute()) {
            return createResponse('error', 'chat_creation_failed', [], 500, $language);
        }
        
        // Start background processing
        $this->processResearch($chatId);
        
        $this->securityManager->logSecurityEvent($userId, 'chat_created', $chatId, $language);
        
        return createResponse('success', 'chat_created', [
            'chat_id' => $chatId,
            'title' => $insertData['is_encrypted'] ? '[ENCRYPTED]' : $title,
            'models' => $selectedModels,
            'depth' => $depth,
            'language' => $language,
            'target_language' => $targetLanguage,
            'encrypted' => !empty($userKey)
        ], 201, $language);
    }
    
    /**
     * Process research with multiple AI models and consensus
     */
    private function processResearch($chatId) {
        $research = $this->getResearch($chatId);
        if (!$research) return;
        
        $models = json_decode($research['models'], true);
        $language = $research['original_language'];
        $targetLanguage = $research['target_language'];
        
        // Update status to processing
        $this->updateResearch($chatId, [
            'status' => 'processing',
            'progress' => 10
        ]);
        
        try {
            // Generate research plan
            $steps = $this->generateResearchPlan($research);
            $this->updateResearch($chatId, ['progress' => 20]);
            
            $results = [];
            $totalSteps = count($steps);
            
            foreach ($steps as $index => $step) {
                // Log research step
                $this->logResearchStep($chatId, $step, $language, 'started');
                
                try {
                    // Use consensus engine for multiple models
                    $consensusResult = $this->consensusEngine->queryMultipleModels(
                        $step['description'], 
                        $models, 
                        $language,
                        $research['user_id']
                    );
                    
                    // Translate to target language if specified
                    if ($targetLanguage && $targetLanguage !== $language) {
                        $translatedContent = $this->translateContent(
                            $consensusResult['content'], 
                            $language, 
                            $targetLanguage
                        );
                        $consensusResult['translated_content'] = $translatedContent;
                        $consensusResult['target_language'] = $targetLanguage;
                    }
                    
                    $results[] = [
                        'step' => $step,
                        'consensus' => $consensusResult,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'language' => $language,
                        'target_language' => $targetLanguage
                    ];
                    
                    $this->logResearchStep($chatId, $step, $language, 'completed', json_encode($consensusResult));
                    
                } catch (Exception $e) {
                    $results[] = [
                        'step' => $step,
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s'),
                        'language' => $language
                    ];
                    
                    $this->logResearchStep($chatId, $step, $language, 'failed', $e->getMessage());
                }
                
                // Update progress
                $progress = 20 + (($index + 1) / $totalSteps) * 60;
                $this->updateResearch($chatId, ['progress' => (int)$progress]);
            }
            
            // Generate final outputs
            $article = $this->generateAcademicArticle($research, $results);
            $notes = $this->generateResearchNotes($results, $language);
            
            // Calculate quality metrics
            $qualityScore = $this->calculateQualityScore($results, $article);
            $wordCount = str_word_count(strip_tags($article['content']));
            $charCount = mb_strlen(strip_tags($article['content']), 'UTF-8');
            $readingTime = ceil($wordCount / 200); // Average reading speed
            
            $this->updateResearch($chatId, ['progress' => 90]);
            
            // Encrypt results if user has encryption enabled
            if ($research['is_encrypted']) {
                $user = $this->getUserById($research['user_id']);
                if ($user['session_key']) {
                    $encryptedResults = EncryptionManager::encryptWithSession(
                        json_encode($article, JSON_UNESCAPED_UNICODE), 
                        $user['session_key']
                    );
                    $encryptedNotes = EncryptionManager::encryptWithSession(
                        json_encode($notes, JSON_UNESCAPED_UNICODE), 
                        $user['session_key']
                    );
                    
                    $this->updateResearch($chatId, [
                        'encrypted_results' => $encryptedResults,
                        'encrypted_notes' => $encryptedNotes,
                        'results' => '[ENCRYPTED]',
                        'notes' => '[ENCRYPTED]',
                        'status' => 'completed',
                        'progress' => 100,
                        'quality_score' => $qualityScore,
                        'word_count' => $wordCount,
                        'character_count' => $charCount,
                        'reading_time' => $readingTime,
                        'completed_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                $this->updateResearch($chatId, [
                    'results' => json_encode($article, JSON_UNESCAPED_UNICODE),
                    'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
                    'status' => 'completed',
                    'progress' => 100,
                    'quality_score' => $qualityScore,
                    'word_count' => $wordCount,
                    'character_count' => $charCount,
                    'reading_time' => $readingTime,
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Update language statistics
            $this->updateLanguageStats($language, $wordCount, $charCount);
            
            $this->securityManager->logSecurityEvent($research['user_id'], 'research_completed', $chatId, $language);
            
        } catch (Exception $e) {
            $this->updateResearch($chatId, [
                'status' => 'failed',
                'progress' => 0,
                'notes' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE)
            ]);
            
            $this->securityManager->logSecurityEvent($research['user_id'], 'research_failed', $chatId . ': ' . $e->getMessage(), $language);
        }
    }
    
    /**
     * Generate research plan steps with language awareness
     */
    private function generateResearchPlan($research) {
        $lang = $research['original_language'];
        $topic = $research['is_encrypted'] ? '[ENCRYPTED TOPIC]' : $research['topic'];
        $description = $research['is_encrypted'] ? '[ENCRYPTED DESCRIPTION]' : $research['description'];
        $depth = $research['depth'];
        
        $prompt = $this->getResearchPlanPrompt($topic, $description, $depth, $lang);
        
        try {
            $models = json_decode($research['models'], true);
            $response = $this->consensusEngine->queryMultipleModels($prompt, $models, $lang, $research['user_id']);
            
            $steps = json_decode($response['content'], true);
            
            return is_array($steps) ? $steps : $this->getDefaultSteps($research);
        } catch (Exception $e) {
            return $this->getDefaultSteps($research);
        }
    }
    
    /**
     * Get research plan prompt in appropriate language
     */
    private function getResearchPlanPrompt($topic, $description, $depth, $language) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        
        if ($language === 'en') {
            return "Create a {$depth}-step research plan about: {$topic}\nDescription: {$description}\n\nProvide as JSON array with 'step' and 'description' keys. Make it comprehensive and academic.";
        } elseif ($language === 'tr') {
            return "{$depth} aşamalı bir araştırma planı oluşturun:\nKonu: {$topic}\nAçıklama: {$description}\n\nJSON formatında 'step' ve 'description' anahtarlarıyla verin. Kapsamlı ve akademik olsun.";
        } elseif ($language === 'zh') {
            return "为以下主题创建{$depth}步研究计划：{$topic}\n描述：{$description}\n\n请以JSON数组格式提供，包含'step'和'description'键。要全面和学术化。";
        } elseif ($language === 'es') {
            return "Crea un plan de investigación de {$depth} pasos sobre: {$topic}\nDescripción: {$description}\n\nProporciona como array JSON con claves 'step' y 'description'. Hazlo comprensivo y académico.";
        } elseif ($language === 'fr') {
            return "Créez un plan de recherche en {$depth} étapes sur : {$topic}\nDescription : {$description}\n\nFournissez sous forme de tableau JSON avec les clés 'step' et 'description'. Rendez-le complet et académique.";
        } elseif ($language === 'de') {
            return "Erstellen Sie einen {$depth}-Schritt-Forschungsplan über: {$topic}\nBeschreibung: {$description}\n\nBereitstellung als JSON-Array mit 'step' und 'description' Schlüsseln. Machen Sie es umfassend und akademisch.";
        } elseif ($language === 'ru') {
            return "Создайте {$depth}-этапный план исследования по теме: {$topic}\nОписание: {$description}\n\nПредоставьте в виде JSON-массива с ключами 'step' и 'description'. Сделайте его всеобъемлющим и академическим.";
        } elseif ($language === 'ja') {
            return "{$topic}について{$depth}段階の研究計画を作成してください\n説明：{$description}\n\n'step'と'description'キーを持つJSON配列として提供してください。包括的で学術的にしてください。";
        } elseif ($language === 'ar') {
            return "أنشئ خطة بحثية من {$depth} خطوات حول: {$topic}\nالوصف: {$description}\n\nقدمها كمصفوفة JSON بمفاتيح 'step' و 'description'. اجعلها شاملة وأكاديمية.";
        } else {
            // Fallback to English for unsupported languages
            return "Create a {$depth}-step research plan about: {$topic}\nDescription: {$description}\n\nProvide as JSON array with 'step' and 'description' keys. Make it comprehensive and academic.";
        }
    }
    
    /**
     * Translate content between languages
     */
    private function translateContent($content, $sourceLanguage, $targetLanguage) {
        if ($sourceLanguage === $targetLanguage) {
            return $content;
        }
        
        $sourceLang = SUPPORTED_LANGUAGES[$sourceLanguage]['native'] ?? $sourceLanguage;
        $targetLang = SUPPORTED_LANGUAGES[$targetLanguage]['native'] ?? $targetLanguage;
        
        $prompt = "Translate the following text from {$sourceLang} to {$targetLang}. Maintain the original meaning, tone, and formatting:\n\n{$content}";
        
        try {
            // Use the best model for translation
            $bestModels = LanguageManager::getBestModelsForLanguage($targetLanguage);
            $response = $this->consensusEngine->queryMultipleModels($prompt, [$bestModels[0]], $targetLanguage, 1);
            return $response['content'];
        } catch (Exception $e) {
            return $content; // Return original if translation fails
        }
    }
    
    /**
     * Generate academic article with enhanced multi-language support
     */
    private function generateAcademicArticle($research, $results) {
        $lang = $research['original_language'];
        $targetLang = $research['target_language'];
        $topic = $research['is_encrypted'] ? '[ENCRYPTED TOPIC]' : $research['topic'];
        
        $combinedResults = '';
        foreach ($results as $result) {
            if (isset($result['consensus']['content'])) {
                $content = $result['consensus']['content'];
                // Use translated content if available
                if ($targetLang && isset($result['consensus']['translated_content'])) {
                    $content = $result['consensus']['translated_content'];
                }
                $combinedResults .= $content . "\n\n";
            }
        }
        
        $prompt = $this->getArticleGenerationPrompt($topic, $combinedResults, $targetLang ?? $lang);
        
        try {
            $models = json_decode($research['models'], true);
            $consensusResult = $this->consensusEngine->queryMultipleModels($prompt, $models, $targetLang ?? $lang, $research['user_id']);
            $content = $consensusResult['content'];
        } catch (Exception $e) {
            $content = LanguageManager::get('article_generation_failed', ['error' => $e->getMessage()], $targetLang ?? $lang);
        }
        
        return [
            'title' => $topic,
            'content' => $content,
            'language' => $targetLang ?? $lang,
            'source_language' => $lang,
            'created_at' => date('Y-m-d H:i:s'),
            'consensus_data' => $results,
            'article_type' => 'academic',
            'structure' => $this->extractArticleStructure($content, $targetLang ?? $lang)
        ];
    }
    
    /**
     * Get article generation prompt in appropriate language
     */
    private function getArticleGenerationPrompt($topic, $results, $language) {
        $structure = $this->getArticleStructure($language);
        
        if ($language === 'en') {
            return "Write a comprehensive academic article in English about: {$topic}\n\nResearch Findings:\n{$results}\n\nInclude these sections:\n{$structure}\n\nUse proper academic formatting and citations.";
        } elseif ($language === 'tr') {
            return "Aşağıdaki konu hakkında Türkçe kapsamlı bir akademik makale yazın: {$topic}\n\nAraştırma Bulguları:\n{$results}\n\nŞu bölümleri içersin:\n{$structure}\n\nUygun akademik formatlamayı ve alıntıları kullanın.";
        } elseif ($language === 'zh') {
            return "撰写一篇关于{$topic}的综合性中文学术文章\n\n研究发现：\n{$results}\n\n包括以下部分：\n{$structure}\n\n使用适当的学术格式和引用。";
        } elseif ($language === 'es') {
            return "Escriba un artículo académico integral en español sobre: {$topic}\n\nHallazgos de investigación:\n{$results}\n\nIncluya estas secciones:\n{$structure}\n\nUse formato académico apropiado y citas.";
        } elseif ($language === 'fr') {
            return "Rédigez un article académique complet en français sur : {$topic}\n\nRésultats de recherche :\n{$results}\n\nIncluez ces sections :\n{$structure}\n\nUtilisez un formatage académique approprié et des citations.";
        } elseif ($language === 'de') {
            return "Schreiben Sie einen umfassenden akademischen Artikel auf Deutsch über: {$topic}\n\nForschungsergebnisse:\n{$results}\n\nFügen Sie diese Abschnitte hinzu:\n{$structure}\n\nVerwenden Sie angemessene akademische Formatierung und Zitate.";
        } elseif ($language === 'ru') {
            return "Напишите всеобъемлющую академическую статью на русском языке о: {$topic}\n\nРезультаты исследования:\n{$results}\n\nВключите эти разделы:\n{$structure}\n\nИспользуйте соответствующее академическое форматирование и цитаты.";
        } elseif ($language === 'ja') {
            return "{$topic}について包括的な学術論文を日本語で書いてください\n\n研究結果：\n{$results}\n\n以下のセクションを含めてください：\n{$structure}\n\n適切な学術的フォーマットと引用を使用してください。";
        } elseif ($language === 'ar') {
            return "اكتب مقالاً أكاديمياً شاملاً باللغة العربية حول: {$topic}\n\nنتائج البحث:\n{$results}\n\nاشمل هذه الأقسام:\n{$structure}\n\nاستخدم التنسيق الأكاديمي المناسب والاستشهادات.";
        } else {
            // Fallback to English
            return "Write a comprehensive academic article in English about: {$topic}\n\nResearch Findings:\n{$results}\n\nInclude these sections:\n{$structure}\n\nUse proper academic formatting and citations.";
        }
    }
    
    /**
     * Get article structure in appropriate language
     */
    private function getArticleStructure($language) {
        $structures = [
            'en' => "1. Title\n2. Abstract\n3. Introduction\n4. Literature Review\n5. Methodology\n6. Results\n7. Discussion\n8. Conclusion\n9. References",
            'tr' => "1. Başlık\n2. Özet\n3. Giriş\n4. Literatür Taraması\n5. Yöntem\n6. Bulgular\n7. Tartışma\n8. Sonuç\n9. Kaynakça",
            'zh' => "1. 标题\n2. 摘要\n3. 引言\n4. 文献综述\n5. 方法\n6. 结果\n7. 讨论\n8. 结论\n9. 参考文献",
            'es' => "1. Título\n2. Resumen\n3. Introducción\n4. Revisión de Literatura\n5. Metodología\n6. Resultados\n7. Discusión\n8. Conclusión\n9. Referencias",
            'fr' => "1. Titre\n2. Résumé\n3. Introduction\n4. Revue de littérature\n5. Méthodologie\n6. Résultats\n7. Discussion\n8. Conclusion\n9. Références",
            'de' => "1. Titel\n2. Zusammenfassung\n3. Einleitung\n4. Literaturübersicht\n5. Methodik\n6. Ergebnisse\n7. Diskussion\n8. Fazit\n9. Literaturverzeichnis",
            'ru' => "1. Заголовок\n2. Аннотация\n3. Введение\n4. Обзор литературы\n5. Методология\n6. Результаты\n7. Обсуждение\n8. Заключение\n9. Список литературы",
            'ja' => "1. タイトル\n2. 概要\n3. 序論\n4. 文献レビュー\n5. 方法論\n6. 結果\n7. 考察\n8. 結論\n9. 参考文献",
            'ar' => "1. العنوان\n2. الملخص\n3. المقدمة\n4. مراجعة الأدبيات\n5. المنهجية\n6. النتائج\n7. المناقشة\n8. الخاتمة\n9. المراجع"
        ];
        
        return $structures[$language] ?? $structures['en'];
    }
    
    /**
     * Extract article structure from generated content
     */
    private function extractArticleStructure($content, $language) {
        $sections = [];
        
        // Define section patterns for different languages
        $patterns = [
            'en' => ['/^#?\s*(Abstract|Introduction|Methodology|Results|Discussion|Conclusion|References)/mi'],
            'tr' => ['/^#?\s*(Özet|Giriş|Yöntem|Bulgular|Tartışma|Sonuç|Kaynakça)/mi'],
            'zh' => ['/^#?\s*(摘要|引言|方法|结果|讨论|结论|参考文献)/mi'],
            'es' => ['/^#?\s*(Resumen|Introducción|Metodología|Resultados|Discusión|Conclusión|Referencias)/mi'],
            'fr' => ['/^#?\s*(Résumé|Introduction|Méthodologie|Résultats|Discussion|Conclusion|Références)/mi'],
            'de' => ['/^#?\s*(Zusammenfassung|Einleitung|Methodik|Ergebnisse|Diskussion|Fazit|Literaturverzeichnis)/mi'],
            'ru' => ['/^#?\s*(Аннотация|Введение|Методология|Результаты|Обсуждение|Заключение|Список литературы)/mi'],
            'ja' => ['/^#?\s*(概要|序論|方法論|結果|考察|結論|参考文献)/mi'],
            'ar' => ['/^#?\s*(الملخص|المقدمة|المنهجية|النتائج|المناقشة|الخاتمة|المراجع)/mi']
        ];
        
        $langPatterns = $patterns[$language] ?? $patterns['en'];
        
        foreach ($langPatterns as $pattern) {
            preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $match) {
                $sections[] = [
                    'title' => trim($match[0]),
                    'position' => $match[1]
                ];
            }
        }
        
        return $sections;
    }
    
    /**
     * Generate research notes with consensus and multi-language support
     */
    private function generateResearchNotes($results, $language) {
        $notes = [];
        
        foreach ($results as $result) {
            if (isset($result['consensus'])) {
                $notes[] = [
                    'step' => $result['step']['step'] ?? 'Unknown',
                    'title' => $result['step']['description'] ?? '',
                    'content' => $result['consensus']['content'] ?? '',
                    'translated_content' => $result['consensus']['translated_content'] ?? null,
                    'consensus_score' => $result['consensus']['consensus_score'] ?? 0,
                    'models_used' => $result['consensus']['all_scores'] ?? [],
                    'method' => $result['consensus']['method'] ?? 'single',
                    'language' => $result['language'] ?? $language,
                    'target_language' => $result['target_language'] ?? null,
                    'timestamp' => $result['timestamp']
                ];
            } else {
                $notes[] = [
                    'step' => $result['step']['step'] ?? 'Unknown',
                    'title' => $result['step']['description'] ?? '',
                    'content' => '',
                    'error' => $result['error'] ?? 'Unknown error',
                    'language' => $result['language'] ?? $language,
                    'timestamp' => $result['timestamp']
                ];
            }
        }
        
        $combined = implode("\n\n", array_column($notes, 'content'));
        
        return [
            'notes' => $notes,
            'combined' => $combined,
            'language' => $language,
            'generated_at' => date('Y-m-d H:i:s'),
            'total_steps' => count($notes),
            'successful_steps' => count(array_filter($notes, function($note) { return !isset($note['error']); }))
        ];
    }
    
    /**
     * Export research to specified format with multi-language support
     */
    public function exportResearch($chatId, $userId, $format, $exportType = 'article', $language = null) {
        // Validate format
        if (!in_array($format, ['pdf', 'docx', 'txt', 'html', 'md'])) {
            return createResponse('error', 'unsupported_format', [], 400, $language);
        }
        
        $research = $this->getResearch($chatId);
        if (!$research || $research['user_id'] != $userId) {
            return createResponse('error', 'chat_not_found', [], 404, $language);
        }
        
        // Use research language if not specified
        if (!$language) {
            $language = $research['target_language'] ?? $research['original_language'];
        }
        
        // Check rate limits
        if (!$this->checkRateLimit($userId)) {
            return createResponse('error', 'rate_limit_exceeded', [], 429, $language);
        }
        
        // Get appropriate content
        $content = '';
        $title = $research['is_encrypted'] ? '[ENCRYPTED]' : $research['title'];
        
        if ($exportType === 'article') {
            if ($research['is_encrypted'] && $research['encrypted_results']) {
                $user = $this->getUserById($userId);
                if ($user['session_key']) {
                    $decryptedResults = EncryptionManager::decryptWithSession(
                        $research['encrypted_results'], 
                        $user['session_key']
                    );
                    $articleData = json_decode($decryptedResults, true);
                    $content = $articleData['content'] ?? '[DECRYPTION FAILED]';
                    $title = $articleData['title'] ?? $title;
                }
            } else {
                $articleData = json_decode($research['results'], true);
                $content = $articleData['content'] ?? '';
                $title = $articleData['title'] ?? $title;
            }
        } else {
            if ($research['is_encrypted'] && $research['encrypted_notes']) {
                $user = $this->getUserById($userId);
                if ($user['session_key']) {
                    $decryptedNotes = EncryptionManager::decryptWithSession(
                        $research['encrypted_notes'], 
                        $user['session_key']
                    );
                    $notesData = json_decode($decryptedNotes, true);
                    $content = $notesData['combined'] ?? '[DECRYPTION FAILED]';
                }
            } else {
                $notesData = json_decode($research['notes'], true);
                $content = $notesData['combined'] ?? '';
            }
        }
        
        if (empty($content)) {
            return createResponse('error', 'no_content_to_export', [], 404, $language);
        }
        
        // Generate language-aware filename
        $filename = generateLanguageAwareFilename($title, $language, $format);
        $filepath = EXPORTS_DIR . "/" . $filename;
        
        // Generate file based on format
        try {
            switch ($format) {
                case 'pdf':
                    $this->generateProfessionalPDF($content, $filepath, $exportType, $language, $title);
                    break;
                case 'docx':
                    $this->generateProfessionalDOCX($content, $filepath, $exportType, $language, $title);
                    break;
                case 'html':
                    $this->generateHTML($content, $filepath, $exportType, $language, $title);
                    break;
                case 'md':
                    $this->generateMarkdown($content, $filepath, $exportType, $language, $title);
                    break;
                case 'txt':
                default:
                    $this->generateTextFile($content, $filepath, $exportType, $language, $title);
                    break;
            }
        } catch (Exception $e) {
            return createResponse('error', 'export_failed', ['error' => $e->getMessage()], 500, $language);
        }
        
        // Get MIME type
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'html' => 'text/html',
            'md' => 'text/markdown',
            'txt' => 'text/plain'
        ];
        
        // Save export record
        $stmt = $this->db->prepare('INSERT INTO exports 
            (chat_id, user_id, format, export_type, language, file_path, original_filename, file_size, mime_type) 
            VALUES (:chat_id, :user_id, :format, :export_type, :language, :file_path, :filename, :file_size, :mime_type)');
        
        $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':format', $format, SQLITE3_TEXT);
        $stmt->bindValue(':export_type', $exportType, SQLITE3_TEXT);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        $stmt->bindValue(':file_path', $filepath, SQLITE3_TEXT);
        $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
        $stmt->bindValue(':file_size', filesize($filepath), SQLITE3_INTEGER);
        $stmt->bindValue(':mime_type', $mimeTypes[$format] ?? 'application/octet-stream', SQLITE3_TEXT);
        $stmt->execute();
        
        $this->securityManager->logSecurityEvent($userId, 'export_created', "$chatId:$format:$exportType", $language);
        
        return createResponse('success', 'export_successful', [
            'download_url' => "/api/exports/download/$filename",
            'file_path' => $filepath,
            'filename' => $filename,
            'file_size' => formatFileSize(filesize($filepath)),
            'format' => $format,
            'language' => $language
        ], 200, $language);
    }
    
    /**
     * Generate professional PDF with language support
     */
    private function generateProfessionalPDF($content, $filepath, $type, $language, $title) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        $direction = $langInfo['rtl'] ? 'rtl' : 'ltr';
        
        $typeLabel = LanguageManager::get($type === 'article' ? 'export_article' : 'export_notes', [], $language);
        
        $html = "<!DOCTYPE html>\n<html lang=\"$language\" dir=\"$direction\">\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<title>$title - $typeLabel</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: 'Arial', sans-serif; line-height: 1.6; margin: 40px; direction: $direction; }\n";
        $html .= "h1 { text-align: center; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }\n";
        $html .= "h2, h3 { color: #007bff; margin-top: 30px; }\n";
        $html .= "p { margin-bottom: 15px; text-align: justify; }\n";
        $html .= ".header { text-align: center; margin-bottom: 40px; }\n";
        $html .= ".meta { font-size: 12px; color: #666; margin-bottom: 20px; }\n";
        $html .= ".footer { margin-top: 40px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }\n";
        
        // Language-specific font optimizations
        if (in_array($language, ['ar', 'fa', 'ur'])) {
            $html .= "body { font-family: 'Amiri', 'Traditional Arabic', serif; }\n";
        } elseif (in_array($language, ['zh', 'zh-cn', 'zh-tw'])) {
            $html .= "body { font-family: 'Microsoft YaHei', 'PingFang SC', 'Hiragino Sans GB', sans-serif; }\n";
        } elseif ($language === 'ja') {
            $html .= "body { font-family: 'Hiragino Kaku Gothic Pro', 'Yu Gothic', 'Meiryo', sans-serif; }\n";
        } elseif ($language === 'ko') {
            $html .= "body { font-family: 'Malgun Gothic', 'Apple SD Gothic Neo', sans-serif; }\n";
        } elseif (in_array($language, ['hi', 'bn', 'gu', 'pa', 'ta', 'te', 'kn', 'ml', 'mr', 'ne'])) {
            $html .= "body { font-family: 'Noto Sans Devanagari', 'Mangal', sans-serif; }\n";
        } elseif ($language === 'th') {
            $html .= "body { font-family: 'Sarabun', 'Tahoma', sans-serif; }\n";
        } elseif ($language === 'ru') {
            $html .= "body { font-family: 'PT Sans', 'Arial', sans-serif; }\n";
        }
        
        $html .= "</style>\n</head>\n<body>\n";
        
        $html .= "<div class=\"header\">\n";
        $html .= "<h1>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</h1>\n";
        $html .= "<div class=\"meta\">\n";
        $html .= "<strong>" . LanguageManager::get('export_type', [], $language) . ":</strong> $typeLabel<br>\n";
        $html .= "<strong>" . LanguageManager::get('language', [], $language) . ":</strong> " . $langInfo['native'] . "<br>\n";
        $html .= "<strong>" . LanguageManager::get('created_at', [], $language) . ":</strong> " . date('Y-m-d H:i:s') . "\n";
        $html .= "</div>\n</div>\n";
        
        $html .= "<div class=\"content\">\n";
        $html .= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        $html .= "\n</div>\n";
        
        $html .= "<div class=\"footer\">\n";
        $html .= LanguageManager::get('generated_by', [], $language) . " Ounachu Search Engine<br>\n";
        $html .= LanguageManager::get('created_at', [], $language) . ": " . date('Y-m-d H:i:s') . "<br>\n";
        $html .= "© 2024 Crimson Glitch - Apache License 2.0\n";
        $html .= "</div>\n</body>\n</html>";
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Generate professional DOCX with language support
     */
    private function generateProfessionalDOCX($content, $filepath, $type, $language, $title) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        $typeLabel = LanguageManager::get($type === 'article' ? 'export_article' : 'export_notes', [], $language);
        
        $header = "=== $title ===\n";
        $header .= LanguageManager::get('export_type', [], $language) . ": $typeLabel\n";
        $header .= LanguageManager::get('language', [], $language) . ": " . $langInfo['native'] . "\n";
        $header .= LanguageManager::get('created_at', [], $language) . ": " . date('Y-m-d H:i:s') . "\n\n";
        $header .= str_repeat("=", 80) . "\n\n";
        
        $footer = "\n\n" . str_repeat("=", 80) . "\n";
        $footer .= LanguageManager::get('generated_by', [], $language) . " Ounachu Search Engine\n";
        $footer .= LanguageManager::get('created_at', [], $language) . ": " . date('Y-m-d H:i:s') . "\n";
        $footer .= "© 2024 Crimson Glitch - Apache License 2.0";
        
        $fullContent = $header . $content . $footer;
        
        // Ensure proper UTF-8 encoding
        $fullContent = mb_convert_encoding($fullContent, 'UTF-8', 'auto');
        
        file_put_contents($filepath, $fullContent);
    }
    
    /**
     * Generate HTML with language support
     */
    private function generateHTML($content, $filepath, $type, $language, $title) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        $direction = $langInfo['rtl'] ? 'rtl' : 'ltr';
        $typeLabel = LanguageManager::get($type === 'article' ? 'export_article' : 'export_notes', [], $language);
        
        $html = "<!DOCTYPE html>\n<html lang=\"$language\" dir=\"$direction\">\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "<title>$title - $typeLabel</title>\n";
        $html .= "<style>\n";
        $html .= "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; margin: 0; padding: 40px; background: #f8f9fa; direction: $direction; }\n";
        $html .= ".container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }\n";
        $html .= "h1 { color: #2c3e50; text-align: center; border-bottom: 3px solid #3498db; padding-bottom: 15px; }\n";
        $html .= "h2, h3 { color: #3498db; }\n";
        $html .= ".meta { background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0; font-size: 14px; }\n";
        $html .= ".content { margin: 30px 0; text-align: justify; }\n";
        $html .= ".footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #bdc3c7; color: #7f8c8d; font-size: 12px; }\n";
        $html .= "</style>\n</head>\n<body>\n";
        
        $html .= "<div class=\"container\">\n";
        $html .= "<h1>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</h1>\n";
        
        $html .= "<div class=\"meta\">\n";
        $html .= "<strong>" . LanguageManager::get('export_type', [], $language) . ":</strong> $typeLabel<br>\n";
        $html .= "<strong>" . LanguageManager::get('language', [], $language) . ":</strong> " . $langInfo['native'] . "<br>\n";
        $html .= "<strong>" . LanguageManager::get('created_at', [], $language) . ":</strong> " . date('Y-m-d H:i:s') . "\n";
        $html .= "</div>\n";
        
        $html .= "<div class=\"content\">\n";
        $html .= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        $html .= "</div>\n";
        
        $html .= "<div class=\"footer\">\n";
        $html .= LanguageManager::get('generated_by', [], $language) . " <strong>Ounachu Search Engine</strong><br>\n";
        $html .= "© 2024 Crimson Glitch - Apache License 2.0\n";
        $html .= "</div>\n";
        
        $html .= "</div>\n</body>\n</html>";
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Generate Markdown with language support
     */
    private function generateMarkdown($content, $filepath, $type, $language, $title) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        $typeLabel = LanguageManager::get($type === 'article' ? 'export_article' : 'export_notes', [], $language);
        
        $markdown = "# $title\n\n";
        $markdown .= "> **" . LanguageManager::get('export_type', [], $language) . ":** $typeLabel  \n";
        $markdown .= "> **" . LanguageManager::get('language', [], $language) . ":** " . $langInfo['native'] . "  \n";
        $markdown .= "> **" . LanguageManager::get('created_at', [], $language) . ":** " . date('Y-m-d H:i:s') . "  \n\n";
        
        $markdown .= "---\n\n";
        $markdown .= $content . "\n\n";
        $markdown .= "---\n\n";
        $markdown .= "*" . LanguageManager::get('generated_by', [], $language) . " **Ounachu Search Engine***  \n";
        $markdown .= "*© 2024 Crimson Glitch - Apache License 2.0*\n";
        
        file_put_contents($filepath, $markdown);
    }
    
    /**
     * Generate text file with language support
     */
    private function generateTextFile($content, $filepath, $type, $language, $title) {
        $langInfo = SUPPORTED_LANGUAGES[$language];
        $typeLabel = LanguageManager::get($type === 'article' ? 'export_article' : 'export_notes', [], $language);
        
        $text = str_repeat("=", 80) . "\n";
        $text .= strtoupper($title) . "\n";
        $text .= str_repeat("=", 80) . "\n\n";
        
        $text .= LanguageManager::get('export_type', [], $language) . ": $typeLabel\n";
        $text .= LanguageManager::get('language', [], $language) . ": " . $langInfo['native'] . "\n";
        $text .= LanguageManager::get('created_at', [], $language) . ": " . date('Y-m-d H:i:s') . "\n\n";
        
        $text .= str_repeat("-", 80) . "\n\n";
        $text .= $content . "\n\n";
        $text .= str_repeat("-", 80) . "\n";
        $text .= LanguageManager::get('generated_by', [], $language) . " Ounachu Search Engine\n";
        $text .= "© 2024 Crimson Glitch - Apache License 2.0\n";
        
        // Ensure proper UTF-8 encoding
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        file_put_contents($filepath, $text);
    }
    
    /**
     * Get research data with decryption support and language awareness
     */
    public function getResearch($chatId, $userKey = null) {
        if (isset($this->cache['researches'][$chatId])) {
            $research = $this->cache['researches'][$chatId];
        } else {
            $stmt = $this->db->prepare('SELECT * FROM researches WHERE chat_id = :chat_id');
            $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
            $result = $stmt->execute();
            $research = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($research) {
                $this->cache['researches'][$chatId] = $research;
            }
        }
        
        if (!$research) return null;
        
        // Set language context
        LanguageManager::setLanguage($research['original_language']);
        
        // Decrypt sensitive fields if encrypted and key provided
        if ($research['is_encrypted'] && $userKey) {
            if ($research['encrypted_title']) {
                $decryptedTitle = EncryptionManager::decryptUserData($research['encrypted_title'], $userKey);
                if ($decryptedTitle) {
                    $research['title'] = $decryptedTitle;
                }
            }
            
            if ($research['encrypted_topic']) {
                $decryptedTopic = EncryptionManager::decryptUserData($research['encrypted_topic'], $userKey);
                if ($decryptedTopic) {
                    $research['topic'] = $decryptedTopic;
                }
            }
            
            if ($research['encrypted_description']) {
                $decryptedDescription = EncryptionManager::decryptUserData($research['encrypted_description'], $userKey);
                if ($decryptedDescription) {
                    $research['description'] = $decryptedDescription;
                }
            }
        }
        
        return $research;
    }
    
    /**
     * Update research data with language context
     */
    private function updateResearch($chatId, $data) {
        $updates = [];
        $values = [':chat_id' => $chatId];
        
        foreach ($data as $key => $value) {
            $updates[] = "$key = :$key";
            $values[":$key"] = $value;
        }
        
        $updates[] = "updated_at = datetime('now')";
        
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
     * Update user session key
     */
    private function updateUserSessionKey($userId, $encryptedSessionKey) {
        $stmt = $this->db->prepare('UPDATE users SET session_key = :session_key, 
                                  last_key_rotation = datetime("now") WHERE id = :user_id');
        $stmt->bindValue(':session_key', $encryptedSessionKey, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    /**
     * Update user model weights based on feedback with language context
     */
    public function updateModelWeights($userId, $language, $category, $modelRatings) {
        foreach ($modelRatings as $model => $rating) {
            $stmt = $this->db->prepare('INSERT OR REPLACE INTO model_weights 
                (user_id, language, topic_category, model_name, accuracy_weight, relevance_weight, speed_weight, updated_at) 
                VALUES (:user_id, :language, :category, :model, :accuracy, :relevance, :speed, datetime("now"))');
            
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':language', $language, SQLITE3_TEXT);
            $stmt->bindValue(':category', $category, SQLITE3_TEXT);
            $stmt->bindValue(':model', $model, SQLITE3_TEXT);
            $stmt->bindValue(':accuracy', $rating['accuracy'] ?? 1.0, SQLITE3_FLOAT);
            $stmt->bindValue(':relevance', $rating['relevance'] ?? 1.0, SQLITE3_FLOAT);
            $stmt->bindValue(':speed', $rating['speed'] ?? 1.0, SQLITE3_FLOAT);
            $stmt->execute();
        }
        
        $this->securityManager->logSecurityEvent($userId, 'model_weights_updated', $category, $language);
    }
    
    /**
     * Get user's preferred models for language
     */
    private function getUserPreferredModels($userId, $language) {
        $stmt = $this->db->prepare('SELECT preferred_models FROM users WHERE id = :user_id');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && $user['preferred_models']) {
            $preferred = json_decode($user['preferred_models'], true);
            if (isset($preferred[$language])) {
                return $preferred[$language];
            }
        }
        
        // Return language-optimized models
        return LanguageManager::getBestModelsForLanguage($language);
    }
    
    /**
     * Categorize research topic with language awareness
     */
    private function categorizeResearchTopic($topic, $language) {
        $topicLower = mb_strtolower($topic);
        
        // Define categories in multiple languages
        $categories = [
            'technology' => ['technology', 'tech', 'computer', 'software', 'teknoloji', 'bilgisayar', 'yazılım', '技术', '科技', 'tecnología', 'technologie', 'технология'],
            'science' => ['science', 'research', 'study', 'bilim', 'araştırma', 'çalışma', '科学', '研究', 'ciencia', 'science', 'наука'],
            'health' => ['health', 'medical', 'medicine', 'sağlık', 'tıp', 'tıbbi', '健康', '医学', 'salud', 'médico', 'здоровье'],
            'business' => ['business', 'economy', 'finance', 'iş', 'ekonomi', 'finans', '商业', '经济', 'negocio', 'économie', 'бизнес'],
            'education' => ['education', 'learning', 'school', 'eğitim', 'öğrenme', 'okul', '教育', '学习', 'educación', 'éducation', 'образование'],
            'culture' => ['culture', 'art', 'history', 'kültür', 'sanat', 'tarih', '文化', '艺术', 'cultura', 'culture', 'культура'],
            'environment' => ['environment', 'climate', 'nature', 'çevre', 'iklim', 'doğa', '环境', '气候', 'medio ambiente', 'environnement', 'окружающая среда'],
            'politics' => ['politics', 'government', 'policy', 'politika', 'hükümet', 'politika', '政治', '政府', 'política', 'politique', 'политика'],
            'sports' => ['sports', 'game', 'athletics', 'spor', 'oyun', 'atletizm', '体育', '运动', 'deportes', 'sport', 'спорт']
        ];
        
        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($topicLower, $keyword) !== false) {
                    return $category;
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Calculate quality score for research
     */
    private function calculateQualityScore($results, $article) {
        $score = 0;
        $maxScore = 100;
        
        // Check completion rate
        $successfulSteps = 0;
        foreach ($results as $result) {
            if (isset($result['consensus']) && !isset($result['error'])) {
                $successfulSteps++;
            }
        }
        $completionRate = $successfulSteps / max(1, count($results));
        $score += $completionRate * 30;
        
        // Check content length
        $contentLength = mb_strlen($article['content'], 'UTF-8');
        if ($contentLength > 5000) {
            $score += 25;
        } elseif ($contentLength > 2000) {
            $score += 20;
        } elseif ($contentLength > 1000) {
            $score += 15;
        } else {
            $score += 10;
        }
        
        // Check consensus scores
        $avgConsensusScore = 0;
        $consensusCount = 0;
        foreach ($results as $result) {
            if (isset($result['consensus']['consensus_score'])) {
                $avgConsensusScore += $result['consensus']['consensus_score'];
                $consensusCount++;
            }
        }
        if ($consensusCount > 0) {
            $avgConsensusScore /= $consensusCount;
            $score += $avgConsensusScore * 25;
        }
        
        // Check structure quality
        if (isset($article['structure']) && count($article['structure']) >= 5) {
            $score += 20;
        } elseif (isset($article['structure']) && count($article['structure']) >= 3) {
            $score += 15;
        } else {
            $score += 5;
        }
        
        return min($maxScore, $score);
    }
    
    /**
     * Update language statistics
     */
    private function updateLanguageStats($language, $wordCount, $charCount) {
        $today = date('Y-m-d');
        
        $stmt = $this->db->prepare('INSERT OR REPLACE INTO language_stats 
            (date, language, total_requests, total_words, total_characters) 
            VALUES (:date, :language, 
                COALESCE((SELECT total_requests FROM language_stats WHERE date = :date AND language = :language), 0) + 1,
                COALESCE((SELECT total_words FROM language_stats WHERE date = :date AND language = :language), 0) + :words,
                COALESCE((SELECT total_characters FROM language_stats WHERE date = :date AND language = :language), 0) + :chars
            )');
        
        $stmt->bindValue(':date', $today, SQLITE3_TEXT);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        $stmt->bindValue(':words', $wordCount, SQLITE3_INTEGER);
        $stmt->bindValue(':chars', $charCount, SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    /**
     * Log research step
     */
    private function logResearchStep($chatId, $step, $language, $status, $response = null) {
        $stmt = $this->db->prepare('INSERT INTO research_steps 
            (chat_id, step_number, step_title, step_description, language, status, ai_response) 
            VALUES (:chat_id, :step_num, :step_title, :step_desc, :language, :status, :response)');
        
        $stmt->bindValue(':chat_id', $chatId, SQLITE3_TEXT);
        $stmt->bindValue(':step_num', $step['step'], SQLITE3_INTEGER);
        $stmt->bindValue(':step_title', $step['title'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':step_desc', $step['description'], SQLITE3_TEXT);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':response', $response, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    /**
     * Get default research steps with language support
     */
    private function getDefaultSteps($research) {
        $lang = $research['original_language'];
        $topic = $research['is_encrypted'] ? '[ENCRYPTED TOPIC]' : $research['topic'];
        
        $steps = [
            'en' => [
                ['step' => 1, 'title' => 'Initial Research', 'description' => "Gather general information about $topic"],
                ['step' => 2, 'title' => 'Key Concepts', 'description' => "Research key concepts related to $topic"],
                ['step' => 3, 'title' => 'Statistical Analysis', 'description' => "Find important statistics about $topic"],
                ['step' => 4, 'title' => 'Current Developments', 'description' => "Examine recent developments about $topic"],
                ['step' => 5, 'title' => 'Conclusion Report', 'description' => "Prepare a conclusion report about $topic"]
            ],
            'tr' => [
                ['step' => 1, 'title' => 'İlk Araştırma', 'description' => "$topic hakkında genel bilgi topla"],
                ['step' => 2, 'title' => 'Temel Kavramlar', 'description' => "$topic ile ilgili temel kavramları araştır"],
                ['step' => 3, 'title' => 'İstatistiksel Analiz', 'description' => "$topic için önemli istatistikleri bul"],
                ['step' => 4, 'title' => 'Güncel Gelişmeler', 'description' => "$topic ile ilgili güncel gelişmeleri incele"],
                ['step' => 5, 'title' => 'Sonuç Raporu', 'description' => "$topic hakkında bir sonuç raporu hazırla"]
            ],
            'zh' => [
                ['step' => 1, 'title' => '初步研究', 'description' => "收集关于$topic的一般信息"],
                ['step' => 2, 'title' => '关键概念', 'description' => "研究与$topic相关的关键概念"],
                ['step' => 3, 'title' => '统计分析', 'description' => "查找关于$topic的重要统计数据"],
                ['step' => 4, 'title' => '最新发展', 'description' => "检查关于$topic的最新发展"],
                ['step' => 5, 'title' => '结论报告', 'description' => "准备关于$topic的结论报告"]
            ],
            'es' => [
                ['step' => 1, 'title' => 'Investigación Inicial', 'description' => "Reunir información general sobre $topic"],
                ['step' => 2, 'title' => 'Conceptos Clave', 'description' => "Investigar conceptos clave relacionados con $topic"],
                ['step' => 3, 'title' => 'Análisis Estadístico', 'description' => "Encontrar estadísticas importantes sobre $topic"],
                ['step' => 4, 'title' => 'Desarrollos Actuales', 'description' => "Examinar desarrollos recientes sobre $topic"],
                ['step' => 5, 'title' => 'Informe de Conclusión', 'description' => "Preparar un informe de conclusión sobre $topic"]
            ],
            'fr' => [
                ['step' => 1, 'title' => 'Recherche Initiale', 'description' => "Rassembler des informations générales sur $topic"],
                ['step' => 2, 'title' => 'Concepts Clés', 'description' => "Rechercher les concepts clés liés à $topic"],
                ['step' => 3, 'title' => 'Analyse Statistique', 'description' => "Trouver des statistiques importantes sur $topic"],
                ['step' => 4, 'title' => 'Développements Actuels', 'description' => "Examiner les développements récents sur $topic"],
                ['step' => 5, 'title' => 'Rapport de Conclusion', 'description' => "Préparer un rapport de conclusion sur $topic"]
            ],
            'de' => [
                ['step' => 1, 'title' => 'Erste Forschung', 'description' => "Allgemeine Informationen über $topic sammeln"],
                ['step' => 2, 'title' => 'Schlüsselkonzepte', 'description' => "Schlüsselkonzepte im Zusammenhang mit $topic erforschen"],
                ['step' => 3, 'title' => 'Statistische Analyse', 'description' => "Wichtige Statistiken über $topic finden"],
                ['step' => 4, 'title' => 'Aktuelle Entwicklungen', 'description' => "Neueste Entwicklungen zu $topic untersuchen"],
                ['step' => 5, 'title' => 'Abschlussbericht', 'description' => "Einen Abschlussbericht über $topic erstellen"]
            ],
            'ru' => [
                ['step' => 1, 'title' => 'Первоначальное исследование', 'description' => "Собрать общую информацию о $topic"],
                ['step' => 2, 'title' => 'Ключевые концепции', 'description' => "Исследовать ключевые концепции, связанные с $topic"],
                ['step' => 3, 'title' => 'Статистический анализ', 'description' => "Найти важную статистику о $topic"],
                ['step' => 4, 'title' => 'Текущие разработки', 'description' => "Изучить последние разработки по $topic"],
                ['step' => 5, 'title' => 'Заключительный отчет', 'description' => "Подготовить заключительный отчет о $topic"]
            ],
            'ja' => [
                ['step' => 1, 'title' => '初期研究', 'description' => "{$topic}に関する一般的な情報を収集する"],
                ['step' => 2, 'title' => '重要概念', 'description' => "{$topic}に関連する重要な概念を研究する"],
                ['step' => 3, 'title' => '統計分析', 'description' => "{$topic}に関する重要な統計を見つける"],
                ['step' => 4, 'title' => '現在の発展', 'description' => "{$topic}に関する最近の発展を調査する"],
                ['step' => 5, 'title' => '結論レポート', 'description' => "{$topic}について結論レポートを準備する"]
            ],
            'ar' => [
                ['step' => 1, 'title' => 'البحث الأولي', 'description' => "جمع معلومات عامة حول $topic"],
                ['step' => 2, 'title' => 'المفاهيم الأساسية', 'description' => "بحث المفاهيم الأساسية المتعلقة بـ $topic"],
                ['step' => 3, 'title' => 'التحليل الإحصائي', 'description' => "العثور على إحصائيات مهمة حول $topic"],
                ['step' => 4, 'title' => 'التطورات الحالية', 'description' => "فحص التطورات الأخيرة حول $topic"],
                ['step' => 5, 'title' => 'تقرير الخاتمة', 'description' => "إعداد تقرير خاتمة حول $topic"]
            ],
            'ko' => [
                ['step' => 1, 'title' => '초기 연구', 'description' => "{$topic}에 대한 일반적인 정보 수집"],
                ['step' => 2, 'title' => '핵심 개념', 'description' => "{$topic}와 관련된 핵심 개념 연구"],
                ['step' => 3, 'title' => '통계 분석', 'description' => "{$topic}에 대한 중요한 통계 찾기"],
                ['step' => 4, 'title' => '현재 발전', 'description' => "{$topic}에 대한 최근 발전 사항 조사"],
                ['step' => 5, 'title' => '결론 보고서', 'description' => "{$topic}에 대한 결론 보고서 준비"]
            ]
        ];
        
        return $steps[$lang] ?? $steps['en'];
    }
    
    /**
     * Log API request with enhanced multi-language tracking
     */
    public function logApiRequest($endpoint, $method, $userId, $statusCode, $responseTime, $language = 'en') {
        $stmt = $this->db->prepare('INSERT INTO api_logs 
            (endpoint, method, user_id, language, user_agent, ip_address, status_code, response_time) 
            VALUES (:endpoint, :method, :user_id, :language, :user_agent, :ip_address, :status_code, :response_time)');
        
        $stmt->bindValue(':endpoint', $endpoint, SQLITE3_TEXT);
        $stmt->bindValue(':method', $method, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':language', $language, SQLITE3_TEXT);
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown', SQLITE3_TEXT);
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'unknown', SQLITE3_TEXT);
        $stmt->bindValue(':status_code', $statusCode, SQLITE3_INTEGER);
        $stmt->bindValue(':response_time', $responseTime, SQLITE3_FLOAT);
        
        $stmt->execute();
    }
}

// ==============================================
// API Router & Request Handler with Multi-Language Support
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
        
        // Detect and set language
        $language = detectUserLanguage($data);
        LanguageManager::init($language);
        
        // Authentication
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $data['api_key'] ?? null;
        $user = $this->engine->authenticateUser($apiKey);
        
        // Check rate limit for authenticated users
        if ($user && !$this->engine->securityManager->checkRateLimit($user['id'], $path)) {
            $this->engine->securityManager->logSecurityEvent($user['id'], 'rate_limit_exceeded', $path, $language);
            $response = createResponse('error', 'rate_limit_exceeded', [], 429, $language);
        } else {
            try {
                switch (true) {
                    case $method === 'POST' && $path === '/api/register':
                        $response = $this->handleRegister($data, $language);
                        break;
                        
                    case $method === 'POST' && $path === '/api/login':
                        $response = $this->handleSecureLogin($data, $language);
                        break;
                        
                    case $method === 'POST' && $path === '/api/chats':
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleCreateSecureChat($user, $data, $language);
                        }
                        break;
                        
                    case $method === 'GET' && preg_match('/\/api\/chats\/([a-zA-Z0-9_]+)/', $path, $matches):
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleGetChat($user, $matches[1], $data, $language);
                        }
                        break;
                        
                    case $method === 'POST' && preg_match('/\/api\/chats\/([a-zA-Z0-9_]+)\/export/', $path, $matches):
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleExport($user, $matches[1], $data, $language);
                        }
                        break;
                        
                    case $method === 'POST' && preg_match('/\/api\/chats\/([a-zA-Z0-9_]+)\/rate/', $path, $matches):
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleRateModelPerformance($user, $matches[1], $data, $language);
                        }
                        break;
                        
                    case $method === 'GET' && $path === '/api/models':
                        $response = $this->handleGetModels($language);
                        break;
                        
                    case $method === 'GET' && $path === '/api/languages':
                        $response = $this->handleGetLanguages($language);
                        break;
                        
                    case $method === 'POST' && $path === '/api/translate':
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleTranslateText($user, $data, $language);
                        }
                        break;
                        
                    case $method === 'POST' && $path === '/api/detect-language':
                        $response = $this->handleDetectLanguage($data, $language);
                        break;
                        
                    case $method === 'GET' && $path === '/api/user/chats':
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleGetUserChats($user, $data, $language);
                        }
                        break;
                        
                    case $method === 'POST' && $path === '/api/user/language':
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleUpdateUserLanguage($user, $data, $language);
                        }
                        break;
                        
                    case $method === 'POST' && $path === '/api/user/encryption':
                        if (!$user) {
                            $response = createResponse('error', 'unauthorized', [], 401, $language);
                        } else {
                            $response = $this->handleEnableEncryption($user, $data, $language);
                        }
                        break;
                        
                    case $method === 'GET' && $path === '/api/stats/languages':
                        if (!$user || !$user['is_admin']) {
                            $response = createResponse('error', 'forbidden', [], 403, $language);
                        } else {
                            $response = $this->handleGetLanguageStats($data, $language);
                        }
                        break;
                        
                    case $method === 'GET' && preg_match('/\/api\/exports\/download\/(.+)/', $path, $matches):
                        $response = $this->handleDownloadExport($matches[1], $data, $language);
                        return; // Direct file download, no JSON response
                        
                    default:
                        $response = createResponse('error', 'endpoint_not_found', [], 404, $language);
                }
            } catch (Exception $e) {
                $response = createResponse('error', 'internal_server_error', [
                    'error' => $e->getMessage()
                ], 500, $language);
                
                if ($user) {
                    $this->engine->securityManager->logSecurityEvent($user['id'], 'api_error', $e->getMessage(), $language);
                }
            }
        }
        
        // Log the request
        $responseTime = microtime(true) - $startTime;
        $this->engine->logApiRequest(
            $path,
            $method,
            $user['id'] ?? null,
            $response['code'] ?? 200,
            $responseTime,
            $language
        );
        
        return $response;
    }
    
    private function handleRegister($data, $language) {
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $userLanguage = $data['language'] ?? $language;
        
        if (empty($username) || empty($email) || empty($password)) {
            return createResponse('error', 'required_fields_missing', [
                'required' => ['username', 'email', 'password']
            ], 400, $language);
        }
        
        // Validate language
        if (!isset(SUPPORTED_LANGUAGES[$userLanguage])) {
            $userLanguage = 'en';
        }
        
        // Basic validation
        if (strlen($password) < 8) {
            return createResponse('error', 'weak_password', [], 400, $language);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return createResponse('error', 'invalid_email', [], 400, $language);
        }
        
        // Check if username or email exists
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email');
        $stmt->bindValue(':username', sanitizeInput($username, $this->db), SQLITE3_TEXT);
        $stmt->bindValue(':email', sanitizeInput($email, $this->db), SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row['count'] > 0) {
            return createResponse('error', 'user_exists', [], 409, $language);
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
        $stmt->bindValue(':language', $userLanguage, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $userId = $this->db->lastInsertRowID();
            
            $this->engine->securityManager->logSecurityEvent($userId, 'user_registered', '', $userLanguage);
            
            return createResponse('success', 'registration_successful', [
                'api_key' => $apiKey,
                'user_id' => $userId,
                'language' => $userLanguage,
                'supported_languages' => array_keys(SUPPORTED_LANGUAGES)
            ], 201, $language);
        }
        
        return createResponse('error', 'registration_failed', [], 500, $language);
    }
    
    private function handleSecureLogin($data, $language) {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $enableEncryption = $data['enable_encryption'] ?? false;
        
        if (empty($username) || empty($password)) {
            return createResponse('error', 'required_fields_missing', [
                'required' => ['username', 'password']
            ], 400, $language);
        }
        
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', sanitizeInput($username, $this->db), SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update language manager to user's preferred language
            LanguageManager::setLanguage($user['language']);
            
            // Generate encryption keys if enabled
            if ($enableEncryption && !$user['encryption_salt']) {
                $keyData = EncryptionManager::generateUserKey($user['id'], $password);
                
                $stmt = $this->db->prepare('UPDATE users SET encryption_salt = :salt WHERE id = :user_id');
                $stmt->bindValue(':salt', $keyData['salt'], SQLITE3_TEXT);
                $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
                $stmt->execute();
                
                $user['encryption_salt'] = $keyData['salt'];
            }
            
            // Generate secure session token
            $secureToken = $this->engine->securityManager->generateSecureToken($user['id']);
            
            $this->engine->securityManager->logSecurityEvent($user['id'], 'successful_login', '', $user['language']);
            
            return createResponse('success', 'login_successful', [
                'api_key' => $user['api_key'],
                'secure_token' => $secureToken,
                'user_id' => $user['id'],
                'language' => $user['language'],
                'timezone' => $user['timezone'],
                'encryption_enabled' => !empty($user['encryption_salt']),
                'supported_languages' => array_keys(SUPPORTED_LANGUAGES),
                'preferred_models' => json_decode($user['preferred_models'], true) ?? []
            ], 200, $user['language']);
        }
        
        $this->engine->securityManager->logSecurityEvent($user['id'] ?? null, 'failed_login', $username, $language);
        
        return createResponse('error', 'invalid_credentials', [], 401, $language);
    }
    
    private function handleCreateSecureChat($user, $data, $language) {
        $required = ['title', 'topic', 'description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return createResponse('error', 'required_field', ['field' => $field], 400, $language);
            }
        }
        
        $userKey = null;
        
        // Check if user wants encryption
        if ($data['enable_encryption'] ?? false) {
            if (empty($user['encryption_salt'])) {
                return createResponse('error', 'encryption_not_enabled', [], 400, $language);
            }
            
            $password = $data['password'] ?? '';
            if (empty($password)) {
                return createResponse('error', 'password_required_for_encryption', [], 400, $language);
            }
            
            $userKey = EncryptionManager::deriveKey($password, $user['encryption_salt']);
        }
        
        // Auto-detect language if not provided
        $detectedLanguage = $data['language'] ?? LanguageManager::detectLanguage($data['topic'] . ' ' . $data['description']);
        
        return $this->engine->createChat(
            $user['id'],
            $data['title'],
            $data['topic'],
            $data['description'],
            $detectedLanguage,
            $data['target_language'] ?? null,
            $data['models'] ?? null,
            $data['depth'] ?? 3,
            $userKey
        );
    }
    
    private function handleGetChat($user, $chatId, $data, $language) {
        $userKey = null;
        
        // If encrypted chat, get user key
        if (!empty($data['password']) && !empty($user['encryption_salt'])) {
            $userKey = EncryptionManager::deriveKey($data['password'], $user['encryption_salt']);
        }
        
        $chat = $this->engine->getResearch($chatId, $userKey);
        
        if (!$chat) {
            return createResponse('error', 'chat_not_found', [], 404, $language);
        }
        
        if ($chat['user_id'] != $user['id'] && !$user['is_admin'] && !$chat['is_public']) {
            return createResponse('error', 'access_denied', [], 403, $language);
        }
        
        // Prepare response data
        $responseData = $chat;
        
        // Decrypt and parse data if available
        if ($chat['results'] && $chat['results'] !== '[ENCRYPTED]') {
            $responseData['results'] = json_decode($chat['results'], true) ?? [];
        }
        if ($chat['notes'] && $chat['notes'] !== '[ENCRYPTED]') {
            $responseData['notes'] = json_decode($chat['notes'], true) ?? [];
        }
        $responseData['models'] = json_decode($chat['models'], true);
        
        // Add language information
        $responseData['language_info'] = [
            'original' => SUPPORTED_LANGUAGES[$chat['original_language']] ?? null,
            'target' => $chat['target_language'] ? SUPPORTED_LANGUAGES[$chat['target_language']] : null,
            'detected' => $chat['detected_language'] ? SUPPORTED_LANGUAGES[$chat['detected_language']] : null
        ];
        
        // Add progress information
        $responseData['progress_info'] = [
            'status' => $chat['status'],
            'progress' => $chat['progress'] ?? 0,
            'quality_score' => $chat['quality_score'] ?? 0,
            'word_count' => $chat['word_count'] ?? 0,
            'reading_time' => $chat['reading_time'] ?? 0
        ];
        
        return createResponse('success', 'chat_retrieved', $responseData, 200, $language);
    }
    
    private function handleExport($user, $chatId, $data, $language) {
        return $this->engine->exportResearch(
            $chatId,
            $user['id'],
            $data['format'] ?? 'pdf',
            $data['type'] ?? 'article',
            $data['language'] ?? $language
        );
    }
    
    private function handleRateModelPerformance($user, $chatId, $data, $language) {
        $ratings = $data['model_ratings'] ?? [];
        $category = $data['category'] ?? 'general';
        $researchLanguage = $data['research_language'] ?? $language;
        
        if (empty($chatId) || empty($ratings)) {
            return createResponse('error', 'required_fields_missing', [
                'required' => ['chat_id', 'model_ratings']
            ], 400, $language);
        }
        
        $this->engine->updateModelWeights($user['id'], $researchLanguage, $category, $ratings);
        
        return createResponse('success', 'model_ratings_updated', [], 200, $language);
    }
    
    private function handleGetModels($language) {
        $models = [];
        foreach (AI_MODELS as $id => $config) {
            $models[] = [
                'id' => $id,
                'name' => $config['name'],
                'provider' => $config['provider'],
                'strengths' => $config['strengths'],
                'max_tokens' => $config['max_tokens'],
                'language_support' => $config['languages'],
                'recommended_for' => $this->getModelRecommendations($id, $language)
            ];
        }
        
        return createResponse('success', 'models_retrieved', [
            'models' => $models,
            'best_for_language' => LanguageManager::getBestModelsForLanguage($language)
        ], 200, $language);
    }
    
    private function handleGetLanguages($language) {
        $languages = [];
        foreach (SUPPORTED_LANGUAGES as $code => $info) {
            $languages[] = [
                'code' => $code,
                'name' => $info['name'],
                'native' => $info['native'],
                'rtl' => $info['rtl'],
                'family' => $info['family'],
                'best_models' => LanguageManager::getBestModelsForLanguage($code)
            ];
        }
        
        // Group by language families
        $families = [];
        foreach ($languages as $lang) {
            $families[$lang['family']][] = $lang;
        }
        
        return createResponse('success', 'languages_retrieved', [
            'languages' => $languages,
            'families' => $families,
            'total_supported' => count(SUPPORTED_LANGUAGES),
            'current_language' => $language
        ], 200, $language);
    }
    
    private function handleTranslateText($user, $data, $language) {
        $text = $data['text'] ?? '';
        $sourceLanguage = $data['source_language'] ?? null;
        $targetLanguage = $data['target_language'] ?? $language;
        
        if (empty($text)) {
            return createResponse('error', 'required_field', ['field' => 'text'], 400, $language);
        }
        
        // Auto-detect source language if not provided
        if (!$sourceLanguage) {
            $sourceLanguage = LanguageManager::detectLanguage($text);
        }
        
        // Validate languages
        if (!isset(SUPPORTED_LANGUAGES[$sourceLanguage]) || !isset(SUPPORTED_LANGUAGES[$targetLanguage])) {
            return createResponse('error', 'unsupported_language', [], 400, $language);
        }
        
        if ($sourceLanguage === $targetLanguage) {
            return createResponse('success', 'translation_completed', [
                'original_text' => $text,
                'translated_text' => $text,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'confidence' => 1.0
            ], 200, $language);
        }
        
        try {
            // Use consensus engine for translation
            $bestModels = LanguageManager::getBestModelsForLanguage($targetLanguage);
            $prompt = $this->getTranslationPrompt($text, $sourceLanguage, $targetLanguage);
            
            $result = $this->engine->consensusEngine->queryMultipleModels(
                $prompt,
                array_slice($bestModels, 0, 2), // Use top 2 models
                $targetLanguage,
                $user['id']
            );
            
            return createResponse('success', 'translation_completed', [
                'original_text' => $text,
                'translated_text' => $result['content'],
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'confidence' => $result['consensus_score'] ?? 0.8,
                'models_used' => $result['all_scores'] ?? []
            ], 200, $language);
            
        } catch (Exception $e) {
            return createResponse('error', 'translation_failed', [
                'error' => $e->getMessage()
            ], 500, $language);
        }
    }
    
    private function handleDetectLanguage($data, $language) {
        $text = $data['text'] ?? '';
        
        if (empty($text)) {
            return createResponse('error', 'required_field', ['field' => 'text'], 400, $language);
        }
        
        $detectedLanguage = LanguageManager::detectLanguage($text);
        $confidence = $this->calculateDetectionConfidence($text, $detectedLanguage);
        
        return createResponse('success', 'language_detected', [
            'detected_language' => $detectedLanguage,
            'language_info' => SUPPORTED_LANGUAGES[$detectedLanguage] ?? null,
            'confidence' => $confidence,
            'alternatives' => $this->getAlternativeLanguages($text),
            'text_length' => mb_strlen($text, 'UTF-8')
        ], 200, $language);
    }
    
    private function handleGetUserChats($user, $data, $language) {
        $limit = min(100, max(1, $data['limit'] ?? 20));
        $offset = max(0, $data['offset'] ?? 0);
        $languageFilter = $data['language_filter'] ?? null;
        $statusFilter = $data['status_filter'] ?? null;
        
        $whereConditions = ['user_id = :user_id'];
        $params = [':user_id' => $user['id']];
        
        if ($languageFilter && isset(SUPPORTED_LANGUAGES[$languageFilter])) {
            $whereConditions[] = 'original_language = :language';
            $params[':language'] = $languageFilter;
        }
        
        if ($statusFilter) {
            $whereConditions[] = 'status = :status';
            $params[':status'] = $statusFilter;
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        $stmt = $this->db->prepare("SELECT chat_id, title, topic, description, original_language, 
                                   target_language, status, progress, quality_score, word_count, 
                                   reading_time, is_encrypted, is_public, category, created_at, updated_at 
                                   FROM researches WHERE $whereClause 
                                   ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $chats = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['language_info'] = [
                'original' => SUPPORTED_LANGUAGES[$row['original_language']] ?? null,
                'target' => $row['target_language'] ? SUPPORTED_LANGUAGES[$row['target_language']] : null
            ];
            $chats[] = $row;
        }
        
        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM researches WHERE $whereClause");
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
        }
        $countResult = $countStmt->execute();
        $totalCount = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
        
        return createResponse('success', 'chats_retrieved', [
            'chats' => $chats,
            'pagination' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'has_next' => ($offset + $limit) < $totalCount
            ],
            'filters' => [
                'language' => $languageFilter,
                'status' => $statusFilter
            ]
        ], 200, $language);
    }
    
    private function handleUpdateUserLanguage($user, $data, $language) {
        $newLanguage = $data['language'] ?? '';
        $timezone = $data['timezone'] ?? null;
        
        if (empty($newLanguage) || !isset(SUPPORTED_LANGUAGES[$newLanguage])) {
            return createResponse('error', 'unsupported_language', [], 400, $language);
        }
        
        $updates = ['language = :language'];
        $params = [':language' => $newLanguage, ':user_id' => $user['id']];
        
        if ($timezone) {
            $updates[] = 'timezone = :timezone';
            $params[':timezone'] = $timezone;
        }
        
        $updates[] = 'updated_at = datetime("now")';
        
        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :user_id');
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, SQLITE3_TEXT);
        }
        
        if ($stmt->execute()) {
            // Update language manager
            LanguageManager::setLanguage($newLanguage);
            
            $this->engine->securityManager->logSecurityEvent($user['id'], 'language_changed', 
                "from {$user['language']} to $newLanguage", $newLanguage);
            
            return createResponse('success', 'language_changed', [
                'new_language' => $newLanguage,
                'language_info' => SUPPORTED_LANGUAGES[$newLanguage],
                'timezone' => $timezone
            ], 200, $newLanguage);
        }
        
        return createResponse('error', 'update_failed', [], 500, $language);
    }
    
    private function handleEnableEncryption($user, $data, $language) {
        $password = $data['password'] ?? '';
        
        if (empty($password)) {
            return createResponse('error', 'password_required_for_encryption', [], 400, $language);
        }
        
        if (!empty($user['encryption_salt'])) {
            return createResponse('error', 'encryption_already_enabled', [], 400, $language);
        }
        
        // Generate encryption key
        $keyData = EncryptionManager::generateUserKey($user['id'], $password);
        
        $stmt = $this->db->prepare('UPDATE users SET encryption_salt = :salt, 
                                  last_key_rotation = datetime("now") WHERE id = :user_id');
        $stmt->bindValue(':salt', $keyData['salt'], SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $this->engine->securityManager->logSecurityEvent($user['id'], 'encryption_enabled', '', $language);
            
            return createResponse('success', 'encryption_enabled', [], 200, $language);
        }
        
        return createResponse('error', 'encryption_failed', [], 500, $language);
    }
    
    private function handleGetLanguageStats($data, $language) {
        $startDate = $data['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $data['end_date'] ?? date('Y-m-d');
        
        $stmt = $this->db->prepare('SELECT language, 
                                   SUM(total_requests) as total_requests,
                                   SUM(total_words) as total_words,
                                   SUM(total_characters) as total_characters,
                                   AVG(avg_response_time) as avg_response_time,
                                   AVG(success_rate) as avg_success_rate
                                   FROM language_stats 
                                   WHERE date BETWEEN :start_date AND :end_date
                                   GROUP BY language
                                   ORDER BY total_requests DESC');
        
        $stmt->bindValue(':start_date', $startDate, SQLITE3_TEXT);
        $stmt->bindValue(':end_date', $endDate, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        $stats = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $row['language_info'] = SUPPORTED_LANGUAGES[$row['language']] ?? null;
            $stats[] = $row;
        }
        
        return createResponse('success', 'language_stats_retrieved', [
            'stats' => $stats,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'total_languages' => count($stats)
        ], 200, $language);
    }
    
    private function handleDownloadExport($filename, $data, $language) {
        $filepath = EXPORTS_DIR . '/' . basename($filename);
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(createResponse('error', 'file_not_found', [], 404, $language), JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Verify user has access to this file
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $data['api_key'] ?? null;
        $user = $this->engine->authenticateUser($apiKey);
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(createResponse('error', 'unauthorized', [], 401, $language), JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $stmt = $this->db->prepare('SELECT * FROM exports WHERE original_filename = :filename AND (user_id = :user_id OR is_public = 1)');
        $stmt->bindValue(':filename', $filename, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $export = $result->fetchArray(SQLITE3_ASSOC);
        
        if (!$export) {
            http_response_code(403);
            echo json_encode(createResponse('error', 'access_denied', [], 403, $language), JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Update download count
        $stmt = $this->db->prepare('UPDATE exports SET download_count = download_count + 1 WHERE id = :id');
        $stmt->bindValue(':id', $export['id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        // Set appropriate headers
        header('Content-Type: ' . ($export['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($filepath);
    }
    
    // Helper methods
    
    private function getModelRecommendations($modelId, $language) {
        $model = AI_MODELS[$modelId];
        $langFamily = LanguageManager::getLanguageFamily($language);
        
        $recommendations = [];
        
        if ($model['languages'] === 'all') {
            $recommendations[] = 'universal';
        }
        
        if (in_array('multilingual', $model['strengths'])) {
            $recommendations[] = 'multilingual_tasks';
        }
        
        if ($langFamily === 'Romance' && $modelId === 'mistral') {
            $recommendations[] = 'european_languages';
        }
        
        if (in_array($language, ['zh', 'ja']) && in_array($modelId, ['gpt4', 'gemini'])) {
            $recommendations[] = 'asian_languages';
        }
        
        return $recommendations;
    }
    
    private function getTranslationPrompt($text, $sourceLanguage, $targetLanguage) {
        $sourceLang = SUPPORTED_LANGUAGES[$sourceLanguage]['native'] ?? $sourceLanguage;
        $targetLang = SUPPORTED_LANGUAGES[$targetLanguage]['native'] ?? $targetLanguage;
        
        return "Translate the following text from {$sourceLang} to {$targetLang}. Maintain the original meaning, tone, and formatting. Provide only the translation without any explanations:\n\n{$text}";
    }
    
    private function calculateDetectionConfidence($text, $detectedLanguage) {
        // Simple confidence calculation based on text characteristics
        $length = mb_strlen($text, 'UTF-8');
        $langInfo = SUPPORTED_LANGUAGES[$detectedLanguage] ?? [];
        
        $confidence = 0.5; // Base confidence
        
        // Increase confidence based on text length
        if ($length > 100) {
            $confidence += 0.3;
        } elseif ($length > 50) {
            $confidence += 0.2;
        } elseif ($length > 20) {
            $confidence += 0.1;
        }
        
        // Increase confidence based on script matching
        if (isset($langInfo['family'])) {
            $expectedScript = $this->getExpectedScriptForFamily($langInfo['family']);
            if ($this->textMatchesScript($text, $expectedScript)) {
                $confidence += 0.2;
            }
        }
        
        return min(1.0, $confidence);
    }
    
    private function getAlternativeLanguages($text) {
        // Return possible alternative languages based on script analysis
        $alternatives = [];
        
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text)) {
            $alternatives = ['zh', 'zh-cn', 'zh-tw', 'ja'];
        } elseif (preg_match('/[\x{0600}-\x{06ff}]/u', $text)) {
            $alternatives = ['ar', 'fa', 'ur'];
        } elseif (preg_match('/[\x{0400}-\x{04ff}]/u', $text)) {
            $alternatives = ['ru', 'bg', 'mk', 'sr'];
        }
        
        return array_slice($alternatives, 0, 3);
    }
    
    private function getExpectedScriptForFamily($family) {
        $scriptMap = [
            'Sino-Tibetan' => 'han',
            'Semitic' => 'arabic',
            'Slavic' => 'cyrillic',
            'Germanic' => 'latin',
            'Romance' => 'latin',
            'Celtic' => 'latin'
        ];
        
        return $scriptMap[$family] ?? 'latin';
    }
    
    private function textMatchesScript($text, $script) {
        $patterns = [
            'latin' => '/[a-zA-Z]/',
            'arabic' => '/[\x{0600}-\x{06ff}]/u',
            'cyrillic' => '/[\x{0400}-\x{04ff}]/u',
            'han' => '/[\x{4e00}-\x{9fff}]/u'
        ];
        
        if (!isset($patterns[$script])) {
            return false;
        }
        
        return preg_match($patterns[$script], $text);
    }
}

// ==============================================
// CLI Handler with Multi-Language Support
// ==============================================

if ($isCli) {
    $engine = new OunachuEngine($db);
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case '--install':
                echo "Ounachu Search Engine Installation\n";
                echo "==================================\n";
                echo "[OK] Database initialized\n";
                echo "[OK] Directories created\n";
                
                // Generate language files
                echo "[INFO] Generating language files...\n";
                LanguageManager::generateAllLanguageFiles();
                echo "[OK] Language files generated for " . count(SUPPORTED_LANGUAGES) . " languages\n";
                
                // Show admin credentials
                $result = $db->querySingle("SELECT api_key FROM users WHERE is_admin = 1 LIMIT 1", true);
                if ($result) {
                    echo "[OK] Admin user exists\n";
                    echo "Admin API Key: " . $result['api_key'] . "\n";
                }
                
                // Check environment variables
                echo "\n[INFO] Checking environment variables...\n";
                $envVars = ['OPENAI_API_KEY', 'GOOGLE_API_KEY', 'ANTHROPIC_API_KEY', 'MISTRAL_API_KEY', 'META_API_KEY'];
                foreach ($envVars as $var) {
                    $value = getenv($var);
                    echo "[$var] " . ($value ? "✓ Set" : "✗ Not set") . "\n";
                }
                
                echo "\n[INFO] Supported languages: " . count(SUPPORTED_LANGUAGES) . "\n";
                echo "[INFO] Language families: " . count(LANGUAGE_FAMILIES) . "\n";
                
                echo "\nInstallation completed!\n";
                echo "Start web server: php -S localhost:8000\n";
                echo "API documentation: php ounachu.php --help\n";
                break;
                
            case '--test':
                echo "Ounachu Test Suite\n";
                echo "==================\n";
                
                // Test database
                echo "[TEST] Database connection... ";
                try {
                    $testQuery = $db->querySingle("SELECT COUNT(*) FROM users");
                    echo "✓ SUCCESS\n";
                } catch (Exception $e) {
                    echo "✗ FAILED: " . $e->getMessage() . "\n";
                }
                
                // Test encryption
                echo "[TEST] Encryption system... ";
                try {
                    $testData = "Multi-language test: Hello, 你好, مرحبا, Привет";
                    $testKey = EncryptionManager::generateUserKey(1, "test123");
                    $encrypted = EncryptionManager::encryptUserData($testData, $testKey['key']);
                    $decrypted = EncryptionManager::decryptUserData($encrypted, $testKey['key']);
                    
                    if ($decrypted === $testData) {
                        echo "✓ SUCCESS\n";
                    } else {
                        echo "✗ FAILED: Encryption/decryption mismatch\n";
                    }
                } catch (Exception $e) {
                    echo "✗ FAILED: " . $e->getMessage() . "\n";
                }
                
                // Test language manager
                echo "[TEST] Language manager... ";
                try {
                    LanguageManager::init('tr');
                    $message = LanguageManager::get('welcome');
                    if (!empty($message)) {
                        echo "✓ SUCCESS\n";
                    } else {
                        echo "✗ FAILED: No translation found\n";
                    }
                } catch (Exception $e) {
                    echo "✗ FAILED: " . $e->getMessage() . "\n";
                }
                
                // Test language detection
                echo "[TEST] Language detection... ";
                try {
                    $detectedEn = LanguageManager::detectLanguage("Hello world");
                    $detectedTr = LanguageManager::detectLanguage("Merhaba dünya");
                    $detectedZh = LanguageManager::detectLanguage("你好世界");
                    
                    if ($detectedEn === 'en' && $detectedTr === 'tr' && $detectedZh === 'zh') {
                        echo "✓ SUCCESS\n";
                    } else {
                        echo "✗ PARTIAL: Some languages not detected correctly\n";
                    }
                } catch (Exception $e) {
                    echo "✗ FAILED: " . $e->getMessage() . "\n";
                }
                
                // Test consensus engine
                echo "[TEST] Consensus engine... ";
                try {
                    $consensusEngine = new ConsensusEngine($db);
                    echo "✓ SUCCESS\n";
                } catch (Exception $e) {
                    echo "✗ FAILED: " . $e->getMessage() . "\n";
                }
                
                echo "\nAll tests completed!\n";
                break;
                
            case '--generate-translations':
                if (isset($argv[2])) {
                    $targetLang = $argv[2];
                    if (!isset(SUPPORTED_LANGUAGES[$targetLang])) {
                        echo "Error: Unsupported language '$targetLang'\n";
                        echo "Supported languages: " . implode(', ', array_keys(SUPPORTED_LANGUAGES)) . "\n";
                        break;
                    }
                    
                    echo "Generating translations for language: $targetLang\n";
                    LanguageManager::generateAllLanguageFiles();
                    echo "Translations generated successfully!\n";
                } else {
                    echo "Usage: php ounachu.php --generate-translations <language_code>\n";
                    echo "Example: php ounachu.php --generate-translations zh\n";
                    echo "Supported languages: " . implode(', ', array_keys(SUPPORTED_LANGUAGES)) . "\n";
                }
                break;
                
            case '--list-languages':
                echo "Supported Languages (" . count(SUPPORTED_LANGUAGES) . " total)\n";
                echo str_repeat("=", 50) . "\n";
                
                foreach (LANGUAGE_FAMILIES as $family => $languages) {
                    echo "\n$family Family:\n";
                    echo str_repeat("-", strlen($family) + 8) . "\n";
                    
                    foreach ($languages as $langCode) {
                        if (isset(SUPPORTED_LANGUAGES[$langCode])) {
                            $info = SUPPORTED_LANGUAGES[$langCode];
                            $rtl = $info['rtl'] ? ' (RTL)' : '';
                            echo sprintf("  %-6s - %-20s (%s)%s\n", 
                                $langCode, 
                                $info['name'], 
                                $info['native'],
                                $rtl
                            );
                        }
                    }
                }
                break;
                
            case '--language-stats':
                echo "Language Usage Statistics\n";
                echo "========================\n";
                
                $stmt = $db->prepare('SELECT language, COUNT(*) as usage_count 
                                    FROM researches 
                                    GROUP BY language 
                                    ORDER BY usage_count DESC');
                $result = $stmt->execute();
                
                echo sprintf("%-8s %-20s %-15s %s\n", "Code", "Language", "Native", "Usage");
                echo str_repeat("-", 60) . "\n";
                
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $langCode = $row['language'];
                    $count = $row['usage_count'];
                    $info = SUPPORTED_LANGUAGES[$langCode] ?? ['name' => 'Unknown', 'native' => 'Unknown'];
                    
                    echo sprintf("%-8s %-20s %-15s %d\n", 
                        $langCode, 
                        $info['name'], 
                        $info['native'], 
                        $count
                    );
                }
                break;
                
            case '--help':
            default:
                echo "Ounachu Search Engine - Multi-Language AI Research Tool\n";
                echo "=======================================================\n\n";
                echo "Created by: Crimson Glitch\n";
                echo "License: Apache License 2.0\n";
                echo "Languages Supported: " . count(SUPPORTED_LANGUAGES) . "\n";
                echo "AI Models: " . count(AI_MODELS) . "\n\n";
                
                echo "Available commands:\n\n";
                echo "  --install                    Initialize and install the application\n";
                echo "  --test                       Run comprehensive test suite\n";
                echo "  --generate-translations <lang>  Generate translation files\n";
                echo "  --list-languages             List all supported languages\n";
                echo "  --language-stats             Show language usage statistics\n";
                echo "  --create-user <user> <email> <pass> [lang]  Create new user\n";
                echo "  --stats                      Show system statistics\n";
                echo "  --cleanup                    Clean old files and logs\n";
                echo "  --backup                     Create database backup\n";
                echo "  --restore <file>             Restore from backup\n";
                echo "  --config                     Show current configuration\n";
                echo "  --help                       Show this help message\n\n";
                
                echo "Multi-Language Features:\n";
                echo "  • " . count(SUPPORTED_LANGUAGES) . " supported languages\n";
                echo "  • " . count(LANGUAGE_FAMILIES) . " language families\n";
                echo "  • Automatic language detection\n";
                echo "  • AI model optimization per language\n";
                echo "  • Real-time translation between languages\n";
                echo "  • RTL (Right-to-Left) script support\n";
                echo "  • Unicode and UTF-8 fully supported\n\n";
                
                echo "API Endpoints:\n";
                echo "  POST /api/register           - User registration\n";
                echo "  POST /api/login              - User authentication\n";
                echo "  POST /api/chats              - Create new research chat\n";
                echo "  GET  /api/chats/<id>         - Get chat information\n";
                echo "  POST /api/chats/<id>/export  - Export research\n";
                echo "  POST /api/chats/<id>/rate    - Rate model performance\n";
                echo "  GET  /api/models             - List AI models\n";
                echo "  GET  /api/languages          - List supported languages\n";
                echo "  POST /api/translate          - Translate text\n";
                echo "  POST /api/detect-language    - Detect text language\n";
                echo "  GET  /api/user/chats         - List user's chats\n";
                echo "  POST /api/user/language      - Update user language\n";
                echo "  POST /api/user/encryption    - Enable encryption\n\n";
                
                echo "Examples:\n";
                echo "  php ounachu.php --install\n";
                echo "  php ounachu.php --generate-translations zh\n";
                echo "  php ounachu.php --create-user admin admin@example.com password123 en\n";
                echo "  php -S localhost:8000  # Start web server\n\n";
                
                echo "Environment Variables:\n";
                echo "  OPENAI_API_KEY      - OpenAI API key (for GPT-4)\n";
                echo "  GOOGLE_API_KEY      - Google API key (for Gemini)\n";
                echo "  ANTHROPIC_API_KEY   - Anthropic API key (for Claude)\n";
                echo "  MISTRAL_API_KEY     - Mistral API key\n";
                echo "  META_API_KEY        - Meta API key (for Llama)\n\n";
                
                echo "Repository: https://github.com/crimsonglitch/ounachu\n";
                echo "Documentation: https://ounachu.com/docs\n";
        }
    }
    exit;
}

// ==============================================
// Web Request Handler
// ==============================================

$engine = new OunachuEngine($db);
$router = new ApiRouter($engine, $db);

// Set headers for JSON API with proper encoding
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, Accept-Language');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $response = $router->handleRequest();
    http_response_code($response['code'] ?? 200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    $language = LanguageManager::getCurrentLanguage();
    $response = createResponse('error', 'internal_server_error', [
        'error' => $e->getMessage()
    ], 500, $language);
    
    http_response_code(500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ==============================================
// Error Handling & Logging with Multi-Language Support
// ==============================================

/**
 * Global error handler with language support
 */
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $language = LanguageManager::getCurrentLanguage();
    
    $errorLog = [
        'timestamp' => date('c'),
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'language' => $language,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ];
    
    error_log(json_encode($errorLog, JSON_UNESCAPED_UNICODE), 3, ROOT_DIR . '/error.log');
    
    if ($severity === E_ERROR || $severity === E_CORE_ERROR || $severity === E_USER_ERROR) {
        http_response_code(500);
        echo json_encode(createResponse('error', 'internal_server_error', [], 500, $language), JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    return true;
});

/**
 * Global exception handler with language support
 */
set_exception_handler(function($exception) {
    $language = LanguageManager::getCurrentLanguage();
    
    $errorLog = [
        'timestamp' => date('c'),
        'type' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'language' => $language,
        'trace' => $exception->getTraceAsString()
    ];
    
    error_log(json_encode($errorLog, JSON_UNESCAPED_UNICODE), 3, ROOT_DIR . '/error.log');
    
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(createResponse('error', 'internal_server_error', [], 500, $language), JSON_UNESCAPED_UNICODE);
    }
});

/**
 * Shutdown function for fatal errors with language support
 */
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $language = LanguageManager::getCurrentLanguage();
        
        $errorLog = [
            'timestamp' => date('c'),
            'type' => 'FATAL',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
            'language' => $language
        ];
        
        error_log(json_encode($errorLog, JSON_UNESCAPED_UNICODE), 3, ROOT_DIR . '/error.log');
        
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(createResponse('error', 'internal_server_error', [], 500, $language), JSON_UNESCAPED_UNICODE);
        }
    }
});

/*
 * ==============================================
 * END OF OUNACHU SEARCH ENGINE
 * ==============================================
 * 
 * Created by: Crimson Glitch
 * Version: 3.0 - Multi-Language Edition
 * License: Apache License 2.0
 * 
 * Features implemented:
 * ✓ Multi-language support (130+ languages)
 * ✓ Language families and script support
 * ✓ Automatic language detection
 * ✓ Real-time translation between languages
 * ✓ Language-optimized AI model selection
 * ✓ RTL (Right-to-Left) script support
 * ✓ Unicode and UTF-8 full support
 * ✓ End-to-end encryption for user data
 * ✓ Multi-AI consensus engine with intelligent scoring
 * ✓ Advanced security monitoring and logging
 * ✓ Rate limiting and abuse prevention
 * ✓ Professional export capabilities (PDF, DOCX, HTML, MD, TXT)
 * ✓ Comprehensive CLI management tools
 * ✓ Error handling and logging system
 * ✓ User preference learning system
 * ✓ Secure token-based authentication
 * ✓ Academic article generation
 * ✓ Research notes compilation
 * ✓ File download management
 * ✓ Database backup and restore
 * ✓ Statistics and monitoring
 * ✓ Cleanup and maintenance tools
 * ✓ Language-specific prompts and responses
 * ✓ Multi-language API documentation
 * ✓ Cross-language research capabilities
 * ✓ Language performance analytics
 * 
 * Supported Languages: 130+
 * Language Families: 25+
 * AI Models: 5 (GPT-4, Gemini, Claude, Mistral, Llama3)
 * Export Formats: 5 (PDF, DOCX, HTML, Markdown, TXT)
 * 
 * Usage:
 * - Installation: php ounachu.php --install
 * - Web server: php -S localhost:8000
 * - CLI help: php ounachu.php --help
 * - Language list: php ounachu.php --list-languages
 * 
 * API Endpoints:
 * - POST /api/register - User registration
 * - POST /api/login - User authentication  
 * - POST /api/chats - Create research chat
 * - GET /api/chats/<id> - Get chat data
 * - POST /api/chats/<id>/export - Export research
 * - GET /api/languages - List supported languages
 * - POST /api/translate - Translate text
 * - POST /api/detect-language - Detect language
 * 
 * Multi-Language Capabilities:
 * - Automatic language detection from text input
 * - AI model optimization based on target language
 * - Cross-language research and translation
 * - Language-specific academic formatting
 * - RTL script support for Arabic, Hebrew, Persian, Urdu
 * - Unicode normalization and encoding
 * - Language family-based model recommendations
 * - Multilingual export with proper fonts and formatting
 * 
 * Requirements:
 * - PHP 8.0+ with SQLite3, cURL, OpenSSL, mbstring extensions
 * - AI API keys set as environment variables
 * - Write permissions for data directories
 * - UTF-8 locale support
 * 
 * License: Apache License 2.0
 * Repository: https://github.com/crimsonglitch/ounachu
 * Documentation: https://ounachu.com/docs
 * 
 * Copyright 2024 Crimson Glitch
 * Licensed under the Apache License, Version 2.0
 * 
 * ==============================================
 */
?>