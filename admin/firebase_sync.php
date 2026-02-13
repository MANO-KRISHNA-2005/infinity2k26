<?php
/**
 * Firebase Firestore REST API Helper
 */
class FirebaseSync {
    private $projectId;
    private $apiKey;

    public function __construct($projectId, $apiKey = "") {
        $this->projectId = $projectId;
        $this->apiKey = $apiKey;
    }

    public function updateDocument($collection, $docId, $data) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collection}/{$docId}";
        
        // Convert data to Firestore REST format
        // This is a simplified version. Firestore REST API requires specific field typing.
        // For simplicity in this event site context, we recommend manual sync if errors occur,
        // or using this basic structure for simple string fields.
        
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Handle basic array/map if needed
                continue; 
            }
            $fields[$key] = ["stringValue" => (string)$value];
        }

        $payload = ["fields" => (object)$fields];
        
        $ch = curl_init($url . ($this->apiKey ? "?key=" . $this->apiKey : ""));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    public function deleteDocument($collection, $docId) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collection}/{$docId}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
?>
