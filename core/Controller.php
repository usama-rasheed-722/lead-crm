<?php
// core/Controller.php
abstract class Controller {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    protected function view($view, $data = []) {
        extract($data);
        include __DIR__ . "/../app/views/{$view}.php";
    }
    
    protected function redirect($url, $addParams = [], $removeParams = []) {
        // Parse current URL query string
        $currentQuery = [];
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $currentQuery);
        }
        
        // Parse target URL to separate base URL and query string
        $queryPos = strpos($url, '?');
        $baseUrl = $queryPos !== false ? substr($url, 0, $queryPos) : $url;
        $targetQuery = [];
        
        if ($queryPos !== false) {
            $queryString = substr($url, $queryPos + 1);
            parse_str($queryString, $targetQuery);
        }
        
        // Merge query strings: current -> target -> addParams (in order of precedence)
        $mergedQuery = array_merge($currentQuery, $targetQuery, $addParams);
        
        // Remove parameters specified in $removeParams
        foreach ($removeParams as $key) {
            unset($mergedQuery[$key]);
        }
        
        // Remove parameters that are explicitly set to null in $addParams
        foreach ($addParams as $key => $value) {
            if ($value === null) {
                unset($mergedQuery[$key]);
            }
        }
        
        // Build final URL with query string
        if (!empty($mergedQuery)) {
            $queryString = http_build_query($mergedQuery);
            $url = $baseUrl . '?' . $queryString;
        } else {
            $url = $baseUrl;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get filters from session or merge with GET parameters
     * @param string $sessionKey The session key to store/retrieve filters
     * @param array $defaultFilters Default filter structure
     * @return array Merged filters
     */
    protected function getFilters($sessionKey, $defaultFilters = []) {
        // Initialize session filters if not exists
        if (!isset($_SESSION['filters'])) {
            $_SESSION['filters'] = [];
        }
        
        // Start with default filters
        $filters = $defaultFilters;
        
        // Merge with session filters if they exist
        if (isset($_SESSION['filters'][$sessionKey])) {
            $filters = array_merge($defaultFilters, $_SESSION['filters'][$sessionKey]);
        }
        
        // Get filters from GET parameters (if provided) - these override session
        $getFilters = [];
        $hasGetParams = false;
        foreach ($defaultFilters as $key => $defaultValue) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $getFilters[$key] = $_GET[$key];
                $hasGetParams = true;
            } elseif (isset($_GET[$key]) && $_GET[$key] === '') {
                // Explicitly empty value means clear this filter
                $getFilters[$key] = '';
                $hasGetParams = true;
            }
        }
        
        // If GET parameters are provided, merge them and update session
        if ($hasGetParams) {
            // Merge GET params with existing session filters
            $filters = array_merge($filters, $getFilters);
            // Remove empty filters before saving to session (but keep explicitly set empty values)
            $filtersToSave = [];
            foreach ($filters as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $filtersToSave[$key] = $value;
                } elseif (isset($getFilters[$key]) && $getFilters[$key] === '') {
                    // Explicitly cleared filter - don't save it
                    unset($filtersToSave[$key]);
                }
            }
            $_SESSION['filters'][$sessionKey] = $filtersToSave;
        }
        
        return $filters;
    }
    
    /**
     * Reset filters for a specific session key
     * @param string $sessionKey The session key to reset
     */
    protected function resetFilters($sessionKey) {
        if (isset($_SESSION['filters'][$sessionKey])) {
            unset($_SESSION['filters'][$sessionKey]);
        }
    }
    
    /**
     * Reset all filters
     */
    protected function resetAllFilters() {
        $_SESSION['filters'] = [];
    }
}
?>
