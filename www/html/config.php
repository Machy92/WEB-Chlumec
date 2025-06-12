<?php



ini_set('display_errors', 1);
error_reporting(E_ALL);


$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co'; 
$supabaseKey_anon = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE'; 
$supabaseKey_service_role = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0NzY0MDIxMywiZXhwIjoyMDYzMjE2MjEzfQ.j5P0CgFejLb99zkwP-4SdUZ6IC-z8HvCY9D0JL0ovWQ'; 


function fetchSupabaseData($table, $queryParams = 'select=*') {
    global $supabaseUrl, $supabaseKey_anon; 

    $url = "{$supabaseUrl}/rest/v1/{$table}?{$queryParams}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabaseKey_anon, 
        'Authorization: Bearer ' . $supabaseKey_anon 
    ]);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);

    if ($httpcode >= 400) { 
        error_log("Supabase fetch error for $table: HTTP $httpcode - $response");
        return null; 
    }
    return json_decode($response, true);
}



function executeSupabaseWrite($method, $table, $payload = null, $queryParams = '') {
    global $supabaseUrl, $supabaseKey_service_role; 

    $url = "{$supabaseUrl}/rest/v1/{$table}{$queryParams}";
    $ch = curl_init($url);

    $headers = [
        'apikey: ' . $supabaseKey_service_role, 
        'Authorization: Bearer ' . $supabaseKey_service_role, 
        'Prefer: return=representation' 
    ];

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $headers[] = 'Content-Type: application/json';
            }
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $headers[] = 'Content-Type: application/json';
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            return null;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode >= 300 && !($method === 'DELETE' && $httpcode === 204) ) { 
        error_log("Supabase write error for $table ($method): HTTP $httpcode - $response");
        
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        $_SESSION['admin_message'] = "Chyba při operaci s databází: HTTP $httpcode.";
        $_SESSION['admin_message_type'] = "danger";
        return null;
    }
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    $_SESSION['admin_message'] = "Operace byla úspěšně provedena.";
    $_SESSION['admin_message_type'] = "success";
    return json_decode($response, true);
}

?>